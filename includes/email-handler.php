<?php
/**
 * Email Handler for Le Bon Resto Plugin
 * 
 * @package LeBonResto
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LeBonResto_Email_Handler {
    
    /**
     * Initialize email handler
     */
    public function __construct() {
        add_action('wp_ajax_lebonresto_send_contact_email', array($this, 'handle_contact_form_ajax'));
        add_action('wp_ajax_nopriv_lebonresto_send_contact_email', array($this, 'handle_contact_form_ajax'));
        add_action('init', array($this, 'init_smtp'));
        add_action('init', array($this, 'create_messages_table'));
        
        // Add admin menu for messages
        add_action('admin_menu', array($this, 'add_messages_admin_menu'));
        
        // Handle Excel export
        add_action('admin_init', array($this, 'handle_excel_export'));
        
        // Add SEO meta descriptions
        add_action('wp_head', array($this, 'add_seo_meta_descriptions'));
    }
    
    /**
     * Initialize SMTP configuration
     */
    public function init_smtp() {
        $options = get_option('lebonresto_options', array());
        
        if (isset($options['smtp_enabled']) && $options['smtp_enabled'] === '1') {
            // Force WordPress to use SMTP
            add_action('phpmailer_init', array($this, 'configure_smtp'));
            
            // Override wp_mail to use SMTP
            add_filter('wp_mail', array($this, 'override_wp_mail'), 1);
        }
    }
    
    /**
     * Configure SMTP settings
     */
    public function configure_smtp($phpmailer) {
        $options = get_option('lebonresto_options', array());
        
        if (!isset($options['smtp_enabled']) || $options['smtp_enabled'] !== '1') {
            return;
        }
        
        $phpmailer->isSMTP();
        $phpmailer->Host = isset($options['smtp_host']) ? $options['smtp_host'] : 'smtp.gmail.com';
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = isset($options['smtp_port']) ? intval($options['smtp_port']) : 587;
        $phpmailer->Username = isset($options['smtp_username']) ? $options['smtp_username'] : '';
        $phpmailer->Password = isset($options['smtp_password']) ? $options['smtp_password'] : '';
        
        // Set encryption
        $encryption = isset($options['smtp_encryption']) ? $options['smtp_encryption'] : 'tls';
        if ($encryption === 'ssl') {
            $phpmailer->SMTPSecure = 'ssl';
        } elseif ($encryption === 'tls') {
            $phpmailer->SMTPSecure = 'tls';
        }
        
        // Set from email and name
        $from_email = isset($options['smtp_from_email']) ? $options['smtp_from_email'] : get_option('admin_email');
        $from_name = isset($options['smtp_from_name']) ? $options['smtp_from_name'] : get_bloginfo('name');
        
        $phpmailer->setFrom($from_email, $from_name);
    }
    
    /**
     * Override wp_mail to ensure SMTP is used
     */
    public function override_wp_mail($args) {
        // Force WordPress to use PHPMailer with SMTP
        add_action('phpmailer_init', array($this, 'configure_smtp'), 999);
        return $args;
    }
    
    /**
     * Handle contact form AJAX submission
     */
    public function handle_contact_form_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'lebonresto_contact_form')) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => __('Security check failed. Please try again.', 'le-bon-resto')
            )));
        }
        
        // Sanitize and validate input
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['mobile']);
        $message = sanitize_textarea_field($_POST['message']);
        $restaurant_id = intval($_POST['restaurant_id']);
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($message)) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => __('Please fill in all required fields.', 'le-bon-resto')
            )));
        }
        
        if (!is_email($email)) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => __('Please enter a valid email address.', 'le-bon-resto')
            )));
        }
        
        // Get restaurant information
        $restaurant_title = get_the_title($restaurant_id);
        $restaurant_url = get_permalink($restaurant_id);
        
        // Get contact email from options
        $options = get_option('lebonresto_options', array());
        $contact_email = isset($options['contact_email']) ? $options['contact_email'] : get_option('admin_email');
        
        // Prepare email content
        $subject = sprintf(__('Nouveau message de contact - %s', 'le-bon-resto'), $restaurant_title);
        
        $email_content = $this->get_email_template($name, $email, $phone, $message, $restaurant_title, $restaurant_url);
        
        // Send email
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $name . ' <' . $email . '>',
            'Reply-To: ' . $email
        );
        
        // Force SMTP configuration before sending
        $this->init_smtp();
        
        $sent = wp_mail($contact_email, $subject, $email_content, $headers);
        
        // Save message to database regardless of email success
        $saved_to_db = $this->save_message_to_db(
            $restaurant_id,
            $restaurant_title,
            $name,
            $email,
            $phone,
            $message
        );
        
        if ($sent) {
            // Send confirmation email to user
            $this->send_confirmation_email($email, $name, $restaurant_title);
            
            wp_die(json_encode(array(
                'success' => true,
                'message' => __('Votre message a été envoyé avec succès! Nous vous répondrons bientôt.', 'le-bon-resto')
            )));
        } else {
            wp_die(json_encode(array(
                'success' => false,
                'message' => __('Une erreur s\'est produite lors de l\'envoi du message. Veuillez réessayer.', 'le-bon-resto')
            )));
        }
    }
    
    /**
     * Get email template
     */
    private function get_email_template($name, $email, $phone, $message, $restaurant_title, $restaurant_url) {
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Nouveau message de contact</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { margin-bottom: 20px; }
                .field { margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-left: 4px solid #fedc00; }
                .field-label { font-weight: bold; color: #555; display: block; margin-bottom: 5px; }
                .field-value { color: #333; }
                .message-content { background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #fedc00; margin-top: 10px; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px; }
                .restaurant-link { display: inline-block; background: #fedc00; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🍽️ Nouveau message de contact</h1>
                    <p>Vous avez reçu un nouveau message via le formulaire de contact de votre site Le Bon Resto</p>
                </div>
                
                <div class="content">
                    <div class="field">
                        <span class="field-label">Restaurant:</span>
                        <span class="field-value">' . esc_html($restaurant_title) . '</span>
                    </div>
                    
                    <div class="field">
                        <span class="field-label">Nom du client:</span>
                        <span class="field-value">' . esc_html($name) . '</span>
                    </div>
                    
                    <div class="field">
                        <span class="field-label">Email:</span>
                        <span class="field-value"><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></span>
                    </div>';
        
        if (!empty($phone)) {
            $template .= '
                    <div class="field">
                        <span class="field-label">Téléphone:</span>
                        <span class="field-value"><a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a></span>
                    </div>';
        }
        
        $template .= '
                    <div class="field">
                        <span class="field-label">Message:</span>
                        <div class="message-content">' . nl2br(esc_html($message)) . '</div>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Ce message a été envoyé depuis le formulaire de contact de votre site Le Bon Resto.</p>
                    <a href="' . esc_url($restaurant_url) . '" class="restaurant-link">Voir la page du restaurant</a>
                    <p style="margin-top: 15px;">
                        <strong>Date:</strong> ' . date('d/m/Y à H:i') . '<br>
                        <strong>IP:</strong> ' . $_SERVER['REMOTE_ADDR'] . '
                    </p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    
    /**
     * Send confirmation email to user
     */
    private function send_confirmation_email($user_email, $user_name, $restaurant_title) {
        $subject = sprintf(__('Confirmation - Votre message a été envoyé à %s', 'le-bon-resto'), $restaurant_title);
        
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirmation de message</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { margin-bottom: 20px; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>✅ Message envoyé avec succès!</h1>
                </div>
                
                <div class="content">
                    <p>Bonjour ' . esc_html($user_name) . ',</p>
                    
                    <p>Nous avons bien reçu votre message concernant le restaurant <strong>' . esc_html($restaurant_title) . '</strong>.</p>
                    
                    <p>Notre équipe vous répondra dans les plus brefs délais.</p>
                    
                    <p>Merci pour votre intérêt et à bientôt!</p>
                    
                    <p>L\'équipe Le Bon Resto</p>
                </div>
                
                <div class="footer">
                    <p>Cet email est envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>';
        
        $options = get_option('lebonresto_options', array());
        $from_email = isset($options['smtp_from_email']) ? $options['smtp_from_email'] : get_option('admin_email');
        $from_name = isset($options['smtp_from_name']) ? $options['smtp_from_name'] : get_bloginfo('name');
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );
        
        wp_mail($user_email, $subject, $template, $headers);
    }
    
    /**
     * Create messages table
     */
    public function create_messages_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lebonresto_messages';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            restaurant_id bigint(20) NOT NULL,
            restaurant_name varchar(255) NOT NULL,
            user_name varchar(255) NOT NULL,
            user_email varchar(255) NOT NULL,
            user_phone varchar(50) DEFAULT '',
            message text NOT NULL,
            ip_address varchar(45) DEFAULT '',
            user_agent text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY restaurant_id (restaurant_id),
            KEY user_email (user_email),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Save message to database
     */
    private function save_message_to_db($restaurant_id, $restaurant_name, $user_name, $user_email, $user_phone, $message) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lebonresto_messages';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'restaurant_id' => $restaurant_id,
                'restaurant_name' => $restaurant_name,
                'user_name' => $user_name,
                'user_email' => $user_email,
                'user_phone' => $user_phone,
                'message' => $message,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ),
            array(
                '%d', // restaurant_id
                '%s', // restaurant_name
                '%s', // user_name
                '%s', // user_email
                '%s', // user_phone
                '%s', // message
                '%s', // ip_address
                '%s'  // user_agent
            )
        );
        
        return $result !== false;
    }
    
    /**
     * Add messages admin menu
     */
    public function add_messages_admin_menu() {
        // Add submenu under Restaurants
        add_submenu_page(
            'edit.php?post_type=restaurant',
            __('Messages de Contact', 'le-bon-resto'),
            __('Messages', 'le-bon-resto'),
            'manage_options',
            'lebonresto-messages',
            array($this, 'messages_admin_page')
        );
        
        // Also add as a top-level menu for easier access
        add_menu_page(
            __('Messages de Contact', 'le-bon-resto'),
            __('Messages Contact', 'le-bon-resto'),
            'manage_options',
            'lebonresto-messages-standalone',
            array($this, 'messages_admin_page'),
            'dashicons-email-alt',
            30
        );
    }
    
    /**
     * Messages admin page
     */
    public function messages_admin_page() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lebonresto_messages';
        
        // Check if table exists, if not create it
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            $this->create_messages_table();
        }
        
        // Handle message deletion
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['message_id'])) {
            $message_id = intval($_GET['message_id']);
            $wpdb->delete($table_name, array('id' => $message_id), array('%d'));
            echo '<div class="notice notice-success"><p>Message supprimé avec succès!</p></div>';
        }
        
        // Get messages
        $messages = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        
        ?>
        <style>
        .lebonresto-messages-buttons .button {
            margin-right: 10px;
        }
        .lebonresto-messages-buttons .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
            vertical-align: middle;
            margin-right: 5px;
        }
        .lebonresto-messages-buttons .button-primary .dashicons {
            color: #fff;
        }
        </style>
        
        <div class="wrap">
            <h1><?php _e('Messages de Contact', 'le-bon-resto'); ?></h1>
            
            <div class="tablenav top">
                <div class="alignleft actions lebonresto-messages-buttons">
                    <a href="<?php echo admin_url('admin.php?page=lebonresto-messages&export=excel'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-download"></span> Exporter CSV/Excel
                    </a>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Restaurant</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">
                                Aucun message trouvé.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <tr>
                                <td><?php echo esc_html($message->id); ?></td>
                                <td>
                                    <strong><?php echo esc_html($message->restaurant_name); ?></strong>
                                    <br>
                                    <small>ID: <?php echo esc_html($message->restaurant_id); ?></small>
                                </td>
                                <td><?php echo esc_html($message->user_name); ?></td>
                                <td>
                                    <a href="mailto:<?php echo esc_attr($message->user_email); ?>" style="text-decoration: none;">
                                        <span class="dashicons dashicons-email" style="font-size: 14px; vertical-align: middle; margin-right: 5px; color: #0073aa;"></span>
                                        <?php echo esc_html($message->user_email); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($message->user_phone)): ?>
                                        <a href="tel:<?php echo esc_attr($message->user_phone); ?>" style="text-decoration: none;">
                                            <span class="dashicons dashicons-phone" style="font-size: 14px; vertical-align: middle; margin-right: 5px; color: #0073aa;"></span>
                                            <?php echo esc_html($message->user_phone); ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="max-width: 300px; word-wrap: break-word;">
                                        <?php echo esc_html(wp_trim_words($message->message, 20)); ?>
                                        <?php if (strlen($message->message) > 100): ?>
                                            <br><a href="#" onclick="alert('<?php echo esc_js($message->message); ?>'); return false;" style="text-decoration: none; color: #0073aa;">
                                                <span class="dashicons dashicons-visibility" style="font-size: 12px; vertical-align: middle; margin-right: 3px;"></span>Voir le message complet
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo esc_html(date('d/m/Y H:i', strtotime($message->created_at))); ?>
                                    <br>
                                    <small style="color: #666;">
                                        IP: <?php echo esc_html($message->ip_address); ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=lebonresto-messages&action=delete&message_id=' . $message->id); ?>" 
                                       class="button button-small" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message?');"
                                       style="color: #a00; text-decoration: none;">
                                        <span class="dashicons dashicons-trash" style="font-size: 14px; vertical-align: middle; margin-right: 3px;"></span> Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Handle Excel export
     */
    public function handle_excel_export() {
        if (isset($_GET['page']) && $_GET['page'] === 'lebonresto-messages' && isset($_GET['export']) && $_GET['export'] === 'excel') {
            $this->export_messages_to_excel();
        }
    }
    
    /**
     * Export messages to Excel
     */
    private function export_messages_to_excel() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lebonresto_messages';
        $messages = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        
        // Set headers for Excel download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="messages_contact_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Cache-Control: max-age=0');
        
        // Output UTF-8 BOM for Excel compatibility
        echo "\xEF\xBB\xBF";
        
        // Create CSV content
        $this->generate_csv_content($messages);
        exit;
    }
    
    /**
     * Generate CSV content
     */
    private function generate_csv_content($messages) {
        // Headers
        $headers = array('ID', 'Restaurant ID', 'Nom du Restaurant', 'Nom du Client', 'Email', 'Téléphone', 'Message', 'Adresse IP', 'Date de Création');
        
        // Output headers
        echo $this->array_to_csv_line($headers);
        
        // Output data rows
        foreach ($messages as $message) {
            $data = array(
                $message->id,
                $message->restaurant_id,
                $message->restaurant_name,
                $message->user_name,
                $message->user_email,
                $message->user_phone,
                $message->message,
                $message->ip_address,
                $message->created_at
            );
            
            echo $this->array_to_csv_line($data);
        }
    }
    
    /**
     * Convert array to CSV line
     */
    private function array_to_csv_line($array) {
        $csv_line = '';
        foreach ($array as $index => $value) {
            if ($index > 0) {
                $csv_line .= ',';
            }
            
            // Escape quotes and wrap in quotes if contains comma, quote, or newline
            $value = str_replace('"', '""', $value);
            if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false || strpos($value, "\r") !== false) {
                $csv_line .= '"' . $value . '"';
            } else {
                $csv_line .= $value;
            }
        }
        $csv_line .= "\n";
        
        return $csv_line;
    }
    
    /**
     * Add SEO meta descriptions for restaurant pages
     */
    public function add_seo_meta_descriptions() {
        // Only add meta descriptions on restaurant-related pages
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php') && !is_page_template('templates/single-restaurant.php')) {
            return;
        }
        
        // Get current language (default to French)
        $current_lang = $this->get_current_language();
        
        // Get meta description based on page type and language
        $meta_description = $this->get_meta_description($current_lang);
        
        if ($meta_description) {
            echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
            
            // Add Open Graph meta tags
            echo '<meta property="og:description" content="' . esc_attr($meta_description) . '">' . "\n";
            echo '<meta property="og:type" content="website">' . "\n";
            echo '<meta property="og:locale" content="' . $this->get_og_locale($current_lang) . '">' . "\n";
            
            // Add Twitter Card meta tags
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
            echo '<meta name="twitter:description" content="' . esc_attr($meta_description) . '">' . "\n";
        }
    }
    
    /**
     * Get current language
     */
    private function get_current_language() {
        // Check if WPML or Polylang is active
        if (function_exists('pll_current_language')) {
            return pll_current_language();
        }
        
        if (function_exists('wpml_get_current_language')) {
            return wpml_get_current_language();
        }
        
        // Check URL parameters for language
        if (isset($_GET['lang'])) {
            $lang = sanitize_text_field($_GET['lang']);
            if (in_array($lang, ['fr', 'ar', 'en'])) {
                return $lang;
            }
        }
        
        // Check browser language
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (in_array($browser_lang, ['fr', 'ar', 'en'])) {
                return $browser_lang;
            }
        }
        
        // Default to French for Morocco
        return 'fr';
    }
    
    /**
     * Get meta description based on page type and language
     */
    private function get_meta_description($lang) {
        if (is_singular('restaurant')) {
            return $this->get_restaurant_detail_meta($lang);
        } elseif (is_page_template('templates/all-restaurants.php')) {
            return $this->get_all_restaurants_meta($lang);
        } elseif (is_page_template('templates/single-restaurant.php')) {
            return $this->get_single_restaurant_meta($lang);
        }
        
        return '';
    }
    
    /**
     * Get restaurant detail page meta description
     */
    private function get_restaurant_detail_meta($lang) {
        $restaurant_name = get_the_title();
        $cuisine_type = get_post_meta(get_the_ID(), '_restaurant_cuisine_type', true);
        $city = get_post_meta(get_the_ID(), '_restaurant_city', true);
        $description = get_post_meta(get_the_ID(), '_restaurant_description', true);
        
        // Use city from meta or default to Casablanca
        $city = $city ?: 'Casablanca';
        $cuisine_type = $cuisine_type ?: 'cuisine marocaine';
        
        $meta_descriptions = [
            'fr' => "Découvrez {$restaurant_name} à {$city}, Maroc. Restaurant spécialisé en {$cuisine_type} avec une ambiance authentique. Réservation en ligne, menus, photos et avis clients. Le meilleur de la gastronomie marocaine à {$city}.",
            'ar' => "اكتشف مطعم {$restaurant_name} في الدار البيضاء، المغرب. مطعم متخصص في {$cuisine_type} مع أجواء أصيلة. حجز عبر الإنترنت، قوائم الطعام، الصور وآراء العملاء. أفضل المأكولات المغربية في الدار البيضاء.",
            'en' => "Discover {$restaurant_name} restaurant in {$city}, Morocco. Specialized in {$cuisine_type} with authentic atmosphere. Online booking, menus, photos and customer reviews. The best of Moroccan gastronomy in {$city}."
        ];
        
        return $meta_descriptions[$lang] ?? $meta_descriptions['fr'];
    }
    
    /**
     * Get all restaurants page meta description
     */
    private function get_all_restaurants_meta($lang) {
        $meta_descriptions = [
            'fr' => "Guide complet des meilleurs restaurants à Casablanca, Maroc. Découvrez plus de 500 restaurants, cafés et bars avec photos, menus, avis et réservations en ligne. Cuisine marocaine, internationale, fast-food et gastronomie fine.",
            'ar' => "دليل شامل لأفضل المطاعم في الدار البيضاء، المغرب. اكتشف أكثر من 500 مطعم ومقهى وبار مع الصور وقوائم الطعام والآراء والحجز عبر الإنترنت. المأكولات المغربية والدولية والوجبات السريعة والمأكولات الفاخرة.",
            'en' => "Complete guide to the best restaurants in Casablanca, Morocco. Discover over 500 restaurants, cafes and bars with photos, menus, reviews and online booking. Moroccan, international, fast-food and fine dining cuisine."
        ];
        
        return $meta_descriptions[$lang] ?? $meta_descriptions['fr'];
    }
    
    /**
     * Get single restaurant page meta description
     */
    private function get_single_restaurant_meta($lang) {
        $meta_descriptions = [
            'fr' => "Restaurant d'exception à Casablanca, Maroc. Cuisine authentique, ambiance chaleureuse et service impeccable. Découvrez nos spécialités culinaires, réservez votre table et vivez une expérience gastronomique unique au cœur de la capitale économique.",
            'ar' => "مطعم استثنائي في الدار البيضاء، المغرب. مطبخ أصيل وأجواء دافئة وخدمة لا تشوبها شائبة. اكتشف تخصصاتنا الطهوية واحجز طاولتك واستمتع بتجربة طهوية فريدة في قلب العاصمة الاقتصادية.",
            'en' => "Exceptional restaurant in Casablanca, Morocco. Authentic cuisine, warm atmosphere and impeccable service. Discover our culinary specialties, book your table and experience a unique gastronomic journey in the heart of the economic capital."
        ];
        
        return $meta_descriptions[$lang] ?? $meta_descriptions['fr'];
    }
    
    /**
     * Get Open Graph locale
     */
    private function get_og_locale($lang) {
        $locales = [
            'fr' => 'fr_FR',
            'ar' => 'ar_MA',
            'en' => 'en_US'
        ];
        
        return $locales[$lang] ?? 'fr_FR';
    }
    
}

// Initialize email handler
new LeBonResto_Email_Handler();
