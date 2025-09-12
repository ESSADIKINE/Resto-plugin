<?php
/**
 * Performance Optimization for Le Bon Resto Plugin
 * 
 * @package LeBonResto
 */

if (!defined('ABSPATH')) {
    exit;
}

class LeBonResto_Performance_Optimization {
    
    private $plugin_version;
    
    public function __construct() {
        $this->plugin_version = LEBONRESTO_PLUGIN_VERSION;
        
        // Initialize performance optimizations
        add_action('init', array($this, 'init_performance_optimizations'));
        add_action('wp_enqueue_scripts', array($this, 'optimize_script_loading'));
        add_action('wp_head', array($this, 'add_critical_css'), 1);
        add_action('wp_footer', array($this, 'add_deferred_scripts'));
        
        // Image optimization
        add_filter('wp_get_attachment_image_attributes', array($this, 'optimize_image_loading'), 10, 3);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_webp_support'));
        
        // Database optimization
        add_action('wp_ajax_lebonresto_cleanup_cache', array($this, 'cleanup_cache'));
        add_action('wp_ajax_nopriv_lebonresto_cleanup_cache', array($this, 'cleanup_cache'));
        
        // Caching
        add_action('save_post', array($this, 'clear_restaurant_cache'), 10, 2);
        add_action('delete_post', array($this, 'clear_restaurant_cache'), 10, 2);
    }
    
    /**
     * Initialize performance optimizations
     */
    public function init_performance_optimizations() {
        // Add resource hints
        add_action('wp_head', array($this, 'add_resource_hints'), 1);
        
        // Optimize database queries
        add_action('pre_get_posts', array($this, 'optimize_restaurant_queries'));
        
        // Add compression headers
        add_action('init', array($this, 'add_compression_headers'));
    }
    
    /**
     * Add resource hints for better performance
     */
    public function add_resource_hints() {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return;
        }
        
        echo '<!-- Resource Hints -->' . "\n";
        
        // DNS prefetch
        echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//unpkg.com">' . "\n";
        
        // Preconnect
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>' . "\n";
        
        // Preload critical resources
        echo '<link rel="preload" href="' . LEBONRESTO_PLUGIN_URL . 'assets/css/restaurant-detail.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
        echo '<link rel="preload" href="' . LEBONRESTO_PLUGIN_URL . 'assets/js/restaurant-detail.js" as="script">' . "\n";
        
        // Prefetch next page
        $this->add_prefetch_hints();
    }
    
    /**
     * Add prefetch hints for next page
     */
    private function add_prefetch_hints() {
        if (is_singular('restaurant')) {
            // Prefetch related restaurants
            $related_restaurants = $this->get_related_restaurants();
            foreach ($related_restaurants as $restaurant) {
                echo '<link rel="prefetch" href="' . get_permalink($restaurant->ID) . '">' . "\n";
            }
        }
    }
    
    /**
     * Get related restaurants
     */
    private function get_related_restaurants() {
        $restaurant_id = get_the_ID();
        $cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true);
        
        $args = array(
            'post_type' => 'restaurant',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'post__not_in' => array($restaurant_id),
            'meta_query' => array(
                array(
                    'key' => '_restaurant_cuisine_type',
                    'value' => $cuisine_type,
                    'compare' => '='
                )
            )
        );
        
        return get_posts($args);
    }
    
    /**
     * Optimize script loading
     */
    public function optimize_script_loading() {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return;
        }
        
        // Defer non-critical scripts
        add_filter('script_loader_tag', array($this, 'defer_scripts'), 10, 2);
        
        // Load scripts conditionally
        $this->load_conditional_scripts();
    }
    
    /**
     * Defer scripts for better performance
     */
    public function defer_scripts($tag, $handle) {
        $defer_scripts = array(
            'lebonresto-lazy-loading',
            'lebonresto-all-restaurants',
            'lebonresto-detail-js',
            'leaflet-js',
            'bootstrap-js'
        );
        
        if (in_array($handle, $defer_scripts)) {
            return str_replace('<script ', '<script defer ', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Load conditional scripts
     */
    private function load_conditional_scripts() {
        // Only load map scripts if coordinates are available
        if (is_singular('restaurant')) {
            $restaurant_id = get_the_ID();
            $latitude = get_post_meta($restaurant_id, '_restaurant_latitude', true);
            $longitude = get_post_meta($restaurant_id, '_restaurant_longitude', true);
            
            if (!$latitude || !$longitude) {
                wp_dequeue_script('leaflet-js');
                wp_dequeue_style('leaflet-css');
            }
        }
    }
    
    /**
     * Add critical CSS
     */
    public function add_critical_css() {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return;
        }
        
        $critical_css = $this->get_critical_css();
        if ($critical_css) {
            echo '<style id="lebonresto-critical-css">' . $critical_css . '</style>' . "\n";
        }
    }
    
    /**
     * Get critical CSS
     */
    private function get_critical_css() {
        $critical_css = '';
        
        if (is_singular('restaurant')) {
            $critical_css = $this->get_restaurant_critical_css();
        } elseif (is_page_template('templates/all-restaurants.php')) {
            $critical_css = $this->get_all_restaurants_critical_css();
        }
        
        return apply_filters('lebonresto_critical_css', $critical_css);
    }
    
    /**
     * Get restaurant critical CSS
     */
    private function get_restaurant_critical_css() {
        return '
        .restaurant-card {
            display: block;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .restaurant-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .restaurant-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .restaurant-content {
            padding: 20px;
        }
        
        .restaurant-actions {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #fedc00;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #e6c600;
        }
        ';
    }
    
    /**
     * Get all restaurants critical CSS
     */
    private function get_all_restaurants_critical_css() {
        return '
        .restaurants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .restaurant-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .restaurant-card:hover {
            transform: translateY(-2px);
        }
        
        .restaurant-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .restaurant-info {
            padding: 20px;
        }
        
        .restaurant-name {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .restaurant-description {
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .restaurant-actions {
            display: flex;
            gap: 10px;
        }
        ';
    }
    
    /**
     * Add deferred scripts
     */
    public function add_deferred_scripts() {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return;
        }
        
        ?>
        <script>
        // Load non-critical CSS
        function loadCSS(href) {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            document.head.appendChild(link);
        }
        
        // Load CSS after page load
        window.addEventListener('load', function() {
            loadCSS('<?php echo LEBONRESTO_PLUGIN_URL; ?>assets/css/restaurant-detail.css');
            loadCSS('<?php echo LEBONRESTO_PLUGIN_URL; ?>assets/css/all-restaurants.css');
        });
        
        // Preload next page
        function preloadNextPage() {
            var links = document.querySelectorAll('a[href*="/restaurant/"]');
            links.forEach(function(link) {
                link.addEventListener('mouseenter', function() {
                    var prefetchLink = document.createElement('link');
                    prefetchLink.rel = 'prefetch';
                    prefetchLink.href = this.href;
                    document.head.appendChild(prefetchLink);
                });
            });
        }
        
        // Initialize preloading
        document.addEventListener('DOMContentLoaded', preloadNextPage);
        </script>
        <?php
    }
    
    /**
     * Optimize image loading
     */
    public function optimize_image_loading($attr, $attachment, $size) {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return $attr;
        }
        
        // Add lazy loading
        $attr['loading'] = 'lazy';
        
        // Add decoding
        $attr['decoding'] = 'async';
        
        // Optimize alt text
        if (empty($attr['alt'])) {
            $restaurant_name = get_the_title();
            $attr['alt'] = "Image du restaurant {$restaurant_name} à Casablanca, Maroc";
        }
        
        // Add WebP support
        $attr['data-webp'] = 'true';
        
        return $attr;
    }
    
    /**
     * Enqueue WebP support
     */
    public function enqueue_webp_support() {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return;
        }
        
        wp_enqueue_script(
            'lebonresto-webp-support',
            LEBONRESTO_PLUGIN_URL . 'assets/js/webp-support.js',
            array(),
            $this->plugin_version,
            true
        );
    }
    
    /**
     * Optimize restaurant queries
     */
    public function optimize_restaurant_queries($query) {
        if (!is_admin() && $query->is_main_query()) {
            if (is_post_type_archive('restaurant') || is_tax('cuisine_type')) {
                // Optimize restaurant queries
                $query->set('posts_per_page', 12);
                $query->set('meta_query', array(
                    array(
                        'key' => '_seo_noindex',
                        'value' => '1',
                        'compare' => '!='
                    )
                ));
            }
        }
    }
    
    /**
     * Add compression headers
     */
    public function add_compression_headers() {
        if (!is_admin() && !headers_sent()) {
            // Enable GZIP compression
            if (extension_loaded('zlib') && !ob_get_level()) {
                ob_start('ob_gzhandler');
            }
        }
    }
    
    /**
     * Clear restaurant cache
     */
    public function clear_restaurant_cache($post_id, $post) {
        if ($post->post_type === 'restaurant') {
            // Clear any cached data
            wp_cache_delete($post_id, 'restaurant_data');
            wp_cache_delete('restaurant_sitemap', 'lebonresto');
            wp_cache_delete('restaurant_structured_data_' . $post_id, 'lebonresto');
        }
    }
    
    /**
     * Cleanup cache
     */
    public function cleanup_cache() {
        // Clear all plugin caches
        wp_cache_flush();
        
        // Clear object cache
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group('lebonresto');
        }
        
        wp_die('Cache cleared successfully');
    }
    
    /**
     * Get performance metrics
     */
    public static function get_performance_metrics() {
        $metrics = array(
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'database_queries' => get_num_queries(),
            'loaded_scripts' => count(wp_scripts()->done),
            'loaded_styles' => count(wp_styles()->done)
        );
        
        return $metrics;
    }
    
    /**
     * Add performance monitoring
     */
    public function add_performance_monitoring() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_action('wp_footer', function() {
                $metrics = self::get_performance_metrics();
                echo '<!-- Performance Metrics -->' . "\n";
                echo '<!-- Memory Usage: ' . size_format($metrics['memory_usage']) . ' -->' . "\n";
                echo '<!-- Peak Memory: ' . size_format($metrics['peak_memory']) . ' -->' . "\n";
                echo '<!-- Execution Time: ' . round($metrics['execution_time'], 4) . 's -->' . "\n";
                echo '<!-- Database Queries: ' . $metrics['database_queries'] . ' -->' . "\n";
            });
        }
    }
}

// Initialize performance optimization
new LeBonResto_Performance_Optimization();
