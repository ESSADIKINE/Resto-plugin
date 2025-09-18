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
 * All Restaurants Page Shortcode [lebonresto_all_page]
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
    wp_enqueue_style('tailwind-css', 'https://cdn.tailwindcss.com', array(), '3.4.0');

    // Enqueue All Restaurants CSS
        wp_enqueue_style(
        'lebonresto-all-restaurants-css',
        LEBONRESTO_PLUGIN_URL . 'assets/css/all-restaurants.css',
        array('tailwind-css'),
        LEBONRESTO_PLUGIN_VERSION
    );

    // Enqueue All Restaurants JavaScript
        wp_enqueue_script(
        'lebonresto-all-restaurants-js',
        LEBONRESTO_PLUGIN_URL . 'assets/js/all-restaurants.js',
        array('jquery'),
            LEBONRESTO_PLUGIN_VERSION,
            true
        );

    // Start output buffering
    ob_start();
    
    // Include the all restaurants template
    include LEBONRESTO_PLUGIN_PATH . 'templates/all-restaurants.php';
    
    return ob_get_clean();
}

/**
 * Single Restaurant Page Shortcode [lebonresto_single_page]
 */
function lebonresto_single_restaurant_page_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'restaurant_id' => get_the_ID(),
            'show_map' => 'true',
            'show_virtual_tour' => 'true',
            'show_filters' => 'true',
        ),
        $atts,
        'lebonresto_single_page'
    );

    // Enqueue required styles and scripts
    wp_enqueue_style('tailwind-css', 'https://cdn.tailwindcss.com', array(), '3.4.0');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);
    wp_enqueue_script('lebonresto-single-updated', LEBONRESTO_PLUGIN_URL . 'assets/js/single-restaurant.js', array('leaflet-js', 'wp-api'), LEBONRESTO_PLUGIN_VERSION, true);
    wp_enqueue_script('lebonresto-single-map', LEBONRESTO_PLUGIN_URL . 'assets/js/single-restaurant-map.js', array('leaflet-js', 'wp-api'), LEBONRESTO_PLUGIN_VERSION, true);
    wp_enqueue_style('lebonresto-debug-css', LEBONRESTO_PLUGIN_URL . 'assets/css/single-restaurant.css', array('tailwind-css'), LEBONRESTO_PLUGIN_VERSION);

    // Start output buffering
    ob_start();
    
    // Include the single restaurant template
    include LEBONRESTO_PLUGIN_PATH . 'templates/single-restaurant.php';
    
    return ob_get_clean();
}

/**
 * Map Page Shortcode [lebonresto_map_page]
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
    wp_enqueue_style('tailwind-css', 'https://cdn.tailwindcss.com', array(), '3.4.0');

    // Enqueue Leaflet CSS and JS
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);

    // Start output buffering
    ob_start();
    ?>
    <div class="lebonresto-map-page bg-gray-50 min-h-screen">
        <!-- Filter Header -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
            <div class="container mx-auto px-4 py-4">
                <div class="flex flex-col lg:flex-row items-center gap-4">
                    <!-- Restaurant Name Search -->
                    <div class="flex-1">
                            <input 
                                type="text" 
                                id="restaurant-name-filter" 
                            placeholder="<?php _e('Search restaurants...', 'le-bon-resto'); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            />
                        </div>
                        
                    <!-- City Filter -->
                    <div class="w-full lg:w-48">
                            <input 
                                type="text" 
                                id="city-filter" 
                            placeholder="<?php _e('City...', 'le-bon-resto'); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            />
                        </div>
                        
                    <!-- Cuisine Filter -->
                    <div class="w-full lg:w-48">
                            <select 
                                id="cuisine-filter" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            >
                                <option value=""><?php _e('All Cuisines', 'le-bon-resto'); ?></option>
                            <option value="french"><?php _e('French', 'le-bon-resto'); ?></option>
                            <option value="italian"><?php _e('Italian', 'le-bon-resto'); ?></option>
                            <option value="mediterranean"><?php _e('Mediterranean', 'le-bon-resto'); ?></option>
                            </select>
                        </div>
                        
                    <!-- Search Button -->
                            <button 
                        id="search-restaurants"
                        class="w-full lg:w-auto px-6 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200"
                    >
                        <i class="fas fa-search mr-2"></i><?php _e('Search', 'le-bon-resto'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
        <!-- Map Container -->
        <div class="p-4">
            <div class="bg-white rounded-lg shadow-lg p-4">
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
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    </style>

    <?php
    return ob_get_clean();
}

/**
 * Restaurant Details Page Shortcode [lebonresto_details_page]
 */
function lebonresto_restaurant_details_page_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'restaurant_id' => get_the_ID(),
            'show_gallery' => 'true',
            'show_video' => 'true',
            'show_info' => 'true',
        ),
        $atts,
        'lebonresto_details_page'
    );

    // Enqueue required styles and scripts
    wp_enqueue_style('tailwind-css', 'https://cdn.tailwindcss.com', array(), '3.4.0');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);
    wp_enqueue_style('lebonresto-detail-css', LEBONRESTO_PLUGIN_URL . 'assets/css/restaurant-detail.css', array('tailwind-css'), LEBONRESTO_PLUGIN_VERSION);

    // Start output buffering
    ob_start();
    
    // Include the restaurant detail template
    include LEBONRESTO_PLUGIN_PATH . 'templates/restaurant-detail.php';
    
    return ob_get_clean();
}

/**
 * Map Only Shortcode [lebonresto_map_only]
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

    // Enqueue Tailwind CSS
    wp_enqueue_style('tailwind-css', 'https://cdn.tailwindcss.com', array(), '3.4.0');

    // Enqueue Leaflet CSS and JS
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);

    return sprintf(
        '<div id="lebonresto-map-only" style="width: %s; height: %s; border: 1px solid #ddd; border-radius: 8px;"></div>',
        esc_attr($atts['width']),
        esc_attr($atts['height'])
    );
}

/**
 * Gallery Only Shortcode [lebonresto_gallery_only]
 */
function lebonresto_gallery_only_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'restaurant_id' => get_the_ID(),
            'columns' => 3,
            'show_captions' => 'true',
        ),
        $atts,
        'lebonresto_gallery_only'
    );

    // Enqueue Tailwind CSS
    wp_enqueue_style('tailwind-css', 'https://cdn.tailwindcss.com', array(), '3.4.0');

    // Get restaurant ID
    $restaurant_id = !empty($atts['restaurant_id']) ? intval($atts['restaurant_id']) : get_the_ID();
    
    if (!$restaurant_id || get_post_type($restaurant_id) !== 'restaurant') {
        return '<div class="error">' . __('Invalid restaurant ID or not a restaurant post.', 'le-bon-resto') . '</div>';
    }

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

    if (empty($gallery_images)) {
        return '<div class="text-center py-8 text-gray-500">' . __('No gallery images found.', 'le-bon-resto') . '</div>';
    }

    // Start output buffering
    ob_start();
    ?>
    <div class="lebonresto-gallery-only">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-<?php echo esc_attr($atts['columns']); ?> gap-4">
            <?php foreach ($gallery_images as $image): ?>
                <div class="relative group cursor-pointer">
                    <img src="<?php echo esc_url($image['url']); ?>" 
                         alt="<?php echo esc_attr($image['alt'] ?: get_the_title($restaurant_id)); ?>" 
                         class="w-full h-48 object-cover rounded-lg shadow-md transition duration-300 group-hover:shadow-lg group-hover:scale-105">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition duration-300 rounded-lg flex items-center justify-center">
                        <i class="fas fa-search-plus text-white text-2xl opacity-0 group-hover:opacity-100 transition duration-300"></i>
                    </div>
                    <?php if ($atts['show_captions'] === 'true' && !empty($image['alt'])): ?>
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-2 rounded-b-lg">
                            <p class="text-sm"><?php echo esc_html($image['alt']); ?></p>
                </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <style>
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
        
        .lebonresto-gallery-only .group:hover img {
            transform: scale(1.05);
        }
    </style>

    <?php
    return ob_get_clean();
}

/**
 * Restaurant Detail Shortcode [lebonresto_detail]
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
    wp_enqueue_style('tailwind-css', 'https://cdn.tailwindcss.com', array(), '3.4.0');

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
            <div class="relative z-10 container mx-auto px-4 py-16">
                <div class="max-w-4xl">
                    <h1 class="text-5xl font-bold text-white mb-4">
                            <?php echo esc_html($restaurant->post_title); ?>
                        </h1>
                    
                        <?php if ($is_featured === '1'): ?>
                        <div class="inline-block px-4 py-2 bg-yellow-400 text-gray-800 text-sm font-semibold rounded-full mb-6">
                                <i class="fas fa-star mr-1"></i><?php _e('Featured Restaurant', 'le-bon-resto'); ?>
                    </div>
                        <?php endif; ?>
                        
                    <div class="flex flex-wrap gap-4 mb-6">
                    <?php if ($address || $city): ?>
                            <div class="flex items-center text-white">
                                <i class="fas fa-map-marker-alt mr-2 text-yellow-400"></i>
                                <span><?php echo esc_html($address); ?><?php if ($city): ?>, <?php echo esc_html($city); ?><?php endif; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($cuisine_type): ?>
                            <div class="flex items-center text-white">
                                <i class="fas fa-utensils mr-2 text-yellow-400"></i>
                                <span><?php echo esc_html(ucfirst($cuisine_type)); ?></span>
                        </div>
                    <?php endif; ?>
                    </div>
                    
                    <?php if ($description): ?>
                        <p class="text-xl text-gray-200 leading-relaxed"><?php echo esc_html($description); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Restaurant Information Cards -->
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                    <?php if ($phone): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-phone text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php _e('Phone', 'le-bon-resto'); ?></h3>
                            <p class="text-gray-600"><?php echo esc_html($phone); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($email): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-envelope text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php _e('Email', 'le-bon-resto'); ?></h3>
                            <p class="text-gray-600"><?php echo esc_html($email); ?></p>
                        </div>
                    <?php endif; ?>
                
                <?php if ($video_url): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-play-circle text-red-600 text-2xl"></i>
                </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php _e('Video', 'le-bon-resto'); ?></h3>
                        <p class="text-gray-600"><?php _e('Available', 'le-bon-resto'); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($virtual_tour_url): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-vr-cardboard text-purple-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php _e('Virtual Tour', 'le-bon-resto'); ?></h3>
                        <p class="text-gray-600"><?php _e('Available', 'le-bon-resto'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Video Section -->
            <?php if ($video_url && $atts['show_video'] === 'true'): ?>
                <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">
                        <i class="fas fa-play-circle mr-2 text-red-600"></i>
                        <?php _e('Restaurant Video', 'le-bon-resto'); ?>
                    </h2>
                    <div class="relative" style="padding-bottom: 56.25%; height: 0; overflow: hidden;">
                        <iframe src="<?php echo esc_url($video_url); ?>" 
                                class="absolute top-0 left-0 w-full h-full rounded-lg" 
                                frameborder="0" 
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Virtual Tour Section -->
            <?php if ($virtual_tour_url): ?>
                <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">
                        <i class="fas fa-vr-cardboard mr-2 text-purple-600"></i>
                        <?php _e('Virtual Tour', 'le-bon-resto'); ?>
                    </h2>
                    <div class="relative" style="padding-bottom: 56.25%; height: 0; overflow: hidden;">
                        <iframe src="<?php echo esc_url($virtual_tour_url); ?>" 
                                class="absolute top-0 left-0 w-full h-full rounded-lg" 
                                frameborder="0" 
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Gallery Section -->
            <?php if (!empty($gallery_images) && $atts['show_gallery'] === 'true'): ?>
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">
                        <i class="fas fa-images mr-2 text-yellow-600"></i>
                        <?php _e('Photo Gallery', 'le-bon-resto'); ?>
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($gallery_images as $image): ?>
                            <div class="relative group cursor-pointer">
                                <img src="<?php echo esc_url($image['url']); ?>" 
                                     alt="<?php echo esc_attr($image['alt'] ?: $restaurant->post_title); ?>" 
                                     class="w-full h-48 object-cover rounded-lg shadow-md transition duration-300 group-hover:shadow-lg group-hover:scale-105">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition duration-300 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-search-plus text-white text-2xl opacity-0 group-hover:opacity-100 transition duration-300"></i>
                        </div>
                </div>
            <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
        
        .lebonresto-detail-layout .group:hover img {
            transform: scale(1.05);
        }
    </style>

    <script>
        // Gallery data for JavaScript
        window.lebonrestoGalleryData = <?php echo wp_json_encode($gallery_images); ?>;
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
add_shortcode('lebonresto_all_restaurants_new', 'lebonresto_all_restaurants_new_shortcode');

/**
 * New All Restaurants Shortcode with Advanced Layout
 * [lebonresto_all_restaurants_new]
 */
function lebonresto_all_restaurants_new_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'per_page' => 12,
            'show_pagination' => 'true',
            'show_sorting' => 'true',
            'show_filters' => 'true',
        ),
        $atts,
        'lebonresto_all_restaurants_new'
    );

    // Enqueue required styles and scripts
    wp_enqueue_style('tailwind-css', 'https://cdn.tailwindcss.com', array(), '3.4.0');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
    wp_enqueue_style('lebonresto-all-restaurants-css', LEBONRESTO_PLUGIN_URL . 'assets/css/all-restaurants.css', array('tailwind-css'), LEBONRESTO_PLUGIN_VERSION);
    wp_enqueue_script('lebonresto-all-restaurants-js', LEBONRESTO_PLUGIN_URL . 'assets/js/all-restaurants.js', array('jquery', 'wp-api'), LEBONRESTO_PLUGIN_VERSION, true);

    // Start output buffering
    ob_start();

    // Include the all restaurants template
    $template_path = LEBONRESTO_PLUGIN_PATH . 'templates/all-restaurants.php';
    if (file_exists($template_path)) {
        // Remove get_header() and get_footer() calls from template when used as shortcode
        $template_content = file_get_contents($template_path);
        
        // Remove PHP tags that call get_header() and get_footer()
        $template_content = preg_replace('/get_header\(\);?\s*/', '', $template_content);
        $template_content = preg_replace('/get_footer\(\);?\s*/', '', $template_content);
        
        // Execute the modified template
        eval('?>' . $template_content);
    } else {
        echo '<div class="lebonresto-error">All restaurants template not found.</div>';
    }

    // Return the output
    return ob_get_clean();
}
