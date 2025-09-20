<?php
/**
 * Template Functions for Restaurant Detail Pages
 * 
 * @package LeBonResto
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load custom single restaurant template
 */
function lebonresto_single_template($template) {
    global $post;

    if ($post->post_type == 'restaurant' && is_single()) {
        $plugin_template = LEBONRESTO_PLUGIN_PATH . 'templates/single-restaurant.php';
        
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    return $template;
}
add_filter('single_template', 'lebonresto_single_template');

/**
 * Load custom all restaurants page template
 */
function lebonresto_all_restaurants_template($template) {
    global $post;

    // Check if this is the "All Restaurants" page or /all route
    if (is_page() && $post && $post->post_name === 'all') {
        $plugin_template = LEBONRESTO_PLUGIN_PATH . 'templates/all-restaurants.php';
        
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    return $template;
}
add_filter('page_template', 'lebonresto_all_restaurants_template');

/**
 * Enqueue styles and scripts for restaurant detail pages
 */
function lebonresto_enqueue_detail_page_assets() {
    if (is_singular('restaurant')) {
        // Enqueue Tailwind CSS
        wp_enqueue_style(
            'tailwind-css',
            'https://cdn.tailwindcss.com',
            array(),
            '3.4.0'
        );
        
        // Add custom color CSS
        lebonresto_add_custom_color_css();

        // Enqueue Lightbox for gallery
        wp_enqueue_style(
            'lebonresto-lightbox',
            'https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/css/lightbox.min.css',
            array(),
            '2.11.4'
        );

        wp_enqueue_script(
            'lebonresto-lightbox',
            'https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/js/lightbox.min.js',
            array('jquery'),
            '2.11.4',
            true
        );

        // Custom restaurant detail page styles and scripts
        wp_enqueue_style(
            'lebonresto-detail',
            LEBONRESTO_PLUGIN_URL . 'assets/css/restaurant-detail.css',
            array(),
            LEBONRESTO_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'lebonresto-detail',
            LEBONRESTO_PLUGIN_URL . 'assets/js/restaurant-detail.js',
            array('jquery'),
            LEBONRESTO_PLUGIN_VERSION,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'lebonresto_enqueue_detail_page_assets');

/**
 * Create restaurant detail page CSS file
 */
function lebonresto_create_detail_css() {
    $css_dir = LEBONRESTO_PLUGIN_PATH . 'assets/css/';
    $css_file = $css_dir . 'restaurant-detail.css';
    
    // Create directory if it doesn't exist
    if (!file_exists($css_dir)) {
        wp_mkdir_p($css_dir);
    }
    
    // Create CSS file if it doesn't exist
    if (!file_exists($css_file)) {
        $css_content = '/* Le Bon Resto - Restaurant Detail Page Styles */

.lebonresto-detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.lebonresto-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.lebonresto-gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.lebonresto-gallery-item:hover {
    transform: scale(1.05);
}

.lebonresto-gallery-item img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    display: block;
}

.lebonresto-video-container {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    margin: 20px 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.lebonresto-video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}

.lebonresto-virtual-tour {
    width: 100%;
    height: 600px;
    border: none;
    border-radius: 8px;
    margin: 20px 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.lebonresto-section {
    margin: 40px 0;
}

.lebonresto-section h2 {
    color: #fedc00;
    border-bottom: 2px solid #fedc00;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.lebonresto-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.lebonresto-info-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #fedc00;
}

.lebonresto-info-item strong {
    color: #333;
    display: block;
    margin-bottom: 5px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .lebonresto-detail-container {
        padding: 10px;
    }
    
    .lebonresto-gallery-grid {
        grid-template-columns: 1fr;
    }
    
    .lebonresto-info-grid {
        grid-template-columns: 1fr;
    }
}

/* Lightbox customization */
.lb-data .lb-caption {
    font-size: 16px;
    font-weight: 500;
    color: #fedc00;
}

.lb-data .lb-number {
    color: #fedc00;
}';

        file_put_contents($css_file, $css_content);
    }
}

/**
 * Create restaurant detail page JavaScript file
 */
function lebonresto_create_detail_js() {
    $js_dir = LEBONRESTO_PLUGIN_PATH . 'assets/js/';
    $js_file = $js_dir . 'restaurant-detail.js';
    
    // Create JavaScript file if it doesn't exist
    if (!file_exists($js_file)) {
        $js_content = '(function($) {
    "use strict";

    $(document).ready(function() {
        // Initialize lightbox for gallery
        lightbox.option({
            "resizeDuration": 200,
            "wrapAround": true,
            "albumLabel": "Image %1 of %2"
        });

        // Smooth scroll for anchor links
        $("a[href^=\"#\"]").on("click", function(e) {
            e.preventDefault();
            
            var target = $(this.getAttribute("href"));
            if (target.length) {
                $("html, body").stop().animate({
                    scrollTop: target.offset().top - 80
                }, 1000);
            }
        });

        // Video autoplay detection and control
        $(".lebonresto-video-container iframe").each(function() {
            var src = $(this).attr("src");
            if (src && src.indexOf("autoplay=1") === -1) {
                if (src.indexOf("?") > -1) {
                    $(this).attr("src", src + "&autoplay=0&controls=1");
                } else {
                    $(this).attr("src", src + "?autoplay=0&controls=1");
                }
            }
        });

        // Gallery image lazy loading
        $(".lebonresto-gallery-item img").each(function() {
            $(this).on("load", function() {
                $(this).parent().addClass("loaded");
            });
        });

        // Virtual tour iframe optimization
        $(".lebonresto-virtual-tour").on("load", function() {
            $(this).addClass("loaded");
        });
    });

})(jQuery);';

        file_put_contents($js_file, $js_content);
    }
}

// Create asset files on init
add_action('init', 'lebonresto_create_detail_css');
add_action('init', 'lebonresto_create_detail_js');

/**
 * Create All Restaurants page on plugin activation
 */
function lebonresto_create_all_restaurants_page() {
    // First, check if there's an old page with 'all-restaurants' slug and update it
    $old_page = get_page_by_path('all-restaurants');
    if ($old_page) {
        // Update the old page to use the new slug
        wp_update_post(array(
            'ID' => $old_page->ID,
            'post_name' => 'all'
        ));
        return;
    }
    
    // Check if the page already exists
    $page = get_page_by_path('all');
    
    if (!$page) {
        // Create the page
        $page_data = array(
            'post_title'    => __('All Restaurants', 'le-bon-resto'),
            'post_name'     => 'all',
            'post_content'  => '[lebonresto_all_page]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
        );
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id && !is_wp_error($page_id)) {
            // Set page template
            update_post_meta($page_id, '_wp_page_template', 'templates/all-restaurants.php');
            
            // Add to admin notice
            set_transient('lebonresto_all_restaurants_page_created', true, 30);
        }
    }
}

// Create the page on plugin activation
add_action('lebonresto_plugin_activated', 'lebonresto_create_all_restaurants_page');

/**
 * Create All Restaurants page immediately (for manual creation)
 */
function lebonresto_create_all_restaurants_page_now() {
    // First, check if there's an old page with 'all-restaurants' slug and update it
    $old_page = get_page_by_path('all-restaurants');
    if ($old_page) {
        // Update the old page to use the new slug
        wp_update_post(array(
            'ID' => $old_page->ID,
            'post_name' => 'all'
        ));
        return $old_page->ID;
    }
    
    // Check if the page already exists
    $page = get_page_by_path('all');
    
    if (!$page) {
        // Create the page
        $page_data = array(
            'post_title'    => __('All Restaurants', 'le-bon-resto'),
            'post_name'     => 'all',
            'post_content'  => '[lebonresto_all_page]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
        );
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id && !is_wp_error($page_id)) {
            return $page_id;
        }
    } else {
        return $page->ID;
    }
    
    return false;
}


/**
 * Add custom color CSS to the page
 */
function lebonresto_add_custom_color_css() {
    $options = get_option('lebonresto_options', array());
    $primary_color = isset($options['primary_color']) ? $options['primary_color'] : '#fedc00';
    
    wp_add_inline_style('tailwind-css', "
        :root {
            --lebonresto-primary-color: {$primary_color};
        }
        
        .lebonresto-detail-container h1,
        .lebonresto-detail-container h2 {
            color: {$primary_color} !important;
        }
        
        .lebonresto-detail-container .border-yellow-400,
        .lebonresto-detail-container .bg-yellow-400 {
            border-color: {$primary_color} !important;
            background-color: {$primary_color} !important;
        }
        
        .lebonresto-detail-container .hover\\:bg-yellow-500:hover {
            background-color: " . lebonresto_darken_color($primary_color, 10) . " !important;
        }
        
        .lebonresto-section h2 {
            border-bottom-color: {$primary_color} !important;
        }
        
        .lebonresto-info-item {
            border-left-color: {$primary_color} !important;
        }
        
        .lb-data .lb-caption,
        .lb-data .lb-number {
            color: {$primary_color} !important;
        }
    ");
}

/**
 * Helper function to darken a color
 */
function lebonresto_darken_color($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) !== 6) return $hex;
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r - ($r * $percent / 100)));
    $g = max(0, min(255, $g - ($g * $percent / 100)));
    $b = max(0, min(255, $b - ($b * $percent / 100)));
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

/**
 * Helper function to get restaurant gallery images
 */
function lebonresto_get_gallery_images($post_id) {
    $gallery = get_post_meta($post_id, '_restaurant_gallery', true);
    
    if (empty($gallery)) {
        return array();
    }
    
    $image_ids = explode(',', $gallery);
    $images = array();
    
    foreach ($image_ids as $image_id) {
        $image_id = intval($image_id);
        if ($image_id) {
            $image_data = array(
                'id' => $image_id,
                'url' => wp_get_attachment_url($image_id),
                'thumbnail' => wp_get_attachment_image_url($image_id, 'medium'),
                'title' => get_the_title($image_id),
                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
            );
            
            if ($image_data['url']) {
                $images[] = $image_data;
            }
        }
    }
    
    return $images;
}

/**
 * Helper function to parse video URL and get embed code
 */
function lebonresto_get_video_embed($video_url) {
    if (empty($video_url)) {
        return '';
    }
    
    // YouTube
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches)) {
        $video_id = $matches[1];
        return '<iframe src="https://www.youtube.com/embed/' . esc_attr($video_id) . '?rel=0&showinfo=0" allowfullscreen></iframe>';
    }
    
    // Vimeo
    if (preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches)) {
        $video_id = $matches[1];
        return '<iframe src="https://player.vimeo.com/video/' . esc_attr($video_id) . '" allowfullscreen></iframe>';
    }
    
    return '';
}

/**
 * Helper function to get virtual tour embed
 */
function lebonresto_get_virtual_tour_embed($tour_url) {
    if (empty($tour_url)) {
        return '';
    }
    
    // Check if it's a local path
    if (strpos($tour_url, 'http') !== 0) {
        // Local path - convert to URL
        $tour_url = home_url($tour_url);
    }
    
    return '<iframe src="' . esc_url($tour_url) . '" class="lebonresto-virtual-tour" allowfullscreen></iframe>';
}
