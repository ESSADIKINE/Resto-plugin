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
