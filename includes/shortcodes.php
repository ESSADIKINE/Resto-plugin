<?php
/**
 * Shortcodes Implementation
 * 
 * @package LeBonResto
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the [lebonresto_map] shortcode
 */
function lebonresto_map_shortcode($atts) {
    // Get default values from settings
    $options = get_option('lebonresto_options', array());
    $default_lat = isset($options['default_map_center_lat']) ? $options['default_map_center_lat'] : 48.8566;
    $default_lng = isset($options['default_map_center_lng']) ? $options['default_map_center_lng'] : 2.3522;
    $default_zoom = isset($options['default_zoom_level']) ? $options['default_zoom_level'] : 12;
    
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'width' => '100%',
            'height' => '500px',
            'zoom' => $default_zoom,
            'center_lat' => $default_lat,
            'center_lng' => $default_lng,
        ),
        $atts,
        'lebonresto_map'
    );

    // Enqueue Tailwind CSS
    wp_enqueue_style(
        'tailwind-css',
        'https://cdn.tailwindcss.com',
        array(),
        '3.4.0'
    );

    // Ensure scripts are enqueued
    if (!wp_script_is('leaflet-js', 'enqueued')) {
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

        // Get plugin options for the map
        $options = get_option('lebonresto_options', array());
        
        // Enqueue Leaflet Fullscreen plugin if enabled
        if (isset($options['enable_fullscreen']) && $options['enable_fullscreen'] === '1') {
            wp_enqueue_style(
                'leaflet-fullscreen-css',
                'https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/2.4.0/Control.FullScreen.css',
                array('leaflet-css'),
                '2.4.0'
            );
            
            wp_enqueue_script(
                'leaflet-fullscreen-js',
                'https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/2.4.0/Control.FullScreen.js',
                array('leaflet-js'),
                '2.4.0',
                true
            );
        }
        
        // Localize script with REST API data and settings
        wp_localize_script(
            'lebonresto-map',
            'lebonrestoAjax',
            array(
                'apiUrl' => home_url('/wp-json/lebonresto/v1/restaurants'),
                'cuisineTypesUrl' => home_url('/wp-json/lebonresto/v1/cuisine-types'),
                'nonce' => wp_create_nonce('wp_rest'),
                'mapConfig' => array(
                    'zoom' => intval($atts['zoom']),
                    'centerLat' => floatval($atts['center_lat']),
                    'centerLng' => floatval($atts['center_lng']),
                ),
                'pluginSettings' => array(
                    'defaultRadius' => isset($options['default_radius']) ? intval($options['default_radius']) : 25,
                    'maxRadius' => isset($options['max_radius']) ? intval($options['max_radius']) : 100,
                    'enableLayerSwitcher' => isset($options['enable_layer_switcher']) && $options['enable_layer_switcher'] === '1',
                    'enableFullscreen' => isset($options['enable_fullscreen']) && $options['enable_fullscreen'] === '1',
                    'primaryColor' => isset($options['primary_color']) ? $options['primary_color'] : '#FFC107',
                ),
            )
        );
    }

    // Get cuisine types for dropdown
    $cuisine_types = lebonresto_get_cuisine_types();

    // Build the search form HTML
    $search_form = lebonresto_build_search_form($cuisine_types);

    // Build the map container HTML
    $map_html = sprintf(
        '<div id="lebonresto-map" style="width: %s; height: %s;"></div>',
        esc_attr($atts['width']),
        esc_attr($atts['height'])
    );

    return $search_form . $map_html;
}

/**
 * Build the advanced search form HTML with visitemonresto.com style UX
 */
function lebonresto_build_search_form($cuisine_types) {
    // Get settings
    $options = get_option('lebonresto_options', array());
    $default_radius = isset($options['default_radius']) ? intval($options['default_radius']) : 25;
    $max_radius = isset($options['max_radius']) ? intval($options['max_radius']) : 100;
    
    ob_start();
    ?>
    <div class="lebonresto-advanced-controls bg-white rounded-lg shadow-lg mb-6 border border-gray-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-yellow-400 to-orange-400 p-4 rounded-t-lg">
            <h3 class="text-xl font-bold text-gray-800"><?php _e('Discover Great Restaurants', 'le-bon-resto'); ?></h3>
            <p class="text-gray-700 text-sm mt-1"><?php _e('Use filters below to find the perfect dining experience', 'le-bon-resto'); ?></p>
            </div>

        <div class="p-6">
            <!-- Top Row: Search and Sort -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <label for="restaurant-search" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-2"></i><?php _e('Search Restaurant or City', 'le-bon-resto'); ?>
                </label>
                <input 
                    type="text" 
                        id="restaurant-search" 
                        name="search" 
                        placeholder="<?php _e('Enter restaurant name or city...', 'le-bon-resto'); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent text-lg"
                />
            </div>

                <!-- Sort Dropdown -->
                <div>
                    <label for="restaurant-sort" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort mr-2"></i><?php _e('Sort By', 'le-bon-resto'); ?>
                </label>
                <select 
                        id="restaurant-sort" 
                        name="sort"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent text-lg"
                    >
                        <option value="featured"><?php _e('Featured First', 'le-bon-resto'); ?></option>
                        <option value="newest"><?php _e('Newest', 'le-bon-resto'); ?></option>
                        <option value="distance"><?php _e('Distance (Closest)', 'le-bon-resto'); ?></option>
                </select>
                </div>
            </div>

            <!-- Filters Row -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Radius Filter -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-compass mr-2"></i><?php _e('Search Radius', 'le-bon-resto'); ?>
                </label>
                    <div class="space-y-3">
                        <input 
                            type="range" 
                            id="radius-slider" 
                            min="1" 
                            max="<?php echo esc_attr($max_radius); ?>" 
                            value="<?php echo esc_attr($default_radius); ?>"
                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                            style="background: linear-gradient(to right, #FFC107 0%, #FFC107 var(--value), #e5e7eb var(--value), #e5e7eb 100%);"
                        />
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>1km</span>
                            <span id="radius-value" class="font-bold text-yellow-600"><?php echo esc_html($default_radius); ?>km</span>
                            <span><?php echo esc_html($max_radius); ?>km</span>
                        </div>
                        <p class="text-xs text-gray-500" id="radius-status">
                            <?php _e('Enable location for radius filtering', 'le-bon-resto'); ?>
                </p>
            </div>
                </div>
                
                <!-- Cuisine Categories -->
                <div class="lg:col-span-2 bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-utensils mr-2"></i><?php _e('Cuisine Types', 'le-bon-resto'); ?>
                    </label>
                    <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto">
                        <?php foreach ($cuisine_types as $cuisine): ?>
                            <label class="flex items-center space-x-2 text-sm hover:bg-white p-2 rounded cursor-pointer transition-colors">
                                <input 
                                    type="checkbox" 
                                    name="cuisines[]" 
                                    value="<?php echo esc_attr($cuisine); ?>"
                                    class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 focus:ring-2"
                                />
                                <span class="text-gray-700"><?php echo esc_html(ucfirst($cuisine)); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Featured Toggle -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-star mr-2"></i><?php _e('Filter Options', 'le-bon-resto'); ?>
                    </label>
                    <div class="space-y-3">
                        <label class="flex items-center space-x-3">
                            <input 
                                type="checkbox" 
                                id="featured-only" 
                                name="featured_only"
                                class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 focus:ring-2"
                            />
                            <span class="text-sm text-gray-700"><?php _e('Featured Only', 'le-bon-resto'); ?></span>
                        </label>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-2 mt-4">
                <button 
                                type="button" 
                    id="lebonresto-search-btn"
                                class="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    style="background-color: #FFC107;"
                >
                                <i class="fas fa-search mr-2"></i><?php _e('Search', 'le-bon-resto'); ?>
                </button>
            <button 
                type="button" 
                id="lebonresto-clear-btn"
                                class="w-full text-gray-600 hover:text-gray-800 text-sm font-medium py-2 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200"
            >
                                <i class="fas fa-eraser mr-2"></i><?php _e('Clear All', 'le-bon-resto'); ?>
            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Results Info -->
            <div class="mt-6 flex justify-between items-center">
                <div id="lebonresto-results-count" class="text-sm text-gray-600"></div>
                <div id="lebonresto-location-info" class="text-xs text-gray-500"></div>
            </div>
        </div>
        </div>

    <!-- No Results Placeholder (hidden by default) -->
    <div id="no-results-placeholder" class="hidden bg-white rounded-lg shadow-lg p-8 text-center mb-6">
        <div class="text-gray-400 mb-4">
            <i class="fas fa-search text-4xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-700 mb-2"><?php _e('Sorry, no results found', 'le-bon-resto'); ?></h3>
        <p class="text-gray-500 mb-4"><?php _e('Try adjusting your search criteria or expanding your search radius', 'le-bon-resto'); ?></p>
        <button 
            type="button" 
            id="expand-search-btn"
            class="bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold py-2 px-6 rounded-lg transition duration-200"
            style="background-color: #FFC107;"
        >
            <i class="fas fa-expand-arrows-alt mr-2"></i><?php _e('Expand Search', 'le-bon-resto'); ?>
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Radius slider functionality
            const radiusSlider = document.getElementById('radius-slider');
            const radiusValue = document.getElementById('radius-value');
            const radiusStatus = document.getElementById('radius-status');
            
            function updateSliderBackground() {
                const value = ((radiusSlider.value - radiusSlider.min) / (radiusSlider.max - radiusSlider.min)) * 100;
                radiusSlider.style.setProperty('--value', value + '%');
            }
            
            radiusSlider.addEventListener('input', function() {
                radiusValue.textContent = this.value + 'km';
                updateSliderBackground();
            });
            
            // Initialize slider background
            updateSliderBackground();
            
            // Location handling
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        window.userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        radiusStatus.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1"></i><?php _e('Location enabled', 'le-bon-resto'); ?>';
                        document.getElementById('lebonresto-location-info').innerHTML = '<i class="fas fa-map-marker-alt text-green-500 mr-1"></i><?php _e('Using your location for distance calculations', 'le-bon-resto'); ?>';
                    },
                    function(error) {
                        radiusStatus.innerHTML = '<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i><?php _e('Location access denied', 'le-bon-resto'); ?>';
                        document.getElementById('lebonresto-location-info').innerHTML = '<i class="fas fa-info-circle text-gray-400 mr-1"></i><?php _e('Enable location for distance filtering', 'le-bon-resto'); ?>';
                    }
                );
            }
        });
    </script>

    <style>
        /* Custom slider styles */
        .slider::-webkit-slider-thumb {
            appearance: none;
            height: 20px;
            width: 20px;
            border-radius: 50%;
            background: #FFC107;
            cursor: pointer;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .slider::-moz-range-thumb {
            height: 20px;
            width: 20px;
            border-radius: 50%;
            background: #FFC107;
            cursor: pointer;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* FontAwesome CDN fallback */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    </style>

    <?php
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('lebonresto_map', 'lebonresto_map_shortcode');

/**
 * Add shortcode button to TinyMCE editor (optional enhancement)
 */
function lebonresto_add_shortcode_button() {
    // Check if user has permissions
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return;
    }

    // Check if rich editing is enabled
    if (get_user_option('rich_editing') !== 'true') {
        return;
    }

    // Add TinyMCE button
    add_filter('mce_external_plugins', 'lebonresto_add_tinymce_plugin');
    add_filter('mce_buttons', 'lebonresto_register_mce_button');
}

function lebonresto_add_tinymce_plugin($plugin_array) {
    $plugin_array['lebonresto_button'] = LEBONRESTO_PLUGIN_URL . 'assets/js/tinymce-plugin.js';
    return $plugin_array;
}

function lebonresto_register_mce_button($buttons) {
    array_push($buttons, 'lebonresto_button');
    return $buttons;
}

// Initialize TinyMCE button
add_action('admin_head', 'lebonresto_add_shortcode_button');

/**
 * Create TinyMCE plugin file
 */
function lebonresto_create_tinymce_plugin() {
    $tinymce_js_path = LEBONRESTO_PLUGIN_PATH . 'assets/js/tinymce-plugin.js';
    
    // Create the TinyMCE plugin file if it doesn't exist
    if (!file_exists($tinymce_js_path)) {
        $tinymce_content = '(function() {
    tinymce.PluginManager.add("lebonresto_button", function(editor, url) {
        editor.addButton("lebonresto_button", {
            title: "Le Bon Resto Shortcodes",
            icon: "dashicon dashicons-location-alt",
            type: "menubutton",
            menu: [
                {
                    text: "Interactive Map",
            onclick: function() {
                editor.insertContent("[lebonresto_map]");
            }
                },
                {
                    text: "All Restaurants Page",
                    onclick: function() {
                        editor.insertContent("[lebonresto_all]");
                    }
                },
                {
                    text: "Restaurant List",
                    onclick: function() {
                        editor.insertContent("[lebonresto_list limit=\"10\"]");
                    }
                }
            ]
        });
    });
})();';

        file_put_contents($tinymce_js_path, $tinymce_content);
    }
}

// Create TinyMCE plugin file on init
add_action('init', 'lebonresto_create_tinymce_plugin');

/**
 * Restaurant listing shortcode (bonus feature)
 */
function lebonresto_restaurant_list_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'limit' => 10,
            'cuisine' => '',
            'city' => '',
        ),
        $atts,
        'lebonresto_list'
    );

    // Query arguments
    $args = array(
        'post_type' => 'restaurant',
        'post_status' => 'publish',
        'posts_per_page' => intval($atts['limit']),
    );

    // Add meta query for filtering
    $meta_query = array();
    
    if (!empty($atts['cuisine'])) {
        $meta_query[] = array(
            'key' => '_restaurant_cuisine_type',
            'value' => sanitize_text_field($atts['cuisine']),
            'compare' => '='
        );
    }
    
    if (!empty($atts['city'])) {
        $meta_query[] = array(
            'key' => '_restaurant_city',
            'value' => sanitize_text_field($atts['city']),
            'compare' => 'LIKE'
        );
    }

    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }

    // Execute query
    $restaurants = new WP_Query($args);
    
    if (!$restaurants->have_posts()) {
        return '<p>' . __('No restaurants found.', 'le-bon-resto') . '</p>';
    }

    // Build output
    $output = '<div class="lebonresto-restaurant-list">';
    
    while ($restaurants->have_posts()) {
        $restaurants->the_post();
        
        $restaurant_id = get_the_ID();
        $address = get_post_meta($restaurant_id, '_restaurant_address', true);
        $city = get_post_meta($restaurant_id, '_restaurant_city', true);
        $cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true);
        $description = get_post_meta($restaurant_id, '_restaurant_description', true);
        
        $output .= '<div class="restaurant-item">';
        $output .= '<h3>' . get_the_title() . '</h3>';
        
        if ($description) {
            $output .= '<p>' . esc_html($description) . '</p>';
        }
        
        if ($address || $city) {
            $output .= '<p><strong>' . __('Address:', 'le-bon-resto') . '</strong> ';
            $output .= esc_html($address);
            if ($city) {
                $output .= ', ' . esc_html($city);
            }
            $output .= '</p>';
        }
        
        if ($cuisine_type) {
            $output .= '<p><strong>' . __('Cuisine:', 'le-bon-resto') . '</strong> ' . esc_html(ucfirst($cuisine_type)) . '</p>';
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    // Reset post data
    wp_reset_postdata();
    
    return $output;
}

// Register the restaurant list shortcode
add_shortcode('lebonresto_list', 'lebonresto_restaurant_list_shortcode');

/**
 * All Restaurants page shortcode [lebonresto_all]
 */
function lebonresto_all_restaurants_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'per_page' => 12,
            'show_pagination' => 'true',
            'show_sorting' => 'true',
            'show_filters' => 'true',
        ),
        $atts,
        'lebonresto_all'
    );

    // Enqueue Tailwind CSS with fallback
    wp_enqueue_style(
        'tailwind-css',
        'https://cdn.tailwindcss.com',
        array(),
        '3.4.0'
    );

    // Enqueue dedicated All Restaurants CSS
    wp_enqueue_style(
        'lebonresto-all-restaurants-css',
        LEBONRESTO_PLUGIN_URL . 'assets/css/all-restaurants.css',
        array('tailwind-css'),
        LEBONRESTO_PLUGIN_VERSION
    );

    // Enqueue debug CSS for additional fallback styling
    wp_enqueue_style(
        'lebonresto-all-debug-css',
        LEBONRESTO_PLUGIN_URL . 'assets/css/debug.css',
        array('lebonresto-all-restaurants-css'),
        LEBONRESTO_PLUGIN_VERSION
    );

    // Add inline backup styles if Tailwind fails to load
    wp_add_inline_style('tailwind-css', '
    /* All Restaurants Page Backup Styles */
    .lebonresto-all-restaurants { min-height: 100vh; background-color: #f9fafb; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
    .grid { display: grid; }
    .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
    .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    @media (min-width: 768px) { .md\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1024px) { .lg\\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    @media (min-width: 1280px) { .xl\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .gap-6 { gap: 1.5rem; }
    .gap-8 { gap: 2rem; }
    .bg-gray-50 { background-color: #f9fafb; }
    .bg-white { background-color: #ffffff; }
    .bg-yellow-400 { background-color: #fbbf24; }
    .hover\\:bg-yellow-500:hover { background-color: #f59e0b; }
    .text-gray-600 { color: #4b5563; }
    .text-gray-700 { color: #374151; }
    .text-gray-800 { color: #1f2937; }
    .text-4xl { font-size: 2.25rem; }
    .text-lg { font-size: 1.125rem; }
    .text-xl { font-size: 1.25rem; }
    .font-bold { font-weight: 700; }
    .font-semibold { font-weight: 600; }
    .rounded-lg { border-radius: 0.5rem; }
    .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    .p-4 { padding: 1rem; }
    .p-6 { padding: 1.5rem; }
    .p-8 { padding: 2rem; }
    .px-4 { padding-left: 1rem; padding-right: 1rem; }
    .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
    .mb-4 { margin-bottom: 1rem; }
    .mb-6 { margin-bottom: 1.5rem; }
    .mb-8 { margin-bottom: 2rem; }
    .w-full { width: 100%; }
    .max-w-2xl { max-width: 42rem; }
    .mx-auto { margin-left: auto; margin-right: auto; }
    .text-center { text-align: center; }
    .sticky { position: sticky; }
    .top-4 { top: 1rem; }
    .flex { display: flex; }
    .flex-col { flex-direction: column; }
    .items-center { align-items: center; }
    .justify-between { justify-content: space-between; }
    .space-x-3 > * + * { margin-left: 0.75rem; }
    .space-y-3 > * + * { margin-top: 0.75rem; }
    .hidden { display: none; }
    .overflow-y-auto { overflow-y: auto; }
    .max-h-96 { max-height: 24rem; }
    .transition { transition: all 0.15s ease; }
    .duration-200 { transition-duration: 200ms; }
    .transform { transform: translateZ(0); }
    .hover\\:scale-105:hover { transform: scale(1.05); }
    ');

    // Enqueue custom script for all restaurants page
    wp_enqueue_script(
        'lebonresto-all-restaurants',
        LEBONRESTO_PLUGIN_URL . 'assets/js/all-restaurants.js',
        array('jquery', 'wp-api'),
        LEBONRESTO_PLUGIN_VERSION,
        true
    );

    // Get plugin options
    $options = get_option('lebonresto_options', array());
    
    // Localize script with REST API data and settings
    wp_localize_script(
        'lebonresto-all-restaurants',
        'lebonrestoAll',
        array(
            'apiUrl' => home_url('/wp-json/lebonresto/v1/restaurants'),
            'cuisineTypesUrl' => home_url('/wp-json/lebonresto/v1/cuisine-types'),
            'nonce' => wp_create_nonce('wp_rest'),
            'perPage' => intval($atts['per_page']),
            'showPagination' => $atts['show_pagination'] === 'true',
            'showSorting' => $atts['show_sorting'] === 'true',
            'showFilters' => $atts['show_filters'] === 'true',
            'settings' => array(
                'defaultRadius' => isset($options['default_radius']) ? intval($options['default_radius']) : 25,
                'maxRadius' => isset($options['max_radius']) ? intval($options['max_radius']) : 100,
                'primaryColor' => isset($options['primary_color']) ? $options['primary_color'] : '#FFC107',
            ),
            'strings' => array(
                'searchPlaceholder' => __('Search restaurants...', 'le-bon-resto'),
                'cityPlaceholder' => __('Enter city...', 'le-bon-resto'),
                'allCuisines' => __('All Cuisines', 'le-bon-resto'),
                'searchButton' => __('Search Restaurants', 'le-bon-resto'),
                'clearFilters' => __('Clear Filters', 'le-bon-resto'),
                'sortNewest' => __('Newest First', 'le-bon-resto'),
                'sortOldest' => __('Oldest First', 'le-bon-resto'),
                'sortFeatured' => __('Featured First', 'le-bon-resto'),
                'sortDistance' => __('Distance', 'le-bon-resto'),
                'sortName' => __('Name A-Z', 'le-bon-resto'),
                'viewDetails' => __('View Details', 'le-bon-resto'),
                'featured' => __('Featured', 'le-bon-resto'),
                'kmAway' => __('%s km away', 'le-bon-resto'),
                'noResults' => __('No restaurants found matching your criteria.', 'le-bon-resto'),
                'loadingMore' => __('Loading more restaurants...', 'le-bon-resto'),
                'loadMore' => __('Load More', 'le-bon-resto'),
                'resultsFound' => __('%s restaurants found', 'le-bon-resto'),
                'locationRequired' => __('Enable location for distance filtering', 'le-bon-resto'),
                'locationEnabled' => __('Location enabled', 'le-bon-resto'),
                'locationDenied' => __('Location access denied', 'le-bon-resto'),
            )
        )
    );

    // Get cuisine types for dropdown
    $cuisine_types = lebonresto_get_cuisine_types();

    // Build the HTML output
    ob_start();
    ?>
    
    <div class="lebonresto-all-restaurants bg-gray-50 min-h-screen" id="lebonresto-all-container" style="background-color: #f9fafb; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <!-- Main Container -->
        <div class="container mx-auto px-4 py-8" style="max-width: 1400px; margin: 0 auto; padding: 2rem 1rem;">
            
            <!-- Page Header -->
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-bold text-gray-800 mb-4" style="color: #FFC107;">
                    <?php _e('All Restaurants', 'le-bon-resto'); ?>
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    <?php _e('Discover amazing restaurants in your area. Use the filters below to find exactly what you\'re looking for.', 'le-bon-resto'); ?>
                </p>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8" style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
                <style>
                @media (min-width: 1024px) {
                    .grid.lg\\:grid-cols-4 {
                        grid-template-columns: 1fr 3fr !important;
                    }
                }
                </style>
                
                <!-- Left Sidebar (25% width on desktop) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">
                            <i class="fas fa-filter mr-2" style="color: #FFC107;"></i>
                            <?php _e('Filter Restaurants', 'le-bon-resto'); ?>
                        </h2>
                        
                        <!-- Restaurant Name Input -->
                        <div class="mb-6">
                            <label for="restaurant-name-filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Restaurant Name', 'le-bon-resto'); ?>
                            </label>
                            <input 
                                type="text" 
                                id="restaurant-name-filter" 
                                name="restaurant_name"
                                placeholder="<?php echo esc_attr(__('Search restaurants...', 'le-bon-resto')); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            />
                        </div>
                        
                        <!-- City Input -->
                        <div class="mb-6">
                            <label for="city-filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('City', 'le-bon-resto'); ?>
                            </label>
                            <input 
                                type="text" 
                                id="city-filter" 
                                name="city"
                                placeholder="<?php echo esc_attr(__('Enter city...', 'le-bon-resto')); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            />
                        </div>
                        
                        <!-- Cuisine Type Dropdown -->
                        <div class="mb-6">
                            <label for="cuisine-filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Cuisine Type', 'le-bon-resto'); ?>
                            </label>
                            <select 
                                id="cuisine-filter" 
                                name="cuisine"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            >
                                <option value=""><?php _e('All Cuisines', 'le-bon-resto'); ?></option>
                                <?php foreach ($cuisine_types as $cuisine): ?>
                                    <option value="<?php echo esc_attr($cuisine); ?>">
                                        <?php echo esc_html(ucfirst($cuisine)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Distance Slider -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Distance Range', 'le-bon-resto'); ?>
                            </label>
                            <div class="space-y-3">
                                <input 
                                    type="range" 
                                    id="distance-slider" 
                                    name="distance"
                                    min="1" 
                                    max="100" 
                                    value="25"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                                    disabled
                                />
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>1km</span>
                                    <span id="distance-value" class="font-bold text-yellow-600">25km</span>
                                    <span>100km</span>
                                </div>
                                <p class="text-xs text-gray-500" id="distance-status">
                                    <?php _e('Enable location for distance filtering', 'le-bon-resto'); ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Featured Only Toggle -->
                        <div class="mb-6">
                            <label class="flex items-center space-x-3">
                                <input 
                                    type="checkbox" 
                                    id="featured-only" 
                                    name="featured_only"
                                    class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 focus:ring-2"
                                />
                                <span class="text-sm text-gray-700">
                                    <i class="fas fa-star mr-1" style="color: #FFC107;"></i>
                                    <?php _e('Featured restaurants only', 'le-bon-resto'); ?>
                                </span>
                            </label>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            <button 
                                type="button" 
                                id="search-restaurants-btn"
                                class="w-full py-3 px-6 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                                style="background-color: #FFC107;"
                            >
                                <i class="fas fa-search mr-2"></i>
                                <?php _e('Search Restaurants', 'le-bon-resto'); ?>
                            </button>
                            
                            <button 
                                type="button" 
                                id="clear-filters-btn"
                                class="w-full py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-200"
                            >
                                <i class="fas fa-eraser mr-2"></i>
                                <?php _e('Clear Filters', 'le-bon-resto'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column (75% width on desktop) -->
                <div class="lg:col-span-3">
                    
                    <!-- Results Header with Sorting -->
                    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div id="results-count" class="text-gray-600">
                                <i class="fas fa-search mr-2"></i>
                                <?php _e('Loading restaurants...', 'le-bon-resto'); ?>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <label for="sort-order" class="text-sm font-medium text-gray-700">
                                    <?php _e('Sort by:', 'le-bon-resto'); ?>
                                </label>
                                <select 
                                    id="sort-order" 
                                    name="sort"
                                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                                >
                                    <option value="featured"><?php _e('Featured First', 'le-bon-resto'); ?></option>
                                    <option value="newest"><?php _e('Newest First', 'le-bon-resto'); ?></option>
                                    <option value="oldest"><?php _e('Oldest First', 'le-bon-resto'); ?></option>
                                    <option value="distance"><?php _e('Distance', 'le-bon-resto'); ?></option>
                                    <option value="name"><?php _e('Name A-Z', 'le-bon-resto'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Restaurant Cards Grid -->
                    <div id="restaurants-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                        <!-- Cards will be loaded here via JavaScript -->
                        <div class="col-span-full text-center py-12">
                            <div class="loading-spinner mx-auto mb-4"></div>
                            <p class="text-gray-500"><?php _e('Loading restaurants...', 'le-bon-resto'); ?></p>
                        </div>
                    </div>
                    
                    <!-- Load More / Pagination -->
                    <div id="pagination-container" class="text-center">
                        <button 
                            id="load-more-btn" 
                            class="hidden px-8 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200"
                            style="background-color: #FFC107;"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            <?php _e('Load More Restaurants', 'le-bon-resto'); ?>
                        </button>
                    </div>
                    
                    <!-- No Results Message -->
                    <div id="no-results" class="hidden text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-search text-5xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">
                            <?php _e('No restaurants found', 'le-bon-resto'); ?>
                        </h3>
                        <p class="text-gray-500 mb-6">
                            <?php _e('Try adjusting your search criteria or clearing some filters.', 'le-bon-resto'); ?>
                        </p>
                        <button 
                            onclick="clearAllFilters()"
                            class="px-6 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200"
                            style="background-color: #FFC107;"
                        >
                            <i class="fas fa-eraser mr-2"></i>
                            <?php _e('Clear All Filters', 'le-bon-resto'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom slider styles */
        .slider::-webkit-slider-thumb {
            appearance: none;
            height: 20px;
            width: 20px;
            border-radius: 50%;
            background: #FFC107;
            cursor: pointer;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .slider::-moz-range-thumb {
            height: 20px;
            width: 20px;
            border-radius: 50%;
            background: #FFC107;
            cursor: pointer;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Loading spinner */
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #FFC107;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Card hover effects */
        .restaurant-card {
            transition: all 0.3s ease;
        }

        .restaurant-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        /* Featured badge animation */
        .featured-badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* FontAwesome CDN */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    </style>

    <?php
    return ob_get_clean();
}

// Register the all restaurants shortcode
add_shortcode('lebonresto_all', 'lebonresto_all_restaurants_shortcode');

/**
 * 1. All Restaurants Page - 30vh sidebar filters + 70vh cards (no map, no virtual tour)
 */
function lebonresto_all_restaurants_page_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'per_page' => 12,
            'show_pagination' => 'true',
            'show_sorting' => 'true',
            'show_filters' => 'true',
        ),
        $atts,
        'lebonresto_all_page'
    );

    // Enqueue Tailwind CSS
    wp_enqueue_style(
        'tailwind-css',
        'https://cdn.tailwindcss.com',
        array(),
        '3.4.0'
    );

    // Enqueue All Restaurants CSS
    wp_enqueue_style(
        'lebonresto-all-restaurants-css',
        LEBONRESTO_PLUGIN_URL . 'assets/css/all-restaurants.css',
        array('tailwind-css'),
        LEBONRESTO_PLUGIN_VERSION
    );

    // Enqueue All Restaurants JavaScript
    wp_enqueue_script(
        'lebonresto-all-restaurants',
        LEBONRESTO_PLUGIN_URL . 'assets/js/all-restaurants.js',
        array('jquery', 'wp-api'),
        LEBONRESTO_PLUGIN_VERSION,
        true
    );

    // Get plugin options
    $options = get_option('lebonresto_options', array());
    
    // Localize script
    wp_localize_script(
        'lebonresto-all-restaurants',
        'lebonrestoAll',
        array(
            'apiUrl' => home_url('/wp-json/lebonresto/v1/restaurants'),
            'cuisineTypesUrl' => home_url('/wp-json/lebonresto/v1/cuisine-types'),
            'nonce' => wp_create_nonce('wp_rest'),
            'perPage' => intval($atts['per_page']),
            'showPagination' => $atts['show_pagination'] === 'true',
            'showSorting' => $atts['show_sorting'] === 'true',
            'showFilters' => $atts['show_filters'] === 'true',
            'settings' => array(
                'defaultRadius' => isset($options['default_radius']) ? intval($options['default_radius']) : 25,
                'maxRadius' => isset($options['max_radius']) ? intval($options['max_radius']) : 100,
                'primaryColor' => isset($options['primary_color']) ? $options['primary_color'] : '#FFC107',
            ),
        )
    );

    // Get cuisine types
    $cuisine_types = lebonresto_get_cuisine_types();

    // Build HTML output
    ob_start();
    ?>
    <div class="lebonresto-all-restaurants-page bg-gray-50 min-h-screen">
        <div class="container mx-auto px-4 py-8" style="max-width: 1400px;">
            
            <!-- Page Header -->
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-bold text-gray-800 mb-4" style="color: #FFC107;">
                    <?php _e('All Restaurants', 'le-bon-resto'); ?>
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    <?php _e('Discover amazing restaurants in your area. Use the filters to find exactly what you\'re looking for.', 'le-bon-resto'); ?>
                </p>
            </div>

            <!-- Two Column Layout: 30vh sidebar + 70vh cards -->
            <div class="grid grid-cols-1 lg:grid-cols-10 gap-8" style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
                <style>
                @media (min-width: 1024px) {
                    .grid.lg\\:grid-cols-10 {
                        grid-template-columns: 3fr 7fr !important;
                    }
                }
                </style>
                
                <!-- Left Sidebar (30% width) -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">
                            <i class="fas fa-filter mr-2" style="color: #FFC107;"></i>
                            <?php _e('Filter Restaurants', 'le-bon-resto'); ?>
                        </h2>
                        
                        <!-- Restaurant Name Input -->
                        <div class="mb-6">
                            <label for="restaurant-name-filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Restaurant Name', 'le-bon-resto'); ?>
                            </label>
                            <input 
                                type="text" 
                                id="restaurant-name-filter" 
                                name="restaurant_name"
                                placeholder="<?php echo esc_attr(__('Search restaurants...', 'le-bon-resto')); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            />
                        </div>
                        
                        <!-- City Input -->
                        <div class="mb-6">
                            <label for="city-filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('City', 'le-bon-resto'); ?>
                            </label>
                            <input 
                                type="text" 
                                id="city-filter" 
                                name="city"
                                placeholder="<?php echo esc_attr(__('Enter city...', 'le-bon-resto')); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            />
                        </div>
                        
                        <!-- Cuisine Type Dropdown -->
                        <div class="mb-6">
                            <label for="cuisine-filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Cuisine Type', 'le-bon-resto'); ?>
                            </label>
                            <select 
                                id="cuisine-filter" 
                                name="cuisine"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            >
                                <option value=""><?php _e('All Cuisines', 'le-bon-resto'); ?></option>
                                <?php foreach ($cuisine_types as $cuisine): ?>
                                    <option value="<?php echo esc_attr($cuisine); ?>">
                                        <?php echo esc_html(ucfirst($cuisine)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Featured Only Toggle -->
                        <div class="mb-6">
                            <label class="flex items-center space-x-3">
                                <input 
                                    type="checkbox" 
                                    id="featured-only" 
                                    name="featured_only"
                                    class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 focus:ring-2"
                                />
                                <span class="text-sm text-gray-700">
                                    <i class="fas fa-star mr-1" style="color: #FFC107;"></i>
                                    <?php _e('Featured restaurants only', 'le-bon-resto'); ?>
                                </span>
                            </label>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            <button 
                                type="button" 
                                id="search-restaurants-btn"
                                class="w-full py-3 px-6 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                                style="background-color: #FFC107;"
                            >
                                <i class="fas fa-search mr-2"></i>
                                <?php _e('Search Restaurants', 'le-bon-resto'); ?>
                            </button>
                            
                            <button 
                                type="button" 
                                id="clear-filters-btn"
                                class="w-full py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-200"
                            >
                                <i class="fas fa-eraser mr-2"></i>
                                <?php _e('Clear Filters', 'le-bon-resto'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column (70% width) -->
                <div class="lg:col-span-7">
                    
                    <!-- Results Header with Sorting -->
                    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div id="results-count" class="text-gray-600">
                                <i class="fas fa-search mr-2"></i>
                                <?php _e('Loading restaurants...', 'le-bon-resto'); ?>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <label for="sort-order" class="text-sm font-medium text-gray-700">
                                    <?php _e('Sort by:', 'le-bon-resto'); ?>
                                </label>
                                <select 
                                    id="sort-order" 
                                    name="sort"
                                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                                >
                                    <option value="featured"><?php _e('Featured First', 'le-bon-resto'); ?></option>
                                    <option value="newest"><?php _e('Newest First', 'le-bon-resto'); ?></option>
                                    <option value="oldest"><?php _e('Oldest First', 'le-bon-resto'); ?></option>
                                    <option value="name"><?php _e('Name A-Z', 'le-bon-resto'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Restaurant Cards Grid -->
                    <div id="restaurants-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                        <!-- Cards will be loaded here via JavaScript -->
                        <div class="col-span-full text-center py-12">
                            <div class="loading-spinner mx-auto mb-4"></div>
                            <p class="text-gray-500"><?php _e('Loading restaurants...', 'le-bon-resto'); ?></p>
                        </div>
                    </div>
                    
                    <!-- Load More Button -->
                    <div id="pagination-container" class="text-center">
                        <button 
                            id="load-more-btn" 
                            class="hidden px-8 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200"
                            style="background-color: #FFC107;"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            <?php _e('Load More Restaurants', 'le-bon-resto'); ?>
                        </button>
                    </div>
                    
                    <!-- No Results Message -->
                    <div id="no-results" class="hidden text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-search text-5xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">
                            <?php _e('No restaurants found', 'le-bon-resto'); ?>
                        </h3>
                        <p class="text-gray-500 mb-6">
                            <?php _e('Try adjusting your search criteria or clearing some filters.', 'le-bon-resto'); ?>
                        </p>
                        <button 
                            onclick="clearAllFilters()"
                            class="px-6 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200"
                            style="background-color: #FFC107;"
                        >
                            <i class="fas fa-eraser mr-2"></i>
                            <?php _e('Clear All Filters', 'le-bon-resto'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Loading spinner */
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #FFC107;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Card hover effects */
        .restaurant-card {
            transition: all 0.3s ease;
        }

        .restaurant-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        /* FontAwesome CDN */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    </style>

    <?php
    return ob_get_clean();
}

/**
 * 2. Single Restaurant Page - Already exists, just ensure it's properly registered
 */
function lebonresto_single_restaurant_page_shortcode($atts) {
    // This shortcode just displays the current single restaurant page
    // The template is handled by the single-restaurant.php template
    return '<div class="lebonresto-single-restaurant-page">' . 
           __('This shortcode displays the single restaurant page. Use it on individual restaurant pages.', 'le-bon-resto') . 
           '</div>';
}

/**
 * 3. Map Page - 100vh width, 75vh height with filters in header
 */
function lebonresto_map_page_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'width' => '100%',
            'height' => '75vh',
            'zoom' => 12,
            'center_lat' => 48.8566,
            'center_lng' => 2.3522,
        ),
        $atts,
        'lebonresto_map_page'
    );

    // Enqueue Tailwind CSS
    wp_enqueue_style(
        'tailwind-css',
        'https://cdn.tailwindcss.com',
        array(),
        '3.4.0'
    );

    // Enqueue Leaflet CSS and JS
    wp_enqueue_style(
        'leaflet-css',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
        array(),
        '1.9.4'
    );

    wp_enqueue_script(
        'leaflet-js',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        array(),
        '1.9.4',
        true
    );

    // Enqueue custom map script
    wp_enqueue_script(
        'lebonresto-map-page',
        LEBONRESTO_PLUGIN_URL . 'assets/js/single-restaurant-map.js',
        array('leaflet-js', 'wp-api'),
        LEBONRESTO_PLUGIN_VERSION,
        true
    );

    // Get plugin options
    $options = get_option('lebonresto_options', array());
    
    // Localize script
    wp_localize_script(
        'lebonresto-map-page',
        'lebonrestoMapPage',
        array(
            'apiUrl' => home_url('/wp-json/lebonresto/v1/restaurants'),
            'cuisineTypesUrl' => home_url('/wp-json/lebonresto/v1/cuisine-types'),
            'nonce' => wp_create_nonce('wp_rest'),
            'mapConfig' => array(
                'zoom' => intval($atts['zoom']),
                'centerLat' => floatval($atts['center_lat']),
                'centerLng' => floatval($atts['center_lng']),
            ),
            'pluginSettings' => array(
                'defaultRadius' => isset($options['default_radius']) ? intval($options['default_radius']) : 25,
                'maxRadius' => isset($options['max_radius']) ? intval($options['max_radius']) : 100,
                'primaryColor' => isset($options['primary_color']) ? $options['primary_color'] : '#FFC107',
            ),
        )
    );

    // Get cuisine types
    $cuisine_types = lebonresto_get_cuisine_types();

    // Build HTML output
    ob_start();
    ?>
    <div class="lebonresto-map-page bg-gray-50 min-h-screen">
        <div class="container mx-auto px-4 py-8" style="max-width: 100%;">
            
            <!-- Page Header -->
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-bold text-gray-800 mb-4" style="color: #FFC107;">
                    <?php _e('Restaurant Map', 'le-bon-resto'); ?>
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    <?php _e('Explore restaurants on the map. Use filters to find the perfect dining experience.', 'le-bon-resto'); ?>
                </p>
            </div>

            <!-- Filters Header -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    
                    <!-- Restaurant Name Search -->
                    <div class="lg:col-span-2">
                        <label for="restaurant-name-filter" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-2"></i><?php _e('Search Restaurant or City', 'le-bon-resto'); ?>
                        </label>
                        <input 
                            type="text" 
                            id="restaurant-name-filter" 
                            name="search" 
                            placeholder="<?php _e('Enter restaurant name or city...', 'le-bon-resto'); ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent text-lg"
                        />
                    </div>

                    <!-- City Filter -->
                    <div>
                        <label for="city-filter" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php _e('City', 'le-bon-resto'); ?>
                        </label>
                        <input 
                            type="text" 
                            id="city-filter" 
                            name="city"
                            placeholder="<?php _e('Enter city...', 'le-bon-resto'); ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        />
                    </div>

                    <!-- Cuisine Filter -->
                    <div>
                        <label for="cuisine-filter" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php _e('Cuisine Type', 'le-bon-resto'); ?>
                        </label>
                        <select 
                            id="cuisine-filter" 
                            name="cuisine"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        >
                            <option value=""><?php _e('All Cuisines', 'le-bon-resto'); ?></option>
                            <?php foreach ($cuisine_types as $cuisine): ?>
                                <option value="<?php echo esc_attr($cuisine); ?>">
                                    <?php echo esc_html(ucfirst($cuisine)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-4 mt-6">
                    <button 
                        type="button" 
                        id="search-restaurants-btn"
                        class="px-6 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                        style="background-color: #FFC107;"
                    >
                        <i class="fas fa-search mr-2"></i><?php _e('Search', 'le-bon-resto'); ?>
                    </button>
                    
                    <button 
                        type="button" 
                        id="clear-filters-btn"
                        class="px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-200"
                    >
                        <i class="fas fa-eraser mr-2"></i><?php _e('Clear', 'le-bon-resto'); ?>
                    </button>
                </div>
            </div>

            <!-- Map Container -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div id="lebonresto-map-page" style="width: 100%; height: 75vh;"></div>
            </div>

            <!-- Results Info -->
            <div class="mt-6 flex justify-between items-center">
                <div id="map-results-count" class="text-sm text-gray-600"></div>
                <div id="map-location-info" class="text-xs text-gray-500"></div>
            </div>
        </div>
    </div>

    <style>
        /* FontAwesome CDN */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    </style>

    <?php
    return ob_get_clean();
}

/**
 * 4. Restaurant Details Page - Gallery grids with video and information
 */
function lebonresto_restaurant_details_page_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'restaurant_id' => '',
            'show_gallery' => 'true',
            'show_video' => 'true',
            'show_info' => 'true',
        ),
        $atts,
        'lebonresto_details_page'
    );

    // Enqueue Tailwind CSS
    wp_enqueue_style(
        'tailwind-css',
        'https://cdn.tailwindcss.com',
        array(),
        '3.4.0'
    );

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

    // Enqueue restaurant detail styles and scripts
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

    // Get restaurant ID
    $restaurant_id = !empty($atts['restaurant_id']) ? intval($atts['restaurant_id']) : get_the_ID();
    
    if (!$restaurant_id || get_post_type($restaurant_id) !== 'restaurant') {
        return '<div class="error">' . __('Invalid restaurant ID or not a restaurant post.', 'le-bon-resto') . '</div>';
    }

    // Get restaurant data
    $restaurant = get_post($restaurant_id);
    $description = get_post_meta($restaurant_id, '_restaurant_description', true);
    $address = get_post_meta($restaurant_id, '_restaurant_address', true);
    $city = get_post_meta($restaurant_id, '_restaurant_city', true);
    $cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true);
    $phone = get_post_meta($restaurant_id, '_restaurant_phone', true);
    $email = get_post_meta($restaurant_id, '_restaurant_email', true);
    $video_url = get_post_meta($restaurant_id, '_restaurant_video_url', true);
    $virtual_tour_url = get_post_meta($restaurant_id, '_restaurant_virtual_tour_url', true);
    $is_featured = get_post_meta($restaurant_id, '_restaurant_is_featured', true);

    // Get gallery images
    $gallery_images = lebonresto_get_gallery_images($restaurant_id);

    // Build HTML output
    ob_start();
    ?>
    <div class="lebonresto-restaurant-details-page bg-gray-50 min-h-screen">
        <div class="container mx-auto px-4 py-8" style="max-width: 1200px;">
            
            <!-- Restaurant Header -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between mb-6">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-800 mb-2" style="color: #FFC107;">
                            <?php echo esc_html($restaurant->post_title); ?>
                        </h1>
                        <?php if ($is_featured === '1'): ?>
                            <span class="inline-block px-3 py-1 bg-yellow-400 text-gray-800 text-sm font-semibold rounded-full">
                                <i class="fas fa-star mr-1"></i><?php _e('Featured Restaurant', 'le-bon-resto'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Contact Buttons -->
                    <div class="flex flex-wrap gap-3 mt-4 lg:mt-0">
                        <?php if ($phone): ?>
                            <a href="tel:<?php echo esc_attr($phone); ?>" 
                               class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition duration-200">
                                <i class="fas fa-phone mr-2"></i><?php echo esc_html($phone); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($email): ?>
                            <a href="mailto:<?php echo esc_attr($email); ?>" 
                               class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition duration-200">
                                <i class="fas fa-envelope mr-2"></i><?php _e('Email', 'le-bon-resto'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Restaurant Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php if ($address || $city): ?>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-gray-800 mb-2">
                                <i class="fas fa-map-marker-alt mr-2" style="color: #FFC107;"></i>
                                <?php _e('Address', 'le-bon-resto'); ?>
                            </h3>
                            <p class="text-gray-600">
                                <?php echo esc_html($address); ?>
                                <?php if ($city): ?>
                                    <br><?php echo esc_html($city); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($cuisine_type): ?>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-gray-800 mb-2">
                                <i class="fas fa-utensils mr-2" style="color: #FFC107;"></i>
                                <?php _e('Cuisine Type', 'le-bon-resto'); ?>
                            </h3>
                            <p class="text-gray-600"><?php echo esc_html(ucfirst($cuisine_type)); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($phone): ?>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-gray-800 mb-2">
                                <i class="fas fa-phone mr-2" style="color: #FFC107;"></i>
                                <?php _e('Phone', 'le-bon-resto'); ?>
                            </h3>
                            <p class="text-gray-600"><?php echo esc_html($phone); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($email): ?>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-gray-800 mb-2">
                                <i class="fas fa-envelope mr-2" style="color: #FFC107;"></i>
                                <?php _e('Email', 'le-bon-resto'); ?>
                            </h3>
                            <p class="text-gray-600"><?php echo esc_html($email); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <?php if ($description): ?>
                    <div class="mt-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-3">
                            <?php _e('About This Restaurant', 'le-bon-resto'); ?>
                        </h3>
                        <p class="text-gray-600 leading-relaxed"><?php echo esc_html($description); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Video Section -->
            <?php if ($video_url && $atts['show_video'] === 'true'): ?>
                <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6" style="color: #FFC107;">
                        <i class="fas fa-play-circle mr-2"></i>
                        <?php _e('Restaurant Video', 'le-bon-resto'); ?>
                    </h2>
                    <div class="lebonresto-video-container">
                        <?php echo lebonresto_get_video_embed($video_url); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Gallery Section -->
            <?php if (!empty($gallery_images) && $atts['show_gallery'] === 'true'): ?>
                <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6" style="color: #FFC107;">
                        <i class="fas fa-images mr-2"></i>
                        <?php _e('Restaurant Gallery', 'le-bon-resto'); ?>
                    </h2>
                    <div class="lebonresto-gallery-grid">
                        <?php foreach ($gallery_images as $image): ?>
                            <div class="lebonresto-gallery-item">
                                <a href="<?php echo esc_url($image['url']); ?>" 
                                   data-lightbox="restaurant-gallery" 
                                   data-title="<?php echo esc_attr($image['title']); ?>">
                                    <img src="<?php echo esc_url($image['thumbnail']); ?>" 
                                         alt="<?php echo esc_attr($image['alt']); ?>" 
                                         loading="lazy">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Virtual Tour Section -->
            <?php if ($virtual_tour_url): ?>
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6" style="color: #FFC107;">
                        <i class="fas fa-vr-cardboard mr-2"></i>
                        <?php _e('Virtual Tour', 'le-bon-resto'); ?>
                    </h2>
                    <div class="lebonresto-virtual-tour-container">
                        <?php echo lebonresto_get_virtual_tour_embed($virtual_tour_url); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .lebonresto-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
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

        .lebonresto-virtual-tour-container {
            width: 100%;
            height: 600px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .lebonresto-virtual-tour-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* FontAwesome CDN */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    </style>

    <?php
    return ob_get_clean();
}

/**
 * 5. Map Only Shortcode
 */
function lebonresto_map_only_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'width' => '100%',
            'height' => '500px',
            'zoom' => 12,
            'center_lat' => 48.8566,
            'center_lng' => 2.3522,
        ),
        $atts,
        'lebonresto_map_only'
    );

    // Enqueue Leaflet CSS and JS
    wp_enqueue_style(
        'leaflet-css',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
        array(),
        '1.9.4'
    );

    wp_enqueue_script(
        'leaflet-js',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        array(),
        '1.9.4',
        true
    );

    // Enqueue custom map script
    wp_enqueue_script(
        'lebonresto-map-only',
        LEBONRESTO_PLUGIN_URL . 'assets/js/single-restaurant-map.js',
        array('leaflet-js', 'wp-api'),
        LEBONRESTO_PLUGIN_VERSION,
        true
    );

    // Get plugin options
    $options = get_option('lebonresto_options', array());
    
    // Localize script
    wp_localize_script(
        'lebonresto-map-only',
        'lebonrestoMapOnly',
        array(
            'apiUrl' => home_url('/wp-json/lebonresto/v1/restaurants'),
            'nonce' => wp_create_nonce('wp_rest'),
            'mapConfig' => array(
                'zoom' => intval($atts['zoom']),
                'centerLat' => floatval($atts['center_lat']),
                'centerLng' => floatval($atts['center_lng']),
            ),
            'pluginSettings' => array(
                'primaryColor' => isset($options['primary_color']) ? $options['primary_color'] : '#FFC107',
            ),
        )
    );

    return sprintf(
        '<div id="lebonresto-map-only" style="width: %s; height: %s; border: 1px solid #ddd; border-radius: 8px;"></div>',
        esc_attr($atts['width']),
        esc_attr($atts['height'])
    );
}

/**
 * 6. Gallery Grids Only Shortcode
 */
function lebonresto_gallery_only_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'restaurant_id' => '',
            'columns' => 3,
            'show_titles' => 'true',
        ),
        $atts,
        'lebonresto_gallery_only'
    );

    // Enqueue Tailwind CSS
    wp_enqueue_style(
        'tailwind-css',
        'https://cdn.tailwindcss.com',
        array(),
        '3.4.0'
    );

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

    // Get restaurant ID
    $restaurant_id = !empty($atts['restaurant_id']) ? intval($atts['restaurant_id']) : get_the_ID();
    
    if (!$restaurant_id || get_post_type($restaurant_id) !== 'restaurant') {
        return '<div class="error">' . __('Invalid restaurant ID or not a restaurant post.', 'le-bon-resto') . '</div>';
    }

    // Get gallery images
    $gallery_images = lebonresto_get_gallery_images($restaurant_id);

    if (empty($gallery_images)) {
        return '<div class="text-center py-8 text-gray-500">' . __('No gallery images found for this restaurant.', 'le-bon-resto') . '</div>';
    }

    // Build HTML output
    ob_start();
    ?>
    <div class="lebonresto-gallery-only">
        <div class="lebonresto-gallery-grid" style="display: grid; grid-template-columns: repeat(<?php echo intval($atts['columns']); ?>, 1fr); gap: 20px;">
            <?php foreach ($gallery_images as $image): ?>
                <div class="lebonresto-gallery-item">
                    <a href="<?php echo esc_url($image['url']); ?>" 
                       data-lightbox="restaurant-gallery" 
                       data-title="<?php echo esc_attr($image['title']); ?>">
                        <img src="<?php echo esc_url($image['thumbnail']); ?>" 
                             alt="<?php echo esc_attr($image['alt']); ?>" 
                             loading="lazy"
                             style="width: 100%; height: 250px; object-fit: cover; border-radius: 8px;">
                    </a>
                    <?php if ($atts['show_titles'] === 'true' && !empty($image['title'])): ?>
                        <div class="mt-2 text-center">
                            <p class="text-sm text-gray-600"><?php echo esc_html($image['title']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <style>
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

        @media (max-width: 768px) {
            .lebonresto-gallery-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 480px) {
            .lebonresto-gallery-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <?php
    return ob_get_clean();
}

/**
 * Restaurant Detail Page Shortcode [lebonresto_detail]
 */
function lebonresto_restaurant_detail_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'restaurant_id' => '',
            'show_gallery' => 'true',
            'show_video' => 'true',
            'show_info' => 'true',
        ),
        $atts,
        'lebonresto_detail'
    );

    // Enqueue Tailwind CSS
    wp_enqueue_style(
        'tailwind-css',
        'https://cdn.tailwindcss.com',
        array(),
        '3.4.0'
    );

    // Enqueue restaurant detail styles and scripts
    wp_enqueue_style(
        'lebonresto-detail-css',
        LEBONRESTO_PLUGIN_URL . 'assets/css/restaurant-detail.css',
        array('tailwind-css'),
        LEBONRESTO_PLUGIN_VERSION
    );

    wp_enqueue_script(
        'lebonresto-detail-js',
        LEBONRESTO_PLUGIN_URL . 'assets/js/restaurant-detail.js',
        array('jquery'),
        LEBONRESTO_PLUGIN_VERSION,
        true
    );

    // Get restaurant ID
    $restaurant_id = !empty($atts['restaurant_id']) ? intval($atts['restaurant_id']) : get_the_ID();
    
    if (!$restaurant_id || get_post_type($restaurant_id) !== 'restaurant') {
        return '<div class="error">' . __('Invalid restaurant ID or not a restaurant post.', 'le-bon-resto') . '</div>';
    }

    // Get restaurant data
    $restaurant = get_post($restaurant_id);
    $description = get_post_meta($restaurant_id, '_restaurant_description', true);
    $address = get_post_meta($restaurant_id, '_restaurant_address', true);
    $city = get_post_meta($restaurant_id, '_restaurant_city', true);
    $cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true);
    $phone = get_post_meta($restaurant_id, '_restaurant_phone', true);
    $email = get_post_meta($restaurant_id, '_restaurant_email', true);
    $video_url = get_post_meta($restaurant_id, '_restaurant_video_url', true);
    $virtual_tour_url = get_post_meta($restaurant_id, '_restaurant_virtual_tour_url', true);
    $is_featured = get_post_meta($restaurant_id, '_restaurant_is_featured', true);
    $principal_image = get_post_meta($restaurant_id, '_restaurant_principal_image', true);

    // Get gallery images
    if (function_exists('lebonresto_get_gallery_images')) {
        $gallery_images = lebonresto_get_gallery_images($restaurant_id);
    } else {
        $gallery_ids = get_post_meta($restaurant_id, '_restaurant_gallery', true);
        $gallery_images = array();
        
        if ($gallery_ids) {
            $image_ids = explode(',', $gallery_ids);
            foreach ($image_ids as $image_id) {
                $image_id = intval($image_id);
                if ($image_id) {
                    $image_url = wp_get_attachment_image_url($image_id, 'large');
                    if ($image_url) {
                        $gallery_images[] = array(
                            'id' => $image_id,
                            'url' => $image_url,
                            'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true)
                        );
                    }
                }
            }
        }
    }

    // Build HTML output
    ob_start();
    ?>
    <div class="lebonresto-detail-layout min-h-screen bg-gray-50">
        
        <!-- Hero Section with Restaurant Info -->
        <div class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 overflow-hidden">
            <!-- Background Image -->
            <?php if ($principal_image): ?>
            <div class="absolute inset-0 z-0">
                <img src="<?php echo esc_url($principal_image); ?>" 
                     alt="<?php echo esc_attr($restaurant->post_title); ?>" 
                     class="w-full h-full object-cover opacity-40">
                <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-black/40 to-black/60"></div>
            </div>
            <?php endif; ?>
            
            <!-- Content -->
            <div class="relative z-10 container mx-auto px-4 py-16 lg:py-24">
                <div class="max-w-4xl mx-auto text-center">
                    <!-- Featured Badge -->
                    <?php if ($is_featured === '1'): ?>
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-yellow-400 text-yellow-900 text-sm font-semibold mb-6">
                        <i class="fas fa-star mr-2"></i>
                        <?php _e('Featured Restaurant', 'le-bon-resto'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Restaurant Title -->
                    <h1 class="text-4xl lg:text-6xl font-bold text-white mb-6">
                        <?php echo esc_html($restaurant->post_title); ?>
                    </h1>
                    
                    <!-- Restaurant Meta Info -->
                    <div class="flex flex-wrap justify-center items-center gap-6 text-gray-300 mb-8">
                        <?php if ($city): ?>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-yellow-400 mr-2"></i>
                            <span><?php echo esc_html($city); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($cuisine_type): ?>
                        <div class="flex items-center">
                            <i class="fas fa-utensils text-yellow-400 mr-2"></i>
                            <span><?php echo esc_html(ucfirst($cuisine_type)); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($phone): ?>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-yellow-400 mr-2"></i>
                            <a href="tel:<?php echo esc_attr($phone); ?>" class="hover:text-yellow-400 transition-colors">
                                <?php echo esc_html($phone); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap justify-center gap-4">
                        <?php if ($phone): ?>
                        <a href="tel:<?php echo esc_attr($phone); ?>" 
                           class="inline-flex items-center px-6 py-3 bg-yellow-400 text-yellow-900 font-semibold rounded-lg hover:bg-yellow-500 transition-colors">
                            <i class="fas fa-phone mr-2"></i>
                            <?php _e('Call Now', 'le-bon-resto'); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($email): ?>
                        <a href="mailto:<?php echo esc_attr($email); ?>" 
                           class="inline-flex items-center px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm">
                            <i class="fas fa-envelope mr-2"></i>
                            <?php _e('Send Email', 'le-bon-resto'); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($virtual_tour_url): ?>
                        <a href="<?php echo esc_url($virtual_tour_url); ?>" 
                           target="_blank"
                           class="inline-flex items-center px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm">
                            <i class="fas fa-vr-cardboard mr-2"></i>
                            <?php _e('Virtual Tour', 'le-bon-resto'); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="container mx-auto px-4 py-12">
            <div class="max-w-6xl mx-auto">
                
                <!-- Restaurant Description -->
                <?php if ($description): ?>
                <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">
                        <i class="fas fa-info-circle text-yellow-400 mr-3"></i>
                        <?php _e('About This Restaurant', 'le-bon-resto'); ?>
                    </h2>
                    <div class="prose prose-lg max-w-none text-gray-700">
                        <?php echo wpautop(esc_html($description)); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Gallery Section -->
                <?php if (!empty($gallery_images) || $video_url): ?>
                <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-8">
                        <i class="fas fa-images text-yellow-400 mr-3"></i>
                        <?php _e('Gallery & Media', 'le-bon-resto'); ?>
                    </h2>
                    
                    <!-- Video Section -->
                    <?php if ($video_url): ?>
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Restaurant Video', 'le-bon-resto'); ?></h3>
                        <div class="relative aspect-video rounded-xl overflow-hidden shadow-lg">
                            <iframe src="<?php echo esc_url($video_url); ?>" 
                                    class="w-full h-full" 
                                    frameborder="0" 
                                    allowfullscreen
                                    loading="lazy">
                            </iframe>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Image Gallery Grid -->
                    <?php if (!empty($gallery_images)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="gallery-grid">
                        <?php foreach ($gallery_images as $index => $image): ?>
                        <div class="group relative overflow-hidden rounded-xl shadow-lg cursor-pointer gallery-item" 
                             data-index="<?php echo $index; ?>">
                            <img src="<?php echo esc_url($image['url']); ?>" 
                                 alt="<?php echo esc_attr($image['alt'] ?: $restaurant->post_title); ?>" 
                                 class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 flex items-center justify-center">
                                <i class="fas fa-search-plus text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Restaurant Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                    <!-- Contact Information -->
                    <div class="bg-white rounded-2xl shadow-xl p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">
                            <i class="fas fa-address-book text-yellow-400 mr-3"></i>
                            <?php _e('Contact Information', 'le-bon-resto'); ?>
                        </h3>
                        
                        <div class="space-y-4">
                            <?php if ($address): ?>
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-yellow-400 mt-1 mr-3"></i>
                                <div>
                                    <p class="font-semibold text-gray-900"><?php _e('Address', 'le-bon-resto'); ?></p>
                                    <p class="text-gray-700"><?php echo esc_html($address); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($phone): ?>
                            <div class="flex items-center">
                                <i class="fas fa-phone text-yellow-400 mr-3"></i>
                                <div>
                                    <p class="font-semibold text-gray-900"><?php _e('Phone', 'le-bon-resto'); ?></p>
                                    <a href="tel:<?php echo esc_attr($phone); ?>" class="text-yellow-600 hover:text-yellow-700">
                                        <?php echo esc_html($phone); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($email): ?>
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-yellow-400 mr-3"></i>
                                <div>
                                    <p class="font-semibold text-gray-900"><?php _e('Email', 'le-bon-resto'); ?></p>
                                    <a href="mailto:<?php echo esc_attr($email); ?>" class="text-yellow-600 hover:text-yellow-700">
                                        <?php echo esc_html($email); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Restaurant Features -->
                    <div class="bg-white rounded-2xl shadow-xl p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">
                            <i class="fas fa-star text-yellow-400 mr-3"></i>
                            <?php _e('Restaurant Features', 'le-bon-resto'); ?>
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <i class="fas fa-utensils text-yellow-400 mr-3"></i>
                                <span class="text-gray-700">
                                    <strong><?php _e('Cuisine Type:', 'le-bon-resto'); ?></strong> 
                                    <?php echo esc_html(ucfirst($cuisine_type ?: __('Not specified', 'le-bon-resto'))); ?>
                                </span>
                            </div>
                            
                            <?php if ($is_featured === '1'): ?>
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-400 mr-3"></i>
                                <span class="text-gray-700">
                                    <strong><?php _e('Featured Restaurant', 'le-bon-resto'); ?></strong>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($virtual_tour_url): ?>
                            <div class="flex items-center">
                                <i class="fas fa-vr-cardboard text-yellow-400 mr-3"></i>
                                <span class="text-gray-700">
                                    <strong><?php _e('Virtual Tour Available', 'le-bon-resto'); ?></strong>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($video_url): ?>
                            <div class="flex items-center">
                                <i class="fas fa-video text-yellow-400 mr-3"></i>
                                <span class="text-gray-700">
                                    <strong><?php _e('Restaurant Video Available', 'le-bon-resto'); ?></strong>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Back to Restaurants -->
                <div class="text-center">
                    <a href="<?php echo home_url('/restaurants/'); ?>" 
                       class="inline-flex items-center px-8 py-4 bg-yellow-400 text-yellow-900 font-semibold rounded-lg hover:bg-yellow-500 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <?php _e('Back to All Restaurants', 'le-bon-resto'); ?>
                    </a>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Gallery Lightbox Modal -->
    <div id="gallery-lightbox" class="fixed inset-0 bg-black/90 z-50 hidden items-center justify-center p-4">
        <div class="relative max-w-4xl max-h-full">
            <button id="close-lightbox" class="absolute top-4 right-4 text-white text-2xl hover:text-yellow-400 z-10">
                <i class="fas fa-times"></i>
            </button>
            <button id="prev-image" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-2xl hover:text-yellow-400 z-10">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button id="next-image" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-2xl hover:text-yellow-400 z-10">
                <i class="fas fa-chevron-right"></i>
            </button>
            <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
            <div id="image-counter" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white text-sm bg-black/50 px-3 py-1 rounded-full"></div>
        </div>
    </div>

    <script type="application/json" id="gallery-data">
    <?php echo wp_json_encode($gallery_images); ?>
    </script>

    <?php
    return ob_get_clean();
}

// Register all shortcodes
add_shortcode('lebonresto_all_page', 'lebonresto_all_restaurants_page_shortcode');
add_shortcode('lebonresto_single_page', 'lebonresto_single_restaurant_page_shortcode');
add_shortcode('lebonresto_map_page', 'lebonresto_map_page_shortcode');
add_shortcode('lebonresto_details_page', 'lebonresto_restaurant_details_page_shortcode');
add_shortcode('lebonresto_map_only', 'lebonresto_map_only_shortcode');
add_shortcode('lebonresto_gallery_only', 'lebonresto_gallery_only_shortcode');
add_shortcode('lebonresto_detail', 'lebonresto_restaurant_detail_shortcode');
