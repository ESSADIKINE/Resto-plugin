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
        
        // Add rewrite rules for details pages - high priority
        add_action('init', array($this, 'add_rewrite_rules'), 5);
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_details_redirect'));
        
        // Prevent WordPress from redirecting /all to other URLs
        add_filter('redirect_canonical', array($this, 'prevent_all_redirect'), 10, 2);
        
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
        require_once LEBONRESTO_PLUGIN_PATH . 'includes/email-handler.php';
        require_once LEBONRESTO_PLUGIN_PATH . 'includes/seo-meta.php';
        require_once LEBONRESTO_PLUGIN_PATH . 'includes/seo-advanced.php';
        require_once LEBONRESTO_PLUGIN_PATH . 'includes/html-optimization.php';
        require_once LEBONRESTO_PLUGIN_PATH . 'includes/seo-hooks.php';
        require_once LEBONRESTO_PLUGIN_PATH . 'includes/performance-optimization.php';
        
        // Load admin interface if in admin
        if (is_admin()) {
            require_once LEBONRESTO_PLUGIN_PATH . 'includes/admin.php';
        }
    }
    
    /**
     * Add rewrite rules for details pages and all restaurants page
     */
    public function add_rewrite_rules() {
        // Details page rule
        add_rewrite_rule(
            '^details/([^/]+)/?$',
            'index.php?restaurant_slug=$matches[1]',
            'top'
        );
        
        // All restaurants page rule - high priority to override any page redirects
        add_rewrite_rule(
            '^all/?$',
            'index.php?all_restaurants=1',
            'top'
        );
        
        // Prevent redirect from /all to other URLs
        add_rewrite_rule(
            '^all$',
            'index.php?all_restaurants=1',
            'top'
        );
    }
    
    /**
     * Force add rewrite rules (backup method)
     */
    public function force_add_rewrite_rules() {
        global $wp_rewrite;
        
        // Add the details rule directly to the rewrite rules array
        $wp_rewrite->add_rule(
            '^details/([^/]+)/?$',
            'index.php?restaurant_slug=$matches[1]',
            'top'
        );
        
        // Add the all restaurants rule directly to the rewrite rules array
        $wp_rewrite->add_rule(
            '^all/?$',
            'index.php?all_restaurants=1',
            'top'
        );
        
        // Also add without trailing slash to prevent redirects
        $wp_rewrite->add_rule(
            '^all$',
            'index.php?all_restaurants=1',
            'top'
        );
    }
    
    /**
     * Add custom query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'restaurant_slug';
        $vars[] = 'all_restaurants';
        return $vars;
    }
    
    /**
     * Handle details page redirect and all restaurants page
     */
    public function handle_details_redirect() {
        $restaurant_slug = get_query_var('restaurant_slug');
        $all_restaurants = get_query_var('all_restaurants');
        
        // Handle all restaurants page
        if ($all_restaurants) {
            // Set up the query for all restaurants page
            global $wp_query;
            $wp_query->is_single = false;
            $wp_query->is_singular = false;
            $wp_query->is_page = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_search = false;
            $wp_query->is_404 = false;
            
            // Load the all restaurants template
            $template_path = LEBONRESTO_PLUGIN_PATH . 'templates/all-restaurants.php';
            if (file_exists($template_path)) {
                include $template_path;
                exit;
            }
        }
        
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
                global $post, $wp_query;
                $post = $restaurant[0];
                setup_postdata($post);
                
                // Set up the query to make have_posts() work
                $wp_query->is_single = true;
                $wp_query->is_singular = true;
                $wp_query->is_page = false;
                $wp_query->is_home = false;
                $wp_query->is_archive = false;
                $wp_query->is_search = false;
                $wp_query->is_404 = false;
                $wp_query->posts = array($post);
                $wp_query->post_count = 1;
                $wp_query->current_post = -1;
                $wp_query->in_the_loop = false;
                
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
            // Force add rewrite rules first
            $this->add_rewrite_rules();
            $this->force_add_rewrite_rules();
            
            // Then flush
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
     * Prevent WordPress from redirecting /all to other URLs
     */
    public function prevent_all_redirect($redirect_url, $requested_url) {
        // If the requested URL is /all, don't redirect it
        if (strpos($requested_url, '/all') !== false) {
            return false;
        }
        return $redirect_url;
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
        
        // Create the all restaurants page if it doesn't exist
        if (function_exists('lebonresto_create_all_restaurants_page_now')) {
            lebonresto_create_all_restaurants_page_now();
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
