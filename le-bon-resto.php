<?php
/**
 * Plugin Name: Le Bon Resto
 * Description: A WordPress plugin for managing restaurants with map integration using OpenStreetMap and Leaflet.js
 * Version: 1.4.0
 * Author: Your Name
 * Text Domain: le-bon-resto
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LEBONRESTO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LEBONRESTO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LEBONRESTO_PLUGIN_VERSION', '1.4.0');

/**
 * Main plugin class
 */
class LeBonResto {
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin functionality
     */
    public function init() {
        // Load plugin files
        $this->load_includes();
        
        // Load text domain for translations
        load_plugin_textdomain('le-bon-resto', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Force register post type immediately if not already done
        if (!post_type_exists('restaurant')) {
            if (function_exists('lebonresto_register_restaurant_cpt')) {
                lebonresto_register_restaurant_cpt();
            }
        }
        
        // Add rewrite rules for details pages
        add_action('init', array($this, 'add_rewrite_rules'), 20);
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_details_redirect'));
        
        // Force add rewrite rules on every init
        add_action('init', array($this, 'force_add_rewrite_rules'), 30);
        
        // Add admin notice for rewrite rules
        add_action('admin_notices', array($this, 'rewrite_rules_notice'));
        
        // Add admin action to flush rewrite rules
        add_action('admin_action_lebonresto_flush_rewrite_rules', array($this, 'flush_rewrite_rules_action'));
        
        // Add debug function for testing
        add_action('wp_ajax_lebonresto_test_routing', array($this, 'test_routing'));
    }
    
    /**
     * Load required files
     */
    private function load_includes() {
        require_once LEBONRESTO_PLUGIN_PATH . 'includes/cpt.php';
        require_once LEBONRESTO_PLUGIN_PATH . 'includes/scripts.php';
        require_once LEBONRESTO_PLUGIN_PATH . 'includes/shortcodes.php';
        require_once LEBONRESTO_PLUGIN_PATH . 'includes/api.php';
        require_once LEBONRESTO_PLUGIN_PATH . 'includes/templates.php';
        
        // Load admin interface if in admin
        if (is_admin()) {
            require_once LEBONRESTO_PLUGIN_PATH . 'includes/admin.php';
        }
    }
    
    /**
     * Add rewrite rules for details pages
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^details/([^/]+)/?$',
            'index.php?restaurant_slug=$matches[1]',
            'top'
        );
    }
    
    /**
     * Force add rewrite rules (backup method)
     */
    public function force_add_rewrite_rules() {
        global $wp_rewrite;
        
        // Add the rule directly to the rewrite rules array
        $wp_rewrite->add_rule(
            '^details/([^/]+)/?$',
            'index.php?restaurant_slug=$matches[1]',
            'top'
        );
    }
    
    /**
     * Add custom query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'restaurant_slug';
        return $vars;
    }
    
    /**
     * Handle details page redirect
     */
    public function handle_details_redirect() {
        $restaurant_slug = get_query_var('restaurant_slug');
        
        if ($restaurant_slug) {
            // First try exact slug match
            $restaurant = get_posts(array(
                'post_type' => 'restaurant',
                'name' => $restaurant_slug,
                'posts_per_page' => 1,
                'post_status' => 'publish'
            ));
            
            // If no exact match, try to find by title similarity
            if (empty($restaurant)) {
                $restaurants = get_posts(array(
                    'post_type' => 'restaurant',
                    'posts_per_page' => -1,
                    'post_status' => 'publish'
                ));
                
                foreach ($restaurants as $rest) {
                    $title_slug = sanitize_title($rest->post_title);
                    if ($title_slug === $restaurant_slug) {
                        $restaurant = array($rest);
                        break;
                    }
                }
            }
            
            if (!empty($restaurant)) {
                // Set up the post data
                global $post;
                $post = $restaurant[0];
                setup_postdata($post);
                
                // Load the restaurant detail template
                $template_path = LEBONRESTO_PLUGIN_PATH . 'templates/restaurant-detail.php';
                if (file_exists($template_path)) {
                    include $template_path;
                    exit;
                }
            } else {
                // Restaurant not found, show 404
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                get_template_part('404');
                exit;
            }
        }
    }
    
    /**
     * Admin notice for rewrite rules
     */
    public function rewrite_rules_notice() {
        if (current_user_can('manage_options') && get_transient('lebonresto_rewrite_notice')) {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p><strong>Le Bon Resto:</strong> ' . __('Rewrite rules have been updated. Details pages should now work correctly.', 'le-bon-resto') . '</p>';
            echo '</div>';
            delete_transient('lebonresto_rewrite_notice');
        }
    }
    
    /**
     * Flush rewrite rules action
     */
    public function flush_rewrite_rules_action() {
        if (current_user_can('manage_options')) {
            flush_rewrite_rules();
            set_transient('lebonresto_rewrite_notice', true, 30);
            wp_redirect(admin_url('edit.php?post_type=restaurant&rewrite_flushed=1'));
            exit;
        }
    }
    
    /**
     * Test routing function
     */
    public function test_routing() {
        if (current_user_can('manage_options')) {
            $restaurants = get_posts(array(
                'post_type' => 'restaurant',
                'posts_per_page' => 5,
                'post_status' => 'publish'
            ));
            
            $results = array();
            foreach ($restaurants as $restaurant) {
                $slug = sanitize_title($restaurant->post_title);
                $results[] = array(
                    'id' => $restaurant->ID,
                    'title' => $restaurant->post_title,
                    'slug' => $slug,
                    'url' => home_url('/details/' . $slug . '/')
                );
            }
            
            wp_send_json_success($results);
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Make sure CPT is registered before flushing
        $this->load_includes();
        
        // Register the post type immediately
        if (function_exists('lebonresto_register_restaurant_cpt')) {
            lebonresto_register_restaurant_cpt();
        }
        
        // Flush rewrite rules to ensure custom post type URLs work
        flush_rewrite_rules();
        
        // Set a flag to show activation success
        set_transient('lebonresto_activation_notice', true, 30);
        set_transient('lebonresto_rewrite_notice', true, 30);
        
        // Trigger custom activation hook
        do_action('lebonresto_plugin_activated');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules on deactivation
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new LeBonResto();
