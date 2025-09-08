<?php
/**
 * Admin Interface for Le Bon Resto Plugin
 * 
 * @package LeBonResto
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin class for Le Bon Resto
 */
class LeBonResto_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Use lower priority to ensure post type is registered first
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
        add_action('wp_head', array($this, 'frontend_custom_css'));
        add_action('admin_notices', array($this, 'admin_notices'));
        
        // Custom post type admin enhancements
        add_filter('manage_restaurant_posts_columns', array($this, 'add_restaurant_columns'));
        add_action('manage_restaurant_posts_custom_column', array($this, 'restaurant_column_content'), 10, 2);
        add_filter('manage_edit-restaurant_sortable_columns', array($this, 'restaurant_sortable_columns'));
        add_action('pre_get_posts', array($this, 'restaurant_orderby'));
        add_filter('posts_where', array($this, 'restaurant_search_where'));
    }
    
    /**
     * Show admin notices
     */
    public function admin_notices() {
        // Show activation success notice
        if (get_transient('lebonresto_activation_notice')) {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo __('Le Bon Resto plugin activated successfully! You can now add restaurants and configure settings.', 'le-bon-resto');
            echo '</p></div>';
            delete_transient('lebonresto_activation_notice');
        }
        
        // Check if restaurant post type exists
        if (!post_type_exists('restaurant') && current_user_can('manage_options')) {
            echo '<div class="notice notice-error"><p>';
            echo __('Le Bon Resto: Restaurant post type is not registered. Please deactivate and reactivate the plugin.', 'le-bon-resto');
            echo '</p></div>';
        }
        
        // Show success message after settings save
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] && isset($_GET['page']) && $_GET['page'] === 'lebonresto-settings') {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo __('Le Bon Resto settings saved successfully!', 'le-bon-resto');
            echo '</p></div>';
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Check if post type exists before creating menu
        if (!post_type_exists('restaurant')) {
            // If post type doesn't exist, create a simple menu with warning
            add_menu_page(
                __('Le Bon Resto', 'le-bon-resto'),
                __('Le Bon Resto', 'le-bon-resto'),
                'manage_options',
                'lebonresto',
                array($this, 'admin_page_error'),
                'dashicons-store',
                25
            );
            return;
        }
        
        // Main menu
        add_menu_page(
            __('Le Bon Resto', 'le-bon-resto'),
            __('Le Bon Resto', 'le-bon-resto'),
            'manage_options',
            'lebonresto',
            array($this, 'admin_page_restaurants'),
            'dashicons-store',
            25
        );
        
        // Submenu - Dashboard (rename the first item)
        add_submenu_page(
            'lebonresto',
            __('Dashboard', 'le-bon-resto'),
            __('Dashboard', 'le-bon-resto'),
            'manage_options',
            'lebonresto',
            array($this, 'admin_page_restaurants')
        );
        
        // Note: Restaurants submenu will be automatically added by WordPress
        // because we set 'show_in_menu' => 'lebonresto' in the CPT registration
        
        // Submenu - Settings
        add_submenu_page(
            'lebonresto',
            __('Le Bon Resto Settings', 'le-bon-resto'),
            __('Settings', 'le-bon-resto'),
            'manage_options',
            'lebonresto-settings',
            array($this, 'admin_page_settings')
        );
    }
    
    /**
     * Error page when post type is not registered
     */
    public function admin_page_error() {
        ?>
        <div class="wrap">
            <h1><?php _e('Le Bon Resto - Setup Required', 'le-bon-resto'); ?></h1>
            
            <div class="notice notice-error">
                <p><strong><?php _e('Restaurant post type is not registered!', 'le-bon-resto'); ?></strong></p>
                <p><?php _e('Please deactivate and reactivate the Le Bon Resto plugin to fix this issue.', 'le-bon-resto'); ?></p>
            </div>
            
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle"><?php _e('Troubleshooting Steps', 'le-bon-resto'); ?></h2>
                </div>
                <div class="inside">
                    <ol>
                        <li><?php _e('Go to Plugins → Installed Plugins', 'le-bon-resto'); ?></li>
                        <li><?php _e('Deactivate "Le Bon Resto"', 'le-bon-resto'); ?></li>
                        <li><?php _e('Activate "Le Bon Resto" again', 'le-bon-resto'); ?></li>
                        <li><?php _e('Return to this page', 'le-bon-resto'); ?></li>
                    </ol>
                    
                    <p><strong><?php _e('If the problem persists:', 'le-bon-resto'); ?></strong></p>
                    <ul>
                        <li><?php _e('Check for PHP errors in your error log', 'le-bon-resto'); ?></li>
                        <li><?php _e('Verify all plugin files are uploaded correctly', 'le-bon-resto'); ?></li>
                        <li><?php _e('Try deactivating other plugins temporarily', 'le-bon-resto'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Admin page for restaurants overview
     */
    public function admin_page_restaurants() {
        // Check if restaurant post type exists before counting
        if (post_type_exists('restaurant')) {
            $restaurant_count = wp_count_posts('restaurant');
            $published_count = isset($restaurant_count->publish) ? $restaurant_count->publish : 0;
            $draft_count = isset($restaurant_count->draft) ? $restaurant_count->draft : 0;
        } else {
            $published_count = 0;
            $draft_count = 0;
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Le Bon Resto Dashboard', 'le-bon-resto'); ?></h1>
            
            <div class="lebonresto-admin-dashboard">
                <div class="postbox-container" style="width: 100%;">
                    
                    <!-- Statistics Cards -->
                    <div class="lebonresto-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                        
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2 class="hndle"><?php _e('Published Restaurants', 'le-bon-resto'); ?></h2>
                            </div>
                            <div class="inside">
                                <div style="text-align: center; padding: 20px;">
                                    <div style="font-size: 36px; font-weight: bold; color: #FFC107;"><?php echo $published_count; ?></div>
                                    <p><?php _e('Active listings', 'le-bon-resto'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2 class="hndle"><?php _e('Draft Restaurants', 'le-bon-resto'); ?></h2>
                            </div>
                            <div class="inside">
                                <div style="text-align: center; padding: 20px;">
                                    <div style="font-size: 36px; font-weight: bold; color: #666;"><?php echo $draft_count; ?></div>
                                    <p><?php _e('Pending publication', 'le-bon-resto'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2 class="hndle"><?php _e('Plugin Version', 'le-bon-resto'); ?></h2>
                            </div>
                            <div class="inside">
                                <div style="text-align: center; padding: 20px;">
                                    <div style="font-size: 24px; font-weight: bold; color: #0073aa;"><?php echo LEBONRESTO_PLUGIN_VERSION; ?></div>
                                    <p><?php _e('Current version', 'le-bon-resto'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="hndle"><?php _e('Quick Actions', 'le-bon-resto'); ?></h2>
                        </div>
                        <div class="inside">
                            <p><strong><?php _e('Get started with Le Bon Resto:', 'le-bon-resto'); ?></strong></p>
                            <ul style="list-style: disc; padding-left: 20px;">
                                <li><a href="<?php echo admin_url('post-new.php?post_type=restaurant'); ?>"><?php _e('Add your first restaurant', 'le-bon-resto'); ?></a></li>
                                <li><a href="<?php echo admin_url('admin.php?page=lebonresto-settings'); ?>"><?php _e('Configure plugin settings', 'le-bon-resto'); ?></a></li>
                                <li><a href="<?php echo admin_url('edit.php?post_type=restaurant'); ?>"><?php _e('View all restaurants', 'le-bon-resto'); ?></a></li>
                            </ul>
                            
                            <h4><?php _e('Usage Instructions:', 'le-bon-resto'); ?></h4>
                            <p><?php _e('To display the restaurant map on any page or post, use the shortcode:', 'le-bon-resto'); ?></p>
                            <code style="background: #f0f0f0; padding: 5px 10px; border-radius: 3px;">[lebonresto_map]</code>
                            
                            <h4 style="margin-top: 20px;"><?php _e('Shortcode Options:', 'le-bon-resto'); ?></h4>
                            <ul style="list-style: disc; padding-left: 20px;">
                                <li><code>width</code> - <?php _e('Map width (default: 100%)', 'le-bon-resto'); ?></li>
                                <li><code>height</code> - <?php _e('Map height (default: 500px)', 'le-bon-resto'); ?></li>
                                <li><code>zoom</code> - <?php _e('Initial zoom level (default: 12)', 'le-bon-resto'); ?></li>
                                <li><code>center_lat</code> - <?php _e('Map center latitude', 'le-bon-resto'); ?></li>
                                <li><code>center_lng</code> - <?php _e('Map center longitude', 'le-bon-resto'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <style>
        .lebonresto-admin-dashboard .postbox {
            margin-bottom: 20px;
        }
        
        .lebonresto-admin-dashboard .postbox-header h2 {
            color: #FFC107;
            font-weight: 600;
        }
        
        .lebonresto-admin-dashboard code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: Monaco, Consolas, monospace;
        }
        </style>
        <?php
    }
    
    /**
     * Admin page for settings
     */
    public function admin_page_settings() {
        ?>
        <div class="wrap">
            <h1><?php _e('Le Bon Resto Settings', 'le-bon-resto'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('lebonresto_settings');
                do_settings_sections('lebonresto_settings');
                submit_button();
                ?>
            </form>
        </div>
        
        <style>
        .wrap h1 {
            color: #FFC107;
        }
        
        .form-table th {
            color: #333;
            font-weight: 600;
        }
        
        .lebonresto-color-preview {
            width: 50px;
            height: 25px;
            border: 1px solid #ddd;
            border-radius: 3px;
            display: inline-block;
            margin-left: 10px;
            vertical-align: middle;
        }
        </style>
        <?php
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        // Register settings
        register_setting('lebonresto_settings', 'lebonresto_options', array($this, 'validate_settings'));
        
        // Add settings section
        add_settings_section(
            'lebonresto_general',
            __('General Settings', 'le-bon-resto'),
            array($this, 'settings_section_callback'),
            'lebonresto_settings'
        );
        
        // Add settings fields
        add_settings_field(
            'default_map_center_lat',
            __('Default Map Center Latitude', 'le-bon-resto'),
            array($this, 'setting_field_lat'),
            'lebonresto_settings',
            'lebonresto_general'
        );
        
        add_settings_field(
            'default_map_center_lng',
            __('Default Map Center Longitude', 'le-bon-resto'),
            array($this, 'setting_field_lng'),
            'lebonresto_settings',
            'lebonresto_general'
        );
        
        add_settings_field(
            'default_zoom_level',
            __('Default Map Zoom Level', 'le-bon-resto'),
            array($this, 'setting_field_zoom'),
            'lebonresto_settings',
            'lebonresto_general'
        );
        
        add_settings_field(
            'default_radius',
            __('Default Search Radius', 'le-bon-resto'),
            array($this, 'setting_field_default_radius'),
            'lebonresto_settings',
            'lebonresto_general'
        );
        
        add_settings_field(
            'max_radius',
            __('Maximum Search Radius', 'le-bon-resto'),
            array($this, 'setting_field_max_radius'),
            'lebonresto_settings',
            'lebonresto_general'
        );
        
        add_settings_field(
            'distance_filter_options',
            __('Distance Filter Options', 'le-bon-resto'),
            array($this, 'setting_field_distance'),
            'lebonresto_settings',
            'lebonresto_general'
        );
        
        add_settings_field(
            'enable_layer_switcher',
            __('Enable Map Layer Switcher', 'le-bon-resto'),
            array($this, 'setting_field_layer_switcher'),
            'lebonresto_settings',
            'lebonresto_general'
        );
        
        add_settings_field(
            'enable_fullscreen',
            __('Enable Fullscreen Toggle', 'le-bon-resto'),
            array($this, 'setting_field_fullscreen'),
            'lebonresto_settings',
            'lebonresto_general'
        );
        
        add_settings_field(
            'primary_color',
            __('Primary Color', 'le-bon-resto'),
            array($this, 'setting_field_color'),
            'lebonresto_settings',
            'lebonresto_general'
        );
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure the default settings for your Le Bon Resto plugin.', 'le-bon-resto') . '</p>';
    }
    
    /**
     * Get plugin options with defaults
     */
    public function get_options() {
        $defaults = array(
            'default_map_center_lat' => '48.8566',
            'default_map_center_lng' => '2.3522',
            'default_zoom_level' => '12',
            'default_radius' => '25',
            'max_radius' => '100',
            'distance_filter_options' => '10,25,50,100',
            'enable_layer_switcher' => '1',
            'enable_fullscreen' => '1',
            'primary_color' => '#FFC107'
        );
        
        $options = get_option('lebonresto_options', array());
        return wp_parse_args($options, $defaults);
    }
    
    /**
     * Setting field callbacks
     */
    public function setting_field_lat() {
        $options = $this->get_options();
        echo '<input type="number" step="any" name="lebonresto_options[default_map_center_lat]" value="' . esc_attr($options['default_map_center_lat']) . '" />';
        echo '<p class="description">' . __('Default latitude for map center (e.g., 48.8566 for Paris)', 'le-bon-resto') . '</p>';
    }
    
    public function setting_field_lng() {
        $options = $this->get_options();
        echo '<input type="number" step="any" name="lebonresto_options[default_map_center_lng]" value="' . esc_attr($options['default_map_center_lng']) . '" />';
        echo '<p class="description">' . __('Default longitude for map center (e.g., 2.3522 for Paris)', 'le-bon-resto') . '</p>';
    }
    
    public function setting_field_zoom() {
        $options = $this->get_options();
        echo '<input type="number" min="1" max="18" name="lebonresto_options[default_zoom_level]" value="' . esc_attr($options['default_zoom_level']) . '" />';
        echo '<p class="description">' . __('Default zoom level for the map (1-18, where 18 is closest)', 'le-bon-resto') . '</p>';
    }
    
    public function setting_field_default_radius() {
        $options = $this->get_options();
        echo '<input type="number" min="1" max="1000" name="lebonresto_options[default_radius]" value="' . esc_attr($options['default_radius']) . '" />';
        echo '<p class="description">' . __('Default search radius in kilometers when the map loads', 'le-bon-resto') . '</p>';
    }
    
    public function setting_field_max_radius() {
        $options = $this->get_options();
        echo '<input type="number" min="1" max="1000" name="lebonresto_options[max_radius]" value="' . esc_attr($options['max_radius']) . '" />';
        echo '<p class="description">' . __('Maximum search radius allowed in the radius slider', 'le-bon-resto') . '</p>';
    }
    
    public function setting_field_distance() {
        $options = $this->get_options();
        echo '<input type="text" name="lebonresto_options[distance_filter_options]" value="' . esc_attr($options['distance_filter_options']) . '" />';
        echo '<p class="description">' . __('Comma-separated list of distance options in kilometers (e.g., 10,25,50,100)', 'le-bon-resto') . '</p>';
    }
    
    public function setting_field_layer_switcher() {
        $options = $this->get_options();
        echo '<label><input type="checkbox" name="lebonresto_options[enable_layer_switcher]" value="1" ' . checked($options['enable_layer_switcher'], '1', false) . ' /> ';
        echo __('Allow users to switch between Standard, Satellite, and Terrain map layers', 'le-bon-resto') . '</label>';
    }
    
    public function setting_field_fullscreen() {
        $options = $this->get_options();
        echo '<label><input type="checkbox" name="lebonresto_options[enable_fullscreen]" value="1" ' . checked($options['enable_fullscreen'], '1', false) . ' /> ';
        echo __('Enable fullscreen toggle button on the map', 'le-bon-resto') . '</label>';
    }
    
    public function setting_field_color() {
        $options = $this->get_options();
        echo '<input type="color" name="lebonresto_options[primary_color]" value="' . esc_attr($options['primary_color']) . '" />';
        echo '<span class="lebonresto-color-preview" style="background-color: ' . esc_attr($options['primary_color']) . ';"></span>';
        echo '<p class="description">' . __('Primary color used throughout the plugin interface and frontend', 'le-bon-resto') . '</p>';
    }
    
    /**
     * Validate settings
     */
    public function validate_settings($input) {
        $validated = array();
        
        // Validate latitude
        if (isset($input['default_map_center_lat'])) {
            $lat = floatval($input['default_map_center_lat']);
            if ($lat >= -90 && $lat <= 90) {
                $validated['default_map_center_lat'] = $lat;
            }
        }
        
        // Validate longitude
        if (isset($input['default_map_center_lng'])) {
            $lng = floatval($input['default_map_center_lng']);
            if ($lng >= -180 && $lng <= 180) {
                $validated['default_map_center_lng'] = $lng;
            }
        }
        
        // Validate zoom level
        if (isset($input['default_zoom_level'])) {
            $zoom = intval($input['default_zoom_level']);
            if ($zoom >= 1 && $zoom <= 18) {
                $validated['default_zoom_level'] = $zoom;
            }
        }
        
        // Validate default radius
        if (isset($input['default_radius'])) {
            $radius = intval($input['default_radius']);
            if ($radius >= 1 && $radius <= 1000) {
                $validated['default_radius'] = $radius;
            }
        }
        
        // Validate max radius
        if (isset($input['max_radius'])) {
            $max_radius = intval($input['max_radius']);
            if ($max_radius >= 1 && $max_radius <= 1000) {
                $validated['max_radius'] = $max_radius;
            }
        }
        
        // Validate layer switcher
        $validated['enable_layer_switcher'] = isset($input['enable_layer_switcher']) ? '1' : '0';
        
        // Validate fullscreen
        $validated['enable_fullscreen'] = isset($input['enable_fullscreen']) ? '1' : '0';
        
        // Validate distance options
        if (isset($input['distance_filter_options'])) {
            $distances = sanitize_text_field($input['distance_filter_options']);
            // Validate that it's a comma-separated list of numbers
            $distance_array = explode(',', $distances);
            $valid_distances = array();
            foreach ($distance_array as $distance) {
                $distance = trim($distance);
                if (is_numeric($distance) && $distance > 0) {
                    $valid_distances[] = intval($distance);
                }
            }
            if (!empty($valid_distances)) {
                $validated['distance_filter_options'] = implode(',', $valid_distances);
            }
        }
        
        // Validate color
        if (isset($input['primary_color'])) {
            $color = sanitize_hex_color($input['primary_color']);
            if ($color) {
                $validated['primary_color'] = $color;
            }
        }
        
        return $validated;
    }
    
    /**
     * Enqueue admin styles
     */
    public function admin_styles($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'lebonresto') !== false || get_current_screen()->post_type === 'restaurant') {
            $options = $this->get_options();
            $primary_color = $options['primary_color'];
            
            wp_add_inline_style('wp-admin', "
                .toplevel_page_lebonresto .wp-menu-image:before,
                .menu-icon-restaurant .wp-menu-image:before {
                    color: {$primary_color} !important;
                }
                
                .lebonresto-admin-dashboard .postbox-header h2,
                .wrap h1 {
                    color: {$primary_color} !important;
                }
                
                .button-primary {
                    background: {$primary_color} !important;
                    border-color: {$primary_color} !important;
                }
                
                .button-primary:hover {
                    background: " . $this->darken_color($primary_color, 10) . " !important;
                    border-color: " . $this->darken_color($primary_color, 10) . " !important;
                }
            ");
        }
    }
    
    /**
     * Add frontend custom CSS
     */
    public function frontend_custom_css() {
        $options = $this->get_options();
        $primary_color = $options['primary_color'];
        
        echo "<style type='text/css'>
            :root {
                --lebonresto-primary-color: {$primary_color};
            }
            
            .lebonresto-search-form button[type='submit'],
            .lebonresto-popup a,
            h1, h2, h3 {
                background-color: {$primary_color} !important;
            }
            
            .lebonresto-search-form input:focus,
            .lebonresto-search-form select:focus {
                border-color: {$primary_color} !important;
                box-shadow: 0 0 0 1px {$primary_color} !important;
            }
        </style>";
    }
    
    /**
     * Helper function to darken a color
     */
    private function darken_color($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = max(0, min(255, $r - ($r * $percent / 100)));
        $g = max(0, min(255, $g - ($g * $percent / 100)));
        $b = max(0, min(255, $b - ($b * $percent / 100)));
        
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
    
    /**
     * Add custom columns to restaurant admin list
     */
    public function add_restaurant_columns($columns) {
        $new_columns = array();
        
        // Keep checkbox and title
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        
        // Add our custom columns
        $new_columns['city'] = __('City', 'le-bon-resto');
        $new_columns['cuisine_type'] = __('Cuisine Type', 'le-bon-resto');
        $new_columns['phone'] = __('Phone', 'le-bon-resto');
        $new_columns['email'] = __('Email', 'le-bon-resto');
        $new_columns['coordinates'] = __('Coordinates', 'le-bon-resto');
        $new_columns['principal_image'] = __('Principal Image', 'le-bon-resto');
        $new_columns['media'] = __('Media', 'le-bon-resto');
        
        // Add remaining default columns
        if (isset($columns['date'])) {
            $new_columns['date'] = $columns['date'];
        }
        
        return $new_columns;
    }
    
    /**
     * Display content for custom columns
     */
    public function restaurant_column_content($column, $post_id) {
        switch ($column) {
            case 'city':
                $city = get_post_meta($post_id, '_restaurant_city', true);
                echo $city ? esc_html($city) : '—';
                break;
                
            case 'cuisine_type':
                $cuisine = get_post_meta($post_id, '_restaurant_cuisine_type', true);
                if ($cuisine) {
                    echo '<span class="cuisine-badge" style="background: #FFC107; color: #000; padding: 2px 8px; border-radius: 3px; font-size: 11px;">' . esc_html(ucfirst($cuisine)) . '</span>';
                } else {
                    echo '—';
                }
                break;
                
            case 'phone':
                $phone = get_post_meta($post_id, '_restaurant_phone', true);
                if ($phone) {
                    echo '<a href="tel:' . esc_attr($phone) . '" style="color: #0073aa; text-decoration: none;">' . esc_html($phone) . '</a>';
                } else {
                    echo '—';
                }
                break;
                
            case 'email':
                $email = get_post_meta($post_id, '_restaurant_email', true);
                if ($email) {
                    echo '<a href="mailto:' . esc_attr($email) . '" style="color: #0073aa; text-decoration: none;">' . esc_html($email) . '</a>';
                } else {
                    echo '—';
                }
                break;
                
            case 'coordinates':
                $lat = get_post_meta($post_id, '_restaurant_latitude', true);
                $lng = get_post_meta($post_id, '_restaurant_longitude', true);
                if ($lat && $lng) {
                    echo '<small>' . number_format(floatval($lat), 4) . ', ' . number_format(floatval($lng), 4) . '</small>';
                } else {
                    echo '<span style="color: #d63638;">⚠ ' . __('Missing', 'le-bon-resto') . '</span>';
                }
                break;
                
            case 'principal_image':
                $principal_image_id = get_post_meta($post_id, '_restaurant_principal_image', true);
                if ($principal_image_id) {
                    $image_url = wp_get_attachment_image_url($principal_image_id, 'thumbnail');
                    if ($image_url) {
                        echo '<img src="' . esc_url($image_url) . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;" />';
                    }
                } else {
                    echo '<span style="color: #d63638;">⚠ ' . __('Missing', 'le-bon-resto') . '</span>';
                }
                break;
                
            case 'media':
                $principal_image = get_post_meta($post_id, '_restaurant_principal_image', true);
                $gallery = get_post_meta($post_id, '_restaurant_gallery', true);
                $video = get_post_meta($post_id, '_restaurant_video_url', true);
                $tour = get_post_meta($post_id, '_restaurant_virtual_tour_url', true);
                
                $media_items = array();
                if ($principal_image) {
                    $media_items[] = '🖼️';
                }
                if ($gallery) {
                    $image_count = count(explode(',', $gallery));
                    $media_items[] = '📷 ' . $image_count;
                }
                if ($video) {
                    $media_items[] = '🎥';
                }
                if ($tour) {
                    $media_items[] = '🏛️';
                }
                
                echo $media_items ? implode(' ', $media_items) : '—';
                break;
        }
    }
    
    /**
     * Make columns sortable
     */
    public function restaurant_sortable_columns($columns) {
        $columns['city'] = 'city';
        $columns['cuisine_type'] = 'cuisine_type';
        return $columns;
    }
    
    /**
     * Handle custom column sorting
     */
    public function restaurant_orderby($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        $orderby = $query->get('orderby');
        
        if ($orderby === 'city') {
            $query->set('meta_key', '_restaurant_city');
            $query->set('orderby', 'meta_value');
        }
        
        if ($orderby === 'cuisine_type') {
            $query->set('meta_key', '_restaurant_cuisine_type');
            $query->set('orderby', 'meta_value');
        }
    }
    
    /**
     * Add search functionality for custom fields
     */
    public function restaurant_search_where($where) {
        global $pagenow, $wpdb;
        
        if (is_admin() && $pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'restaurant' && isset($_GET['s']) && !empty($_GET['s'])) {
            $search = $_GET['s'];
            $where .= " OR EXISTS (
                SELECT * FROM {$wpdb->postmeta} 
                WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID 
                AND (
                    ({$wpdb->postmeta}.meta_key = '_restaurant_city' AND {$wpdb->postmeta}.meta_value LIKE '%{$search}%') OR
                    ({$wpdb->postmeta}.meta_key = '_restaurant_cuisine_type' AND {$wpdb->postmeta}.meta_value LIKE '%{$search}%') OR
                    ({$wpdb->postmeta}.meta_key = '_restaurant_address' AND {$wpdb->postmeta}.meta_value LIKE '%{$search}%')
                )
            )";
        }
        
        return $where;
    }
}

// Initialize the admin class
new LeBonResto_Admin();
