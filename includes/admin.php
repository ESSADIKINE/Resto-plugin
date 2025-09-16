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
        
        // Import/Export functionality
        add_action('admin_init', array($this, 'handle_import_export'));
        
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
        
        // Submenu - Import/Export
        add_submenu_page(
            'lebonresto',
            __('Import/Export', 'le-bon-resto'),
            __('Import/Export', 'le-bon-resto'),
            'manage_options',
            'lebonresto-import-export',
            array($this, 'admin_page_import_export')
        );
        
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
                                    <div style="font-size: 36px; font-weight: bold; color: #fedc00;"><?php echo $published_count; ?></div>
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
            color: #fedc00;
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
            color: #fedc00;
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
        
        // Google Maps API Key
        add_settings_field(
            'google_maps_api_key',
            __('Google Maps API Key', 'le-bon-resto'),
            array($this, 'setting_field_google_maps_api_key'),
            'lebonresto_settings',
            'lebonresto_general'
        );
        
        // Currency Setting
        add_settings_field(
            'currency',
            __('Currency', 'le-bon-resto'),
            array($this, 'setting_field_currency'),
            'lebonresto_settings',
            'lebonresto_general'
        );
        
        // Restaurant Options Management
        add_settings_field(
            'restaurant_options',
            __('Restaurant Options', 'le-bon-resto'),
            array($this, 'setting_field_restaurant_options'),
            'lebonresto_settings',
            'lebonresto_general'
        );
        
        
        
        // Add admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
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
            'primary_color' => '#fedc00',
            'google_maps_api_key' => 'AIzaSyDXSSijLxRtL9tz7FbYqvnB3eWwTojpNlI',
            'currency' => 'MAD',
            'restaurant_options' => array(
                'Accès PMR (Personnes à Mobilité Réduite)',
                'Chauffage',
                'Climatisation',
                'Équipements écologiques',
                'Parking gratuit',
                'Proximité avec les transports en commun',
                'Salle à manger privée',
                'Salle de réception privatisable',
                'Salles insonorisées',
                'Système de ventilation efficace',
                'Wi-Fi gratuit'
            )
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
    
    public function setting_field_google_maps_api_key() {
        $options = $this->get_options();
        $api_key = isset($options['google_maps_api_key']) ? $options['google_maps_api_key'] : '';
        $default_api_key = 'AIzaSyDXSSijLxRtL9tz7FbYqvnB3eWwTojpNlI';
        
        echo '<input type="text" name="lebonresto_options[google_maps_api_key]" value="' . esc_attr($api_key) . '" class="regular-text" placeholder="' . esc_attr($default_api_key) . '" />';
        echo '<p class="description">' . __('Google Maps API key with Places API enabled. Used to automatically fetch restaurant reviews and ratings.', 'le-bon-resto') . '</p>';
        
        // Show current status
        $current_key = $api_key ?: $default_api_key;
        echo '<div style="background: #e7f3ff; padding: 1rem; border-radius: 4px; margin-top: 0.5rem; border-left: 4px solid #2196F3;">';
        echo '<strong>' . __('Current API Key:', 'le-bon-resto') . '</strong> ' . esc_html($current_key);
        echo '<br><small>' . __('This key is used to automatically fetch Google reviews for restaurants.', 'le-bon-resto') . '</small>';
        echo '</div>';
    }
    
    public function setting_field_currency() {
        $options = $this->get_options();
        $currency = isset($options['currency']) ? $options['currency'] : 'MAD';
        
        echo '<select name="lebonresto_options[currency]" id="currency">';
        echo '<option value="MAD" ' . selected($currency, 'MAD', false) . '>MAD - Moroccan Dirham (د.م.)</option>';
        echo '<option value="EUR" ' . selected($currency, 'EUR', false) . '>EUR - Euro (€)</option>';
        echo '<option value="USD" ' . selected($currency, 'USD', false) . '>USD - US Dollar ($)</option>';
        echo '</select>';
        echo '<p class="description">' . __('Select the currency to display for restaurant prices.', 'le-bon-resto') . '</p>';
        
        // Show currency symbols
        echo '<div style="background: #f0f0f1; padding: 1rem; border-radius: 4px; margin-top: 0.5rem;">';
        echo '<strong>' . __('Currency Symbols:', 'le-bon-resto') . '</strong><br>';
        echo '<span style="margin-right: 1rem;"><strong>MAD:</strong> د.م. (Moroccan Dirham)</span>';
        echo '<span style="margin-right: 1rem;"><strong>EUR:</strong> € (Euro)</span>';
        echo '<span><strong>USD:</strong> $ (US Dollar)</span>';
        echo '</div>';
    }
    
    public function setting_field_restaurant_options() {
        $options = $this->get_options();
        $restaurant_options = isset($options['restaurant_options']) ? $options['restaurant_options'] : array();
        
        echo '<div id="restaurant-options-container">';
        echo '<p class="description">' . __('Add or remove restaurant options that will be available for selection in the restaurant edit form.', 'le-bon-resto') . '</p>';
        
        if (!empty($restaurant_options)) {
            foreach ($restaurant_options as $index => $option) {
                echo '<div class="restaurant-option-row" style="margin-bottom: 10px; display: flex; align-items: center;">';
                echo '<input type="text" name="lebonresto_options[restaurant_options][]" value="' . esc_attr($option) . '" class="regular-text" style="margin-right: 10px;" />';
                echo '<button type="button" class="button remove-option" style="color: #d63638;">' . __('Remove', 'le-bon-resto') . '</button>';
                echo '</div>';
            }
        }
        
        echo '<div class="restaurant-option-row" style="margin-bottom: 10px; display: flex; align-items: center;">';
        echo '<input type="text" name="lebonresto_options[restaurant_options][]" value="" class="regular-text" style="margin-right: 10px;" placeholder="' . __('Enter new option...', 'le-bon-resto') . '" />';
        echo '<button type="button" class="button remove-option" style="color: #d63638;">' . __('Remove', 'le-bon-resto') . '</button>';
        echo '</div>';
        
        echo '<button type="button" id="add-restaurant-option" class="button button-secondary">' . __('Add New Option', 'le-bon-resto') . '</button>';
        echo '</div>';
        
        echo '<script>
        jQuery(document).ready(function($) {
            $("#add-restaurant-option").click(function() {
                var newRow = $("<div class=\"restaurant-option-row\" style=\"margin-bottom: 10px; display: flex; align-items: center;\">" +
                    "<input type=\"text\" name=\"lebonresto_options[restaurant_options][]\" value=\"\" class=\"regular-text\" style=\"margin-right: 10px;\" placeholder=\"' . __('Enter new option...', 'le-bon-resto') . '\" />" +
                    "<button type=\"button\" class=\"button remove-option\" style=\"color: #d63638;\">' . __('Remove', 'le-bon-resto') . '</button>" +
                    "</div>");
                $("#restaurant-options-container").append(newRow);
            });
            
            $(document).on("click", ".remove-option", function() {
                $(this).closest(".restaurant-option-row").remove();
            });
        });
        </script>';
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
        
        // Validate restaurant options
        if (isset($input['restaurant_options']) && is_array($input['restaurant_options'])) {
            $validated_options = array();
            foreach ($input['restaurant_options'] as $option) {
                $option = sanitize_text_field($option);
                if (!empty($option)) {
                    $validated_options[] = $option;
                }
            }
            $validated['restaurant_options'] = $validated_options;
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
                
                .restaurant-options-container {
                    max-height: 300px;
                    overflow-y: auto;
                    border: 1px solid #ddd;
                    padding: 15px;
                    border-radius: 4px;
                    background: #f9f9f9;
                }
                .restaurant-options-container label {
                    display: flex;
                    align-items: center;
                    padding: 8px 0;
                    border-bottom: 1px solid #eee;
                    margin-bottom: 0;
                }
                .restaurant-options-container label:last-child {
                    border-bottom: none;
                }
                .restaurant-options-container input[type='checkbox'] {
                    margin-right: 10px;
                    transform: scale(1.2);
                }
                .restaurant-options-container label:hover {
                    background: #f0f0f0;
                    padding-left: 5px;
                    border-radius: 3px;
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
                    echo '<span class="cuisine-badge" style="background: #fedc00; color: #000; padding: 2px 8px; border-radius: 3px; font-size: 11px;">' . esc_html(ucfirst($cuisine)) . '</span>';
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
                
                // Principal Image
                if ($principal_image) {
                    $image_url = wp_get_attachment_image_url($principal_image, 'thumbnail');
                    if ($image_url) {
                        $media_items[] = '<img src="' . esc_url($image_url) . '" style="width: 30px; height: 30px; object-fit: cover; border-radius: 3px; border: 1px solid #ddd; margin-right: 5px;" title="Principal Image" />';
                    } else {
                        $media_items[] = '🖼️';
                    }
                }
                
                // Gallery Images
                if ($gallery) {
                    $image_ids = explode(',', $gallery);
                    $valid_images = 0;
                    foreach ($image_ids as $image_id) {
                        if (wp_get_attachment_url($image_id)) {
                            $valid_images++;
                        }
                    }
                    if ($valid_images > 0) {
                        $media_items[] = '<span title="Gallery Images">📷 ' . $valid_images . '</span>';
                    }
                }
                
                // Video
                if ($video) {
                    $media_items[] = '<span title="Video URL">🎥</span>';
                }
                
                // Virtual Tour
                if ($tour) {
                    $media_items[] = '<span title="Virtual Tour">🏛️</span>';
                }
                
                if (empty($media_items)) {
                    echo '<span style="color: #d63638;">⚠ ' . __('No Media', 'le-bon-resto') . '</span>';
                } else {
                    echo '<div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">' . implode('', $media_items) . '</div>';
                }
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
    
    /**
     * Admin page for import/export
     */
    public function admin_page_import_export() {
        ?>
        <div class="wrap">
            <h1><?php _e('Import/Export Restaurant Data', 'le-bon-resto'); ?></h1>
            
            <div class="lebonresto-import-export-container">
                
                <!-- Export Section -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Export Restaurant Data', 'le-bon-resto'); ?></h2>
                    </div>
                    <div class="inside">
                        <p><?php _e('Export all restaurant data to a CSV or JSON file for backup or migration purposes.', 'le-bon-resto'); ?></p>
                        
                        <form method="post" action="">
                            <?php wp_nonce_field('lebonresto_export', 'lebonresto_export_nonce'); ?>
                            <input type="hidden" name="action" value="export_restaurants">
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Export Format', 'le-bon-resto'); ?></th>
                                    <td>
                                        <label>
                                            <input type="radio" name="export_format" value="csv" checked>
                                            <?php _e('CSV (Comma Separated Values)', 'le-bon-resto'); ?>
                                        </label><br>
                                        <label>
                                            <input type="radio" name="export_format" value="json">
                                            <?php _e('JSON (JavaScript Object Notation)', 'le-bon-resto'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Include Media URLs', 'le-bon-resto'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="include_media" value="1" checked>
                                            <?php _e('Include image and media URLs in export', 'le-bon-resto'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Restaurant Status', 'le-bon-resto'); ?></th>
                                    <td>
                                        <label>
                                            <input type="radio" name="restaurant_status" value="all" checked>
                                            <?php _e('All restaurants', 'le-bon-resto'); ?>
                                        </label><br>
                                        <label>
                                            <input type="radio" name="restaurant_status" value="published">
                                            <?php _e('Published only', 'le-bon-resto'); ?>
                                        </label><br>
                                        <label>
                                            <input type="radio" name="restaurant_status" value="draft">
                                            <?php _e('Draft only', 'le-bon-resto'); ?>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                            
                            <p class="submit">
                                <input type="submit" class="button-primary" value="<?php _e('Export Data', 'le-bon-resto'); ?>">
                            </p>
                        </form>
                    </div>
                </div>
                
                <!-- Import Section -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Import Restaurant Data', 'le-bon-resto'); ?></h2>
                    </div>
                    <div class="inside">
                        <p><?php _e('Import restaurant data from a CSV or JSON file. Make sure the file format matches the export format.', 'le-bon-resto'); ?></p>
                        
                        <form method="post" enctype="multipart/form-data" action="">
                            <?php wp_nonce_field('lebonresto_import', 'lebonresto_import_nonce'); ?>
                            <input type="hidden" name="action" value="import_restaurants">
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Import File', 'le-bon-resto'); ?></th>
                                    <td>
                                        <input type="file" name="import_file" accept=".csv,.json" required>
                                        <p class="description"><?php _e('Select a CSV or JSON file to import. Maximum file size: 10MB', 'le-bon-resto'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Import Mode', 'le-bon-resto'); ?></th>
                                    <td>
                                        <label>
                                            <input type="radio" name="import_mode" value="create" checked>
                                            <?php _e('Create new restaurants only', 'le-bon-resto'); ?>
                                        </label><br>
                                        <label>
                                            <input type="radio" name="import_mode" value="update">
                                            <?php _e('Update existing restaurants (by title)', 'le-bon-resto'); ?>
                                        </label><br>
                                        <label>
                                            <input type="radio" name="import_mode" value="replace">
                                            <?php _e('Replace all restaurants (delete existing first)', 'le-bon-resto'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Default Status', 'le-bon-resto'); ?></th>
                                    <td>
                                        <select name="default_status">
                                            <option value="draft"><?php _e('Draft', 'le-bon-resto'); ?></option>
                                            <option value="publish"><?php _e('Published', 'le-bon-resto'); ?></option>
                                        </select>
                                        <p class="description"><?php _e('Status for imported restaurants if not specified in file', 'le-bon-resto'); ?></p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p class="submit">
                                <input type="submit" class="button-primary" value="<?php _e('Import Data', 'le-bon-resto'); ?>">
                            </p>
                        </form>
                    </div>
                </div>
                
                <!-- Sample Files Section -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Sample Files', 'le-bon-resto'); ?></h2>
                    </div>
                    <div class="inside">
                        <p><?php _e('Download sample files to understand the expected format for import:', 'le-bon-resto'); ?></p>
                        
                        <p>
                            <a href="<?php echo admin_url('admin.php?page=lebonresto-import-export&action=download_sample&format=csv'); ?>" class="button">
                                <?php _e('Download Sample CSV', 'le-bon-resto'); ?>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=lebonresto-import-export&action=download_sample&format=json'); ?>" class="button">
                                <?php _e('Download Sample JSON', 'le-bon-resto'); ?>
                            </a>
                        </p>
                    </div>
                </div>
                
            </div>
        </div>
        
        <style>
        .lebonresto-import-export-container .postbox {
            margin-bottom: 20px;
        }
        
        .lebonresto-import-export-container .postbox-header h2 {
            color: #fedc00;
            font-weight: 600;
        }
        
        .lebonresto-import-export-container .form-table th {
            width: 200px;
            padding: 20px 10px 20px 0;
        }
        
        .lebonresto-import-export-container .form-table td {
            padding: 20px 10px;
        }
        
        .lebonresto-import-export-container input[type="file"] {
            width: 100%;
            max-width: 400px;
        }
        </style>
        <?php
    }
    
    /**
     * Handle import/export actions
     */
    public function handle_import_export() {
        // Handle export
        if (isset($_POST['action']) && $_POST['action'] === 'export_restaurants' && 
            wp_verify_nonce($_POST['lebonresto_export_nonce'], 'lebonresto_export')) {
            $this->export_restaurants();
        }
        
        // Handle import
        if (isset($_POST['action']) && $_POST['action'] === 'import_restaurants' && 
            wp_verify_nonce($_POST['lebonresto_import_nonce'], 'lebonresto_import')) {
            $this->import_restaurants();
        }
        
        // Handle sample file download
        if (isset($_GET['action']) && $_GET['action'] === 'download_sample' && 
            isset($_GET['format']) && in_array($_GET['format'], ['csv', 'json'])) {
            $this->download_sample_file($_GET['format']);
        }
    }
    
    /**
     * Export restaurants data
     */
    private function export_restaurants() {
        $format = sanitize_text_field($_POST['export_format']);
        $include_media = isset($_POST['include_media']);
        $status = sanitize_text_field($_POST['restaurant_status']);
        
        // Build query args
        $args = array(
            'post_type' => 'restaurant',
            'posts_per_page' => -1,
            'post_status' => $status === 'all' ? ['publish', 'draft'] : $status
        );
        
        $restaurants = get_posts($args);
        $export_data = array();
        
        foreach ($restaurants as $restaurant) {
            $data = array(
                'title' => $restaurant->post_title,
                'content' => $restaurant->post_content,
                'status' => $restaurant->post_status,
                'date' => $restaurant->post_date,
                'address' => get_post_meta($restaurant->ID, '_restaurant_address', true),
                'city' => get_post_meta($restaurant->ID, '_restaurant_city', true),
                'cuisine_type' => get_post_meta($restaurant->ID, '_restaurant_cuisine_type', true),
                'description' => get_post_meta($restaurant->ID, '_restaurant_description', true),
                'phone' => get_post_meta($restaurant->ID, '_restaurant_phone', true),
                'email' => get_post_meta($restaurant->ID, '_restaurant_email', true),
                'min_price' => get_post_meta($restaurant->ID, '_restaurant_min_price', true),
                'max_price' => get_post_meta($restaurant->ID, '_restaurant_max_price', true),
                'currency' => get_post_meta($restaurant->ID, '_restaurant_currency', true),
                'latitude' => get_post_meta($restaurant->ID, '_restaurant_latitude', true),
                'longitude' => get_post_meta($restaurant->ID, '_restaurant_longitude', true),
                'google_maps_link' => get_post_meta($restaurant->ID, '_restaurant_google_maps_link', true),
                'is_featured' => get_post_meta($restaurant->ID, '_restaurant_is_featured', true),
                'video_url' => get_post_meta($restaurant->ID, '_restaurant_video_url', true),
                'virtual_tour_url' => get_post_meta($restaurant->ID, '_restaurant_virtual_tour_url', true),
                'website_url' => get_post_meta($restaurant->ID, '_restaurant_website_url', true),
                'price_range' => get_post_meta($restaurant->ID, '_restaurant_price_range', true),
                'opening_hours' => get_post_meta($restaurant->ID, '_restaurant_opening_hours', true),
                'restaurant_options' => get_post_meta($restaurant->ID, '_restaurant_options', true),
                'principal_image' => get_post_meta($restaurant->ID, '_restaurant_principal_image', true),
                'gallery' => get_post_meta($restaurant->ID, '_restaurant_gallery', true),
                'blog_title' => get_post_meta($restaurant->ID, '_restaurant_blog_title', true),
                'blog_content' => get_post_meta($restaurant->ID, '_restaurant_blog_content', true),
                'selected_options' => get_post_meta($restaurant->ID, '_restaurant_selected_options', true),
                'menus' => get_post_meta($restaurant->ID, '_restaurant_menus', true),
                'google_place_id' => get_post_meta($restaurant->ID, '_restaurant_google_place_id', true)
            );
            
            if ($include_media) {
                $principal_image = get_post_meta($restaurant->ID, '_restaurant_principal_image', true);
                $gallery = get_post_meta($restaurant->ID, '_restaurant_gallery', true);
                
                if ($principal_image) {
                    $data['principal_image_url'] = wp_get_attachment_url($principal_image);
                }
                
                if ($gallery) {
                    $gallery_urls = array();
                    $image_ids = explode(',', $gallery);
                    foreach ($image_ids as $image_id) {
                        $url = wp_get_attachment_url($image_id);
                        if ($url) {
                            $gallery_urls[] = $url;
                        }
                    }
                    $data['gallery_urls'] = implode(',', $gallery_urls);
                }
            }
            
            $export_data[] = $data;
        }
        
        if ($format === 'csv') {
            $this->export_csv($export_data);
        } else {
            $this->export_json($export_data);
        }
    }
    
    /**
     * Export data as CSV
     */
    private function export_csv($data) {
        $filename = 'restaurants_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Write headers
            fputcsv($output, array_keys($data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export data as JSON
     */
    private function export_json($data) {
        $filename = 'restaurants_export_' . date('Y-m-d_H-i-s') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Import restaurants data
     */
    private function import_restaurants() {
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __('Error uploading file. Please try again.', 'le-bon-resto') . '</p></div>';
            });
            return;
        }
        
        $file = $_FILES['import_file'];
        $import_mode = sanitize_text_field($_POST['import_mode']);
        $default_status = sanitize_text_field($_POST['default_status']);
        
        // Validate file type
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, ['csv', 'json'])) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __('Invalid file type. Please upload a CSV or JSON file.', 'le-bon-resto') . '</p></div>';
            });
            return;
        }
        
        // Read file content
        $content = file_get_contents($file['tmp_name']);
        if ($content === false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __('Error reading file. Please try again.', 'le-bon-resto') . '</p></div>';
            });
            return;
        }
        
        // Parse data based on format
        if ($file_extension === 'csv') {
            $data = $this->parse_csv($content);
        } else {
            $data = json_decode($content, true);
        }
        
        if (!$data || !is_array($data)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __('Invalid file format. Please check your file and try again.', 'le-bon-resto') . '</p></div>';
            });
            return;
        }
        
        // Handle import mode
        if ($import_mode === 'replace') {
            // Delete all existing restaurants
            $existing = get_posts(array(
                'post_type' => 'restaurant',
                'posts_per_page' => -1,
                'post_status' => 'any'
            ));
            
            foreach ($existing as $post) {
                wp_delete_post($post->ID, true);
            }
        }
        
        // Import data
        $imported = 0;
        $updated = 0;
        $errors = 0;
        
        foreach ($data as $row) {
            $result = $this->import_restaurant($row, $import_mode, $default_status);
            
            if ($result === 'imported') {
                $imported++;
            } elseif ($result === 'updated') {
                $updated++;
            } else {
                $errors++;
            }
        }
        
        // Show results
        $message = sprintf(
            __('Import completed: %d imported, %d updated, %d errors', 'le-bon-resto'),
            $imported,
            $updated,
            $errors
        );
        
        add_action('admin_notices', function() use ($message) {
            echo '<div class="notice notice-success"><p>' . $message . '</p></div>';
        });
    }
    
    /**
     * Parse CSV content
     */
    private function parse_csv($content) {
        $lines = str_getcsv($content, "\n");
        $data = array();
        
        if (empty($lines)) {
            return $data;
        }
        
        $headers = str_getcsv($lines[0]);
        
        for ($i = 1; $i < count($lines); $i++) {
            $row = str_getcsv($lines[$i]);
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        
        return $data;
    }
    
    /**
     * Import single restaurant
     */
    private function import_restaurant($data, $import_mode, $default_status) {
        $title = sanitize_text_field($data['title'] ?? '');
        if (empty($title)) {
            return 'error';
        }
        
        // Check if restaurant exists (for update mode)
        $existing_post = null;
        if ($import_mode === 'update') {
            $existing = get_posts(array(
                'post_type' => 'restaurant',
                'name' => sanitize_title($title),
                'posts_per_page' => 1,
                'post_status' => 'any'
            ));
            
            if (!empty($existing)) {
                $existing_post = $existing[0];
            }
        }
        
        // Prepare post data
        $post_data = array(
            'post_title' => $title,
            'post_content' => wp_kses_post($data['content'] ?? ''),
            'post_status' => sanitize_text_field($data['status'] ?? $default_status),
            'post_type' => 'restaurant'
        );
        
        if ($existing_post) {
            $post_data['ID'] = $existing_post->ID;
            $post_id = wp_update_post($post_data);
            $action = 'updated';
        } else {
            $post_id = wp_insert_post($post_data);
            $action = 'imported';
        }
        
        if (is_wp_error($post_id)) {
            return 'error';
        }
        
        // Update meta fields
        $meta_fields = array(
            '_restaurant_address' => 'address',
            '_restaurant_city' => 'city',
            '_restaurant_cuisine_type' => 'cuisine_type',
            '_restaurant_description' => 'description',
            '_restaurant_phone' => 'phone',
            '_restaurant_email' => 'email',
            '_restaurant_min_price' => 'min_price',
            '_restaurant_max_price' => 'max_price',
            '_restaurant_currency' => 'currency',
            '_restaurant_latitude' => 'latitude',
            '_restaurant_longitude' => 'longitude',
            '_restaurant_google_maps_link' => 'google_maps_link',
            '_restaurant_is_featured' => 'is_featured',
            '_restaurant_video_url' => 'video_url',
            '_restaurant_virtual_tour_url' => 'virtual_tour_url',
            '_restaurant_website_url' => 'website_url',
            '_restaurant_price_range' => 'price_range',
            '_restaurant_opening_hours' => 'opening_hours',
            '_restaurant_options' => 'restaurant_options',
            '_restaurant_principal_image' => 'principal_image',
            '_restaurant_gallery' => 'gallery',
            '_restaurant_blog_title' => 'blog_title',
            '_restaurant_blog_content' => 'blog_content',
            '_restaurant_selected_options' => 'selected_options',
            '_restaurant_menus' => 'menus',
            '_restaurant_google_place_id' => 'google_place_id'
        );
        
        foreach ($meta_fields as $meta_key => $data_key) {
            if (isset($data[$data_key])) {
                $value = $data[$data_key];
                
                // Handle different data types
                if (is_array($value)) {
                    // For arrays (like selected_options, menus, gallery)
                    $sanitized_value = array();
                    foreach ($value as $item) {
                        if (is_array($item)) {
                            $sanitized_item = array();
                            foreach ($item as $key => $val) {
                                if ($key === 'file_url') {
                                    $sanitized_item[$key] = esc_url_raw($val);
                                } else {
                                    $sanitized_item[$key] = sanitize_text_field($val);
                                }
                            }
                            $sanitized_value[] = $sanitized_item;
                        } else {
                            $sanitized_value[] = sanitize_text_field($item);
                        }
                    }
                    update_post_meta($post_id, $meta_key, $sanitized_value);
                } elseif (!empty($value)) {
                    // Handle different field types
                    if (in_array($meta_key, ['_restaurant_google_maps_link', '_restaurant_video_url', '_restaurant_virtual_tour_url', '_restaurant_website_url'])) {
                        update_post_meta($post_id, $meta_key, esc_url_raw($value));
                    } elseif ($meta_key === '_restaurant_email') {
                        update_post_meta($post_id, $meta_key, sanitize_email($value));
                    } elseif (in_array($meta_key, ['_restaurant_min_price', '_restaurant_max_price'])) {
                        update_post_meta($post_id, $meta_key, floatval($value));
                    } elseif ($meta_key === '_restaurant_blog_content') {
                        update_post_meta($post_id, $meta_key, wp_kses_post($value));
                    } else {
                        update_post_meta($post_id, $meta_key, sanitize_text_field($value));
                    }
                }
            }
        }
        
        return $action;
    }
    
    /**
     * Download sample file
     */
    private function download_sample_file($format) {
        $sample_data = array(
            array(
                'title' => 'Sample Restaurant 1',
                'content' => 'This is a sample restaurant description.',
                'status' => 'publish',
                'address' => '123 Main Street',
                'city' => 'Paris',
                'cuisine_type' => 'french',
                'description' => 'A wonderful French restaurant',
                'phone' => '+33 1 23 45 67 89',
                'email' => 'contact@samplerestaurant1.com',
                'min_price' => '25.00',
                'max_price' => '65.00',
                'currency' => 'MAD',
                'latitude' => '48.8566',
                'longitude' => '2.3522',
                'google_maps_link' => 'https://maps.google.com/?q=123+Main+Street+Paris',
                'is_featured' => '1',
                'video_url' => 'https://example.com/video1.mp4',
                'virtual_tour_url' => 'https://example.com/tour1',
                'website_url' => 'https://samplerestaurant1.com',
                'price_range' => '€€',
                'opening_hours' => 'Mon-Sun: 12:00-22:00',
                'restaurant_options' => 'Wi-Fi gratuit,Parking gratuit',
                'principal_image' => 'https://example.com/principal1.jpg',
                'gallery' => 'https://example.com/gallery1.jpg,https://example.com/gallery2.jpg',
                'blog_title' => 'Our Restaurant Story',
                'blog_content' => 'This is our restaurant blog content with rich HTML formatting.',
                'selected_options' => 'Wi-Fi gratuit,Parking gratuit,Climatisation',
                'menus' => '[{"name":"Menu Principal","file_id":"123","file_url":"https://example.com/menu1.pdf"}]',
                'google_place_id' => 'ChIJN1t_tDeuEmsRUsoyG83frY4'
            ),
            array(
                'title' => 'Sample Restaurant 2',
                'content' => 'Another sample restaurant description.',
                'status' => 'draft',
                'address' => '456 Oak Avenue',
                'city' => 'Lyon',
                'cuisine_type' => 'italian',
                'description' => 'Authentic Italian cuisine',
                'phone' => '+33 4 12 34 56 78',
                'email' => 'info@samplerestaurant2.com',
                'min_price' => '15.00',
                'max_price' => '35.00',
                'currency' => 'EUR',
                'latitude' => '45.7640',
                'longitude' => '4.8357',
                'google_maps_link' => 'https://maps.google.com/?q=456+Oak+Avenue+Lyon',
                'is_featured' => '0',
                'video_url' => '',
                'virtual_tour_url' => '',
                'website_url' => 'https://samplerestaurant2.com',
                'price_range' => '€€€',
                'opening_hours' => 'Tue-Sat: 19:00-23:00',
                'restaurant_options' => 'Salle à manger privée',
                'principal_image' => '',
                'gallery' => '',
                'blog_title' => '',
                'blog_content' => '',
                'selected_options' => 'Salle à manger privée',
                'menus' => '',
                'google_place_id' => ''
            )
        );
        
        if ($format === 'csv') {
            $filename = 'sample_restaurants.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, array_keys($sample_data[0]));
            foreach ($sample_data as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        } else {
            $filename = 'sample_restaurants.json';
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo json_encode($sample_data, JSON_PRETTY_PRINT);
        }
        
        exit;
    }
}

// Initialize the admin class
new LeBonResto_Admin();
