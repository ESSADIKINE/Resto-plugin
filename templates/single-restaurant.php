<?php
/**
 * Single Restaurant Template - Updated Layout
 * 
 * @package LeBonResto
 */

get_header(); 

// Enqueue Tailwind CSS with fallback
wp_enqueue_style(
    'tailwind-css',
    'https://cdn.tailwindcss.com',
    array(),
    '3.4.0'
);

// Add inline backup styles if Tailwind fails to load
wp_add_inline_style('tailwind-css', '
/* Tailwind Backup Styles */
.min-h-screen { min-height: 100vh; }
.bg-gray-50 { background-color: #f9fafb; }
.bg-gray-100 { background-color: #f3f4f6; }
.bg-white { background-color: #ffffff; }
.bg-yellow-400 { background-color: #fbbf24; }
.bg-yellow-500 { background-color: #f59e0b; }
.text-gray-700 { color: #374151; }
.text-gray-800 { color: #1f2937; }
.text-gray-600 { color: #4b5563; }
.text-yellow-600 { color: #d97706; }
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
.hover\\:bg-yellow-500:hover { background-color: #f59e0b; }
.hover\\:bg-gray-300:hover { background-color: #d1d5db; }
.focus\\:outline-none:focus { outline: 2px solid transparent; outline-offset: 2px; }
.focus\\:ring-2:focus { box-shadow: 0 0 0 2px #fbbf24; }
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
    width: 100% !important;
    background-color:rgb(255, 255, 255) !important;
    border-right: 3px solid rgb(255, 255, 255) !important;
}

@media (max-width: 1023px) {
    #restaurants-map {
        height: 40vh !important;
        min-height: 40vh !important;
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
        height: 40vh !important;
        min-height: 40vh !important;
    }
}

.virtual-tour-section iframe {
    width: 100% !important;
}

/* MOBILE FILTER STYLES - INLINE FOR IMMEDIATE APPLICATION */
.mobile-filter-toggle {
    position: fixed !important;
    top: 20px !important;
    left: 20px !important;
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
    border: 2px solid #FFC107 !important;
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
    background: #FFC107 !important;
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
    border-color: #fbbf24 !important;
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1) !important;
    outline: none !important;
    transform: translateY(-1px) !important;
}

.mobile-filter-panel input[type="checkbox"] {
    width: 1.125rem !important;
    height: 1.125rem !important;
    accent-color: #FFC107 !important;
    border-radius: 0.25rem !important;
    border: 2px solid #d1d5db !important;
}

.mobile-filter-panel input[type="checkbox"]:checked {
    background-color: #FFC107 !important;
    border-color: #FFC107 !important;
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
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
    color: #1f2937 !important;
    font-weight: 700 !important;
}

.mobile-filter-panel #mobile-search-restaurants:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
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
    border-color: #fbbf24 !important;
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
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
    color: #1f2937 !important;
    font-weight: 700 !important;
}

#search-restaurants:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
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
    border-color: #fbbf24 !important;
}

.loading-spinner {
    border: 4px solid rgb(255, 255, 255) !important;
    border-top: 4px solid #fbbf24 !important;
    border-right: 4px solid #f59e0b !important;
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
    
    .virtual-tour-section {
        height: 350px !important;
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
        $email = get_post_meta($current_restaurant_id, '_restaurant_email', true);
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
                style="background-color: #FFC107;"
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
                                style="background-color: #FFC107;"
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

        <!-- Desktop Filter Header (Hidden on Mobile) -->
        <div class="filter-header w-full bg-gray-100 border-b border-gray-200 sticky top-0 z-50 hidden lg:block">
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
                                    <i class="fas fa-star mr-1" style="color: #FFC107;"></i>
                                    <?php _e('Featured', 'le-bon-resto'); ?>
                            </span>
                            </label>
                        </div>

                        <!-- Search Button -->
                        <button 
                            id="search-restaurants"
                            class="w-full lg:w-auto px-6 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200"
                            style="background-color: #FFC107;"
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
                        style="background-color: #FFC107;"
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
                <div id="restaurants-container" class="flex-1 p-4 overflow-y-auto align-items-center">
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
            "email": <?php echo wp_json_encode($email); ?>,
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

// Initialize location detection for distance filtering
document.addEventListener('DOMContentLoaded', function() {
    const distanceFilter = document.getElementById('distance-filter');
    
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
        rightColumn.style.scrollbarColor = '#FFC107 #f3f4f6';
            
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
    
    // Handle window resize to update scroll behavior
    window.addEventListener('resize', function() {
        const rightColumn = document.querySelector('.right-column');
        const restaurantsContainer = document.querySelector('#restaurants-container');
        
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
            } else {
                // Enable scroll on desktop
                rightColumn.style.overflowY = 'auto';
                rightColumn.style.maxHeight = '100vh';
                rightColumn.style.scrollbarWidth = 'thin';
                rightColumn.style.scrollbarColor = '#FFC107 #f3f4f6';
                
                // Enable scroll in restaurants container on desktop
                if (restaurantsContainer) {
                    restaurantsContainer.style.overflowY = 'auto';
                    restaurantsContainer.style.maxHeight = '100%';
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

// Localize script data
<?php
wp_localize_script(
    'lebonresto-single-map',
    'lebonrestoSingle',
    array(
        'apiUrl' => home_url('/wp-json/lebonresto/v1/restaurants'),
        'cuisineTypesUrl' => home_url('/wp-json/lebonresto/v1/cuisine-types'),
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
            'emailTitle' => __('Email restaurant', 'le-bon-resto'),
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
    min-height: 65vh !important;
    max-height: 65vh !important;
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
        height: 6vh !important;
        min-height: 400px !important;
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
    border-color: #fbbf24 !important;
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
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
    color: #1f2937 !important;
    font-weight: 700 !important;
}

#search-restaurants:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
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
    border-color: #fbbf24 !important;
}

.restaurant-card.ring-2 {
    border-color: #fbbf24 !important;
    box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.3), 0 8px 24px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px) scale(1.02) !important;
}

/* Loading Spinner */
.loading-spinner {
    border: 4px solid #f3f4f6 !important;
    border-top: 4px solid #fbbf24 !important;
    border-right: 4px solid #f59e0b !important;
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
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
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
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
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
    background: #FFC107;
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
    border: 2px solid #FFC107;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
}

.leaflet-popup-content-wrapper {
    border-radius: 8px;
}

/* Button hover effects */
.bg-yellow-400:hover {
    background-color: #f59e0b !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

/* Restaurant card highlighting */
.restaurant-card.ring-2 {
    animation: pulse-ring 1s ease-in-out;
}

@keyframes pulse-ring {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
    70% { box-shadow: 0 0 0 6px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

/* Focus states for accessibility */
input:focus,
select:focus,
button:focus {
    outline: 0px solid #FFC107 !important;
    outline-offset: 0px;
}



/* Mobile Filter Styles */
.mobile-filter-toggle {
    position: fixed;
    top: 16px;
    left: 16px;
    z-index: 50;
}

.mobile-filter-toggle button {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
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
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
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
    background: #FFC107;
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
    border-color: #fbbf24 !important;
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
        height: 40vh !important;
        min-height: 40vh !important;
    }
    
    /* Mobile virtual tour height - 40vh */
    .virtual-tour-section {
        height: 40vh !important;
        min-height: 40vh !important;
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
}

.restaurant-popup-content {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    min-width: 280px !important;
    max-width: 320px !important;
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
    border-color: #fbbf24 !important;
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

/* Featured marker glow */
.featured-restaurant-marker div {
    box-shadow: 0 0 8px rgba(251, 191, 36, 0.5) !important;
}

/* Horizontal Restaurant Cards Layout */
#restaurants-list {
    overflow-x: auto !important;
    overflow-y: hidden !important;
    max-height: none !important;
    height: auto !important;
    flex-wrap: nowrap !important;
    flex-direction: row !important;
    gap: 8px !important;
    padding: 8px !important;
    justify-content: center !important;
}

.restaurant-card {
    height: 120px !important;
    min-width: 300px !important;
    flex-shrink: 0 !important;
    padding: 12px !important;
    border-radius: 8px !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
    transition: all 0.2s ease !important;
    display: flex !important;
    align-items: center !important;
}

.restaurant-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-1px) !important;
}

.restaurant-card .flex {
    width: 100% !important;
}

.restaurant-card img {
    width: 200px !important;
    height: 120px !important;
    object-fit: cover !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
}

.restaurant-card .flex-shrink-0 {
    flex-shrink: 0 !important;
}

.restaurant-card .flex-1 {
    padding-left: 20px;
    min-width: 0 !important;
    height: 100% !important;
    flex-direction: column !important;
    justify-content: center !important;
}

.restaurant-card h4 {
    font-size: 18px !important;
    font-weight: 600 !important;
    margin: 0 !important;
    line-height: 1.2 !important;
}

.restaurant-card .space-y-0\.5 > * + * {
    margin-top: 2px !important;
}

.restaurant-card p {
    font-size: 11px !important;
    margin: 0 !important;
    line-height: 1.3 !important;
    color: #6b7280 !important;
}

.restaurant-card .text-xs {
    font-size: 10px !important;
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
    background-color: #fef3c7 !important;
}

.restaurant-card .text-yellow-800 {
    color: #92400e !important;
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
    background-color: #fbbf24 !important;
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
        padding: 4px !important;
        gap: 6px !important;
    }
    
    .restaurant-card {
        height: 100px !important;
        min-width: 90% !important;
        padding: 8px !important;
    }
    
    .restaurant-card img {
        width: 80px !important;
        height: 80px !important;
    }
    
    .restaurant-card .flex-1 {
        padding-left: 12px !important;
    }
    
    .restaurant-card h4 {
        font-size: 12px !important;
    }
    
    .restaurant-card p {
        font-size: 10px !important;
    }
    
    .restaurant-card .space-y-2 > * + * {
        margin-top: 4px !important;
    }
    
    .restaurant-card .bg-gray-50 {
        padding: 0px 8px !important;
    }
    
    .restaurant-card .fas {
        font-size: 10px !important;
    }
}

@media (max-width: 480px) {
    .restaurant-card {
        height: 100px !important;
        min-width: 90% !important;
        padding: 6px !important;
        width: 90% !important;


    }
    
    .restaurant-card img {
        width: 120px !important;
        height: 100px !important;
    }
    
    .restaurant-card .flex-1 {
        padding-left: 14px !important;
    }
    
    .restaurant-card h4 {
        font-size: 11px !important;
    }
    
    .restaurant-card p {
        font-size: 9px !important;
    }
    
    .restaurant-card .bg-gray-50 {
        padding: 0px 6px !important;
    }
}

</style>

<?php get_footer(); ?>
