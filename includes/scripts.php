<?php
/**
 * Scripts and Styles Enqueuing
 * 
 * @package LeBonResto
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Leaflet scripts and styles
 */
function lebonresto_enqueue_scripts() {
    // Enqueue Font Awesome CSS
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        array(),
        '6.4.0'
    );

    // Enqueue Leaflet CSS
    wp_enqueue_style(
        'leaflet-css',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
        array(),
        '1.9.4'
    );

    // Enqueue Leaflet JS
    wp_enqueue_script(
        'leaflet-js',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        array(),
        '1.9.4',
        true
    );

    // Enqueue custom map script
    wp_enqueue_script(
        'lebonresto-map',
        LEBONRESTO_PLUGIN_URL . 'assets/js/map.js',
        array('leaflet-js', 'wp-api'),
        LEBONRESTO_PLUGIN_VERSION,
        true
    );

    // Localize script with REST API data
    wp_localize_script(
        'lebonresto-map',
        'lebonrestoAjax',
        array(
            'apiUrl' => home_url('/wp-json/wp/v2/restaurant'),
            'nonce' => wp_create_nonce('wp_rest'),
        )
    );
}

/**
 * Conditionally enqueue scripts only when shortcode is present
 */
function lebonresto_enqueue_scripts_conditionally() {
    global $post;
    
    // Check if we have a post and if it contains our shortcode
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'lebonresto_map')) {
        lebonresto_enqueue_scripts();
    }
}

/**
 * Enqueue Font Awesome on restaurant pages
 */
function lebonresto_enqueue_font_awesome() {
    // Load Font Awesome on restaurant pages
    if (is_singular('restaurant')) {
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            array(),
            '6.4.0'
        );
    }
}

// Hook to enqueue scripts
add_action('wp_enqueue_scripts', 'lebonresto_enqueue_scripts_conditionally');
add_action('wp_enqueue_scripts', 'lebonresto_enqueue_font_awesome');

/**
 * Create assets directory and map.js file if they don't exist
 */
function lebonresto_create_map_js() {
    $assets_dir = LEBONRESTO_PLUGIN_PATH . 'assets/js/';
    $map_js_path = $assets_dir . 'map.js';
    
    // Create directory if it doesn't exist
    if (!file_exists($assets_dir)) {
        wp_mkdir_p($assets_dir);
    }
    
    // Create map.js file if it doesn't exist
    if (!file_exists($map_js_path)) {
        $map_js_content = '(function($) {
    "use strict";

    // Initialize map when document is ready
    $(document).ready(function() {
        initializeLeBonRestoMap();
    });

    function initializeLeBonRestoMap() {
        // Check if map container exists
        const mapContainer = document.getElementById("lebonresto-map");
        if (!mapContainer) {
            return;
        }

        // Initialize map centered on Paris
        const map = L.map("lebonresto-map").setView([48.8566, 2.3522], 12);

        // Add OpenStreetMap tiles
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\'
        }).addTo(map);

        // Load restaurants from REST API
        loadRestaurants(map);
    }

    function loadRestaurants(map) {
        // Fetch restaurants from WordPress REST API
        fetch(lebonrestoAjax.apiUrl)
            .then(response => response.json())
            .then(restaurants => {
                restaurants.forEach(restaurant => {
                    addRestaurantMarker(map, restaurant);
                });
            })
            .catch(error => {
                console.error("Error loading restaurants:", error);
            });
    }

    function addRestaurantMarker(map, restaurant) {
        // Check if restaurant has valid coordinates
        const lat = parseFloat(restaurant.restaurant_meta.latitude);
        const lng = parseFloat(restaurant.restaurant_meta.longitude);

        if (isNaN(lat) || isNaN(lng)) {
            return; // Skip restaurants without valid coordinates
        }

        // Create marker
        const marker = L.marker([lat, lng]).addTo(map);

        // Create popup content
        const popupContent = createPopupContent(restaurant);
        marker.bindPopup(popupContent);
    }

    function createPopupContent(restaurant) {
        const title = restaurant.title.rendered || "Restaurant";
        const address = restaurant.restaurant_meta.address || "";
        const city = restaurant.restaurant_meta.city || "";
        const cuisineType = restaurant.restaurant_meta.cuisine_type || "";
        const description = restaurant.restaurant_meta.description || "";

        let content = `<div class="lebonresto-popup">`;
        content += `<h3>${title}</h3>`;
        
        if (address || city) {
            content += `<p><strong>Address:</strong> ${address}`;
            if (city) {
                content += `, ${city}`;
            }
            content += `</p>`;
        }
        
        if (cuisineType) {
            content += `<p><strong>Cuisine:</strong> ${cuisineType}</p>`;
        }
        
        if (description) {
            content += `<p>${description}</p>`;
        }
        
        content += `</div>`;
        
        return content;
    }

})(jQuery);';

        file_put_contents($map_js_path, $map_js_content);
    }
}

// Create map.js file on plugin activation
add_action('init', 'lebonresto_create_map_js');

/**
 * Add custom CSS for the map popup
 */
function lebonresto_add_custom_css() {
    // Only add CSS if shortcode is present
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'lebonresto_map')) {
        ?>
        <style type="text/css">
            .lebonresto-popup h3 {
                margin: 0 0 10px 0;
                color: #333;
                font-size: 16px;
            }
            
            .lebonresto-popup p {
                margin: 5px 0;
                font-size: 14px;
                line-height: 1.4;
            }
            
            .lebonresto-popup strong {
                color: #666;
            }
            
            #lebonresto-map {
                border: 1px solid #ddd;
                border-radius: 4px;
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'lebonresto_add_custom_css');
