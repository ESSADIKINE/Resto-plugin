<?php
/**
 * Single Restaurant Template - Updated Layout
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
$seo_description = "Restaurant d'exception à {$city}, Maroc. Cuisine authentique, ambiance chaleureuse et service impeccable. Découvrez nos spécialités culinaires avec visite virtuelle 360°, réservez votre table et vivez une expérience gastronomique unique au cœur de la capitale économique.";

// Add SEO meta tags to head
add_action('wp_head', function() use ($restaurant_name, $city, $cuisine_type, $seo_description) {
    echo '<!-- SEO Meta Descriptions -->' . "\n";
    echo '<meta name="description" content="' . esc_attr($seo_description) . '">' . "\n";
    echo '<meta name="keywords" content="restaurant ' . esc_attr($restaurant_name) . ', ' . esc_attr($cuisine_type) . ' ' . esc_attr($city) . ', gastronomie Maroc, réservation restaurant ' . esc_attr($city) . ', visite virtuelle restaurant, tour virtuel restaurant, visite 360 restaurant, tour 360 restaurant, visite immersive restaurant">' . "\n";
    echo '<meta name="robots" content="index, follow">' . "\n";
    echo '<meta name="author" content="' . get_bloginfo('name') . '">' . "\n";
    
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

// Enqueue Tailwind CSS with fallback
wp_enqueue_style(
    'tailwind-css',
    'https://cdn.tailwindcss.com',
    array(),
    '3.4.0'
);

// Enqueue FontAwesome CSS
wp_enqueue_style(
    'font-awesome',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
    array(),
    '6.0.0'
);

// Add inline backup styles if Tailwind fails to load
wp_add_inline_style('tailwind-css', '
/* Tailwind Backup Styles */
.min-h-screen { min-height: 100vh; }
.bg-gray-50 { background-color: #f9fafb; }
.bg-gray-100 { background-color: #f3f4f6; }
.bg-white { background-color: #ffffff; }
.bg-yellow-400 { background-color: #fedc00; }
.bg-yellow-500 { background-color: #fedc00; }
.text-gray-700 { color: #374151; }
.text-gray-800 { color: #1f2937; }
.text-gray-600 { color: #4b5563; }
.text-yellow-600 { color: #fedc00; }
.border { border-width: 1px; }
.border-gray-200 { border-color: #e5e7eb; }
.border-gray-300 { border-color: #d1d5db; }
.rounded-lg { border-radius: 0.5rem; }
.shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
.shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
.p-4 { padding: 1rem; }
.p-6 { padding: 1.5rem; }
.px-4 { padding-left: 1rem; padding-right: 1rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.py-4 { padding-top: 1rem; padding-bottom: 1rem; }
.mb-4 { margin-bottom: 1rem; }
.mb-6 { margin-bottom: 1.5rem; }
.w-full { width: 100%; }
.h-full { height: 100%; }
.h-screen { height: 100vh; }
.flex { display: flex; }
.grid { display: grid; }
.container { max-width: 1200px; margin: 0 auto; }
.sticky { position: sticky; }
.top-0 { top: 0; }
.z-50 { z-index: 50; }
.flex-1 { flex: 1 1 0%; }
.flex-col { flex-direction: column; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-4 { gap: 1rem; }
.space-y-3 > * + * { margin-top: 0.75rem; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }
.text-sm { font-size: 0.875rem; }
.text-lg { font-size: 1.125rem; }
.text-xl { font-size: 1.25rem; }
        .transition { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
        
        /* Remove underlines from icon links */
        a[href^="tel:"], a[href^="mailto:"] {
            text-decoration: none !important;
        }
        
        a[href^="tel:"]:hover, a[href^="mailto:"]:hover {
            text-decoration: none !important;
        }
        
.duration-200 { transition-duration: 200ms; }
.hover\\:bg-yellow-500:hover { background-color: #fedc00; }
.hover\\:bg-gray-300:hover { background-color: #d1d5db; }
.focus\\:outline-none:focus { outline: 2px solid transparent; outline-offset: 2px; }
.focus\\:ring-2:focus { box-shadow: 0 0 0 2px #fedc00; }
.max-h-96 { max-height: 24rem; }
.h-96 { height: 24rem; }
.overflow-y-auto { overflow-y: auto; }
@media (min-width: 1024px) {
    .lg\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .lg\\:flex-row { flex-direction: row; }
    .lg\\:w-48 { width: 12rem; }
    .lg\\:w-auto { width: auto; }
}
');

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

// Enqueue updated single restaurant script
wp_enqueue_script(
    'lebonresto-single-updated',
    LEBONRESTO_PLUGIN_URL . 'assets/js/single-restaurant.js',
    array('leaflet-js', 'wp-api'),
    LEBONRESTO_PLUGIN_VERSION . '.' . time() . '.' . rand(1000, 9999), // Aggressive cache busting
    true
);

// Enqueue single restaurant map script (the one we've been modifying)
wp_enqueue_script(
    'lebonresto-single-map',
    LEBONRESTO_PLUGIN_URL . 'assets/js/single-restaurant-map.js',
    array('leaflet-js', 'wp-api'),
    LEBONRESTO_PLUGIN_VERSION . '.' . time() . '.' . rand(1000, 9999), // Aggressive cache busting
    true
);

// Enqueue debug CSS for fallback styling
wp_enqueue_style(
    'lebonresto-debug-css',
    LEBONRESTO_PLUGIN_URL . 'assets/css/single-restaurant.css',
    array('tailwind-css'),
    LEBONRESTO_PLUGIN_VERSION . '.' . time() // Cache busting
);

// Add critical inline styles to ensure they're applied
wp_add_inline_style('lebonresto-debug-css', '
/* Critical inline styles for immediate application */
.lebonresto-single-layout {
    background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%) !important;
    min-height: 100vh !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif !important;
}

.two-column-layout {
    display: grid !important;
    grid-template-columns: 4fr 5fr !important;
    gap: 0 !important;
}

@media (max-width: 1023px) {
    .two-column-layout {
        grid-template-columns: 1fr !important;
    }
}

#restaurants-map {
    border-radius: 0px !important;
    width: 100% !important;
    background-color:rgb(255, 255, 255) !important;
    border-right: 3px solid rgb(255, 255, 255) !important;
}

@media (max-width: 1023px) {
    #restaurants-map {
        border-right: none !important;
        border-bottom: 3px solid rgb(255, 255, 255) !important;
    }
}

.virtual-tour-section {
    height: 64vh !important;
    border-bottom: 3px solid rgb(255, 255, 255) !important;
    background: linear-gradient(135deg, rgb(255, 255, 255) 0%, rgb(255, 255, 255) 100%) !important;
    position: relative !important;
    overflow: hidden !important;
}

@media (max-width: 1023px) {
    .virtual-tour-section {
        height: 50vh !important;
        min-height: 50vh !important;
    }
}

.virtual-tour-section iframe {
    width: 100% !important;
}

/* MOBILE FILTER STYLES - INLINE FOR IMMEDIATE APPLICATION */
.mobile-filter-toggle {
    position: fixed !important;
    top: 20px !important;
    right: 20px !important;
    z-index: 50 !important;
    display: block !important;
    transition: all 0.3s ease !important;
}

/* When filter is open, move icon to top-right */
.mobile-filter-toggle.filter-open {
    top: 20px !important;
    left: auto !important;
    right: 20px !important;
}

.mobile-filter-toggle button {
    background: rgba(255, 255, 255, 0.95) !important;
    border: 2px solid #fedc00 !important;
    border-radius: 12px !important;
    padding: 12px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    transition: all 0.3s ease !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 48px !important;
    height: 48px !important;
}

.mobile-filter-toggle button:hover {
    background: #fedc00 !important;
    transform: scale(1.05) !important;
}

.mobile-filter-toggle button::before {
    content: "☰" !important;
    display: block !important;
    font-size: 18px !important;
    font-weight: bold !important;
    color: #1f2937 !important;
}

.mobile-filter-toggle button:hover::before {
    color: #ffffff !important;
}

.filter-icon {
    display: none !important;
}

.filter-line {
    display: none !important;
}

.mobile-filter-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    background: rgba(0, 0, 0, 0.5) !important;
    z-index: 40 !important;
    display: none !important;
}

.mobile-filter-overlay:not(.hidden) {
    display: block !important;
}

.mobile-filter-panel {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    bottom: 0 !important;
    width: 320px !important;
    background: white !important;
    transform: translateX(-100%) !important;
    transition: transform 0.3s ease !important;
    overflow-y: auto !important;
    z-index: 41 !important;
    justify-items: center !important;
}

.mobile-filter-panel:not(.-translate-x-full) {
    transform: translateX(0) !important;
}

.mobile-filter-header {
    background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%) !important;
    border-bottom: 2px solid #e5e7eb !important;
    padding: 20px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    border-radius: 16px 16px 0 0 !important;
}

.mobile-filter-title {
    font-size: 18px !important;
    font-weight: 700 !important;
    margin: 0 !important;
    color: #1f2937 !important;
    display: flex !important;
    align-items: center !important;
}

.mobile-filter-close {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
    border: 2px solid #d1d5db !important;
    color: #374151 !important;
    font-size: 18px !important;
    cursor: pointer !important;
    padding: 8px !important;
    width: 36px !important;
    height: 36px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 12px !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.mobile-filter-close:hover {
    background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.mobile-filter-content {
    padding: 20px !important;
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    min-height: 100% !important;
}

.mobile-filter-group {
    margin-bottom: 20px !important;
}

.mobile-filter-label {
    display: block !important;
    font-weight: 600 !important;
    color: #374151 !important;
    margin-bottom: 8px !important;
    font-size: 14px !important;
}

/* Mobile filter form styling - centered with no padding */
.mobile-filter-panel input[type="text"],
.mobile-filter-panel input[type="email"],
.mobile-filter-panel select {
    width: 90% !important;
    border: 2px solid #d1d5db !important;
    border-radius: 12px !important;
    font-size: 16px !important;
    font-weight: 500 !important;
    background-color: #ffffff !important;
    color: #1f2937 !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
    min-height: 48px !important;
    padding: 0px !important;
    text-align: center !important;
}

.mobile-filter-panel input[type="text"]:focus,
.mobile-filter-panel input[type="email"]:focus,
.mobile-filter-panel select:focus {
    border-color: #fedc00 !important;
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1) !important;
    outline: none !important;
    transform: translateY(-1px) !important;
}

.mobile-filter-panel input[type="checkbox"] {
    width: 1.125rem !important;
    height: 1.125rem !important;
    accent-color: #fedc00 !important;
    border-radius: 0.25rem !important;
    border: 2px solid #d1d5db !important;
}

.mobile-filter-panel input[type="checkbox"]:checked {
    background-color: #fedc00 !important;
    border-color: #fedc00 !important;
}

.mobile-filter-panel button {
    height: 48px !important;
    border-radius: 12px !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    padding: 14px 24px !important;
    transition: all 0.3s ease !important;
    border: none !important;
    cursor: pointer !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.mobile-filter-panel button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15) !important;
}

.mobile-filter-panel button:active {
    transform: translateY(0) !important;
}

/* Search button styling - match desktop */
.mobile-filter-panel #mobile-search-restaurants {
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
    color: #1f2937 !important;
    font-weight: 700 !important;
}

.mobile-filter-panel #mobile-search-restaurants:hover {
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
    box-shadow: 0 6px 16px rgba(251, 191, 36, 0.4) !important;
}

/* Clear button styling - match desktop */
.mobile-filter-panel #mobile-clear-filters {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
    color: #374151 !important;
    border: 2px solid #ffffff !important;
}

.mobile-filter-panel #mobile-clear-filters:hover {
    background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

/* Hide labels - match desktop */
.mobile-filter-panel label {
    display: none !important;
}

/* Form groups spacing - exact match with desktop */
.mobile-filter-panel .space-y-4 > * + * {
    margin-top: 16px !important;
}

.mobile-filter-panel .space-y-3 > * + * {
    margin-top: 12px !important;
}

/* Individual form field spacing */
.mobile-filter-panel .space-y-4 > div {
    margin-bottom: 0 !important;
}

.mobile-filter-panel .space-y-4 > div:not(:last-child) {
    margin-bottom: 16px !important;
}

/* Checkbox container - match desktop */
.mobile-filter-panel .flex.items-center {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-top: 12px !important;
}

/* Disabled select styling - match desktop */
.mobile-filter-panel select:disabled {
    background-color: #f9fafb !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
    border-color: #e5e7eb !important;
}

/* Panel overall styling - match desktop filter form */
.mobile-filter-panel {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    border-radius: 16px !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
    border: 2px solid rgba(251, 191, 36, 0.1) !important;
}

/* Form container styling - match desktop filter form */
.mobile-filter-content .space-y-4 {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    border-radius: 12px !important;
    padding: 20px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
    border: 2px solid rgba(251, 191, 36, 0.1) !important;
}

/* Hide mobile filter on desktop */
@media (min-width: 1024px) {
    .mobile-filter-toggle {
        display: none !important;
    }
}

.filter-form {
    background: linear-gradient(135deg, #ffffff 0%, rgb(255, 255, 255) 100%) !important;
    border-radius: 16px !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
    padding: 20px !important;
    border: 2px solid rgba(251, 191, 36, 0.1) !important;
}

.filter-form input,
.filter-form select {
    height: 48px !important;
    border: 2px solid #d1d5db !important;
    border-radius: 12px !important;
    font-size: 16px !important;
    padding: 12px 16px !important;
    transition: all 0.3s ease !important;
    background-color: #ffffff !important;
}

.filter-form input:focus,
.filter-form select:focus {
    border-color: #fedc00 !important;
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1) !important;
    outline: none !important;
}

.filter-form button {
    height: 48px !important;
    border-radius: 12px !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    padding: 14px 24px !important;
    transition: all 0.3s ease !important;
    border: none !important;
    cursor: pointer !important;
}

#search-restaurants {
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
    color: #1f2937 !important;
    font-weight: 700 !important;
}

#search-restaurants:hover {
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(251, 191, 36, 0.4) !important;
}

#clear-filters {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
    color: #374151 !important;
    border: 2px solid #d1d5db !important;
}

.restaurant-card {
    background: linear-gradient(135deg, #ffffff 0%, rgb(255, 255, 255) 100%) !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    margin-bottom: 16px !important;
    border: 2px solid rgb(255, 255, 255) !important;
    transition: all 0.4s ease !important;
}

.restaurant-card:hover {
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-4px) scale(1.02) !important;
    border-color: #fedc00 !important;
}

.loading-spinner {
    border: 4px solid rgb(255, 255, 255) !important;
    border-top: 4px solid #fedc00 !important;
    border-right: 4px solid #fedc00 !important;
    border-radius: 50% !important;
    width: 32px !important;
    height: 32px !important;
    animation: spin 1.2s ease-in-out infinite !important;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .filter-form {
        flex-direction: column !important;
        align-items: stretch !important;
        padding: 20px !important;
        gap: 12px !important;
    }
    
    .filter-form input,
    .filter-form select,
    .filter-form button {
        width: 100% !important;
        min-height: 52px !important;
        font-size: 16px !important;
    }
}
');

?>

<div class="lebonresto-single-layout min-h-screen bg-gray-50">
    <?php while (have_posts()) : the_post(); ?>
        
        <?php
        // Get restaurant meta data
        $current_restaurant_id = get_the_ID();
        $address = get_post_meta($current_restaurant_id, '_restaurant_address', true);
        $city = get_post_meta($current_restaurant_id, '_restaurant_city', true);
        $cuisine_type = get_post_meta($current_restaurant_id, '_restaurant_cuisine_type', true);
        $description = get_post_meta($current_restaurant_id, '_restaurant_description', true);
        $phone = get_post_meta($current_restaurant_id, '_restaurant_phone', true);
        $latitude = get_post_meta($current_restaurant_id, '_restaurant_latitude', true);
        $longitude = get_post_meta($current_restaurant_id, '_restaurant_longitude', true);
        $is_featured = get_post_meta($current_restaurant_id, '_restaurant_is_featured', true);
        $virtual_tour_url = get_post_meta($current_restaurant_id, '_restaurant_virtual_tour_url', true);
        // Get gallery images with fallback
        if (function_exists('lebonresto_get_gallery_images')) {
        $gallery_images = lebonresto_get_gallery_images($current_restaurant_id);
        } else {
            // Fallback: get gallery images manually
            $gallery_ids = get_post_meta($current_restaurant_id, '_restaurant_gallery', true);
            $gallery_images = array();
            
            if ($gallery_ids) {
                $image_ids = explode(',', $gallery_ids);
                foreach ($image_ids as $image_id) {
                    $image_id = intval($image_id);
                    if ($image_id) {
                        $image_url = wp_get_attachment_image_url($image_id, 'medium');
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
        


        // Get cuisine types for filter
        $cuisine_types = lebonresto_get_cuisine_types();
        ?>

        <!-- Mobile Filter Toggle Button -->
        <div class="mobile-filter-toggle lg:hidden fixed top-4 left-4 z-50">
            <button 
                id="mobile-filter-btn"
                class="bg-yellow-400 hover:bg-yellow-500 text-gray-800 p-3 rounded-full shadow-lg transition-all duration-300 transform hover:scale-105"
                style="background-color: #fedc00;"
                title="Open Filters"
            >
            </button>
        </div>

        <!-- Mobile Filter Overlay -->
        <div id="mobile-filter-overlay" class="mobile-filter-overlay fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden">
            <div class="mobile-filter-panel bg-white h-full w-80 transform -translate-x-full transition-transform duration-300">
                <div class="p-4">
                   
                    <!-- Mobile Filter Form -->
                    <div class="space-y-4">
                        <!-- Restaurant Name Search -->
                        <div>
                            <input 
                                type="text" 
                                id="mobile-restaurant-name-filter" 
                                placeholder="<?php _e('Search restaurants...', 'le-bon-resto'); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            />
                        </div>
                        
                        <!-- City Filter -->
                        <div>
                            <input 
                                type="text" 
                                id="mobile-city-filter" 
                                placeholder="<?php _e('City...', 'le-bon-resto'); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            />
                        </div>
                        
                        <!-- Cuisine Filter -->
                        <div>
                            <select 
                                id="mobile-cuisine-filter"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            >
                                <option value=""><?php _e('All Cuisines', 'le-bon-resto'); ?></option>
                                <?php foreach ($cuisine_types as $cuisine): ?>
                                    <option value="<?php echo esc_attr($cuisine); ?>">
                                        <?php echo esc_html(ucfirst($cuisine)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Distance Filter -->
                        <div>
                            <select 
                                id="mobile-distance-filter"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                                disabled
                            >
                                <option value=""><?php _e('Select distance', 'le-bon-resto'); ?></option>
                                <option value="5">5 km</option>
                                <option value="10">10 km</option>
                                <option value="25">25 km</option>
                                <option value="50">50 km</option>
                                <option value="100">100 km</option>
                            </select>
                        </div>
                        
                        <!-- Featured Only Toggle -->
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="mobile-featured-only" 
                                class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 focus:ring-2"
                            />
                            <span class="ml-2 text-sm text-gray-700">
                                <?php _e('Featured Only', 'le-bon-resto'); ?>
                            </span>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-3 pt-4">
                            <button 
                                id="mobile-search-restaurants"
                                class="w-full px-4 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200 text-sm"
                                style="background-color: #fedc00;"
                            >
                                <?php _e('Apply Filters', 'le-bon-resto'); ?>
                            </button>
                            
                            <button 
                                id="mobile-clear-filters"
                                class="w-full px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-200 text-sm"
                            >
                                <?php _e('Clear All', 'le-bon-resto'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Filter Header (Hidden on Mobile, Tablet, and iPad) -->
        <div class="filter-header w-full bg-gray-100 border-b border-gray-200 sticky top-0 z-50">
            <div class="filter-container container mx-auto px-4 py-4">
                <div class="filter-form bg-white rounded-lg shadow-md p-2">
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
                                <?php foreach ($cuisine_types as $cuisine): ?>
                                    <option value="<?php echo esc_attr($cuisine); ?>">
                                        <?php echo esc_html(ucfirst($cuisine)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Distance Filter -->
                        <div class="w-full lg:w-48">
                            <select 
                                id="distance-filter"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                                disabled
                            >
                                <option value=""><?php _e('Distance', 'le-bon-resto'); ?></option>
                                <option value="5">5 km</option>
                                <option value="10">10 km</option>
                                <option value="25">25 km</option>
                                <option value="50">50 km</option>
                                <option value="100">100 km</option>
                            </select>
                        </div>
                        
                        <!-- Featured Only Toggle -->
                        <div class="flex items-center">
                            <label class="flex items-center space-x-2">
                                <input 
                                    type="checkbox" 
                                    id="featured-only" 
                                    class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 focus:ring-2"
                                />
                                <span class="text-sm text-gray-700 whitespace-nowrap">
                                    <i class="fas fa-star mr-1" style="color: #fedc00;"></i>
                                    <?php _e('Featured', 'le-bon-resto'); ?>
                            </span>
                            </label>
                        </div>

                        <!-- Search Button -->
                        <button 
                            id="search-restaurants"
                            class="w-full lg:w-auto px-6 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200"
                            style="background-color: #fedc00;"
                        >
                            <i class="fas fa-search mr-2"></i><?php _e('Search', 'le-bon-resto'); ?>
                        </button>
                        
                        <!-- Clear Button -->
                        <button 
                            id="clear-filters"
                            class="w-full lg:w-auto px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-200"
                        >
                            <?php _e('Clear', 'le-bon-resto'); ?>
                        </button>
                    </div>
                </div>
                        </div>
                </div>

        <!-- Mobile Tab Navigation (Hidden on Desktop) -->
        <div class="mobile-tab-navigation lg:hidden fixed bottom-0 left-0 right-0 z-100 bg-white border-t border-gray-200 shadow-lg">
            <div class="flex">
                <button 
                    id="mobile-tab-vr" 
                    class="mobile-tab-btn"
                    data-tab="vr"
                >
                    <i class="fas fa-vr-cardboard"></i>
                    <span class="tab-text"><?php _e('Virtual Tour', 'le-bon-resto'); ?></span>
                </button>
                <button 
                    id="mobile-tab-map" 
                    class="mobile-tab-btn active"
                    data-tab="map"
                >
                    <i class="fas fa-map-marker-alt"></i>
                    <span class="tab-text"><?php _e('Map', 'le-bon-resto'); ?></span>
                </button>
                        </div>
                </div>

        <!-- Line 2: Two Column Layout (50% each) -->
        <div class="two-column-layout flex-1 grid grid-cols-1 lg:grid-cols-2 min-h-screen">
            
            <!-- Left Column: Map + Gallery (50% width) -->
            <div class="left-column relative bg-white border-r border-gray-200 flex flex-col">
                <!-- Map Section -->
                <div id="restaurants-map" class="w-full flex-1" style="height: 60vh; min-height: 400px;">
                <!-- Map Controls -->
                <div class="button-center">
                    <button 
                        id="center-current-restaurant"
                        class="px-3 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-medium rounded text-sm transition duration-200"
                        style="background-color: #fedc00;"
                        title="<?php _e('Center on current restaurant', 'le-bon-resto'); ?>"
                    >
                        <i class="fas fa-crosshairs mr-1"></i><?php _e('Center', 'le-bon-resto'); ?>
                    </button>
                    </div>
                
                <!-- Results Counter -->
                <div class="results-counter">
                    <span id="map-results-count" class="px-3 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-medium rounded text-sm">
                        <?php _e('Loading restaurants...', 'le-bon-resto'); ?>
                    </span>
                </div>

                </div>
                
                            </div>
                            
            <!-- Right Column: All Sections Combined (50% width) -->
            <div class="right-column flex flex-col bg-white">
                
                <!-- Virtual Tour Section -->
                <div class="virtual-tour-section h-96 border-b border-gray-200 relative">
                    <?php if ($virtual_tour_url): ?>
                        <div class="h-full relative" style="width: -webkit-fill-available;">
                            <iframe 
                                src="<?php echo esc_url($virtual_tour_url); ?>"
                                class="w-full h-full border-none"
                                allowfullscreen
                                loading="lazy"
                            ></iframe>
                        </div>
                    <?php else: ?>
                        <div class="h-full flex items-center justify-center bg-gray-100">
                            <div class="text-center p-8">
                                <i class="fas fa-vr-cardboard text-5xl text-gray-400 mb-4"></i>
                                <h3 class="text-xl font-semibold text-gray-600 mb-2"><?php _e('Virtual Tour', 'le-bon-resto'); ?></h3>
                                <p class="text-gray-500 mb-4"><?php _e('No virtual tour available for this restaurant', 'le-bon-resto'); ?></p>
                            </div>
            </div>
                    <?php endif; ?>
                </div>
                

                </div>
                
        <!-- Current Restaurant Info (Hidden, used by JS) -->
        <script type="application/json" id="current-restaurant-data">
        {
            "id": <?php echo intval($current_restaurant_id); ?>,
            "title": <?php echo wp_json_encode(get_the_title()); ?>,
            "address": <?php echo wp_json_encode($address); ?>,
            "city": <?php echo wp_json_encode($city); ?>,
            "cuisine_type": <?php echo wp_json_encode($cuisine_type); ?>,
            "description": <?php echo wp_json_encode($description); ?>,
            "phone": <?php echo wp_json_encode($phone); ?>,
            "latitude": <?php echo wp_json_encode($latitude); ?>,
            "longitude": <?php echo wp_json_encode($longitude); ?>,
            "is_featured": <?php echo wp_json_encode($is_featured === '1'); ?>,
            "virtual_tour_url": <?php echo wp_json_encode($virtual_tour_url); ?>,
            "link": <?php echo wp_json_encode(get_permalink()); ?>,
            "gallery_images": <?php echo wp_json_encode($gallery_images); ?>
        }
        </script>
        


    <?php endwhile; ?>
</div>
<div>
                                 <!-- Filter Section -->
                <div class="filter-section p-4 bg-gradient-to-r border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <select 
                                id="sort-restaurants"
                                class="px-2 py-1 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-1 focus:ring-yellow-400 bg-white shadow-sm" style="width: 100%;"
                            >
                                <option value="featured"><?php _e('Featured First', 'le-bon-resto'); ?></option>
                                <option value="newest"><?php _e('Newest', 'le-bon-resto'); ?></option>
                                <option value="distance"><?php _e('Distance', 'le-bon-resto'); ?></option>
                                <option value="name"><?php _e('Name A-Z', 'le-bon-resto'); ?></option>
                            </select>
                        </div>
                        </div>
</div>

                <!-- Restaurant Cards Container -->
                <div id="restaurants-container" class="flex-1 p-4 overflow-y-auto align-items-center mobile-cards-section">
                        <!-- Restaurant cards will be loaded here via JavaScript -->
                        <div class="text-center py-8">
                            <div class="loading-spinner mx-auto mb-3"></div>
                            <p class="text-gray-500"><?php _e('Loading restaurants...', 'le-bon-resto'); ?></p>
                        </div>
                    </div>
                
                <!-- Pagination -->
                <div id="pagination-container" class="p-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <span id="pagination-info"><?php _e('Loading...', 'le-bon-resto'); ?></span>
                        </div>
                        <div id="pagination-controls" class="flex items-center space-x-2">
                            <!-- Pagination buttons will be generated here by JavaScript -->
                        </div>
                    </div>
                </div>   
                    </div>




                
<script>

// Mobile filter functionality
function initializeMobileFilters() {
    console.log('🔧 [MOBILE DEBUG] Initializing mobile filters...');
    console.log('🔧 [MOBILE DEBUG] Screen info:');
    console.log('  - window.innerWidth:', window.innerWidth);
    console.log('  - window.innerHeight:', window.innerHeight);
    console.log('  - screen.width:', screen.width);
    console.log('  - screen.height:', screen.height);
    console.log('  - userAgent:', navigator.userAgent);
    console.log('  - is mobile check (768px):', window.innerWidth <= 768);
    
    const mobileFilterBtn = document.getElementById('mobile-filter-btn');
    const mobileFilterOverlay = document.getElementById('mobile-filter-overlay');
    const closeMobileFilters = document.getElementById('close-mobile-filters');
    const mobileFilterPanel = document.querySelector('.mobile-filter-panel');
    
    // Debug element detection
    console.log('🔧 [MOBILE DEBUG] Elements found:');
    console.log('  - mobileFilterBtn:', mobileFilterBtn);
    console.log('  - mobileFilterOverlay:', mobileFilterOverlay);
    console.log('  - closeMobileFilters:', closeMobileFilters);
    console.log('  - mobileFilterPanel:', mobileFilterPanel);
    
    // Check if custom filter icon is working, show fallback if not
    setTimeout(() => {
        console.log('🔧 [MOBILE DEBUG] Checking filter icon...');
        const filterIcon = document.querySelector('.filter-icon');
        const filterText = document.querySelector('.filter-text');
        
        console.log('  - filterIcon:', filterIcon);
        console.log('  - filterText:', filterText);
        
        if (filterIcon && filterText) {
            const computedStyle = window.getComputedStyle(filterIcon);
            console.log('  - filterIcon computed style:', computedStyle.display);
            console.log('  - filterIcon offsetHeight:', filterIcon.offsetHeight);
            
            if (computedStyle.display === 'none' || filterIcon.offsetHeight === 0) {
                console.log('🔧 [MOBILE DEBUG] Showing fallback text icon');
                filterIcon.style.display = 'none';
                filterText.style.display = 'block';
                filterText.style.fontSize = '18px';
                filterText.style.fontWeight = 'bold';
            } else {
                console.log('🔧 [MOBILE DEBUG] Custom filter icon is working');
            }
        } else {
            console.log('🔧 [MOBILE DEBUG] Filter icon elements not found');
        }
    }, 100);
    
    // Toggle mobile filter panel
    if (mobileFilterBtn && mobileFilterOverlay) {
        console.log('🔧 [MOBILE DEBUG] Adding click listener to filter button');
        mobileFilterBtn.addEventListener('click', function() {
            console.log('🔧 [MOBILE DEBUG] Filter button clicked!');
            console.log('  - mobileFilterOverlay classes before:', mobileFilterOverlay.className);
            console.log('  - mobileFilterPanel classes before:', mobileFilterPanel.className);
            
            // Toggle filter state
            const isOpen = mobileFilterOverlay.classList.contains('hidden');
            
            if (isOpen) {
                // Open filter
                mobileFilterOverlay.classList.remove('hidden');
                mobileFilterPanel.classList.remove('-translate-x-full');
                document.body.style.overflow = 'hidden';
                
                // Move icon to top-right
                mobileFilterBtn.parentElement.classList.add('filter-open');
                mobileFilterBtn.title = 'Close Filters';
            } else {
                // Close filter
                closeMobileFilterPanel();
            }
            
            console.log('  - mobileFilterOverlay classes after:', mobileFilterOverlay.className);
            console.log('  - mobileFilterPanel classes after:', mobileFilterPanel.className);
            console.log('  - body overflow:', document.body.style.overflow);
        });
    } else {
        console.log('🔧 [MOBILE DEBUG] Filter button or overlay not found!');
    }
    
    // Close mobile filter panel
    function closeMobileFilterPanel() {
        console.log('🔧 [MOBILE DEBUG] Closing mobile filter panel');
        console.log('  - mobileFilterPanel classes before:', mobileFilterPanel.className);
        
        mobileFilterPanel.classList.add('-translate-x-full');
        setTimeout(() => {
            mobileFilterOverlay.classList.add('hidden');
            document.body.style.overflow = '';
            
            // Reset icon to original position
            mobileFilterBtn.parentElement.classList.remove('filter-open');
            mobileFilterBtn.title = 'Open Filters';
            
            console.log('🔧 [MOBILE DEBUG] Panel closed after timeout');
            console.log('  - mobileFilterOverlay classes after:', mobileFilterOverlay.className);
            console.log('  - body overflow:', document.body.style.overflow);
        }, 300);
    }
    
    // Close on overlay click
    if (mobileFilterOverlay) {
        console.log('🔧 [MOBILE DEBUG] Adding click listener to overlay');
        mobileFilterOverlay.addEventListener('click', function(e) {
            console.log('🔧 [MOBILE DEBUG] Overlay clicked!');
            console.log('  - e.target:', e.target);
            console.log('  - mobileFilterOverlay:', mobileFilterOverlay);
            console.log('  - e.target === mobileFilterOverlay:', e.target === mobileFilterOverlay);
            
            if (e.target === mobileFilterOverlay) {
                console.log('🔧 [MOBILE DEBUG] Closing via overlay click');
                closeMobileFilterPanel();
            }
        });
    } else {
        console.log('🔧 [MOBILE DEBUG] Overlay not found!');
    }
    
    // Sync mobile filters with desktop filters
    console.log('🔧 [MOBILE DEBUG] Syncing mobile filters with desktop');
    syncMobileFilters();
    
    // Mobile filter event listeners
    console.log('🔧 [MOBILE DEBUG] Setting up mobile filter event listeners');
    setupMobileFilterListeners();
    
    console.log('🔧 [MOBILE DEBUG] Mobile filter initialization complete!');
}

// Sync mobile filters with desktop filters
function syncMobileFilters() {
    console.log('🔧 [MOBILE DEBUG] Starting filter sync...');
    
    // Sync from desktop to mobile
    const desktopFilters = {
        name: 'restaurant-name-filter',
        city: 'city-filter',
        cuisine: 'cuisine-filter',
        distance: 'distance-filter',
        featured: 'featured-only'
    };
    
    const mobileFilters = {
        name: 'mobile-restaurant-name-filter',
        city: 'mobile-city-filter',
        cuisine: 'mobile-cuisine-filter',
        distance: 'mobile-distance-filter',
        featured: 'mobile-featured-only'
    };
    
    console.log('🔧 [MOBILE DEBUG] Desktop filters:', desktopFilters);
    console.log('🔧 [MOBILE DEBUG] Mobile filters:', mobileFilters);
    
    Object.keys(desktopFilters).forEach(key => {
        const desktopEl = document.getElementById(desktopFilters[key]);
        const mobileEl = document.getElementById(mobileFilters[key]);
        
        console.log(`🔧 [MOBILE DEBUG] Syncing ${key}:`);
        console.log(`  - desktopEl (${desktopFilters[key]}):`, desktopEl);
        console.log(`  - mobileEl (${mobileFilters[key]}):`, mobileEl);
        
        if (desktopEl && mobileEl) {
            console.log(`  - Setting up sync for ${key}`);
            
            // Sync desktop to mobile
            desktopEl.addEventListener('input', function() {
                console.log(`🔧 [MOBILE DEBUG] Desktop ${key} changed:`, this.value || this.checked);
                if (mobileEl.type === 'checkbox') {
                    mobileEl.checked = this.checked;
                } else {
                    mobileEl.value = this.value;
                }
            });
            
            // Sync mobile to desktop
            mobileEl.addEventListener('input', function() {
                console.log(`🔧 [MOBILE DEBUG] Mobile ${key} changed:`, this.value || this.checked);
                if (desktopEl.type === 'checkbox') {
                    desktopEl.checked = this.checked;
        } else {
                    desktopEl.value = this.value;
                }
            });
        } else {
            console.log(`  - Missing elements for ${key}`);
        }
    });
    
    console.log('🔧 [MOBILE DEBUG] Filter sync complete!');
}

// Setup mobile filter event listeners
function setupMobileFilterListeners() {
    console.log('🔧 [MOBILE DEBUG] Setting up mobile filter event listeners...');
    
    // Mobile search button
    const mobileSearchBtn = document.getElementById('mobile-search-restaurants');
    console.log('🔧 [MOBILE DEBUG] Mobile search button:', mobileSearchBtn);
    
    if (mobileSearchBtn) {
        console.log('🔧 [MOBILE DEBUG] Adding click listener to mobile search button');
        mobileSearchBtn.addEventListener('click', function() {
            console.log('🔧 [MOBILE DEBUG] Mobile search button clicked!');
            
            // Trigger desktop search
            const desktopSearchBtn = document.getElementById('search-restaurants');
            console.log('🔧 [MOBILE DEBUG] Desktop search button:', desktopSearchBtn);
            
            if (desktopSearchBtn) {
                console.log('🔧 [MOBILE DEBUG] Triggering desktop search...');
                desktopSearchBtn.click();
            } else {
                console.log('🔧 [MOBILE DEBUG] Desktop search button not found!');
            }
            
            // Close mobile panel
            const mobileFilterPanel = document.querySelector('.mobile-filter-panel');
            const mobileFilterOverlay = document.getElementById('mobile-filter-overlay');
            console.log('🔧 [MOBILE DEBUG] Closing mobile panel...');
            console.log('  - mobileFilterPanel:', mobileFilterPanel);
            console.log('  - mobileFilterOverlay:', mobileFilterOverlay);
            
            if (mobileFilterPanel && mobileFilterOverlay) {
                mobileFilterPanel.classList.add('-translate-x-full');
                setTimeout(() => {
                    mobileFilterOverlay.classList.add('hidden');
                    document.body.style.overflow = '';
                    console.log('🔧 [MOBILE DEBUG] Mobile panel closed after search');
                }, 300);
            } else {
                console.log('🔧 [MOBILE DEBUG] Mobile panel elements not found for closing!');
            }
        });
    } else {
        console.log('🔧 [MOBILE DEBUG] Mobile search button not found!');
    }
    
    // Mobile clear button
    const mobileClearBtn = document.getElementById('mobile-clear-filters');
    console.log('🔧 [MOBILE DEBUG] Mobile clear button:', mobileClearBtn);
    
    if (mobileClearBtn) {
        console.log('🔧 [MOBILE DEBUG] Adding click listener to mobile clear button');
        mobileClearBtn.addEventListener('click', function() {
            console.log('🔧 [MOBILE DEBUG] Mobile clear button clicked!');
            
            // Clear mobile filters
            const mobileFilters = [
                'mobile-restaurant-name-filter',
                'mobile-city-filter',
                'mobile-cuisine-filter',
                'mobile-distance-filter',
                'mobile-featured-only'
            ];
            
            mobileFilters.forEach(filterId => {
                const element = document.getElementById(filterId);
                if (element) {
                    if (element.type === 'checkbox') {
                        element.checked = false;
                        console.log(`🔧 [MOBILE DEBUG] Cleared checkbox ${filterId}`);
        } else {
                        element.value = '';
                        console.log(`🔧 [MOBILE DEBUG] Cleared input ${filterId}`);
                    }
                } else {
                    console.log(`🔧 [MOBILE DEBUG] Filter element ${filterId} not found!`);
                }
            });
            
            // Trigger desktop clear
            const desktopClearBtn = document.getElementById('clear-filters');
            console.log('🔧 [MOBILE DEBUG] Desktop clear button:', desktopClearBtn);
            
            if (desktopClearBtn) {
                console.log('🔧 [MOBILE DEBUG] Triggering desktop clear...');
                desktopClearBtn.click();
            } else {
                console.log('🔧 [MOBILE DEBUG] Desktop clear button not found!');
            }
        });
    } else {
        console.log('🔧 [MOBILE DEBUG] Mobile clear button not found!');
    }
    
    console.log('🔧 [MOBILE DEBUG] Mobile filter event listeners setup complete!');
}

// Handle tab click
function handleTabClick(e) {
    e.preventDefault();
    e.stopPropagation();
    
    console.log('🔧 [MOBILE TABS] Tab clicked!', this.id);
    console.log('🔧 [MOBILE TABS] Event type:', e.type);
    console.log('🔧 [MOBILE TABS] Current target:', e.currentTarget);
    
    // Only work on mobile devices
    if (window.innerWidth > 1023) {
        console.log('🔧 [MOBILE TABS] Desktop detected - tab switching disabled');
        return;
    }
    
    // Add loading state
    this.classList.add('loading');
    
    // Remove loading state after animation
    setTimeout(() => {
        this.classList.remove('loading');
    }, 800);
    
    const tabType = this.getAttribute('data-tab');
    const vrContent = document.querySelector('.virtual-tour-section');
    const mapContent = document.querySelector('#restaurants-map');
    const leftColumn = document.querySelector('.left-column');
    const rightColumn = document.querySelector('.right-column');
    const tabButtons = document.querySelectorAll('.mobile-tab-btn');
    
    console.log('🔧 [MOBILE TABS] Tab type:', tabType);
    console.log('🔧 [MOBILE TABS] VR content:', vrContent);
    console.log('🔧 [MOBILE TABS] Map content:', mapContent);
    console.log('🔧 [MOBILE TABS] Left column:', leftColumn);
    console.log('🔧 [MOBILE TABS] Right column:', rightColumn);
    
    // Update button states
    tabButtons.forEach(btn => {
        btn.classList.remove('active', 'border-yellow-400', 'bg-yellow-50');
        btn.classList.add('border-transparent');
    });
    
    this.classList.add('active', 'border-yellow-400', 'bg-yellow-50');
    this.classList.remove('border-transparent');
    
    // Show/hide entire columns based on tab (mobile only)
    if (tabType === 'vr') {
        console.log('🔧 [MOBILE TABS] Showing VR - hiding left column, showing right column');
        
        // Hide left column (map)
        if (leftColumn) {
            leftColumn.style.setProperty('display', 'none', 'important');
            console.log('🔧 [MOBILE TABS] Left column hidden');
        } else {
            console.log('🔧 [MOBILE TABS] Left column not found!');
        }
        
        // Show right column (VR)
        if (rightColumn) {
            rightColumn.style.setProperty('display', 'flex', 'important');
            rightColumn.style.setProperty('visibility', 'visible', 'important');
            rightColumn.style.setProperty('opacity', '1', 'important');
            rightColumn.style.setProperty('width', '100%', 'important');
            rightColumn.style.setProperty('flex', '1', 'important');
            console.log('🔧 [MOBILE TABS] Right column shown');
        } else {
            console.log('🔧 [MOBILE TABS] Right column not found!');
        }
        
        // Also show the VR content specifically
        if (vrContent) {
            vrContent.style.setProperty('display', 'block', 'important');
            vrContent.style.setProperty('visibility', 'visible', 'important');
            vrContent.style.setProperty('opacity', '1', 'important');
            vrContent.style.setProperty('position', 'relative', 'important');
            vrContent.style.setProperty('z-index', '10', 'important');
            console.log('🔧 [MOBILE TABS] VR content shown');
        } else {
            console.log('🔧 [MOBILE TABS] VR content not found!');
        }
        
    } else if (tabType === 'map') {
        console.log('🔧 [MOBILE TABS] Showing Map - showing left column, hiding right column');
        
        // Show left column (map)
        if (leftColumn) {
            leftColumn.style.setProperty('display', 'flex', 'important');
            leftColumn.style.setProperty('visibility', 'visible', 'important');
            leftColumn.style.setProperty('opacity', '1', 'important');
            leftColumn.style.setProperty('width', '100%', 'important');
            leftColumn.style.setProperty('flex', '1', 'important');
            console.log('🔧 [MOBILE TABS] Left column shown');
        } else {
            console.log('🔧 [MOBILE TABS] Left column not found!');
        }
        
        // Hide right column (VR)
        if (rightColumn) {
            rightColumn.style.setProperty('display', 'none', 'important');
            console.log('🔧 [MOBILE TABS] Right column hidden');
        } else {
            console.log('🔧 [MOBILE TABS] Right column not found!');
        }
        
        // Also hide the VR content specifically
        if (vrContent) {
            vrContent.style.setProperty('display', 'none', 'important');
            vrContent.style.setProperty('visibility', 'hidden', 'important');
            vrContent.style.setProperty('opacity', '0', 'important');
            console.log('🔧 [MOBILE TABS] VR content hidden');
        } else {
            console.log('🔧 [MOBILE TABS] VR content not found!');
        }
    }
}

// Initialize mobile tab system
function initializeMobileTabs() {
    console.log('🔧 [MOBILE TABS] Initializing mobile tab system...');
    
    // Wait a bit for DOM to be ready
    setTimeout(() => {
        const tabButtons = document.querySelectorAll('.mobile-tab-btn');
        const vrContent = document.querySelector('.virtual-tour-section');
        const mapContent = document.querySelector('#restaurants-map');
        const leftColumn = document.querySelector('.left-column');
        const rightColumn = document.querySelector('.right-column');
        
        console.log('🔧 [MOBILE TABS] Elements found:');
        console.log('  - tabButtons:', tabButtons.length);
        console.log('  - vrContent:', vrContent);
        console.log('  - mapContent:', mapContent);
        console.log('  - leftColumn:', leftColumn);
        console.log('  - rightColumn:', rightColumn);
        
        if (tabButtons.length === 0) {
            console.error('🔧 [MOBILE TABS] No tab buttons found!');
            return;
        }
    
        // Set default view to Map on mobile only
        if (window.innerWidth <= 1023) {
            console.log('🔧 [MOBILE TABS] Setting default Map view on mobile');
            
            // Show left column (map), hide right column (VR) on mobile
            if (leftColumn) {
                leftColumn.style.setProperty('display', 'flex', 'important');
                leftColumn.style.setProperty('visibility', 'visible', 'important');
                leftColumn.style.setProperty('opacity', '1', 'important');
                leftColumn.style.setProperty('width', '100%', 'important');
                leftColumn.style.setProperty('flex', '1', 'important');
            }
            if (rightColumn) {
                rightColumn.style.setProperty('display', 'none', 'important');
            }
            if (vrContent) {
                vrContent.style.setProperty('display', 'none', 'important');
                vrContent.style.setProperty('visibility', 'hidden', 'important');
                vrContent.style.setProperty('opacity', '0', 'important');
            }
        } else {
            // On desktop, ensure both columns are visible
            console.log('🔧 [MOBILE TABS] Desktop detected - showing both columns');
            if (leftColumn) {
                leftColumn.style.setProperty('display', 'flex', 'important');
                leftColumn.style.setProperty('visibility', 'visible', 'important');
                leftColumn.style.setProperty('opacity', '1', 'important');
                leftColumn.style.setProperty('width', '100%', 'important');
                leftColumn.style.setProperty('flex', '1', 'important');
            }
            if (rightColumn) {
                rightColumn.style.setProperty('display', 'flex', 'important');
                rightColumn.style.setProperty('visibility', 'visible', 'important');
                rightColumn.style.setProperty('opacity', '1', 'important');
                rightColumn.style.setProperty('width', '100%', 'important');
                rightColumn.style.setProperty('flex', '1', 'important');
            }
            if (vrContent) {
                vrContent.style.setProperty('display', 'block', 'important');
                vrContent.style.setProperty('visibility', 'visible', 'important');
                vrContent.style.setProperty('opacity', '1', 'important');
            }
        }
            
            // Update tab button states - Map is default active
            const vrTab = document.getElementById('mobile-tab-vr');
            const mapTab = document.getElementById('mobile-tab-map');
            
            if (mapTab) {
                mapTab.classList.add('active');
                mapTab.classList.add('border-yellow-400', 'bg-yellow-50');
                mapTab.classList.remove('border-transparent');
            }
            
            if (vrTab) {
                vrTab.classList.remove('active');
                vrTab.classList.remove('border-yellow-400', 'bg-yellow-50');
                vrTab.classList.add('border-transparent');
            }
        
        // Add click event listeners to tab buttons
        console.log('🔧 [MOBILE TABS] Found', tabButtons.length, 'tab buttons');
        
        // Test direct button access
        const vrButton = document.getElementById('mobile-tab-vr');
        const mapButton = document.getElementById('mobile-tab-map');
        console.log('🔧 [MOBILE TABS] Direct button access:');
        console.log('  - VR button:', vrButton);
        console.log('  - Map button:', mapButton);
        
        tabButtons.forEach((button, index) => {
            console.log(`🔧 [MOBILE TABS] Button ${index}:`, button.id, button);
            
            // Test if button is clickable
            button.style.pointerEvents = 'auto';
            button.style.cursor = 'pointer';
            button.style.zIndex = '10000';
            button.style.position = 'relative';
            
            // Add multiple event listeners to ensure it works
            button.addEventListener('click', handleTabClick);
            button.addEventListener('touchstart', handleTabClick);
            button.addEventListener('touchend', handleTabClick);
        });
        
        // Also add direct event listeners as backup
        if (vrButton) {
            console.log('🔧 [MOBILE TABS] Adding direct event listener to VR button');
            vrButton.addEventListener('click', function(e) {
                console.log('🔧 [MOBILE TABS] Direct VR button clicked!');
                e.preventDefault();
                e.stopPropagation();
                handleTabClick.call(this, e);
            });
        }
        
        if (mapButton) {
            console.log('🔧 [MOBILE TABS] Adding direct event listener to Map button');
            mapButton.addEventListener('click', function(e) {
                console.log('🔧 [MOBILE TABS] Direct Map button clicked!');
                e.preventDefault();
                e.stopPropagation();
                handleTabClick.call(this, e);
            });
        }
        
        // Test if buttons are clickable by adding a simple test
        console.log('🔧 [MOBILE TABS] Testing button clickability...');
        if (vrButton) {
            vrButton.onclick = function() {
                console.log('🔧 [MOBILE TABS] VR button onclick triggered!');
            };
        }
        if (mapButton) {
            mapButton.onclick = function() {
                console.log('🔧 [MOBILE TABS] Map button onclick triggered!');
            };
        }
        
        console.log('🔧 [MOBILE TABS] Mobile tab system initialized!');
        
        // Test if buttons are clickable
        setTimeout(() => {
            console.log('🔧 [MOBILE TABS] Testing button clickability...');
            tabButtons.forEach((button, index) => {
                console.log(`🔧 [MOBILE TABS] Button ${index} (${button.id}):`, {
                    element: button,
                    classes: button.className,
                    style: button.style.cssText,
                    computedStyle: window.getComputedStyle(button)
                });
            });
        }, 1000);
    }, 500); // Wait 500ms for DOM to be ready
}

// Initialize location detection for distance filtering
document.addEventListener('DOMContentLoaded', function() {
    const distanceFilter = document.getElementById('distance-filter');
    
    // Initialize mobile tab system with a small delay to ensure DOM is ready
    setTimeout(() => {
        initializeMobileTabs();
    }, 100);
    
    // Apply essential styles
    const rightColumn = document.querySelector('.right-column');
    const restaurantsContainer = document.querySelector('#restaurants-container');
    
    if (rightColumn) {
        // Check if mobile (screen width <= 1023px)
        if (window.innerWidth <= 1023) {
            // Mobile: Force disable all scrolling
            console.log('🔧 [MOBILE DEBUG] Applying mobile scroll disable...');
            rightColumn.style.overflowY = 'hidden';
            rightColumn.style.overflowX = 'hidden';
            rightColumn.style.maxHeight = 'none';
            rightColumn.style.height = 'auto';
            rightColumn.style.minHeight = 'auto';
            
            // Disable scroll in restaurants container on mobile
            if (restaurantsContainer) {
                console.log('🔧 [MOBILE DEBUG] Disabling restaurants container scroll...');
                restaurantsContainer.style.overflowY = 'visible';
                restaurantsContainer.style.overflowX = 'visible';
                restaurantsContainer.style.maxHeight = 'none';
                restaurantsContainer.style.height = 'auto';
                restaurantsContainer.style.minHeight = 'auto';
            }
            
            // Force all child elements to be visible
            const allChildren = rightColumn.querySelectorAll('*');
            allChildren.forEach(child => {
                child.style.overflow = 'visible';
                child.style.maxHeight = 'none';
            });
            
            console.log('🔧 [MOBILE DEBUG] Mobile scroll disable applied!');
        } else {
            // Enable scroll on desktop
        rightColumn.style.overflowY = 'auto';
        rightColumn.style.maxHeight = '100vh';
        rightColumn.style.scrollbarWidth = 'thin';
        rightColumn.style.scrollbarColor = '#fedc00 #f3f4f6';
            
            // Enable scroll in restaurants container on desktop
            if (restaurantsContainer) {
                restaurantsContainer.style.overflowY = 'auto';
                restaurantsContainer.style.maxHeight = '100%';
            }
        }
    }
    
    // Initialize mobile filter functionality
    console.log('🔧 [MOBILE DEBUG] Calling initializeMobileFilters...');
    initializeMobileFilters();
    
    // Handle window resize to update scroll behavior and tab visibility
    window.addEventListener('resize', function() {
        const rightColumn = document.querySelector('.right-column');
        const restaurantsContainer = document.querySelector('#restaurants-container');
        const vrContent = document.querySelector('.virtual-tour-section');
        const mapContent = document.querySelector('#restaurants-map');
        
        if (rightColumn) {
            if (window.innerWidth <= 1023) {
                // Mobile: Let CSS handle the styling, just ensure no conflicting styles
                rightColumn.style.overflowY = 'hidden';
                rightColumn.style.overflowX = 'hidden';
                rightColumn.style.maxHeight = 'none';
                
                // Disable scroll in restaurants container on mobile
                if (restaurantsContainer) {
                    restaurantsContainer.style.overflowY = 'visible';
                    restaurantsContainer.style.overflowX = 'visible';
                    restaurantsContainer.style.maxHeight = 'none';
                }
                
                // Ensure mobile tab behavior - Map is default
                if (leftColumn) {
                    leftColumn.style.setProperty('display', 'flex', 'important');
                    leftColumn.style.setProperty('width', '100%', 'important');
                }
                if (rightColumn) {
                    rightColumn.style.setProperty('display', 'none', 'important');
                }
            } else {
                // Enable scroll on desktop
                rightColumn.style.overflowY = 'auto';
                rightColumn.style.maxHeight = '100vh';
                rightColumn.style.scrollbarWidth = 'thin';
                rightColumn.style.scrollbarColor = '#fedc00 #f3f4f6';
                
                // Enable scroll in restaurants container on desktop
                if (restaurantsContainer) {
                    restaurantsContainer.style.overflowY = 'auto';
                    restaurantsContainer.style.maxHeight = '100%';
                }
                
                // Ensure both columns are visible on desktop
                if (leftColumn) {
                    leftColumn.style.setProperty('display', 'flex', 'important');
                    leftColumn.style.setProperty('width', '50%', 'important');
                }
                if (rightColumn) {
                    rightColumn.style.setProperty('display', 'flex', 'important');
                    rightColumn.style.setProperty('width', '50%', 'important');
                }
            }
        }
    });
    
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                window.userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                distanceFilter.disabled = false;

            },
            function(error) {
                distanceFilter.disabled = true;
            }
        );
    }
});

// Also initialize on window load as backup
window.addEventListener('load', function() {
    console.log('🔧 [MOBILE TABS] Window loaded, re-initializing mobile tabs...');
    setTimeout(() => {
        initializeMobileTabs();
    }, 200);
});

// Localize script data
<?php
wp_localize_script(
    'lebonresto-single-map',
    'lebonrestoSingle',
    array(
        'apiUrl' => home_url('/wp-json/lebonresto/v1/restaurants'),
        'cuisineTypesUrl' => home_url('/wp-json/lebonresto/v1/cuisine-types'),
        'homeUrl' => home_url('/'),
        'nonce' => wp_create_nonce('wp_rest'),
        'currentRestaurantId' => $current_restaurant_id,
        'mapCenter' => array(
            'lat' => !empty($latitude) ? floatval($latitude) : 48.8566,
            'lng' => !empty($longitude) ? floatval($longitude) : 2.3522
        ),
        'strings' => array(
            'featuredBadge' => __('Featured', 'le-bon-resto'),
            'viewDetails' => __('View Details', 'le-bon-resto'),
            'noRestaurants' => __('No restaurants found', 'le-bon-resto'),
            'loadingError' => __('Error loading restaurants', 'le-bon-resto'),
            'phoneTitle' => __('Call restaurant', 'le-bon-resto'),
            'kmAway' => __('%s km away', 'le-bon-resto'),
            'loadingRestaurants' => __('Loading restaurants...', 'le-bon-resto'),
            'restaurantsFound' => __('%s restaurants found', 'le-bon-resto'),
            'centerOnCurrent' => __('Center on current restaurant', 'le-bon-resto'),
        )
    )
);
?>
</script>

<style>
/* Main Layout Styles */
.lebonresto-single-layout {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif !important;
    background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%) !important;
    min-height: 100vh !important;
}

.two-column-layout {
    display: grid !important;
    grid-template-columns: 4fr 5fr !important;
    gap: 0 !important;
}

@media (max-width: 1023px) {
    .two-column-layout {
        grid-template-columns: 1fr !important;
    }
}

/* Map Container */
#restaurants-map {
    width: 100% !important;
    height: 65vh !important;
    background-color: rgb(255, 255, 255) !important;
    border-right: 3px solid #e5e7eb !important;
    position: relative !important;
    z-index: 1 !important;
}

@media (max-width: 1023px) {
    #restaurants-map {
        border-right: none !important;
        border-bottom: 3px solid rgb(255, 255, 255) !important;
    }
}

/* Virtual Tour Section */
.virtual-tour-section {
    height: 64vh !important;
    border-bottom: 3px solid rgb(255, 255, 255) !important;
    background: linear-gradient(135deg, rgb(255, 255, 255) 0%, rgb(255, 255, 255) 100%) !important;
    position: relative !important;
    overflow: hidden !important;
}

@media (max-width: 1023px) {
    .virtual-tour-section {
        height: 40vh !important;
        min-height: 40vh !important;
    }
}

.virtual-tour-section iframe {
    width: 100% !important;
    height: 100% !important;
    border: none !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1) !important;
}

/* Filter Form */
.filter-form {
    background: linear-gradient(135deg, #ffffff 0%, rgb(255, 255, 255) 100%) !important;
    border-radius: 16px !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
    padding: 20px !important;
    border: 2px solid rgba(251, 191, 36, 0.1) !important;
}

.filter-form input,
.filter-form select {
    height: 48px !important;
    border: 2px solid rgb(255, 255, 255) !important;
    border-radius: 12px !important;
    font-size: 16px !important;
    padding: 12px 16px !important;
    transition: all 0.3s ease !important;
    background-color: #ffffff !important;
    color: #1f2937 !important;
}

.filter-form input:focus,
.filter-form select:focus {
    border-color: #fedc00 !important;
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1) !important;
    outline: none !important;
    transform: translateY(-1px) !important;
}

.filter-form button {
    height: 48px !important;
    border-radius: 12px !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    padding: 14px 24px !important;
    transition: all 0.3s ease !important;
    border: none !important;
    cursor: pointer !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
}

#search-restaurants {
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
    color: #1f2937 !important;
    font-weight: 700 !important;
}

#search-restaurants:hover {
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(251, 191, 36, 0.4) !important;
}

#clear-filters {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
    color: #374151 !important;
    border: 2px solid rgb(255, 255, 255) !important;
}

#clear-filters:hover {
    background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

/* Restaurant Cards */
.restaurant-card {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    margin-bottom: 16px !important;
    border: 2px solid rgb(255, 255, 255) !important;
    transition: all 0.4s ease !important;
    cursor: pointer !important;
    position: relative !important;
    overflow: hidden !important;
    width: 90% !important;
}

.restaurant-card:hover {
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-4px) scale(1.02) !important;
    border-color: #fedc00 !important;
}


/* Loading Spinner */
.loading-spinner {
    border: 4px solid #f3f4f6 !important;
    border-top: 4px solid #fedc00 !important;
    border-right: 4px solid #fedc00 !important;
    border-radius: 50% !important;
    width: 32px !important;
    height: 32px !important;
    animation: spin 1.2s ease-in-out infinite !important;
    display: inline-block !important;
    box-shadow: 0 2px 8px rgba(251, 191, 36, 0.3) !important;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Map Controls */
#center-current-restaurant {
    padding: 12px 18px !important;
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
    color: #1f2937 !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 8px rgba(251, 191, 36, 0.3) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 44px !important;
}

#center-current-restaurant:hover {
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 16px rgba(251, 191, 36, 0.4) !important;
}

#map-results-count {
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px) !important;
    padding: 8px 16px !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #374151 !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    border: 2px solid rgba(251, 191, 36, 0.2) !important;
}

/* Custom scrollbar */
#restaurants-list::-webkit-scrollbar {
    width: 6px;
}

#restaurants-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#restaurants-list::-webkit-scrollbar-thumb {
    background: #fedc00;
    border-radius: 3px;
}

#restaurants-list::-webkit-scrollbar-thumb:hover {
    background: #e6ac00;
}

.current-restaurant-marker {
    z-index: 1000 !important;
}




/* FontAwesome CDN */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

/* Fallback for FontAwesome icons */
.fas, .fab {
    font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands", "FontAwesome", sans-serif !important;
    font-weight: 900 !important;
    font-style: normal !important;
    font-variant: normal !important;
    text-rendering: auto !important;
    line-height: 1 !important;
    -webkit-font-smoothing: antialiased !important;
    -moz-osx-font-smoothing: grayscale !important;
}

.fab {
    font-family: "Font Awesome 6 Brands", "FontAwesome", sans-serif !important;
    font-weight: 400 !important;
}

/* Map marker custom styles */
.current-restaurant-marker,
.featured-restaurant-marker,
.regular-restaurant-marker {
    border: none !important;
    background: transparent !important;
}

.current-restaurant-marker div {
    animation: current-pulse 2s infinite;
}

@keyframes current-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Leaflet popup enhancements */
.restaurant-popup-content {
    font-size: 14px;
    line-height: 1.4;
    min-width: 200px;
}

.current-popup .leaflet-popup-content-wrapper {
    border: 2px solid #fedc00;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
}

.leaflet-popup-content-wrapper {
    border-radius: 8px;
}

/* Button hover effects */
.bg-yellow-400:hover {
    background-color: #fedc00 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}


/* Focus states for accessibility */
input:focus,
select:focus,
button:focus {
    outline: 0px solid #fedc00 !important;
    outline-offset: 0px;
}



/* Mobile Tab Navigation Styles */
.mobile-tab-navigation {
    position: fixed !important;
    bottom: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 9999 !important;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
    border-top: 3px solid #e5e7eb !important;
    box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.12) !important;
    backdrop-filter: blur(20px) !important;
    pointer-events: auto !important;
    animation: slideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

/* Slide up animation for tab bar */
@keyframes slideUp {
    from {
        transform: translateY(100%) !important;
        opacity: 0 !important;
    }
    to {
        transform: translateY(0) !important;
        opacity: 1 !important;
    }
}

.mobile-tab-navigation .flex {
    display: flex !important;
    width: 100% !important;
    height: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

.mobile-tab-btn {
    position: relative !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    border: none !important;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
    cursor: pointer !important;
    color: #6b7280 !important;
    font-weight: 600 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 10px !important;
    pointer-events: auto !important;
    z-index: 101 !important;
    user-select: none !important;
    -webkit-tap-highlight-color: transparent !important;
    flex: 1 !important;
    width: 50% !important;
    min-width: 0 !important;
    height: 100% !important;
    padding: 18px 16px !important;
    margin: 0 !important;
    text-align: center !important;
    font-size: 14px !important;
    line-height: 1.4 !important;
    border-radius: 0 !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
    overflow: hidden !important;
    animation: buttonPulse 3s ease-in-out infinite, colorShift 4s ease-in-out infinite !important;
}

/* Color shifting animation */
@keyframes colorShift {
    0%, 100% {
        color: #6b7280 !important;
    }
    25% {
        color: #3b82f6 !important;
    }
    50% {
        color: #8b5cf6 !important;
    }
    75% {
        color: #06b6d4 !important;
    }
}

/* Continuous shimmer effect */
.mobile-tab-btn::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: -100% !important;
    width: 100% !important;
    height: 100% !important;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent) !important;
    animation: shimmer 2s ease-in-out infinite !important;
    z-index: 1 !important;
}

/* Button pulse animation */
@keyframes buttonPulse {
    0%, 100% {
        transform: scale(1) !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
    }
    50% {
        transform: scale(1.02) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }
}

/* Shimmer animation */
@keyframes shimmer {
    0% {
        left: -100% !important;
    }
    100% {
        left: 100% !important;
    }
}

.mobile-tab-btn.active {
    color: #1f2937 !important;
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
    border-bottom: 4px solid #fedc00 !important;
    font-weight: 700 !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3) !important;
    animation: activePulse 2s ease-in-out infinite !important;
}

.mobile-tab-btn:hover:not(.active) {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%) !important;
    color: #374151 !important;
    transform: translateY(-3px) !important;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
}

.mobile-tab-btn:active {
    transform: translateY(-1px) !important;
    transition: transform 0.1s ease !important;
}

/* Active button pulse animation */
@keyframes activePulse {
    0%, 100% {
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3) !important;
    }
    50% {
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.5) !important;
    }
}

.mobile-tab-btn i {
    font-size: 18px !important;
    flex-shrink: 0 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    z-index: 2 !important;
    animation: iconFloat 2.5s ease-in-out infinite !important;
}

/* Icon floating animation */
@keyframes iconFloat {
    0%, 100% {
        transform: translateY(0px) !important;
    }
    50% {
        transform: translateY(-2px) !important;
    }
}

.mobile-tab-btn:hover i {
    transform: scale(1.1) !important;
    color: #3b82f6 !important;
}

.mobile-tab-btn.active i {
    transform: scale(1.15) !important;
    color: #fedc00 !important;
    animation: iconBounce 1.5s ease-in-out infinite !important;
}

/* Icon bounce animation for active button */
@keyframes iconBounce {
    0%, 100% {
        transform: scale(1.15) !important;
    }
    50% {
        transform: scale(1.25) !important;
    }
}

/* Ensure text doesn't wrap and buttons stay equal width */
.mobile-tab-btn span,
.mobile-tab-btn .tab-text {
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    max-width: 100% !important;
    position: relative !important;
    z-index: 2 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.mobile-tab-btn:hover .tab-text {
    color: #1e40af !important;
    font-weight: 700 !important;
}

.mobile-tab-btn.active .tab-text {
    color: #fedc00 !important;
    font-weight: 800 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
}

/* Force equal width distribution */
.mobile-tab-navigation .flex > * {
    flex: 1 1 0% !important;
    min-width: 0 !important;
    max-width: 50% !important;
}

/* Ensure buttons are perfectly aligned */
.mobile-tab-btn {
    box-sizing: border-box !important;
    border-radius: 0 !important;
    outline: none !important;
}

/* Remove any default button styles that might interfere */
.mobile-tab-btn:focus {
    outline: none !important;
    box-shadow: none !important;
}

.mobile-tab-btn:focus-visible {
    outline: 2px solid #fedc00 !important;
    outline-offset: -2px !important;
}

/* Loading state animation */
.mobile-tab-btn.loading {
    pointer-events: none !important;
    opacity: 0.7 !important;
}

.mobile-tab-btn.loading::after {
    content: '' !important;
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    width: 20px !important;
    height: 20px !important;
    margin: -10px 0 0 -10px !important;
    border: 2px solid #f3f3f3 !important;
    border-top: 2px solid #fedc00 !important;
    border-radius: 50% !important;
    animation: spin 1s linear infinite !important;
    z-index: 3 !important;
}

@keyframes spin {
    0% { transform: rotate(0deg) !important; }
    100% { transform: rotate(360deg) !important; }
}

/* Ripple effect on click */
.mobile-tab-btn::after {
    content: '' !important;
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    width: 0 !important;
    height: 0 !important;
    border-radius: 50% !important;
    background: rgba(251, 191, 36, 0.3) !important;
    transform: translate(-50%, -50%) !important;
    transition: width 0.6s, height 0.6s !important;
    z-index: 1 !important;
}

.mobile-tab-btn:active::after {
    width: 300px !important;
    height: 300px !important;
}

/* Hide filter on mobile, tablet, and iPad - Show only on desktop */
.filter-header {
    display: none !important;
}

@media (min-width: 1280px) {
    .filter-header {
        display: block !important;
    }
}

/* Mobile Content Visibility */
@media (max-width: 1023px) {
    /* Default: Show Map, Hide VR on mobile */
    .virtual-tour-section {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        transition: all 0.3s ease !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1 !important;
    }
    
    #restaurants-map {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        transition: all 0.3s ease !important;
        position: relative !important;
        z-index: 2 !important;
    }
    
    /* Make sure both containers are in the same area on mobile */
    .left-column {
        position: relative !important;
    }
    
    .right-column {
        position: relative !important;
    }
    
    /* Allow JavaScript to override these styles */
    .virtual-tour-section[style*="display: block"] {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .virtual-tour-section[style*="display: none"] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
    
    #restaurants-map[style*="display: block"] {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    #restaurants-map[style*="display: none"] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }

    
    /* Ensure cards section is always visible on mobile */
    .mobile-cards-section {
        position: relative !important;
        z-index: 1 !important;
        background: white !important;
        border-top: 2px solid #e5e7eb !important;
        margin-top: 0 !important;
    }
}

/* Desktop: Show both VR and Map */
@media (min-width: 1024px) {
    .mobile-tab-navigation {
        display: none !important;
    }
    
    .virtual-tour-section {
        display: block !important;
    }
    
    #restaurants-map {
        display: block !important;
    }
    
    /* Reset any mobile overrides on desktop */
    .virtual-tour-section[style*="display: none"] {
        display: block !important;
    }
    
    #restaurants-map[style*="display: none"] {
        display: block !important;
    }
}

/* Mobile Filter Styles */
.mobile-filter-toggle {
    position: fixed;
    top: 16px;
    right: 16px;
    z-index: 50;
}

.mobile-filter-toggle button {
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
    color: #1f2937 !important;
    padding: 12px !important;
    border-radius: 50% !important;
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4) !important;
    border: none !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 48px !important;
    height: 48px !important;
    font-size: 18px !important;
    font-weight: bold !important;
}

/* Fallback filter icon using CSS */
.mobile-filter-toggle button::before {
    content: "☰";
    display: block;
    font-size: 18px;
    font-weight: bold;
    color: #1f2937;
}

.mobile-filter-toggle button:hover::before {
    color: #ffffff;
}

.mobile-filter-toggle button:hover {
    background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
    transform: scale(1.05) !important;
    box-shadow: 0 6px 16px rgba(251, 191, 36, 0.6) !important;
}

/* Custom Filter Icon - Hidden, using CSS fallback instead */
.filter-icon {
    display: none;
}

.filter-line {
    display: none;
}

.mobile-filter-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 40;
    display: none;
}

.mobile-filter-panel {
    background: white;
    height: 100%;
    width: 320px;
    align-content: center;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
}

.mobile-filter-panel::-webkit-scrollbar {
    width: 4px;
}

.mobile-filter-panel::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.mobile-filter-panel::-webkit-scrollbar-thumb {
    background: #fedc00;
    border-radius: 2px;
}

.mobile-filter-panel input,
.mobile-filter-panel select {
    font-size: 14px !important;
    padding: 8px 12px !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 8px !important;
    transition: all 0.2s ease !important;
}

.mobile-filter-panel input:focus,
.mobile-filter-panel select:focus {
    border-color: #fedc00 !important;
    box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.1) !important;
    outline: none !important;
}

.mobile-filter-panel button {
    font-size: 14px !important;
    padding: 12px 16px !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
    border: none !important;
    cursor: pointer !important;
}

.mobile-filter-panel label {
    font-size: 13px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    margin-bottom: 6px !important;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .gap-4 {
        gap: 0.75rem;
    }
    
    .text-4xl {
        font-size: 2rem;
    }
    
    .px-6 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .h-96 {
        height: 300px !important;
    }
    
    .max-h-96 {
        max-height: 300px !important;
    }
    
    /* Hide desktop filter header on mobile */
    .filter-header {
        display: none !important;
    }
    
    /* Adjust layout for mobile */
    .two-column-layout {
        margin-top: 0 !important;
    }
    
    /* Mobile map height - 40vh */
    #restaurants-map {
        height: 50vh !important;
        min-height: 50vh !important;
    }
    
    /* Mobile virtual tour height - 40vh */
    .virtual-tour-section {
        height: 50vh !important;
        min-height: 50vh !important;
    }
    
    /* Disable scroll in right column on mobile - AGGRESSIVE APPROACH */
    .right-column {
        display: flex !important;
        flex-direction: column !important;
        background-color: #ffffff !important;
        overflow: hidden !important;
        overflow-x: hidden !important;
        overflow-y: hidden !important;
        position: relative !important;
        max-height: none !important;
        height: auto !important;
        min-height: auto !important;
    }
    
    /* Force all child elements to not scroll */
    .right-column * {
        overflow: visible !important;
        max-height: none !important;
    }
    
    /* Specifically target restaurants container */
    .right-column #restaurants-container {
        overflow: visible !important;
        overflow-y: visible !important;
        overflow-x: visible !important;
        max-height: none !important;
        height: auto !important;
        min-height: auto !important;
    }
    
    /* Disable scroll in restaurants list on mobile */
    #restaurants-list {
        overflow: hidden !important;
    }
    
    /* Disable scroll in restaurants container on mobile */
    #restaurants-container {
        overflow: visible !important;
        overflow-y: visible !important;
        max-height: none !important;
    }
}



/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .restaurant-card,
    .current-restaurant-marker div {
        animation: none !important;
        transition: none !important;
    }
    
    .restaurant-card:hover {
        transform: none !important;
    }
}

/* Map Popup Styles */
.leaflet-popup-content-wrapper {
    border-radius: 12px !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
    border: 2px solid #f3f4f6 !important;
    transition: all 0.3s ease !important;
}

.restaurant-popup-content {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    min-width: 200px !important;
    max-width: 280px !important;
    transition: all 0.3s ease !important;
}

/* Mobile popup styles */
@media (max-width: 768px) {
    .leaflet-popup-content-wrapper {
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
        border: 1px solid #e5e7eb !important;
    }
    
    .restaurant-popup-content {
        min-width: 180px !important;
        max-width: 220px !important;
        font-size: 12px !important;
    }
    
    .popup-image {
        width: 60px !important;
        height: 60px !important;
    }
    
    .popup-placeholder {
        width: 60px !important;
        height: 60px !important;
    }
}

/* Popup hover effects */
.restaurant-popup-content:hover {
    transform: scale(1.02) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2) !important;
}

.leaflet-popup-content-wrapper:hover {
    border-color: #fedc00 !important;
    box-shadow: 0 12px 32px rgba(251, 191, 36, 0.3) !important;
}

.restaurant-popup-content .flex {
    display: flex !important;
    gap: 12px !important;
}

.restaurant-popup-content .flex-1 {
    flex: 1 !important;
    min-width: 0 !important;
}

.restaurant-popup-content .flex-shrink-0 {
    flex-shrink: 0 !important;
}

.restaurant-popup-content img {
    border-radius: 8px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.restaurant-popup-content h3 {
    font-size: 16px !important;
    font-weight: 600 !important;
    margin: 0 !important;
    line-height: 1.3 !important;
}

.restaurant-popup-content p {
    margin: 0 0 4px 0 !important;
    font-size: 13px !important;
    line-height: 1.4 !important;
}

.restaurant-popup-content .space-x-1 > * + * {
    margin-left: 4px !important;
}

.restaurant-popup-content .space-x-1 {
    display: flex !important;
}

/* Current restaurant popup highlight */
.current-popup .leaflet-popup-content-wrapper {
    border-color: #fedc00 !important;
    box-shadow: 0 8px 24px rgba(251, 191, 36, 0.3) !important;
}

/* Marker animations */
.current-restaurant-marker div {
    animation: current-pulse 2s infinite;
}

@keyframes current-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Restaurant Cards Layout */
#restaurants-list {
    overflow-x: auto !important;
    overflow-y: hidden !important;
    max-height: none !important;
    height: auto !important;
    flex-wrap: nowrap !important;
    flex-direction: row !important;
    gap: 12px !important;
    padding: 12px !important;
    justify-content: center !important;
}

/* Desktop Layout: Image left, content right */
.restaurant-card {
    height: 240px !important;
    min-width: 600px !important;
    flex-shrink: 0 !important;
    padding: 16px !important;
    border-radius: 12px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
    transition: all 0.3s ease !important;
    display: flex !important;
    align-items: center !important;
    flex-direction: row !important;
}

.restaurant-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-1px) !important;
}

.restaurant-card .flex {
    width: 100% !important;
}

.restaurant-card img {
    width: 350px !important;
    height: 200px !important;
    object-fit: cover !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
    flex-shrink: 0 !important;
}

.restaurant-card .flex-shrink-0 {
    flex-shrink: 0 !important;
}

.restaurant-card .flex-1 {
    padding-left: 20px;
    padding-top: 50px;
    min-width: 0 !important;
    height: 100% !important;
    flex-direction: column !important;
    justify-content: flex-start !important;
    flex: 1 !important;
}

.restaurant-card h4 {
    font-size: 20px !important;
    font-weight: 700 !important;
    margin: 0 0 8px 0 !important;
    line-height: 1.3 !important;
    color: #1f2937 !important;
}

.restaurant-card .space-y-0\.5 > * + * {
    margin-top: 4px !important;
}

.restaurant-card p {
    font-size: 13px !important;
    margin: 0 0 6px 0 !important;
    line-height: 1.4 !important;
    color: #6b7280 !important;
}

/* Description styling */
.restaurant-card .description {
    font-size: 12px !important;
    color: #9ca3af !important;
    line-height: 1.4 !important;
    margin: 8px 0 0 0 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 3 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.restaurant-card .text-xs {
    font-size: 12px !important;
}

.restaurant-card .px-1 {
    padding-left: 4px !important;
    padding-right: 4px !important;
}

.restaurant-card .py-0\.5 {
    padding-top: 2px !important;
    padding-bottom: 2px !important;
}

.restaurant-card .px-2 {
    padding-left: 8px !important;
    padding-right: 8px !important;
}

.restaurant-card .py-1 {
    padding-top: 4px !important;
    padding-bottom: 4px !important;
}

.restaurant-card .rounded-full {
    border-radius: 9999px !important;
}

.restaurant-card .bg-yellow-100 {
    background-color: #fedc00 !important;
}

.restaurant-card .text-yellow-800 {
    color: #fedc00 !important;
}

.restaurant-card .bg-red-100 {
    background-color: #fee2e2 !important;
}

.restaurant-card .text-red-800 {
    color: #991b1b !important;
}

.restaurant-card .bg-gray-200 {
    background-color: rgb(255, 255, 255) !important;
}

.restaurant-card .text-gray-600 {
    color: #4b5563 !important;
}

.restaurant-card .bg-yellow-400 {
    background-color: #fedc00 !important;
}

.restaurant-card .text-gray-800 {
    color: #1f2937 !important;
}

.restaurant-card .text-green-600 {
    color: #059669 !important;
}

/* Mobile Responsive Fixes */
@media (max-width: 768px) {
    #restaurants-list {
        padding: 8px !important;
        gap: 10px !important;
        flex-direction: column !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }
    
    .restaurant-card {
        height: 350px !important;
        min-height: 350px !important;
        min-width: 95% !important;
        width: 95% !important;
        padding: 12px !important;
        flex-direction: column !important;
        align-items: stretch !important;
    }
    
    .restaurant-card img {
        width: 100% !important;
        height: 180px !important;
        margin-bottom: 12px !important;
    }
    
    .restaurant-card .flex-1 {
        padding-left: 0 !important;
        padding-top: 16px !important;
    }
    
    .restaurant-card h4 {
        font-size: 18px !important;
        margin-bottom: 8px !important;
    }
    
    .restaurant-card p {
        font-size: 13px !important;
        margin-bottom: 6px !important;
    }
    
    .restaurant-card .description {
        font-size: 12px !important;
        margin-top: 8px !important;
        -webkit-line-clamp: 2 !important;
    }
    
    .restaurant-card .space-y-2 > * + * {
        margin-top: 6px !important;
    }
    
    .restaurant-card .bg-gray-50 {
        padding: 0px 10px !important;
    }
    
    .restaurant-card .fas {
        font-size: 12px !important;
    }
}

@media (max-width: 480px) {
    .restaurant-card {
        height: 350px !important;
        min-height: 350px !important;
        min-width: 95% !important;
        padding: 10px !important;
        width: 95% !important;
        flex-direction: column !important;
    }
    
    .restaurant-card img {
        width: 100% !important;
        height: 150px !important;
        margin-bottom: 10px !important;
    }
    
    .restaurant-card .flex-1 {
        padding-left: 0 !important;
        padding-top: 12px !important;
    }
    
    .restaurant-card h4 {
        font-size: 16px !important;
        margin-bottom: 6px !important;
    }
    
    .restaurant-card p {
        font-size: 11px !important;
        margin-bottom: 4px !important;
    }
    
    .restaurant-card .description {
        font-size: 11px !important;
        margin-top: 6px !important;
        -webkit-line-clamp: 2 !important;
    }
    
    .restaurant-card .bg-gray-50 {
        padding: 0px 8px !important;
    }
}

</style>

<?php get_footer(); ?>