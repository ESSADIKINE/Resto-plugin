<?php
/**
 * Google Forms Integration for Service Requests
 * 
 * @package LeBonResto
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Google Forms Integration Class
 */
class LeBonResto_Google_Forms_Integration {
    
    /**
     * Initialize the integration
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize hooks and filters
     */
    public function init() {
        add_action('wp_ajax_lebonresto_submit_service_request', array($this, 'handle_service_request'));
        add_action('wp_ajax_nopriv_lebonresto_submit_service_request', array($this, 'handle_service_request'));
        add_shortcode('lebonresto_service_request_form', array($this, 'service_request_form_shortcode'));
    }
    
    /**
     * Service request form shortcode
     */
    public function service_request_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Demander nos services',
            'description' => 'Contactez-nous pour découvrir comment nous pouvons améliorer votre présence en ligne',
            'button_text' => 'Envoyer la demande',
            'success_message' => 'Votre demande a été envoyée avec succès! Nous vous contacterons bientôt.',
            'error_message' => 'Une erreur s\'est produite. Veuillez réessayer.',
        ), $atts);
        
        ob_start();
        ?>
        <div class="lebonresto-service-request-form" style="max-width: 600px; margin: 0 auto; padding: 2rem; background: var(--bg-primary, #fff); border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <div class="form-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="color: var(--text-primary, #333); margin-bottom: 1rem; font-size: 2rem;"><?php echo esc_html($atts['title']); ?></h2>
                <p style="color: var(--text-secondary, #666); font-size: 1.1rem; line-height: 1.6;"><?php echo esc_html($atts['description']); ?></p>
            </div>
            
            <form id="lebonresto-service-request-form" class="service-request-form" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <?php wp_nonce_field('lebonresto_service_request', 'lebonresto_service_nonce'); ?>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="restaurant_name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary, #333);">Nom du restaurant *</label>
                        <input type="text" id="restaurant_name" name="restaurant_name" required 
                               style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color, #ddd); border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease;"
                               placeholder="Nom de votre restaurant">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_person" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary, #333);">Nom du contact *</label>
                        <input type="text" id="contact_person" name="contact_person" required 
                               style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color, #ddd); border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease;"
                               placeholder="Votre nom complet">
                    </div>
                </div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary, #333);">Email *</label>
                        <input type="email" id="email" name="email" required 
                               style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color, #ddd); border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease;"
                               placeholder="votre@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary, #333);">Téléphone *</label>
                        <input type="tel" id="phone" name="phone" required 
                               style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color, #ddd); border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease;"
                               placeholder="+212 6XX XXX XXX">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="restaurant_address" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary, #333);">Adresse du restaurant *</label>
                    <input type="text" id="restaurant_address" name="restaurant_address" required 
                           style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color, #ddd); border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease;"
                           placeholder="Adresse complète de votre restaurant">
                </div>
                
                <div class="form-group">
                    <label for="cuisine_type" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary, #333);">Type de cuisine *</label>
                    <select id="cuisine_type" name="cuisine_type" required 
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color, #ddd); border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease; background: white;">
                        <option value="">Sélectionnez le type de cuisine</option>
                        <option value="marocaine">Marocaine</option>
                        <option value="francaise">Française</option>
                        <option value="italienne">Italienne</option>
                        <option value="japonaise">Japonaise</option>
                        <option value="chinoise">Chinoise</option>
                        <option value="indienne">Indienne</option>
                        <option value="mexicaine">Mexicaine</option>
                        <option value="mediterraneenne">Méditerranéenne</option>
                        <option value="americaine">Américaine</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="services_needed" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary, #333);">Services souhaités *</label>
                    <div class="checkbox-group" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="services_needed[]" value="site-web" style="margin: 0;">
                            <span>Site web restaurant</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="services_needed[]" value="reservation-en-ligne" style="margin: 0;">
                            <span>Système de réservation</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="services_needed[]" value="menu-numerique" style="margin: 0;">
                            <span>Menu numérique</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="services_needed[]" value="reseaux-sociaux" style="margin: 0;">
                            <span>Gestion réseaux sociaux</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="services_needed[]" value="seo" style="margin: 0;">
                            <span>Optimisation SEO</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="services_needed[]" value="publicite-en-ligne" style="margin: 0;">
                            <span>Publicité en ligne</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="services_needed[]" value="autre" style="margin: 0;">
                            <span>Autre</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="budget" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary, #333);">Budget approximatif</label>
                    <select id="budget" name="budget" 
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color, #ddd); border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease; background: white;">
                        <option value="">Sélectionnez votre budget</option>
                        <option value="moins-5000">Moins de 5 000 MAD</option>
                        <option value="5000-10000">5 000 - 10 000 MAD</option>
                        <option value="10000-20000">10 000 - 20 000 MAD</option>
                        <option value="20000-50000">20 000 - 50 000 MAD</option>
                        <option value="plus-50000">Plus de 50 000 MAD</option>
                        <option value="a-discuter">À discuter</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="message" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary, #333);">Message supplémentaire</label>
                    <textarea id="message" name="message" rows="4" 
                              style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color, #ddd); border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease; resize: vertical;"
                              placeholder="Décrivez vos besoins spécifiques, vos objectifs, ou toute information supplémentaire..."></textarea>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; line-height: 1.4;">
                        <input type="checkbox" name="privacy_consent" required style="margin: 0; margin-top: 0.2rem;">
                        <span>J'accepte que mes données soient utilisées pour me recontacter concernant ma demande de services. *</span>
                    </label>
                </div>
                
                <div class="form-actions" style="text-align: center;">
                    <button type="submit" id="submit-service-request" 
                            style="background: var(--gradient-primary, linear-gradient(135deg, #fedc00, #ff6b35)); color: white; border: none; padding: 1rem 2rem; border-radius: 50px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        <span class="button-text"><?php echo esc_html($atts['button_text']); ?></span>
                        <span class="loading-spinner" style="display: none;">Envoi en cours...</span>
                    </button>
                </div>
                
                <div id="form-messages" style="margin-top: 1rem; text-align: center;"></div>
            </form>
        </div>
        
        <style>
        .service-request-form input:focus,
        .service-request-form select:focus,
        .service-request-form textarea:focus {
            border-color: var(--primary-color, #fedc00) !important;
            outline: none;
            box-shadow: 0 0 0 3px rgba(254, 220, 0, 0.1);
        }
        
        .service-request-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .service-request-form button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr !important;
            }
            
            .checkbox-group {
                grid-template-columns: 1fr !important;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#lebonresto-service-request-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $button = $('#submit-service-request');
                var $messages = $('#form-messages');
                var $buttonText = $('.button-text');
                var $loadingSpinner = $('.loading-spinner');
                
                // Disable form
                $form.find('input, select, textarea, button').prop('disabled', true);
                $buttonText.hide();
                $loadingSpinner.show();
                $messages.empty();
                
                // Collect form data
                var formData = {
                    action: 'lebonresto_submit_service_request',
                    nonce: $('#lebonresto_service_nonce').val(),
                    restaurant_name: $('#restaurant_name').val(),
                    contact_person: $('#contact_person').val(),
                    email: $('#email').val(),
                    phone: $('#phone').val(),
                    restaurant_address: $('#restaurant_address').val(),
                    cuisine_type: $('#cuisine_type').val(),
                    services_needed: $('input[name="services_needed[]"]:checked').map(function() {
                        return this.value;
                    }).get(),
                    budget: $('#budget').val(),
                    message: $('#message').val(),
                    privacy_consent: $('input[name="privacy_consent"]').is(':checked')
                };
                
                // Submit to Google Forms
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $messages.html('<div class="success-message"><?php echo esc_js($atts['success_message']); ?></div>');
                            $form[0].reset();
                        } else {
                            $messages.html('<div class="error-message">' + (response.data || '<?php echo esc_js($atts['error_message']); ?>') + '</div>');
                        }
                    },
                    error: function() {
                        $messages.html('<div class="error-message"><?php echo esc_js($atts['error_message']); ?></div>');
                    },
                    complete: function() {
                        // Re-enable form
                        $form.find('input, select, textarea, button').prop('disabled', false);
                        $buttonText.show();
                        $loadingSpinner.hide();
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle service request submission
     */
    public function handle_service_request() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'lebonresto_service_request')) {
            wp_die('Security check failed');
        }
        
        // Sanitize and validate data
        $data = array(
            'restaurant_name' => sanitize_text_field($_POST['restaurant_name']),
            'contact_person' => sanitize_text_field($_POST['contact_person']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'restaurant_address' => sanitize_text_field($_POST['restaurant_address']),
            'cuisine_type' => sanitize_text_field($_POST['cuisine_type']),
            'services_needed' => array_map('sanitize_text_field', $_POST['services_needed']),
            'budget' => sanitize_text_field($_POST['budget']),
            'message' => sanitize_textarea_field($_POST['message']),
            'privacy_consent' => isset($_POST['privacy_consent']) ? true : false,
            'submission_date' => current_time('mysql'),
            'ip_address' => $_SERVER['REMOTE_ADDR']
        );
        
        // Validate required fields
        if (empty($data['restaurant_name']) || empty($data['contact_person']) || empty($data['email']) || empty($data['phone']) || empty($data['restaurant_address']) || empty($data['cuisine_type']) || empty($data['services_needed']) || !$data['privacy_consent']) {
            wp_send_json_error('Tous les champs obligatoires doivent être remplis.');
        }
        
        // Submit to Google Forms
        $google_forms_result = $this->submit_to_google_forms($data);
        
        if ($google_forms_result) {
            // Also save locally for backup
            $this->save_service_request_locally($data);
            
            // Send email notification
            $this->send_email_notification($data);
            
            wp_send_json_success('Demande envoyée avec succès!');
        } else {
            wp_send_json_error('Erreur lors de l\'envoi. Veuillez réessayer.');
        }
    }
    
    /**
     * Submit data to Google Forms
     */
    private function submit_to_google_forms($data) {
        // Get Google Forms settings
        $options = get_option('lebonresto_options', array());
        $google_forms_url = isset($options['google_forms_url']) ? $options['google_forms_url'] : '';
        
        if (empty($google_forms_url)) {
            return false;
        }
        
        // Prepare data for Google Forms
        $form_data = array(
            'entry.XXXXXXXX' => $data['restaurant_name'], // Replace with actual field IDs
            'entry.XXXXXXXX' => $data['contact_person'],
            'entry.XXXXXXXX' => $data['email'],
            'entry.XXXXXXXX' => $data['phone'],
            'entry.XXXXXXXX' => $data['restaurant_address'],
            'entry.XXXXXXXX' => $data['cuisine_type'],
            'entry.XXXXXXXX' => implode(', ', $data['services_needed']),
            'entry.XXXXXXXX' => $data['budget'],
            'entry.XXXXXXXX' => $data['message'],
            'entry.XXXXXXXX' => $data['submission_date']
        );
        
        // Submit to Google Forms
        $response = wp_remote_post($google_forms_url, array(
            'body' => $form_data,
            'timeout' => 30
        ));
        
        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }
    
    /**
     * Save service request locally
     */
    private function save_service_request_locally($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lebonresto_service_requests';
        
        $wpdb->insert(
            $table_name,
            array(
                'restaurant_name' => $data['restaurant_name'],
                'contact_person' => $data['contact_person'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'restaurant_address' => $data['restaurant_address'],
                'cuisine_type' => $data['cuisine_type'],
                'services_needed' => json_encode($data['services_needed']),
                'budget' => $data['budget'],
                'message' => $data['message'],
                'submission_date' => $data['submission_date'],
                'ip_address' => $data['ip_address'],
                'status' => 'new'
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Send email notification
     */
    private function send_email_notification($data) {
        $options = get_option('lebonresto_options', array());
        $admin_email = isset($options['admin_email']) ? $options['admin_email'] : get_option('admin_email');
        
        $subject = 'Nouvelle demande de service - ' . $data['restaurant_name'];
        
        $message = "Nouvelle demande de service reçue:\n\n";
        $message .= "Restaurant: " . $data['restaurant_name'] . "\n";
        $message .= "Contact: " . $data['contact_person'] . "\n";
        $message .= "Email: " . $data['email'] . "\n";
        $message .= "Téléphone: " . $data['phone'] . "\n";
        $message .= "Adresse: " . $data['restaurant_address'] . "\n";
        $message .= "Cuisine: " . $data['cuisine_type'] . "\n";
        $message .= "Services: " . implode(', ', $data['services_needed']) . "\n";
        $message .= "Budget: " . $data['budget'] . "\n";
        $message .= "Message: " . $data['message'] . "\n";
        $message .= "Date: " . $data['submission_date'] . "\n";
        
        wp_mail($admin_email, $subject, $message);
    }
}

// Initialize the integration
new LeBonResto_Google_Forms_Integration();
