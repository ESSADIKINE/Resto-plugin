<?php
/**
 * Restaurant Detail Template - VisiteMonResto.com Inspired Design
 * 
 * @package LeBonResto
 */

get_header();

// Get restaurant data for SEO
$restaurant_id = get_the_ID();
$restaurant_name = get_the_title();
$cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true);
$city = get_post_meta($restaurant_id, '_restaurant_city', true);
$description = get_post_meta($restaurant_id, '_restaurant_description', true);

// Use city from meta or default to Casablanca
$city = $city ?: 'Casablanca';
$cuisine_type = $cuisine_type ?: 'cuisine marocaine';

// Generate SEO meta description
$seo_description = "Découvrez {$restaurant_name} à {$city}, Maroc. Restaurant spécialisé en {$cuisine_type} avec visite virtuelle 360°, ambiance authentique. Réservation en ligne, menus, photos, tour virtuel et avis clients. Le meilleur de la gastronomie marocaine à {$city}.";

// Add SEO meta tags to head
add_action('wp_head', function() use ($restaurant_name, $city, $cuisine_type, $seo_description) {
    // Add Font Awesome CSS with multiple fallbacks
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />';
    echo '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css" crossorigin="anonymous" />';
    echo '<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v6.4.0/css/all.css" crossorigin="anonymous" />';
    echo '<!-- SEO Meta Descriptions -->' . "\n";
    echo '<meta name="description" content="' . esc_attr($seo_description) . '">' . "\n";
    echo '<meta name="keywords" content="restaurant ' . esc_attr($restaurant_name) . ', ' . esc_attr($cuisine_type) . ' ' . esc_attr($city) . ', gastronomie Maroc, réservation restaurant ' . esc_attr($city) . ', visite virtuelle restaurant, tour virtuel restaurant, visite 360 restaurant, tour 360 restaurant, visite immersive restaurant">' . "\n";
    echo '<meta name="robots" content="index, follow">' . "\n";
    echo '<meta name="author" content="' . get_bloginfo('name') . '">' . "\n";
    
    // Add Font Awesome fallback CSS
    echo '<style>
    .fas, .far, .fab {
        font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 5 Free", "Font Awesome 5 Pro", "FontAwesome" !important;
        font-weight: 900 !important;
        font-style: normal !important;
        font-variant: normal !important;
        text-rendering: auto !important;
        line-height: 1 !important;
        -webkit-font-smoothing: antialiased !important;
        -moz-osx-font-smoothing: grayscale !important;
    }
    .far {
        font-weight: 400 !important;
    }
    .fab {
        font-family: "Font Awesome 6 Brands", "Font Awesome 5 Brands" !important;
        font-weight: 400 !important;
    }
    </style>';
    
    echo '<!-- Open Graph Meta Tags -->' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($restaurant_name) . ' - Restaurant à ' . esc_attr($city) . ', Maroc">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($seo_description) . '">' . "\n";
    echo '<meta property="og:type" content="restaurant">' . "\n";
    echo '<meta property="og:locale" content="fr_FR">' . "\n";
    echo '<meta property="og:site_name" content="' . get_bloginfo('name') . '">' . "\n";
    echo '<meta property="og:url" content="' . get_permalink() . '">' . "\n";
    
    echo '<!-- Twitter Card Meta Tags -->' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($restaurant_name) . ' - Restaurant à ' . esc_attr($city) . ', Maroc">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($seo_description) . '">' . "\n";
    
    echo '<!-- Structured Data for Restaurants -->' . "\n";
    echo '<script type="application/ld+json">' . "\n";
    echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Restaurant',
        'name' => $restaurant_name,
        'description' => $seo_description,
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => $city,
            'addressCountry' => 'MA'
        ],
        'servesCuisine' => $cuisine_type,
        'url' => get_permalink()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}, 1); 

// Enqueue Tailwind CSS
wp_enqueue_style(
    'tailwind-css',
    'https://cdn.tailwindcss.com',
    array(),
    '3.4.0'
);

// Enqueue Leaflet CSS
wp_enqueue_style(
    'leaflet-css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
    array(),
    '1.9.4'
);

// Enqueue detail page CSS
if (defined('LEBONRESTO_PLUGIN_URL') && defined('LEBONRESTO_PLUGIN_VERSION')) {
wp_enqueue_style(
    'lebonresto-detail-css',
    LEBONRESTO_PLUGIN_URL . 'assets/css/restaurant-detail.css',
    array('tailwind-css', 'leaflet-css'),
        LEBONRESTO_PLUGIN_VERSION
    );
} else {
    // Fallback for missing constants
    wp_enqueue_style(
        'lebonresto-detail-css',
        plugin_dir_url(__FILE__) . '../assets/css/restaurant-detail.css',
        array('tailwind-css', 'leaflet-css'),
        '1.0.0'
    );
}

// Enqueue Bootstrap CSS
wp_enqueue_style(
    'bootstrap-css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    array(),
    '5.3.0'
);

// Enqueue Leaflet JS
wp_enqueue_script(
    'leaflet-js',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
    array(),
    '1.9.4',
    true
);

// Enqueue Bootstrap JS
wp_enqueue_script(
    'bootstrap-js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    array(),
    '5.3.0',
    true
);

// Enqueue detail page JS
if (defined('LEBONRESTO_PLUGIN_URL') && defined('LEBONRESTO_PLUGIN_VERSION')) {
wp_enqueue_script(
    'lebonresto-detail-js',
    LEBONRESTO_PLUGIN_URL . 'assets/js/restaurant-detail.js',
    array('jquery', 'leaflet-js', 'bootstrap-js'),
        LEBONRESTO_PLUGIN_VERSION,
    true
);
} else {
    // Fallback for missing constants
    wp_enqueue_script(
        'lebonresto-detail-js',
        plugin_dir_url(__FILE__) . '../assets/js/restaurant-detail.js',
        array('jquery', 'leaflet-js', 'bootstrap-js'),
        '1.0.0',
        true
    );
}

// Get restaurant data with error handling
$restaurant_id = get_the_ID();

// Check if we have a valid restaurant ID
if (!$restaurant_id) {
    wp_die('Restaurant ID not found. Please check your permalink settings.');
}

// Debug mode - only show in development
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo '<!-- Debug: Restaurant ID = ' . $restaurant_id . ' -->';
    echo '<!-- Debug: Post Type = ' . get_post_type($restaurant_id) . ' -->';
    echo '<!-- Debug: Post Status = ' . get_post_status($restaurant_id) . ' -->';
}

$address = get_post_meta($restaurant_id, '_restaurant_address', true);
$city = get_post_meta($restaurant_id, '_restaurant_city', true);
$cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true);
$description = get_post_meta($restaurant_id, '_restaurant_description', true);

// Provide fallbacks for missing data
$address = $address ?: 'Adresse non disponible';
$city = $city ?: 'Ville non spécifiée';
$cuisine_type = $cuisine_type ?: 'Cuisine non spécifiée';
$description = $description ?: 'Description non disponible';

// Get selected options
$selected_options = get_post_meta($restaurant_id, '_restaurant_selected_options', true);
if (!is_array($selected_options)) {
    $selected_options = array();
}
$phone = get_post_meta($restaurant_id, '_restaurant_phone', true);
$email = get_post_meta($restaurant_id, '_restaurant_email', true);
$latitude = get_post_meta($restaurant_id, '_restaurant_latitude', true);
$longitude = get_post_meta($restaurant_id, '_restaurant_longitude', true);
$google_maps_link = get_post_meta($restaurant_id, '_restaurant_google_maps_link', true);
$is_featured = get_post_meta($restaurant_id, '_restaurant_is_featured', true);
$virtual_tour_url = get_post_meta($restaurant_id, '_restaurant_virtual_tour_url', true);
$video_url = get_post_meta($restaurant_id, '_restaurant_video_url', true);
$menu_image = get_post_meta($restaurant_id, '_restaurant_menu_image', true);
$menus = get_post_meta($restaurant_id, '_restaurant_menus', true);
if (!is_array($menus)) {
    $menus = array();
}
$blog_title = get_post_meta($restaurant_id, '_restaurant_blog_title', true);
$blog_content = get_post_meta($restaurant_id, '_restaurant_blog_content', true);
$selected_options = get_post_meta($restaurant_id, '_restaurant_selected_options', true);
if (!is_array($selected_options)) {
    $selected_options = array();
}

// Get review data
$google_place_id = get_post_meta($restaurant_id, '_restaurant_google_place_id', true);
// These will be automatically fetched from Google Places API
$google_rating = null;
$google_review_count = null;
$tripadvisor_url = null;
$tripadvisor_rating = null;
$tripadvisor_review_count = null;

// Get Google API reviews
$google_api_reviews = get_post_meta($restaurant_id, '_restaurant_google_api_reviews', true);
if (!is_array($google_api_reviews)) {
    $google_api_reviews = array();
}

// Auto-fetch Google Places data using API
$api_key = lebonresto_get_google_maps_api_key();
if ($google_place_id && $api_key) {
    // Fetch data using the stored Place ID
    $places_data = lebonresto_fetch_google_places_data($google_place_id, $api_key);
    
    if ($places_data) {
        // Get rating and review count from API
        if (isset($places_data['rating'])) {
            $google_rating = $places_data['rating'];
        }
        if (isset($places_data['review_count'])) {
            $google_review_count = $places_data['review_count'];
        }
        
        // Store individual reviews if available
        if (isset($places_data['reviews']) && !empty($places_data['reviews'])) {
            $api_reviews = array();
            foreach ($places_data['reviews'] as $review) {
                $api_reviews[] = array(
                    'name' => $review['author_name'],
                    'rating' => $review['rating'],
                    'text' => $review['text'],
                    'date' => date('Y-m-d', $review['time']),
                    'source' => 'google_api'
                );
            }
            // Store API reviews as a separate meta field
            update_post_meta($restaurant_id, '_restaurant_google_api_reviews', $api_reviews);
        }
    }
}

// Helper function to extract Google Maps data (defined in template for simplicity)
if (!function_exists('lebonresto_extract_google_maps_data')) {
    function lebonresto_extract_google_maps_data($google_maps_url) {
        if (empty($google_maps_url)) {
            return false;
        }
        
        $data = array();
        
        // Extract place ID from various Google Maps URL formats
        if (preg_match('/place\/([^\/]+)/', $google_maps_url, $matches)) {
            $data['place_id'] = $matches[1];
        } elseif (preg_match('/maps\/place\/([^\/]+)/', $google_maps_url, $matches)) {
            $data['place_id'] = $matches[1];
        }
        
        // Note: To get actual rating and review count from Google Maps URL without API,
        // you would need to scrape the page or use a third-party service
        // For now, this just extracts the place ID
        
        return $data;
    }
}


// Provide fallbacks for missing data
$phone = $phone ?: '';
$email = $email ?: '';
$latitude = $latitude ?: '';
$longitude = $longitude ?: '';
$google_maps_link = $google_maps_link ?: '';
$is_featured = $is_featured ?: '0';
$virtual_tour_url = $virtual_tour_url ?: '';
$video_url = $video_url ?: '';
$menu_image = $menu_image ?: '';
$blog_title = $blog_title ?: get_the_title();
$blog_content = $blog_content ?: '';

// Get gallery images with error handling
$gallery_images = array();

try {
if (function_exists('lebonresto_get_gallery_images')) {
    $gallery_images = lebonresto_get_gallery_images($restaurant_id);
} else {
    $gallery_ids = get_post_meta($restaurant_id, '_restaurant_gallery', true);
    
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
                            'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: ($blog_title ?: get_the_title())
                    );
                }
            }
        }
    }
    }
} catch (Exception $e) {
    error_log('Error loading gallery images: ' . $e->getMessage());
    $gallery_images = array();
}

// Get principal image with error handling
$principal_image = get_post_meta($restaurant_id, '_restaurant_principal_image', true);
if (!$principal_image && !empty($gallery_images)) {
    $principal_image = $gallery_images[0]['url'];
}

// Provide fallback for missing principal image
if (!$principal_image) {
    $principal_image = 'https://via.placeholder.com/400x300?text=Image+non+disponible';
}

?>

<style>
/* Critical CSS fallback for hosted environments */
.lebonresto-detail-layout {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: #333;
    background: #f8f9fa;
    min-height: 100vh;
}

/* Fix double scrollbar issue - Remove plugin internal scrollbar */
body.lebonresto-detail-page {
    overflow-x: hidden !important;
    overflow-y: auto !important;
}

html.lebonresto-detail-page {
    overflow-x: hidden !important;
    overflow-y: auto !important;
}

/* Remove internal scrollbar from plugin layout - let content flow naturally */
.lebonresto-detail-layout {
    overflow: visible !important;
    min-height: auto !important;
    height: auto !important;
}

/* Ensure content flows naturally without creating scroll containers */
.lebonresto-detail-layout .container {
    overflow: visible !important;
    height: auto !important;
}

/* Fix any conflicting scroll settings from themes */
.lebonresto-detail-layout * {
    box-sizing: border-box;
}

/* Remove any height restrictions that might create scrollbars */
.lebonresto-detail-layout .property-section-wrap {
    overflow: visible !important;
}

/* Ensure main content area doesn't create scrollbars */
.lebonresto-detail-layout #main-content {
    overflow: visible !important;
    height: auto !important;
}

.lebonresto-detail-layout .bt-content-wrap {
    overflow: visible !important;
    height: auto !important;
}

/* Ensure all content sections flow naturally */
.lebonresto-detail-layout .row {
    overflow: visible !important;
    height: auto !important;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -15px;
}

.col-lg-8, .col-lg-4, .col-md-12 {
    padding: 0 15px;
}

.col-lg-8 {
    flex: 0 0 66.666667%;
    max-width: 66.666667%;
}

.col-lg-4 {
    flex: 0 0 33.333333%;
    max-width: 33.333333%;
}

@media (max-width: 991px) {
    .col-lg-8, .col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

.property-section-wrap {
    background: white;
    border-radius: 8px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.block-title-wrap h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #1f2937;
}

.property-navigation {
    background: white;
    padding: 1rem 0;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 2rem;
}

.property-navigation a {
    color: #6b7280;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.property-navigation a:hover {
    color: #fedc00;
    background: #fef3c7;
}

.blog-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.blog-path {
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.blog-path a {
    color: #fedc00;
    text-decoration: none;
}

.cuisine-badge {
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%);
    color: #1f2937;
    padding: 0.5rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    display: inline-block;
}

#restaurant-map {
    width: 100%;
    height: 600px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}


.contact-btn {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    text-decoration: none;
    color: #374151;
    transition: all 0.3s ease;
    margin-bottom: 0.75rem;
}

.contact-btn:hover {
    background: #f9fafb;
    border-color: #fedc00;
    color: #1f2937;
}

.contact-btn i {
    margin-right: 0.75rem;
    color: #fedc00;
}

/* Loading state */
.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #fedc00;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Animated Section Switcher Buttons */
.section-switcher .section-btn {
    display: flex !important;
    align-items: center !important;
    height: 60px;
    width: 60px;
    text-decoration: none;
    margin: 0 5px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50px;
    cursor: pointer;
    box-shadow: 0px 10px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-out;
    border: 2px solid #ffd700;
    position: relative;
    padding: 0;
}

.section-switcher .section-btn:hover {
    width: 200px;
    background: #fff;
    border: 2px solid #ffd700;
}

.section-switcher .section-btn .icon {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 60px;
    width: 60px;
    color: #000;
    border-radius: 50px;
    box-sizing: border-box;
    transition: all 0.3s ease-out;
    flex-shrink: 0;
    position: absolute;
    left: 0;
    top: 0;
}

.section-switcher .section-btn:nth-child(1):hover .icon {
    background: #4285F4;
}

.section-switcher .section-btn:nth-child(2):hover .icon {
    background: #00C851;
}

.section-switcher .section-btn:nth-child(3):hover .icon {
    background: #ff0000;
}

.section-switcher .section-btn:nth-child(4):hover .icon {
    background: #E1306C;
}

.section-switcher .section-btn .icon svg {
    width: 20px !important;
    height: 20px !important;
    transition: all 0.3s ease-out;
    display: block !important;
}

.section-switcher .section-btn:hover .icon svg {
    color: #fff;
}

.section-switcher .section-btn span {
    font-size: 16px;
    font-weight: 500;
    line-height: 60px;
    margin-left: 70px;
    transition: all 0.3s ease-out;
    white-space: nowrap;
    opacity: 0;
    transform: translateX(-10px);
    position: relative;
    z-index: 1;
}

.section-switcher .section-btn:nth-child(1) span {
    color: #4285F4;
}

.section-switcher .section-btn:nth-child(2) span {
    color: #00C851;
}

.section-switcher .section-btn:nth-child(3) span {
    color: #ff0000;
}

.section-switcher .section-btn:nth-child(4) span {
    color: #E1306C;
}

.section-switcher .section-btn:hover span {
    opacity: 1;
    transform: translateX(0);
}

/* Active state styling */
.section-switcher .section-btn.active {
    background: var(--gradient-primary);
    color: var(--bg-primary);
    width: 200px;
}

.section-switcher .section-btn.active .icon {
    background: var(--gradient-primary);
    color: var(--bg-primary);
    position: absolute;
    left: 0;
    top: 0;
}

.section-switcher .section-btn.active .icon svg {
    color: var(--bg-primary);
}

.section-switcher .section-btn.active span {
    color: var(--bg-primary);
    opacity: 1;
    transform: translateX(0);
    margin-left: 70px;
}

/* Ensure icons are visible */
.section-switcher .section-btn .icon svg {
    fill: currentColor !important;
    stroke: none !important;
}

.section-switcher .section-btn .icon svg path {
    fill: currentColor !important;
}

/* Override any conflicting styles */
.section-switcher .section-btn * {
    box-sizing: border-box !important;
}

.section-switcher .section-btn .icon * {
    display: block !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .section-switcher {
        right: 20px !important;
        top: 10px !important;
    }
    
    .section-switcher .section-btn {
        height: 50px;
        width: 50px;
    }
    
    .section-switcher .section-btn:hover {
        width: 160px;
    }
    
    .section-switcher .section-btn .icon {
        height: 50px;
        width: 50px;
        position: absolute;
        left: 0;
        top: 0;
    }
    
    .section-switcher .section-btn .icon svg {
        width: 18px !important;
        height: 18px !important;
    }
    
    .section-switcher .section-btn span {
        font-size: 14px;
        line-height: 50px;
        margin-left: 60px;
    }
    
    .section-switcher .section-btn.active span {
        margin-left: 60px;
    }
}

/* Star Rating Styles */
.star {
    display: inline-block;
    transition: all 0.2s ease;
}

.star-filled {
    color: #fbbf24 !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.star-empty {
    color: #e5e7eb !important;
}

.star-half {
    background: linear-gradient(90deg, #fbbf24 50%, #e5e7eb 50%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Review Platform Styles */
.review-platform {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.review-platform:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.review-platform::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #fedc00, #fbbf24);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.review-platform:hover::before {
    opacity: 1;
}

/* Platform Icons Animation */
.platform-icon {
    transition: all 0.3s ease;
}

.review-platform:hover .platform-icon {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Rating Display Animation */
.rating-display .stars .star {
    transition: all 0.2s ease;
}

.rating-display:hover .stars .star {
    transform: scale(1.1);
}
</style>

<div class="lebonresto-detail-layout">
    <?php 
    // Check if we have posts or if we're in a custom context
    if (have_posts()) : 
        while (have_posts()) : the_post(); 
    ?>
        
        <!-- Header Navigation -->
        <div class="property-navigation-wrap">
            <div class="container-fluid">
                <ul class="property-navigation list-unstyled d-flex justify-content-between">
                    <li class="property-navigation-item">
                        <a class="target" href="#details-section">Détails</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#chef-section">Le mot du Chef</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#options-section">Options</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#address-section">Adresse</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#menu-section">Menu</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#reviews-section">Avis</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Blog Information Section -->
        <div class="blog-info-wrap" style="width: 100%; background: var(--bg-primary); padding: 2rem 0; border-bottom: 1px solid var(--border-color);">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="blog-title" style="font-size: 2.5rem; font-weight: 800; margin: 0; color: var(--text-primary);">
                            <?php echo $blog_title ?: get_the_title(); ?>
                        </h1>
                        <p class="blog-path" style="color: var(--text-secondary); margin: 0.5rem 0; font-size: 1.1rem;">
                            <i class="fas fa-home"></i> 
                            <a href="<?php echo home_url(); ?>" style="color: var(--primary-color); text-decoration: none;">Accueil</a> 
                            <i class="fas fa-chevron-right mx-2"></i>
                            <a href="<?php echo home_url('/restaurants/'); ?>" style="color: var(--primary-color); text-decoration: none;">Restaurants</a>
                            <i class="fas fa-chevron-right mx-2"></i>
                            <span style="color: var(--text-secondary);"><?php echo esc_html($blog_title ?: get_the_title()); ?></span>
                        </p>
                        <p class="blog-date" style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                            <i class="fas fa-calendar"></i> Créé le <?php echo date('j F Y', strtotime(get_the_date())); ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem;">
                            <?php if ($google_rating): ?>
                            <div class="rating-display" style="display: flex; align-items: center; gap: 0.5rem; background: rgba(255, 255, 255, 0.1); padding: 0.5rem 1rem; border-radius: var(--radius-full); backdrop-filter: blur(10px);">
                                <div class="rating-stars" style="display: flex; gap: 0.125rem;">
                                    <?php
                                    $rating = floatval($google_rating);
                                    $full_stars = floor($rating);
                                    $has_half_star = ($rating - $full_stars) >= 0.5;
                                    
                                    // Full stars
                                    for ($i = 0; $i < $full_stars; $i++) {
                                        echo '<i class="fas fa-star" style="color: #ffd700; font-size: 1.1rem;"></i>';
                                    }
                                    
                                    // Half star
                                    if ($has_half_star) {
                                        echo '<i class="fas fa-star-half-alt" style="color: #ffd700; font-size: 1.1rem;"></i>';
                                    }
                                    
                                    // Empty stars
                                    $empty_stars = 5 - $full_stars - ($has_half_star ? 1 : 0);
                                    for ($i = 0; $i < $empty_stars; $i++) {
                                        echo '<i class="far fa-star" style="color: #ddd; font-size: 1.1rem;"></i>';
                                    }
                                    ?>
                                </div>
                                <span class="rating-number" style="font-weight: 700; color: var(--text-primary); font-size: 1.1rem;">
                                    <?php echo number_format($rating, 1); ?>
                                </span>
                                <?php if ($google_review_count): ?>
                                <span class="review-count" style="color: var(--text-secondary); font-size: 0.9rem;">
                                    (<?php echo esc_html($google_review_count); ?> avis)
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($cuisine_type): ?>
                            <span class="cuisine-badge" style="background: var(--gradient-primary); color: var(--bg-primary); padding: 0.5rem 1.5rem; border-radius: var(--radius-full); font-weight: 600; display: inline-block;">
                                <?php echo esc_html(ucfirst($cuisine_type)); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Container with 4 Sections -->
        <div class="container" style="margin-top: 2rem;">
            <div class="property-top-wrap">
                <div class="property-banner" style="position: relative;">
                    <!-- Section Switcher Icons -->
                    <div class="section-switcher" style="position: absolute; top: 20px; right: 50px; z-index: 1000;">
                        <div class="switcher-icons" style="display: inline-flex; background: transparent; padding: 0.5rem; border-radius: var(--radius-full); gap: 0.5rem;">
                            <button class="section-btn active" data-section="map" title="Carte">
                                <div class="icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                                </div>
                                <span>Carte</span>
                            </button>
                            <button class="section-btn" data-section="virtual-tour" title="Visite Virtuelle">
                                <div class="icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                </div>
                                <span>Visite Virtuelle</span>
                            </button>
                            <button class="section-btn" data-section="video" title="Vidéo">
                                <div class="icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
                                </svg>
                                </div>
                                <span>Vidéo</span>
                            </button>
                            <button class="section-btn" data-section="images" title="Images">
                                <div class="icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                </svg>
                                </div>
                                <span>Images</span>
                            </button>
                        </div>
                    </div>

                    <!-- Content Sections -->
                    <div class="content-sections" style="position: relative;">
                        <!-- Map Section (Default) -->
                        <div class="content-section active" id="map-section" style="width: 100%; height: 600px; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg);">
                            <div id="restaurant-map" 
                                 data-lat="<?php echo esc_attr($latitude); ?>" 
                                 data-lng="<?php echo esc_attr($longitude); ?>" 
                                 data-title="<?php echo esc_attr($blog_title ?: get_the_title()); ?>" 
                                 style="width: 100%; height: 100%;">
                                <?php if (!$latitude || !$longitude): ?>
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: var(--bg-tertiary); color: var(--text-muted);">
                                    <div style="text-align: center;">
                                        <i class="fas fa-map-marker-alt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                        <p>Coordonnées non disponibles</p>
                                        <small>Veuillez ajouter la latitude et longitude dans l'admin</small>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Virtual Tour Section -->
                        <?php if ($virtual_tour_url): ?>
                        <div class="content-section" id="virtual-tour-section" style="width: 100%; height: 600px; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg); display: none;">
                            <iframe src="<?php echo esc_url($virtual_tour_url); ?>" 
                                    style="width: 100%; height: 100%; border: none;" 
                                    frameborder="0" 
                                    allowfullscreen>
                            </iframe>
                </div>
                <?php endif; ?>
                    
                    <!-- Video Section -->
                    <?php if ($video_url): ?>
                    <div class="content-section" id="video-section" style="width: 100%; height: 600px; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg); display: none;">
                        <?php
                        // Convert video URL to embed format
                        $embed_url = $video_url;
                        
                        // YouTube URL conversion
                        if (strpos($video_url, 'youtube.com/watch') !== false) {
                            $video_id = '';
                            if (preg_match('/v=([^&]+)/', $video_url, $matches)) {
                                $video_id = $matches[1];
                            }
                            if ($video_id) {
                                $embed_url = 'https://www.youtube.com/embed/' . $video_id . '?rel=0&modestbranding=1&showinfo=0';
                            }
                        } elseif (strpos($video_url, 'youtu.be/') !== false) {
                            $video_id = '';
                            if (preg_match('/youtu\.be\/([^?]+)/', $video_url, $matches)) {
                                $video_id = $matches[1];
                            }
                            if ($video_id) {
                                $embed_url = 'https://www.youtube.com/embed/' . $video_id . '?rel=0&modestbranding=1&showinfo=0';
                            }
                        }
                        // Vimeo URL conversion
                        elseif (strpos($video_url, 'vimeo.com/') !== false) {
                            $video_id = '';
                            if (preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches)) {
                                $video_id = $matches[1];
                            }
                            if ($video_id) {
                                $embed_url = 'https://player.vimeo.com/video/' . $video_id . '?title=0&byline=0&portrait=0';
                            }
                        }
                        // Direct video file - use as is
                        elseif (preg_match('/\.(mp4|webm|ogg)$/i', $video_url)) {
                            $embed_url = $video_url;
                        }
                        ?>
                        <?php if (preg_match('/\.(mp4|webm|ogg)$/i', $video_url)): ?>
                        <!-- Direct video file -->
                        <video controls style="width: 100%; height: 100%; object-fit: cover;">
                            <source src="<?php echo esc_url($embed_url); ?>" type="video/<?php echo pathinfo($video_url, PATHINFO_EXTENSION); ?>">
                            <?php _e('Your browser does not support the video tag.', 'le-bon-resto'); ?>
                        </video>
                        <?php else: ?>
                        <!-- Embedded video (YouTube, Vimeo) -->
                        <iframe src="<?php echo esc_url($embed_url); ?>" 
                                style="width: 100%; height: 100%; border: none;" 
                                    frameborder="0" 
                                    allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                            </iframe>
                        <?php endif; ?>
                        <div class="video-fallback" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-tertiary); display: none; align-items: center; justify-content: center; flex-direction: column; color: var(--text-muted);">
                            <i class="fas fa-video" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Vidéo non disponible</p>
                            <a href="<?php echo esc_url($video_url); ?>" target="_blank" style="color: var(--primary-color); text-decoration: none; margin-top: 1rem;">
                                <i class="fas fa-external-link-alt" style="margin-right: 0.5rem;"></i>
                                Ouvrir la vidéo dans un nouvel onglet
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                        <!-- Images Section -->
                        <div class="content-section" id="images-section" style="width: 100%; height: 600px; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg); display: none;">
                    <?php if (!empty($gallery_images)): ?>
                                <div class="image-slider" style="width: 100%; height: 100%; position: relative;">
                                    <div class="slider-container" style="width: 100%; height: 100%; position: relative; overflow: hidden;">
                        <?php foreach ($gallery_images as $index => $image): ?>
                                            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: <?php echo $index === 0 ? '1' : '0'; ?>; transition: opacity var(--transition-normal);">
                            <img src="<?php echo esc_url($image['url']); ?>" 
                                 alt="<?php echo esc_attr($image['alt'] ?: ($blog_title ?: get_the_title())); ?>" 
                                                     style="width: 100%; height: 100%; object-fit: cover;" />
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="slider-controls" style="position: absolute; bottom: 1rem; left: 50%; transform: translateX(-50%); display: flex; gap: 0.5rem;">
                                        <?php foreach ($gallery_images as $index => $image): ?>
                                            <button class="slider-dot <?php echo $index === 0 ? 'active' : ''; ?>" 
                                                    data-slide="<?php echo $index; ?>" 
                                                    style="width: 12px; height: 12px; border-radius: 50%; border: none; background: <?php echo $index === 0 ? 'var(--primary-color)' : 'rgba(255,255,255,0.5)'; ?>; cursor: pointer; transition: all var(--transition-normal);">
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: var(--bg-tertiary); color: var(--text-muted);">
                                    <div style="text-align: center;">
                                        <i class="fas fa-images" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                        <p>Aucune image disponible</p>
                                    </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="row" style="margin-top: 3rem;">
                <!-- Left Column - 70% -->
                <div class="col-lg-8 col-md-12 bt-content-wrap" id="main-content">
                    <!-- Détails Section -->
                    <div class="property-section-wrap" id="details-section" style="margin-bottom: 2rem;">
                        <div class="block-wrap">
                            <div class="block-title-wrap">
                                <h2>Détails</h2>
                            </div>
                            <div class="block-content-wrap">
                                <div class="detail-wrap">
                                    <ul class="list-2-cols list-unstyled">
                                        <li>
                                            <strong>Type de cuisine:</strong>
                                            <span><?php echo esc_html(ucfirst($cuisine_type ?: 'Non spécifié')); ?></span>
                                        </li>
                                        <li>
                                            <strong>Ville:</strong>
                                            <span><?php echo esc_html($city); ?></span>
                                        </li>
                                        <?php if ($phone): ?>
                                        <li>
                                            <strong>Téléphone:</strong>
                                            <span><a href="tel:<?php echo esc_attr($phone); ?>" style="color: var(--primary-color);"><?php echo esc_html($phone); ?></a></span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if ($email): ?>
                                        <li>
                                            <strong>Email:</strong>
                                            <span><a href="mailto:<?php echo esc_attr($email); ?>" style="color: var(--primary-color);"><?php echo esc_html($email); ?></a></span>
                                        </li>
                <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Le mot du Chef Section -->
                    <div class="property-section-wrap" id="chef-section" style="margin-bottom: 2rem;">
                        <div class="block-wrap">
                            <div class="block-title-wrap">
                                <h2>Le mot du Chef</h2>
                            </div>
                            <div class="block-content-wrap">
                                <div class="blog-content" style="font-size: 1.1rem; line-height: 1.8; color: var(--text-primary);">
                                    <?php if ($blog_content): ?>
                                        <?php echo wpautop($blog_content); ?>
                                    <?php else: ?>
                                        <p>Bienvenue chez <strong><?php echo esc_html(get_the_title()); ?></strong> !</p>
                                        <p>Nous sommes ravis de vous accueillir dans notre établissement. Découvrez notre cuisine authentique et notre ambiance chaleureuse.</p>
                                        <?php if ($description): ?>
                                            <p><?php echo esc_html($description); ?></p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                            
                    <!-- Options Section -->
                    <?php if (!empty($selected_options)): ?>
                    <div class="property-section-wrap" id="options-section" style="margin-bottom: 2rem;">
                        <div class="block-wrap">
                            <div class="block-title-wrap">
                                <h2>Options</h2>
                            </div>
                            <div class="block-content-wrap">
                                <div class="options-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                                    <?php foreach ($selected_options as $option): ?>
                                    <div class="option-item" style="display: flex; align-items: center; padding: 1rem; background: var(--bg-secondary); border-radius: var(--radius-lg); border-left: 4px solid var(--primary-color);">
                                        <i class="fas fa-check-circle" style="color: var(--primary-color); margin-right: 0.75rem; font-size: 1.2rem;"></i>
                                        <span style="font-weight: 500; color: var(--text-primary);"><?php echo esc_html($option); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                    <!-- Adresse Section -->
                    <div class="property-section-wrap" id="address-section" style="margin-bottom: 2rem;">
                        <div class="block-wrap">
                            <div class="block-title-wrap">
                                <h2>Adresse</h2>
                            </div>
                            <div class="block-content-wrap">
                                <div class="address-info" style="padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius-lg);">
                                    <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                                        <i class="fas fa-map-marker-alt" style="color: var(--primary-color); margin-right: 1rem; font-size: 1.5rem;"></i>
                                <div>
                                            <strong style="display: block; color: var(--text-primary);"><?php echo esc_html($address); ?></strong>
                                            <span style="color: var(--text-secondary);"><?php echo esc_html($city); ?></span>
                                        </div>
                                    </div>
                                    <?php 
                                    // Use Google Maps link if available, otherwise use coordinates
                                    $maps_url = '';
                                    if ($google_maps_link) {
                                        $maps_url = $google_maps_link;
                                    } elseif ($latitude && $longitude) {
                                        $maps_url = 'https://www.google.com/maps?q=' . $latitude . ',' . $longitude;
                                    }
                                    ?>
                                    <?php if ($maps_url): ?>
                                    <div style="margin-top: 1rem;">
                                        <a href="<?php echo esc_url($maps_url); ?>" 
                                           target="_blank" 
                                           style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: var(--gradient-primary); color: var(--bg-primary); text-decoration: none; border-radius: var(--radius-full); font-weight: 600;">
                                            <i class="fas fa-external-link-alt" style="margin-right: 0.5rem;"></i>
                                            Voir sur Google Maps
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reviews Section -->
                    <div class="property-section-wrap" id="reviews-section" style="margin-bottom: 2rem;">
                        <div class="block-wrap">
                            <div class="block-title-wrap">
                                <h2>Avis Clients</h2>
                            </div>
                            <div class="block-content-wrap">
                                <div class="reviews-container" style="display: grid; gap: 2rem;">
                                    
                                    <!-- Google Reviews -->
                                    <?php if ($google_place_id): ?>
                                    <div class="review-platform" style="background: var(--bg-secondary); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                                        <div class="platform-header" style="display: flex; align-items: center; margin-bottom: 1rem;">
                                            <div class="platform-icon" style="width: 40px; height: 40px; background: #4285F4; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 style="margin: 0; color: var(--text-primary); font-size: 1.25rem; font-weight: 600;">Google Maps</h3>
                                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Avis Google</p>
                                            </div>
                                        </div>
                                        
                                        <?php if ($google_rating): ?>
                                        <div class="rating-display" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                            <div class="stars" style="display: flex; gap: 2px;">
                                                <?php 
                                                $rating = floatval($google_rating);
                                                for ($i = 1; $i <= 5; $i++): 
                                                    $star_class = $i <= $rating ? 'star-filled' : ($i - 0.5 <= $rating ? 'star-half' : 'star-empty');
                                                ?>
                                                <span class="star <?php echo $star_class; ?>" style="color: #fbbf24; font-size: 1.5rem;">★</span>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="rating-text">
                                                <span style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);"><?php echo number_format($rating, 1); ?></span>
                                                <span style="color: var(--text-secondary); margin-left: 0.5rem;">/ 5</span>
                                            </div>
                                        </div>
                                        <?php if ($google_review_count): ?>
                                        <p style="margin: 0 0 1rem 0; color: var(--text-secondary); font-size: 0.9rem;">
                                            Basé sur <?php echo intval($google_review_count); ?> avis
                                        </p>
                                        <?php endif; ?>
                                        <?php else: ?>
                                        <div style="margin-bottom: 1rem; padding: 1rem; background: #e3f2fd; border-radius: var(--radius-md); border-left: 4px solid #4285F4;">
                                            <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                                <i class="fas fa-star" style="color: #4285F4; margin-right: 0.5rem;"></i>
                                                <strong style="color: var(--text-primary);">Note Google disponible</strong>
                                            </div>
                                            <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
                                                Pour afficher la note Google, ajoutez-la dans l'administration du restaurant.
                                            </p>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div style="margin-top: 1rem;">
                                            <a href="<?php echo esc_url($google_maps_link); ?>" 
                                               target="_blank" 
                                               style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: #4285F4; color: white; text-decoration: none; border-radius: var(--radius-md); font-size: 0.9rem; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(66, 133, 244, 0.3);">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 0.5rem;">
                                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                                </svg>
                                                <?php echo $google_rating ? 'Voir tous les avis sur Google Maps' : 'Voir les avis sur Google Maps'; ?>
                                            </a>
                                        </div>
                                        
                                        <?php if (current_user_can('manage_options') && !$google_rating): ?>
                                        <div style="margin-top: 1rem; padding: 0.75rem; background: #fff3cd; border-radius: var(--radius-md); border: 1px solid #ffeaa7;">
                                            <p style="margin: 0; font-size: 0.8rem; color: #856404;">
                                                <i class="fas fa-info-circle" style="margin-right: 0.25rem;"></i>
                                                <strong>Admin:</strong> 
                                                <?php if (!$api_key): ?>
                                                Ajoutez votre clé API Google Maps dans les paramètres généraux pour récupérer automatiquement les avis.
                                                <?php elseif (!$google_place_id): ?>
                                                Ajoutez un Google Place ID dans la section "Restaurant Reviews" pour récupérer automatiquement les avis.
                                                <?php else: ?>
                                                Les avis Google seront récupérés automatiquement via l'API.
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Individual Google API Reviews -->
                                        <?php if (!empty($google_api_reviews)): ?>
                                        <div style="margin-top: 1.5rem;">
                                            <h4 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 1.1rem; font-weight: 600;">Avis récents Google</h4>
                                            <div style="display: grid; gap: 1rem;">
                                                <?php foreach (array_slice($google_api_reviews, 0, 3) as $review): ?>
                                                <div class="review-item" style="background: white; padding: 1rem; border-radius: var(--radius-md); border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                                        <div>
                                                            <strong style="color: var(--text-primary); font-size: 0.9rem;"><?php echo esc_html($review['name']); ?></strong>
                                                            <div style="display: flex; align-items: center; margin-top: 0.25rem;">
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <span style="color: <?php echo $i <= $review['rating'] ? '#fbbf24' : '#d1d5db'; ?>; font-size: 0.9rem;">★</span>
                                                                <?php endfor; ?>
                                                            </div>
                                                        </div>
                                                        <span style="color: var(--text-secondary); font-size: 0.8rem;"><?php echo esc_html($review['date']); ?></span>
                                                    </div>
                                                    <?php if (!empty($review['text'])): ?>
                                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem; line-height: 1.4;">
                                                        <?php echo esc_html(wp_trim_words($review['text'], 30)); ?>
                                                    </p>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if (count($google_api_reviews) > 3): ?>
                                            <p style="margin: 1rem 0 0 0; text-align: center;">
                                                <a href="<?php echo esc_url($google_maps_link); ?>" target="_blank" style="color: #4285F4; text-decoration: none; font-size: 0.9rem;">
                                                    Voir tous les <?php echo count($google_api_reviews); ?> avis sur Google Maps →
                                                </a>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Fallback when no reviews are available -->
                                    <?php if (!$google_place_id): ?>
                                    <div style="text-align: center; padding: 3rem 2rem; background: var(--bg-secondary); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                                        <i class="fas fa-star" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem; opacity: 0.5;"></i>
                                        <h3 style="color: var(--text-primary); margin-bottom: 1rem;">Aucun avis disponible</h3>
                                        <p style="color: var(--text-secondary); margin-bottom: 0;">Les avis Google apparaîtront automatiquement une fois le Google Place ID ajouté.</p>
                                    </div>
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                            
                </div>
                
                <!-- Right Column - 30% -->
                <div class="col-lg-4 col-md-12 bt-sidebar-wrap">
                    <aside id="sidebar" class="sidebar-wrap">
                    <!-- Menu Section -->
                    <?php if (!empty($menus) || $menu_image): ?>
                        <div class="property-section-wrap" style="margin-bottom: 2rem;">
                        <div class="block-wrap">
                            <div class="block-title-wrap">
                                <h2>Menus</h2>
                            </div>
                            <div class="block-content-wrap">
                                <?php if (!empty($menus)): ?>
                                    <div class="menus-grid" style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                                        <?php foreach ($menus as $index => $menu): ?>
                                            <div class="menu-item1" id="menu-<?php echo $index; ?>" style="border: 1px solid padding: 1.5rem; background: var(--bg-primary); box-shadow: var(--shadow-sm); transition: var(--transition);">
                                                <h3 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 1.25rem; font-weight: 600; cursor: pointer;" 
                                                    class="menu-title" 
                                                    onclick="toggleMenuContent(<?php echo $index; ?>)">
                                                    <i class="fas fa-chevron-down menu-arrow" style="margin-right: 0.5rem; transition: transform 0.3s ease;"></i>
                                                    <?php echo esc_html($menu['name']); ?>
                                                </h3>
                                                <div class="menu-content" id="menu-content-<?php echo $index; ?>" style="display: none;">
                                                    <?php if (!empty($menu['description'])): ?>
                                                        <p style="margin-bottom: 1rem; color: var(--text-secondary);"><?php echo esc_html($menu['description']); ?></p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($menu['file_url'])): ?>
                                                    <a href="<?php echo esc_url($menu['file_url']); ?>" 
                                                       target="_blank" 
                                                           style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; text-decoration: none; font-weight: 600; transition: var(--transition);">
                                                        <i class="fas fa-download" style="margin-right: 0.5rem;"></i>
                                                        Télécharger le menu
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                                <?php if ($menu_image): ?>
                                    <div class="menu-image-section">
                                    <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Menu Principal</h3>
                                    <?php 
                                    $menu_image_url = wp_get_attachment_image_url($menu_image, 'large');
                                    if ($menu_image_url): ?>
                                        <img src="<?php echo esc_url($menu_image_url); ?>" 
                                                 alt="Menu du restaurant <?php echo esc_attr($blog_title ?: get_the_title()); ?>" 
                                             style="max-width: 100%; height: auto; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); cursor: pointer;"
                                                 onclick="openImageModal('<?php echo esc_url($menu_image_url); ?>', 'Menu du restaurant <?php echo esc_attr($blog_title ?: get_the_title()); ?>')" />
                                    <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                            
                     <!-- Address Section -->
                     <?php if ($address || $city): ?>
                     <div class="property-section-wrap" style="margin-bottom: 2rem;">
                         <div class="block-wrap">
                             <div class="block-title-wrap">
                                 <h2>Adresse</h2>
                             </div>
                             <div class="block-content-wrap">
                                 <div class="address-info" style="padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                                     <div class="address-content" style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1rem;">
                                         <div class="address-icon" style="flex-shrink: 0; margin-top: 0.25rem;">
                                             <i class="fas fa-map-marker-alt" style="color: var(--primary-color); font-size: 1.5rem;"></i>
                                         </div>
                                         <div class="address-details">
                                             <?php if ($address): ?>
                                                 <p class="address-street" style="margin: 0 0 0.5rem 0; color: var(--text-primary); font-weight: 600; font-size: 1.1rem;"><?php echo esc_html($address); ?></p>
                                             <?php endif; ?>
                                             <?php if ($city): ?>
                                                 <p class="address-city" style="margin: 0; color: var(--text-secondary); font-size: 1rem;"><?php echo esc_html($city); ?></p>
                                             <?php endif; ?>
                                         </div>
                </div>
                
                                     <!-- Google Maps Button -->
                                     <?php if ($latitude && $longitude): ?>
                                     <div class="map-actions" style="text-align: center;">
                                         <a href="https://www.google.com/maps?q=<?php echo esc_attr($latitude); ?>,<?php echo esc_attr($longitude); ?>" 
                                            target="_blank" 
                                            class="btn btn-primary"
                                            style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: var(--gradient-primary); color: var(--bg-primary); text-decoration: none; border-radius: var(--radius-full); font-weight: 600; transition: var(--transition); box-shadow: var(--shadow-sm);">
                                             <i class="fas fa-map-marked-alt" style="margin-right: 0.5rem;"></i>
                                             Voir sur Google Maps
                                         </a>
                                     </div>
                                     <?php elseif ($google_maps_link): ?>
                                     <div class="map-actions" style="text-align: center;">
                                         <a href="<?php echo esc_url($google_maps_link); ?>" 
                                            target="_blank" 
                                            class="btn btn-primary"
                                            style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: var(--gradient-primary); color: var(--bg-primary); text-decoration: none; border-radius: var(--radius-full); font-weight: 600; transition: var(--transition); box-shadow: var(--shadow-sm);">
                                             <i class="fas fa-map-marked-alt" style="margin-right: 0.5rem;"></i>
                                             Voir sur Google Maps
                                         </a>
                                     </div>
                                     <?php endif; ?>
                                     
                                     <?php if ($phone || $email): ?>
                                     <div class="contact-details" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                                         <div class="contact-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                                             <?php if ($phone): ?>
                                             <div class="contact-item" style="display: flex; align-items: center; gap: 0.75rem;">
                                                 <i class="fas fa-phone" style="color: var(--primary-color); width: 16px;"></i>
                                                 <a href="tel:<?php echo esc_attr($phone); ?>" style="color: var(--text-primary); text-decoration: none; font-weight: 500;"><?php echo esc_html($phone); ?></a>
                                            </div>
                                             <?php endif; ?>
                                             
                                             <?php if ($email): ?>
                                             <div class="contact-item" style="display: flex; align-items: center; gap: 0.75rem;">
                                                 <i class="fas fa-envelope" style="color: var(--primary-color); width: 16px;"></i>
                                                 <a href="mailto:<?php echo esc_attr($email); ?>" style="color: var(--text-primary); text-decoration: none; font-weight: 500;"><?php echo esc_html($email); ?></a>
                                        </div>
                                             <?php endif; ?>
                                    </div>
                                     </div>
                                     <?php endif; ?>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <?php endif; ?>
                     
                     <!-- Restaurant Info Section -->
                     <div class="property-section-wrap" style="margin-bottom: 2rem;">
                         <div class="block-wrap">
                             <div class="block-title-wrap">
                                 <h2>Informations</h2>
                                    </div>
                             <div class="block-content-wrap">
                                 <div class="restaurant-info" style="padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                                     <div class="info-grid" style="display: grid; gap: 1rem;">
                                         <?php if ($cuisine_type): ?>
                                         <div class="info-item" style="display: flex; align-items: center; gap: 0.75rem;">
                                             <i class="fas fa-utensils" style="color: var(--primary-color); width: 20px;"></i>
                                             <div>
                                                 <span style="font-weight: 600; color: var(--text-primary);">Cuisine:</span>
                                                 <span style="color: var(--text-secondary); margin-left: 0.5rem;"><?php echo esc_html(ucfirst($cuisine_type)); ?></span>
                                             </div>
                                         </div>
                                         <?php endif; ?>
                                         
                                         <?php if ($is_featured === '1'): ?>
                                         <div class="info-item" style="display: flex; align-items: center; gap: 0.75rem;">
                                             <i class="fas fa-star" style="color: #fbbf24; width: 20px;"></i>
                                             <div>
                                                 <span style="font-weight: 600; color: var(--text-primary);">Restaurant recommandé</span>
                                    </div>
                                         </div>
                                         <?php endif; ?>
                                         
                                         <?php if ($virtual_tour_url): ?>
                                         <div class="info-item" style="display: flex; align-items: center; gap: 0.75rem;">
                                             <i class="fas fa-vr-cardboard" style="color: var(--primary-color); width: 20px;"></i>
                                             <div>
                                                 <span style="font-weight: 600; color: var(--text-primary);">Visite virtuelle disponible</span>
                                    </div>
                                         </div>
                                         <?php endif; ?>
                                         
                                         <?php if ($video_url): ?>
                                         <div class="info-item" style="display: flex; align-items: center; gap: 0.75rem;">
                                             <i class="fas fa-play-circle" style="color: var(--primary-color); width: 20px;"></i>
                                             <div>
                                                 <span style="font-weight: 600; color: var(--text-primary);">Vidéo disponible</span>
                                             </div>
                                         </div>
                                         <?php endif; ?>
                                     </div>
                                 </div>
                             </div>
                         </div>
                                    </div>
                                    
                     <!-- Contact Actions Section -->
                     <div class="property-section-wrap" style="margin-bottom: 2rem;">
                         <div class="block-wrap">
                             <div class="block-title-wrap">
                                 <h2>Contact</h2>
                             </div>
                             <div class="block-content-wrap">
                                 <div class="contact-actions" style="display: flex; flex-direction: column; gap: 0.75rem;">
                                     <?php if ($phone): ?>
                                     <a href="tel:<?php echo esc_attr($phone); ?>" 
                                        class="contact-btn"
                                        style="display: flex; align-items: center; padding: 0.75rem 1rem; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); text-decoration: none; color: var(--text-primary); transition: var(--transition);">
                                         <i class="fas fa-phone" style="margin-right: 0.75rem; color: var(--primary-color);"></i>
                                         <span style="font-weight: 500;">Appeler: <?php echo esc_html($phone); ?></span>
                                     </a>
                                     <?php endif; ?>
                                     
                                     <?php if ($email): ?>
                                     <a href="mailto:<?php echo esc_attr($email); ?>" 
                                        class="contact-btn"
                                        style="display: flex; align-items: center; padding: 0.75rem 1rem; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); text-decoration: none; color: var(--text-primary); transition: var(--transition);">
                                         <i class="fas fa-envelope" style="margin-right: 0.75rem; color: var(--primary-color);"></i>
                                         <span style="font-weight: 500;">Email: <?php echo esc_html($email); ?></span>
                                     </a>
                                     <?php endif; ?>
                                    
                                    <?php if ($phone): ?>
                                     <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $phone)); ?>" 
                                        target="_blank"
                                        class="contact-btn"
                                        style="display: flex; align-items: center; padding: 0.75rem 1rem; background: #25D366; border: 1px solid #25D366; border-radius: var(--radius-lg); text-decoration: none; color: white; transition: var(--transition);">
                                         <i class="fab fa-whatsapp" style="margin-right: 0.75rem;"></i>
                                         <span style="font-weight: 500;">WhatsApp</span>
                                    </a>
                                    <?php endif; ?>
                                     
                                     <?php if ($virtual_tour_url): ?>
                                     <a href="<?php echo esc_url($virtual_tour_url); ?>" 
                                        target="_blank"
                                        class="contact-btn"
                                        style="display: flex; align-items: center; padding: 0.75rem 1rem; background: var(--gradient-primary); border: 1px solid var(--primary-color); border-radius: var(--radius-lg); text-decoration: none; color: var(--bg-primary); transition: var(--transition);">
                                         <i class="fas fa-vr-cardboard" style="margin-right: 0.75rem;"></i>
                                         <span style="font-weight: 500;">Visite virtuelle</span>
                                     </a>
                                     <?php endif; ?>
                                 </div>
                             </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

        </div>
        
    <?php 
        endwhile; 
    else: 
        // Fallback content when no posts are found
    ?>
        <div class="container" style="padding: 4rem 0; text-align: center;">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div style="background: #f8f9fa; padding: 3rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #ffc107; margin-bottom: 1.5rem;"></i>
                        <h2 style="color: #495057; margin-bottom: 1rem;">Restaurant non trouvé</h2>
                        <p style="color: #6c757d; margin-bottom: 2rem; font-size: 1.1rem;">
                            Le restaurant que vous recherchez n'existe pas ou a été supprimé.
                        </p>
                        <a href="<?php echo home_url('/restaurants/'); ?>" 
                           style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%); color: #1f2937; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                            <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>
                            Retour aux restaurants
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Gallery Lightbox Modal -->
<div id="property-lightbox" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Galerie</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
        </button>
            </div>
            <div class="modal-body">
                <div id="lightbox-gallery"></div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="gallery-data">
<?php echo wp_json_encode($gallery_images); ?>
</script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Section switching functionality
        const sectionButtons = document.querySelectorAll('.section-btn');
        const contentSections = document.querySelectorAll('.content-section');
        
        sectionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetSection = this.getAttribute('data-section');
                
                // Update button states
                sectionButtons.forEach(btn => {
                    btn.classList.remove('active');
                });
                
                this.classList.add('active');
                
                // Update content sections
                contentSections.forEach(section => {
                    section.style.display = 'none';
                    section.classList.remove('active');
                });
                
                const targetElement = document.getElementById(targetSection + '-section');
                if (targetElement) {
                    targetElement.style.display = 'block';
                    targetElement.classList.add('active');
                }
            });
        });

        // Image slider functionality
        const sliderDots = document.querySelectorAll('.slider-dot');
        const slides = document.querySelectorAll('.slide');
        let currentSlide = 0;
        
        sliderDots.forEach((dot, index) => {
            dot.addEventListener('click', function() {
                // Update dots
                sliderDots.forEach(d => {
                    d.classList.remove('active');
                    d.style.background = 'rgba(255,255,255,0.5)';
                });
                
                this.classList.add('active');
                this.style.background = 'var(--primary-color)';
                
                // Update slides
                slides.forEach(slide => {
                    slide.classList.remove('active');
                    slide.style.opacity = '0';
                });
                
                slides[index].classList.add('active');
                slides[index].style.opacity = '1';
                
                currentSlide = index;
            });
        });

        // Auto-advance slider
        if (slides.length > 1) {
            setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;
                
                // Update dots
                sliderDots.forEach((dot, index) => {
                    dot.classList.remove('active');
                    dot.style.background = index === currentSlide ? 'var(--primary-color)' : 'rgba(255,255,255,0.5)';
                });
                
                // Update slides
                slides.forEach((slide, index) => {
                    slide.classList.remove('active');
                    slide.style.opacity = index === currentSlide ? '1' : '0';
                });
            }, 5000);
        }

        // Map initialization (if coordinates are available)
        console.log('Debug - Latitude:', '<?php echo $latitude; ?>');
        console.log('Debug - Longitude:', '<?php echo $longitude; ?>');
        console.log('Debug - Has coordinates:', <?php echo ($latitude && $longitude && is_numeric($latitude) && is_numeric($longitude)) ? 'true' : 'false'; ?>);
        
        // Validate coordinates before using them
        const hasValidCoordinates = <?php echo ($latitude && $longitude && is_numeric($latitude) && is_numeric($longitude)) ? 'true' : 'false'; ?>;
        const restaurantLat = <?php echo $latitude ?: 'null'; ?>;
        const restaurantLng = <?php echo $longitude ?: 'null'; ?>;
        
        <?php if ($latitude && $longitude && is_numeric($latitude) && is_numeric($longitude)): ?>
        function initializeMap() {
            console.log('initializeMap called');
            console.log('Leaflet available:', typeof L !== 'undefined');
            console.log('Coordinates:', restaurantLat, restaurantLng);
            
            if (typeof L === 'undefined') {
                console.error('Leaflet library not loaded');
                return;
            }
            
            // Validate coordinates
            if (!hasValidCoordinates || !restaurantLat || !restaurantLng) {
                console.error('Invalid coordinates for map initialization');
                return;
            }
            
            // Clear any existing content
            const mapContainer = document.getElementById('restaurant-map');
            console.log('Map container found:', !!mapContainer);
            
            if (!mapContainer) {
                console.error('Map container not found');
                return;
            }
            
            // Clear existing map if it exists
            if (window.restaurantMap) {
                window.restaurantMap.remove();
            }
            
            mapContainer.innerHTML = '';
            
            // Ensure container has proper dimensions
            mapContainer.style.width = '100%';
            mapContainer.style.height = '500px';
            
            // Force a reflow to ensure dimensions are applied
            mapContainer.offsetHeight;
            
            try {
                // Initialize the map
                window.restaurantMap = L.map('restaurant-map', {
                    center: [restaurantLat, restaurantLng],
                    zoom: 15,
                    zoomControl: true
                });
                
                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(window.restaurantMap);
                
                // No markers - just popup at coordinates
                
                
                // Create detailed popup content
                const popupContent = `
                    <div class="restaurant-popup-content" style="
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        min-width: 250px;
                        max-width: 300px;
                        padding: 0;
                    ">
                        <div style="
                            display: flex;
                            gap: 12px;
                            align-items: flex-start;
                        ">
                            <div style="flex-shrink: 0;">
                                <img src="<?php echo $principal_image ? wp_get_attachment_image_url($principal_image, 'thumbnail') : 'https://via.placeholder.com/60x60'; ?>" 
                                     alt="<?php echo esc_attr($blog_title ?: get_the_title()); ?>" 
                                     style="
                                        width: 60px;
                                        height: 60px;
                                        object-fit: cover;
                                        border-radius: 8px;
                                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                                     " />
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h3 style="
                                    font-size: 16px;
                                    font-weight: 600;
                                    margin: 0 0 4px 0;
                                    line-height: 1.3;
                                    color: #1f2937;
                                "><?php echo esc_html($blog_title ?: get_the_title()); ?></h3>
                                <p style="
                                    margin: 0 0 4px 0;
                                    font-size: 13px;
                                    line-height: 1.4;
                                    color: #6b7280;
                                "><?php echo esc_html($address); ?></p>
                                <p style="
                                    margin: 0 0 8px 0;
                                    font-size: 13px;
                                    line-height: 1.4;
                                    color: #6b7280;
                                "><?php echo esc_html($city); ?></p>
                                <div style="
                                    display: flex;
                                    gap: 8px;
                                    flex-wrap: wrap;
                                    justify-content: center;
                                ">
                                    <?php if ($phone): ?>
                                    <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $phone)); ?>" 
                                       target="_blank"
                                       title="WhatsApp"
                                       style="
                                           display: inline-flex;
                                           align-items: center;
                                           justify-content: center;
                                           padding: 8px;
                                           background: #25D366;
                                           color: white;
                                           text-decoration: none;
                                           border-radius: 50%;
                                           width: 32px;
                                           height: 32px;
                                           transition: all 0.2s ease;
                                       "
                                       onmouseover="this.style.background='#128C7E'; this.style.transform='scale(1.1)'"
                                       onmouseout="this.style.background='#25D366'; this.style.transform='scale(1)'">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                        </svg>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($google_maps_link): ?>
                                    <a href="<?php echo esc_url($google_maps_link); ?>" 
                                       target="_blank"
                                       title="Google Maps"
                                       style="
                                           display: inline-flex;
                                           align-items: center;
                                           justify-content: center;
                                           padding: 8px;
                                           background: #4285F4;
                                           color: white;
                                           text-decoration: none;
                                           border-radius: 50%;
                                           width: 32px;
                                           height: 32px;
                                           transition: all 0.2s ease;
                                       "
                                       onmouseover="this.style.background='#3367D6'; this.style.transform='scale(1.1)'"
                                       onmouseout="this.style.background='#4285F4'; this.style.transform='scale(1)'">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                    </a>
                                    <?php elseif ($latitude && $longitude): ?>
                                    <a href="https://www.google.com/maps?q=<?php echo esc_attr($latitude); ?>,<?php echo esc_attr($longitude); ?>" 
                                       target="_blank"
                                       title="Google Maps"
                                       style="
                                           display: inline-flex;
                                           align-items: center;
                                           justify-content: center;
                                           padding: 8px;
                                           background: #4285F4;
                                           color: white;
                                           text-decoration: none;
                                           border-radius: 50%;
                                           width: 32px;
                                           height: 32px;
                                           transition: all 0.2s ease;
                                       "
                                       onmouseover="this.style.background='#3367D6'; this.style.transform='scale(1.1)'"
                                       onmouseout="this.style.background='#4285F4'; this.style.transform='scale(1)'">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?php 
                                        $restaurant_name = $blog_title ?: get_the_title();
                                        $restaurant_slug = sanitize_title($restaurant_name);
                                        // Debug: Uncomment the line below to see what values we're getting
                                        // echo '<!-- Debug: blog_title=' . $blog_title . ', get_the_title()=' . get_the_title() . ', restaurant_name=' . $restaurant_name . ', slug=' . $restaurant_slug . ' -->';
                                        echo esc_url(home_url('/details/' . $restaurant_slug . '/#details-section')); 
                                    ?>" 
                                       title="Voir détails"
                                       style="
                                           display: inline-flex;
                                           align-items: center;
                                           justify-content: center;
                                           padding: 8px;
                                           background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%);
                                           color: white;
                                           text-decoration: none;
                                           border-radius: 50%;
                                           width: 32px;
                                           height: 32px;
                                           transition: all 0.2s ease;
                                       "
                                       onmouseover="this.style.transform='scale(1.1)'"
                                       onmouseout="this.style.transform='scale(1)'">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Create popup at coordinates without marker
                const popup = L.popup({
                    className: 'restaurant-popup',
                    closeButton: false,
                    autoClose: false,
                    closeOnClick: false,
                    autoPan: false
                })
                .setLatLng([restaurantLat, restaurantLng])
                .setContent(popupContent)
                .openOn(window.restaurantMap);
                
                // Add pulse animation CSS
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes pulse {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.1); }
                        100% { transform: scale(1); }
                    }
                    .restaurant-detail-marker {
                        animation: pulse 2s infinite;
                    }
                    .restaurant-popup .leaflet-popup-content-wrapper {
                        border-radius: 12px;
                        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
                        border: 2px solid #f3f4f6;
                        transition: all 0.3s ease;
                    }
                    .restaurant-popup .leaflet-popup-content-wrapper:hover {
                        border-color: #fedc00;
                        box-shadow: 0 12px 32px rgba(255, 193, 7, 0.3);
                    }
                `;
                document.head.appendChild(style);
                
                // Invalidate size after a short delay to ensure proper rendering
                setTimeout(function() {
                    if (window.restaurantMap) {
                        window.restaurantMap.invalidateSize();
                    }
                }, 100);
                
                console.log('Map initialized successfully with custom marker and popup');
            } catch (error) {
                console.error('Error initializing map:', error);
                mapContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; background: var(--bg-tertiary); color: var(--text-muted);"><div style="text-align: center;"><i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i><p>Erreur lors du chargement de la carte</p></div></div>';
            }
        }
        
        // Initialize map when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for Leaflet to be available
            function waitForLeaflet() {
                if (typeof L !== 'undefined') {
                    initializeMap();
                } else {
                    setTimeout(waitForLeaflet, 100);
                }
            }
            waitForLeaflet();
        });
        
        // Also try on window load as backup
        window.addEventListener('load', function() {
            if (typeof L !== 'undefined') {
                setTimeout(initializeMap, 100);
            }
        });
        
        // Re-initialize map when switching to map section
        document.addEventListener('click', function(e) {
            if (e.target.closest('.section-btn[data-section="map"]')) {
                // Wait for section to become visible, then initialize map
                setTimeout(function() {
                    const mapSection = document.getElementById('map-section');
                    if (mapSection && mapSection.style.display !== 'none') {
                        initializeMap();
                    }
                }, 200);
            }
        });
        <?php else: ?>
        console.log('No valid coordinates available for map - using default coordinates');
        // Show map with default coordinates (Casablanca) and a message
        function initializeMapWithDefaults() {
            console.log('initializeMapWithDefaults called');
            console.log('Leaflet available:', typeof L !== 'undefined');
            
            if (typeof L === 'undefined') {
                console.error('Leaflet library not loaded');
                return;
            }
            
            // Validate that we have default coordinates
            if (!defaultLat || !defaultLng || isNaN(defaultLat) || isNaN(defaultLng)) {
                console.error('Invalid default coordinates');
                return;
            }
            
            const mapContainer = document.getElementById('restaurant-map');
            if (!mapContainer) {
                console.error('Map container not found');
                return;
            }
            
            // Clear existing map if it exists
            if (window.restaurantMap) {
                window.restaurantMap.remove();
            }
            
            mapContainer.innerHTML = '';
            
            // Ensure container has proper dimensions
            mapContainer.style.width = '100%';
            mapContainer.style.height = '100%';
            
            // Force a reflow to ensure dimensions are applied
            mapContainer.offsetHeight;
            
            try {
                // Initialize the map with default coordinates (Casablanca)
                const defaultLat = 33.5731;
                const defaultLng = -7.5898;
                
                window.restaurantMap = L.map('restaurant-map', {
                    center: [defaultLat, defaultLng],
                    zoom: 15,
                    zoomControl: true
                });
                
                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(window.restaurantMap);
                
                // Create custom SVG icon for restaurant
                const restaurantIcon = L.divIcon({
                    className: 'restaurant-detail-marker',
                    html: `<div style="display: flex; align-items: center; justify-content: center; position: relative; width: 40px; height: 40px;">
                             <svg style="width: 40px; height: 40px;" fill="currentColor" viewBox="0 0 64 64">
                               <path fill="#ff9800" d="M53 24.267C53 42.633 32 61 32 61S11 42.633 11 24.267a21 21 0 1 1 42 0z"/>
                               <circle cx="32" cy="24" r="17" fill="#eeeeee"/>
                               <ellipse cx="39" cy="20" fill="#ff9800" rx="4" ry="5"/>
                               <path d="M32 2a22.16 22.16 0 0 0-22 22.267c0 7.841 3.6 16.542 10.7 25.86a86.428 86.428 0 0 0 10.642 11.626 1 1 0 0 0 1.316 0A86.428 86.428 0 0 0 43.3 50.127C50.4 40.809 54 32.108 54 24.267A22.16 22.16 0 0 0 32 2zm0 57.646c-3.527-3.288-20-19.5-20-35.379a20 20 0 1 1 40 0c0 15.88-16.473 32.091-20 35.379z" fill="#000000"/>
                               <path d="M32 6a18 18 0 1 0 18 18A18.021 18.021 0 0 0 32 6zm0 34a16 16 0 1 1 16-16 16.019 16.019 0 0 1-16 16z" fill="#000000"/>
                               <path d="M30 22c0 .188 0 .382-.582.673L28 23.382V14h-2v9.382l-1.418-.709C24 22.382 24 22.188 24 22v-8h-2v8a2.7 2.7 0 0 0 1.687 2.462l1.948.974a3 3 0 0 0 .365.131V36h2V25.567a3 3 0 0 0 .365-.131l1.947-.974A2.7 2.7 0 0 0 32 22v-8h-2zM39 14c-2.757 0-5 2.691-5 6 0 2.9 1.721 5.321 4 5.879V36h2V25.879c2.279-.558 4-2.981 4-5.879 0-3.309-2.243-6-5-6zm0 10c-1.654 0-3-1.794-3-4s1.346-4 3-4 3 1.794 3 4-1.346 4-3 4z" fill="#000000"/>
                             </svg>
                           </div>`,
                    iconSize: [40, 40],
                    iconAnchor: [20, 40],
                    popupAnchor: [0, -40]
                });
                
                // Add custom marker
                const marker = L.marker([defaultLat, defaultLng], {
                    icon: restaurantIcon
                }).addTo(window.restaurantMap);
                
                // Create popup content with warning
                const popupContent = `
                    <div class="restaurant-popup-content" style="
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        min-width: 250px;
                        max-width: 300px;
                        padding: 0;
                    ">
                        <div style="
                            display: flex;
                            gap: 12px;
                            align-items: flex-start;
                        ">
                            <div style="flex-shrink: 0;">
                                <div style="
                                    width: 60px;
                                    height: 60px;
                                    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%);
                                    border-radius: 8px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                                ">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                    </svg>
                                </div>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h3 style="
                                    font-size: 16px;
                                    font-weight: 600;
                                    margin: 0 0 4px 0;
                                    line-height: 1.3;
                                    color: #1f2937;
                                "><?php echo esc_html($blog_title ?: get_the_title()); ?></h3>
                                <p style="
                                    margin: 0 0 8px 0;
                                    font-size: 13px;
                                    line-height: 1.4;
                                    color: #ef4444;
                                    font-weight: 500;
                                ">⚠️ Coordonnées non disponibles</p>
                                <p style="
                                    margin: 0 0 8px 0;
                                    font-size: 12px;
                                    line-height: 1.4;
                                    color: #6b7280;
                                ">Veuillez ajouter la latitude et longitude dans l'admin</p>
                            </div>
                        </div>
                    </div>
                `;
                
                // Create popup at coordinates without marker
                const popup = L.popup({
                    className: 'restaurant-popup',
                    closeButton: false,
                    autoClose: false,
                    closeOnClick: false,
                    autoPan: false
                })
                .setLatLng([defaultLat, defaultLng])
                .setContent(popupContent)
                .openOn(window.restaurantMap);
                
                // Add pulse animation CSS
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes pulse {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.1); }
                        100% { transform: scale(1); }
                    }
                    .restaurant-detail-marker {
                        animation: pulse 2s infinite;
                    }
                    .restaurant-popup .leaflet-popup-content-wrapper {
                        border-radius: 12px;
                        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
                        border: 2px solid #f3f4f6;
                        transition: all 0.3s ease;
                    }
                    .restaurant-popup .leaflet-popup-content-wrapper:hover {
                        border-color: #fedc00;
                        box-shadow: 0 12px 32px rgba(255, 193, 7, 0.3);
                    }
                `;
                document.head.appendChild(style);
                
                // Invalidate size after a short delay to ensure proper rendering
                setTimeout(function() {
                    if (window.restaurantMap) {
                        window.restaurantMap.invalidateSize();
                    }
                }, 100);
                
                console.log('Map with default coordinates initialized successfully');
            } catch (error) {
                console.error('Error initializing map with defaults:', error);
                mapContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; background: var(--bg-tertiary); color: var(--text-muted);"><div style="text-align: center;"><i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i><p>Erreur lors du chargement de la carte</p></div></div>';
            }
        }
        
        // Initialize map with defaults when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for Leaflet to be available
            function waitForLeaflet() {
                if (typeof L !== 'undefined') {
                    initializeMapWithDefaults();
                } else {
                    setTimeout(waitForLeaflet, 100);
                }
            }
            waitForLeaflet();
        });
        
        // Also try on window load as backup
        window.addEventListener('load', function() {
            if (typeof L !== 'undefined') {
                setTimeout(initializeMapWithDefaults, 100);
            }
        });
        <?php endif; ?>

        // Navigation links (no smooth scrolling)
        document.querySelectorAll('.property-navigation .target').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'auto', block: 'start' });
                }
            });
        });

        // Handle video loading errors
        const videoIframe = document.querySelector('#video-section iframe');
        if (videoIframe) {
            videoIframe.addEventListener('error', function() {
                const fallback = document.querySelector('.video-fallback');
                if (fallback) {
                    fallback.style.display = 'flex';
                    videoIframe.style.display = 'none';
                }
            });
            
            // Also check if iframe loads but shows error content
            videoIframe.addEventListener('load', function() {
                setTimeout(() => {
                    try {
                        // Try to access iframe content to check if it loaded properly
                        const iframeDoc = videoIframe.contentDocument || videoIframe.contentWindow.document;
                        if (iframeDoc && iframeDoc.body && iframeDoc.body.innerHTML.includes('error') || 
                            iframeDoc && iframeDoc.body && iframeDoc.body.innerHTML.includes('not available')) {
                            const fallback = document.querySelector('.video-fallback');
                            if (fallback) {
                                fallback.style.display = 'flex';
                                videoIframe.style.display = 'none';
                            }
                        }
                    } catch (e) {
                        // Cross-origin error, which is normal for YouTube
                        // Video should load fine in this case
                    }
                }, 2000);
            });
        }
    });

    // Image modal functionality
    function openImageModal(imageUrl, imageAlt) {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        `;
        
        const img = document.createElement('img');
        img.src = imageUrl;
        img.alt = imageAlt;
        img.style.cssText = `
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 8px;
        `;
        
        modal.appendChild(img);
        document.body.appendChild(modal);
        
        modal.addEventListener('click', function() {
            document.body.removeChild(modal);
        });
    }
</script>

<!-- Menu Popup Modal -->
<div id="menu-popup" class="menu-popup" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 10000; backdrop-filter: blur(5px);">
    <div class="menu-popup-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: var(--bg-primary); border-radius: var(--radius-xl); padding: 2rem; max-width: 90vw; max-height: 90vh; box-shadow: var(--shadow-2xl); overflow: hidden;">
        <div class="menu-popup-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
            <h3 id="menu-popup-title" style="margin: 0; color: var(--text-primary); font-size: 1.5rem; font-weight: 600;"></h3>
            <button id="menu-popup-close" style="background: none; border: none; font-size: 1.5rem; color: var(--text-secondary); cursor: pointer; padding: 0.5rem; border-radius: var(--radius-md); transition: var(--transition);">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="menu-popup-body" style="text-align: center;">
            <div id="menu-popup-image-container" style="max-height: 70vh; overflow: auto;">
                <!-- Menu image will be loaded here -->
            </div>
            <div class="menu-popup-actions" style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center;">
                <a id="menu-popup-download" href="#" target="_blank" style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: var(--gradient-primary); color: var(--bg-primary); text-decoration: none; border-radius: var(--radius-full); font-weight: 600; transition: var(--transition);">
                    <i class="fas fa-download" style="margin-right: 0.5rem;"></i>
                    Télécharger
                </a>
                <button id="menu-popup-close-btn" style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: var(--radius-full); font-weight: 600; cursor: pointer; transition: var(--transition);">
                    <i class="fas fa-times" style="margin-right: 0.5rem;"></i>
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Menu popup functionality
function openMenuPopup(menuIndex, menuName, fileUrl) {
    const popup = document.getElementById('menu-popup');
    const title = document.getElementById('menu-popup-title');
    const imageContainer = document.getElementById('menu-popup-image-container');
    const downloadLink = document.getElementById('menu-popup-download');
    
    // Set title
    title.textContent = menuName;
    
    // Set download link
    downloadLink.href = fileUrl;
    
    // Check if file is an image
    const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(fileUrl);
    
    if (isImage) {
        // Display image
        imageContainer.innerHTML = `
            <img src="${fileUrl}" 
                 alt="${menuName}" 
                 style="max-width: 100%; height: auto; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);" 
                 onload="this.style.opacity='1'" 
                 style="opacity: 0; transition: opacity 0.3s ease;" />
        `;
    } else {
        // Display PDF or other file
        imageContainer.innerHTML = `
            <div style="padding: 2rem; text-align: center;">
                <i class="fas fa-file-pdf" style="font-size: 4rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">Ce fichier ne peut pas être affiché en aperçu.</p>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Cliquez sur "Télécharger" pour ouvrir le fichier.</p>
            </div>
        `;
    }
    
    // Show popup
    popup.style.display = 'block';
    document.body.style.overflow = 'hidden';
    document.documentElement.classList.add('lebonresto-detail-page');
    
    // Add animation
    popup.style.opacity = '0';
    setTimeout(() => {
        popup.style.opacity = '1';
    }, 10);
}

// Close popup functions
function closeMenuPopup() {
    const popup = document.getElementById('menu-popup');
    popup.style.opacity = '0';
    setTimeout(() => {
        popup.style.display = 'none';
        document.body.style.overflow = 'auto';
        document.documentElement.classList.remove('lebonresto-detail-page');
    }, 300);
}

// Apply classes for scrollbar management (minimal approach)
document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('lebonresto-detail-page');
    document.documentElement.classList.add('lebonresto-detail-page');
});

// Event listeners
document.getElementById('menu-popup-close').addEventListener('click', closeMenuPopup);
document.getElementById('menu-popup-close-btn').addEventListener('click', closeMenuPopup);

// Close on background click
document.getElementById('menu-popup').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMenuPopup();
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMenuPopup();
    }
});

        // Menu links (no smooth scrolling)
        document.querySelectorAll('.menu-link1').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ 
                        behavior: 'auto', 
                        block: 'start' 
                    });
                    // Highlight the target menu
                    targetElement.style.border = '2px solid var(--primary-color)';
                    targetElement.style.boxShadow = 'var(--shadow-lg)';
                    setTimeout(() => {
                        targetElement.style.border = '1px solid var(--border-color)';
                        targetElement.style.boxShadow = 'var(--shadow-sm)';
                    }, 2000);
                }
            });
        });


        // Menu toggle functionality
        function toggleMenuContent(index) {
            const content = document.getElementById('menu-content-' + index);
            const arrow = document.querySelector('#menu-' + index + ' .menu-arrow');
            
            if (content.style.display === 'none' || content.style.display === '') {
                content.style.display = 'block';
                arrow.style.transform = 'rotate(180deg)';
                    } else {
                content.style.display = 'none';
                arrow.style.transform = 'rotate(0deg)';
            }
        }
</script>

<!-- Font Awesome Fallback Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Font Awesome is loaded
    function checkFontAwesome() {
        const testIcon = document.createElement('i');
        testIcon.className = 'fas fa-star';
        testIcon.style.position = 'absolute';
        testIcon.style.left = '-9999px';
        document.body.appendChild(testIcon);
        
        const computedStyle = window.getComputedStyle(testIcon);
        const fontFamily = computedStyle.getPropertyValue('font-family');
        
        document.body.removeChild(testIcon);
        
        // If Font Awesome is not loaded, try to load it
        if (!fontFamily.includes('Font Awesome') && !fontFamily.includes('FontAwesome')) {
            console.log('Font Awesome not detected, loading fallback...');
            
            // Create and inject Font Awesome CSS
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
            link.crossOrigin = 'anonymous';
            document.head.appendChild(link);
            
            // Also try the official Font Awesome CDN
            const link2 = document.createElement('link');
            link2.rel = 'stylesheet';
            link2.href = 'https://use.fontawesome.com/releases/v6.4.0/css/all.css';
            link2.crossOrigin = 'anonymous';
            document.head.appendChild(link2);
        }
    }
    
    // Check immediately and after a short delay
    checkFontAwesome();
    setTimeout(checkFontAwesome, 1000);
});
</script>

<?php get_footer(); ?>