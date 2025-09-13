<?php
/**
 * Custom REST API Endpoints
 * 
 * @package LeBonResto
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom REST API endpoints
 */
function lebonresto_register_api_endpoints() {
    register_rest_route('lebonresto/v1', '/restaurants', array(
        'methods' => 'GET',
        'callback' => 'lebonresto_get_restaurants_endpoint',
        'permission_callback' => '__return_true',
        'args' => array(
            'name' => array(
                'description' => 'Restaurant name search',
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'city' => array(
                'description' => 'City filter',
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'cuisine' => array(
                'description' => 'Cuisine type filter',
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'distance' => array(
                'description' => 'Distance in km',
                'type' => 'number',
                'sanitize_callback' => 'absint',
            ),
            'lat' => array(
                'description' => 'User latitude for distance calculation',
                'type' => 'number',
                'sanitize_callback' => function($value) { return floatval($value); },
            ),
            'lng' => array(
                'description' => 'User longitude for distance calculation',
                'type' => 'number',
                'sanitize_callback' => function($value) { return floatval($value); },
            ),
            'sort' => array(
                'description' => 'Sort order: featured, newest, distance',
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'featured_only' => array(
                'description' => 'Show only featured restaurants',
                'type' => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
            ),
            'page' => array(
                'description' => 'Page number for pagination',
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 1,
            ),
            'per_page' => array(
                'description' => 'Number of restaurants per page',
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 12,
            ),
        ),
    ));
}
add_action('rest_api_init', 'lebonresto_register_api_endpoints');

/**
 * Clear restaurant cache when restaurants are updated
 */
function lebonresto_clear_restaurant_cache() {
    // Clear all restaurant-related transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_lebonresto_restaurants_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_lebonresto_restaurants_%'");
    
    // Clear cuisine types cache
    delete_transient('lebonresto_cuisine_types');
}

// Clear cache when restaurants are saved, updated, or deleted
add_action('save_post_restaurant', 'lebonresto_clear_restaurant_cache');
add_action('delete_post', 'lebonresto_clear_restaurant_cache');
add_action('wp_trash_post', 'lebonresto_clear_restaurant_cache');

/**
 * Custom REST API endpoint callback
 */
function lebonresto_get_restaurants_endpoint($request) {
    try {
        // Set execution time limit
        set_time_limit(30);
        
        $params = $request->get_params();
        
        // Handle pagination
        $page = isset($params['page']) ? max(1, intval($params['page'])) : 1;
        $per_page = isset($params['per_page']) ? max(1, min(100, intval($params['per_page']))) : 12;
        
        // Create cache key based on parameters
        $cache_key = 'lebonresto_restaurants_' . md5(serialize($params));
        
        // Try to get from cache first
        $cached_result = get_transient($cache_key);
        if ($cached_result !== false) {
            return rest_ensure_response($cached_result);
        }
    
    // Base query arguments
    $args = array(
        'post_type' => 'restaurant',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'meta_query' => array(),
    );

    // Filter by name (post title)
    if (!empty($params['name'])) {
        $args['s'] = $params['name'];
    }

    // Filter by city
    if (!empty($params['city'])) {
        $args['meta_query'][] = array(
            'key' => '_restaurant_city',
            'value' => $params['city'],
            'compare' => 'LIKE'
        );
    }

    // Filter by cuisine type
    if (!empty($params['cuisine'])) {
        $args['meta_query'][] = array(
            'key' => '_restaurant_cuisine_type',
            'value' => $params['cuisine'],
            'compare' => '='
        );
    }
    
    // Filter featured only
    if (!empty($params['featured_only'])) {
        $args['meta_query'][] = array(
            'key' => '_restaurant_is_featured',
            'value' => '1',
            'compare' => '='
        );
    }

    // Execute query
    $restaurants_query = new WP_Query($args);
    $restaurants = array();
    

    if ($restaurants_query->have_posts()) {
        while ($restaurants_query->have_posts()) {
            $restaurants_query->the_post();
            
            $post_id = get_the_ID();
            $restaurant_data = lebonresto_prepare_restaurant_data($post_id);
            
            // Apply distance filter if specified
            if (!empty($params['distance']) && !empty($params['lat']) && !empty($params['lng'])) {
                $restaurant_lat = floatval($restaurant_data['restaurant_meta']['latitude']);
                $restaurant_lng = floatval($restaurant_data['restaurant_meta']['longitude']);
                
                if ($restaurant_lat && $restaurant_lng) {
                    $distance = lebonresto_calculate_distance(
                        $params['lat'], 
                        $params['lng'], 
                        $restaurant_lat, 
                        $restaurant_lng
                    );
                    
                    // Skip if distance exceeds filter
                    if ($distance > $params['distance']) {
                        continue;
                    }
                    
                    // Add distance to restaurant data
                    $restaurant_data['distance'] = round($distance, 2);
                }
            }
            
            $restaurants[] = $restaurant_data;
        }
        wp_reset_postdata();
    }

    // Apply sorting
    $sort_order = isset($params['sort']) ? $params['sort'] : 'featured';
    
    if (in_array($sort_order, ['featured', 'newest', 'oldest', 'distance', 'name'])) {
        usort($restaurants, function($a, $b) use ($sort_order) {
            // Featured restaurants always come first (except for specific sorts)
            if ($sort_order !== 'oldest' && $sort_order !== 'name') {
                $a_featured = $a['restaurant_meta']['is_featured'] === '1';
                $b_featured = $b['restaurant_meta']['is_featured'] === '1';
                
                if ($a_featured && !$b_featured) return -1;
                if (!$a_featured && $b_featured) return 1;
            }
            
            // Apply secondary sort
            switch ($sort_order) {
                case 'distance':
                    $distance_a = isset($a['distance']) ? $a['distance'] : PHP_INT_MAX;
                    $distance_b = isset($b['distance']) ? $b['distance'] : PHP_INT_MAX;
                    return $distance_a <=> $distance_b;
                    
                case 'newest':
                    return intval($b['id']) - intval($a['id']); // Higher ID = newer
                    
                case 'oldest':
                    return intval($a['id']) - intval($b['id']); // Lower ID = older
                    
                case 'name':
                    return strcasecmp($a['title']['rendered'], $b['title']['rendered']);
                    
                case 'featured':
                default:
                    // For featured sort, just maintain featured first, then by ID (newest)
                    return intval($b['id']) - intval($a['id']);
            }
        });
    } elseif (!empty($params['distance']) && !empty($params['lat']) && !empty($params['lng'])) {
        // Legacy distance sorting
        usort($restaurants, function($a, $b) {
            $distance_a = isset($a['distance']) ? $a['distance'] : PHP_INT_MAX;
            $distance_b = isset($b['distance']) ? $b['distance'] : PHP_INT_MAX;
            return $distance_a <=> $distance_b;
        });
    }

        // Cache the result for 5 minutes
        set_transient($cache_key, $restaurants, 300);
        
        return rest_ensure_response($restaurants);
        
    } catch (Exception $e) {
        error_log('Le Bon Resto API Error: ' . $e->getMessage());
        return new WP_Error('api_error', 'An error occurred while fetching restaurants: ' . $e->getMessage(), array('status' => 500));
    }
}

/**
 * Prepare restaurant data for API response (optimized for performance)
 */
function lebonresto_prepare_restaurant_data($post_id) {
    // Get all meta data in one query
    $meta_keys = array(
        '_restaurant_description',
        '_restaurant_address', 
        '_restaurant_city',
        '_restaurant_latitude',
        '_restaurant_longitude',
        '_restaurant_cuisine_type',
        '_restaurant_phone',
        '_restaurant_email',
        '_restaurant_is_featured',
        '_restaurant_principal_image',
        '_restaurant_gallery',
        '_restaurant_video_url',
        '_restaurant_virtual_tour_url'
    );
    
    $meta_data = get_post_meta($post_id);
    $restaurant_meta = array();
    
    // Extract meta values
    foreach ($meta_keys as $key) {
        $restaurant_meta[str_replace('_restaurant_', '', $key)] = isset($meta_data[$key][0]) ? $meta_data[$key][0] : '';
    }
    
    // Get principal image URLs (only if needed)
    $principal_image_urls = array();
    if (!empty($restaurant_meta['principal_image'])) {
        $principal_image_id = $restaurant_meta['principal_image'];
        $principal_image_urls = array(
            'full' => wp_get_attachment_image_url($principal_image_id, 'full'),
            'medium' => wp_get_attachment_image_url($principal_image_id, 'medium'),
            'thumbnail' => wp_get_attachment_image_url($principal_image_id, 'thumbnail'),
        );
    }
    
    // Get gallery image URLs (only if needed)
    $gallery_urls = array();
    if (!empty($restaurant_meta['gallery'])) {
        $image_ids = explode(',', $restaurant_meta['gallery']);
        foreach ($image_ids as $image_id) {
            $image_id = intval($image_id);
            if ($image_id) {
                $gallery_urls[] = array(
                    'id' => $image_id,
                    'full' => wp_get_attachment_image_url($image_id, 'full'),
                    'medium' => wp_get_attachment_image_url($image_id, 'medium'),
                    'thumbnail' => wp_get_attachment_image_url($image_id, 'thumbnail'),
                );
            }
        }
    }
    
    return array(
        'id' => $post_id,
        'title' => array(
            'rendered' => get_the_title($post_id)
        ),
        'link' => home_url('/details/' . get_post_field('post_name', $post_id) . '/'),
        'single_link' => get_permalink($post_id),
        'restaurant_meta' => array(
            'description' => $restaurant_meta['description'],
            'address' => $restaurant_meta['address'],
            'city' => $restaurant_meta['city'],
            'latitude' => $restaurant_meta['latitude'],
            'longitude' => $restaurant_meta['longitude'],
            'cuisine_type' => $restaurant_meta['cuisine_type'],
            'phone' => $restaurant_meta['phone'],
            'email' => $restaurant_meta['email'],
            'is_featured' => $restaurant_meta['is_featured'],
            'principal_image' => $principal_image_urls,
            'gallery_images' => $gallery_urls,
            'video_url' => $restaurant_meta['video_url'],
            'virtual_tour_url' => $restaurant_meta['virtual_tour_url'],
        ),
    );
}

/**
 * Calculate distance between two points using Haversine formula
 */
function lebonresto_calculate_distance($lat1, $lng1, $lat2, $lng2) {
    $earth_radius = 6371; // Earth's radius in kilometers

    $lat1_rad = deg2rad($lat1);
    $lng1_rad = deg2rad($lng1);
    $lat2_rad = deg2rad($lat2);
    $lng2_rad = deg2rad($lng2);

    $delta_lat = $lat2_rad - $lat1_rad;
    $delta_lng = $lng2_rad - $lng1_rad;

    $a = sin($delta_lat / 2) * sin($delta_lat / 2) +
         cos($lat1_rad) * cos($lat2_rad) *
         sin($delta_lng / 2) * sin($delta_lng / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earth_radius * $c;

    return $distance;
}

/**
 * Get unique cuisine types for filter dropdown (optimized with caching)
 */
function lebonresto_get_cuisine_types() {
    // Try to get from cache first
    $cached_cuisines = get_transient('lebonresto_cuisine_types');
    if ($cached_cuisines !== false) {
        return $cached_cuisines;
    }
    
    global $wpdb;
    
    $cuisine_types = $wpdb->get_col(
        "SELECT DISTINCT meta_value 
         FROM {$wpdb->postmeta} 
         WHERE meta_key = '_restaurant_cuisine_type' 
         AND meta_value != '' 
         ORDER BY meta_value ASC"
    );
    
    // Cache for 1 hour
    set_transient('lebonresto_cuisine_types', $cuisine_types, 3600);
    
    return $cuisine_types;
}

/**
 * REST endpoint for getting cuisine types
 */
function lebonresto_register_cuisine_endpoint() {
    register_rest_route('lebonresto/v1', '/cuisine-types', array(
        'methods' => 'GET',
        'callback' => 'lebonresto_get_cuisine_types_endpoint',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'lebonresto_register_cuisine_endpoint');

/**
 * Cuisine types endpoint callback
 */
function lebonresto_get_cuisine_types_endpoint($request) {
    $cuisine_types = lebonresto_get_cuisine_types();
    return rest_ensure_response($cuisine_types);
}
