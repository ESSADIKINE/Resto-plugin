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

// Enqueue single restaurant script (main script)
wp_enqueue_script(
    'lebonresto-single-js',
    LEBONRESTO_PLUGIN_URL . 'assets/js/single-restaurant.js',
    array('jquery', 'leaflet-js', 'wp-api'),
    LEBONRESTO_PLUGIN_VERSION . '.debug' . time(), // Force cache invalidation
    true
);

// Enqueue single restaurant CSS
wp_enqueue_style(
    'lebonresto-single-css',
    LEBONRESTO_PLUGIN_URL . 'assets/css/single-restaurant.css',
    array('tailwind-css'),
    LEBONRESTO_PLUGIN_VERSION
);

// Enqueue all restaurants CSS for card styling
wp_enqueue_style(
    'lebonresto-all-restaurants-css',
    LEBONRESTO_PLUGIN_URL . 'assets/css/all-restaurants.css',
    array('tailwind-css'),
    LEBONRESTO_PLUGIN_VERSION
);

// Add critical inline styles to ensure they're applied
wp_add_inline_style('lebonresto-single-css', '
/* Critical inline styles for immediate application */
:root {
    --primary-color: #fedc00;
    --primary-dark: #e6c200;
    --text-primary: #1a1a1a;
    --text-secondary: #4a4a4a;
    --text-muted: #767676;
    --border-color: #e0e0e0;
    --border-light: #f0f0f0;
    --bg-white: #ffffff;
    --bg-gray-50: #fafafa;
    --bg-gray-100: #f5f5f5;
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    --radius-sm: 4px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --transition: all 0.2s ease;
    --success-color: #10b981;
    --error-color: #ef4444;
}

.lebonresto-single-layout {
    background: linear-gradient(135deg, var(--bg-gray-50) 0%, var(--border-color) 100%) !important;
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
    background-color: var(--bg-white) !important;
    border-right: 3px solid var(--bg-white) !important;
}

@media (max-width: 1023px) {
    #restaurants-map {
        border-right: none !important;
        border-bottom: 3px solid var(--bg-white) !important;
    }
}

.virtual-tour-section {
    height: 64vh !important;
    border-bottom: 3px solid var(--bg-white) !important;
    background: linear-gradient(135deg, var(--bg-white) 0%, var(--bg-white) 100%) !important;
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
.mobile-filter-toggle1 {
    position: fixed !important;
    buttom: 70px !important;
    right: 20px !important;
    z-index: 50 !important;
    display: block !important;
    transition: all 0.3s ease !important;
}

/* When filter is open, move icon to top-right */
.mobile-filter-toggle1.filter-open {
    buttom: 70px !important;
    left: auto !important;
    right: 20px !important;
}

.mobile-filter-toggle1 button {
    background: rgba(255, 255, 255, 0.95) !important;
    border: 2px solid var(--primary-color) !important;
    border-radius: var(--radius-lg) !important;
    padding: 12px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    transition: var(--transition) !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 120px !important;
    height: 48px !important;
}

.mobile-filter-toggle1 button:hover {
    background: var(--primary-color) !important;
    transform: scale(1.05) !important;
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
    z-index: 999 !important;
    display: none !important;
    opacity: 0 !important;
    transition: opacity 0.3s ease !important;
}

.mobile-filter-overlay.show {
    display: block !important;
    opacity: 1 !important;
}

.mobile-filter-overlay:not(.hidden) {
    display: block !important;
    opacity: 1 !important;
}

/* Mobile Filter Panel - Essential Styles Only */
.mobile-filter-panel {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
}

.mobile-filter-panel .space-y-4 > div {
    margin-bottom: 20px !important;
}

.mobile-filter-panel input,
.mobile-filter-panel select {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 12px !important;
    padding: 14px 16px !important;
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #1f2937 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.mobile-filter-panel input:focus,
.mobile-filter-panel select:focus {
    outline: none !important;
    border-color: #fedc00 !important;
    box-shadow: 0 0 0 4px rgba(254, 220, 0, 0.15), 0 4px 12px rgba(254, 220, 0, 0.1) !important;
    background: #ffffff !important;
    transform: translateY(-1px) !important;
}

.mobile-filter-panel input::placeholder {
    color: #9ca3af !important;
    font-weight: 400 !important;
}

.mobile-filter-panel input:hover,
.mobile-filter-panel select:hover {
    border-color: #fbbf24 !important;
    box-shadow: 0 2px 8px rgba(254, 220, 0, 0.1) !important;
}

.mobile-filter-panel input[type="checkbox"] {
    width: 20px !important;
    height: 20px !important;
    accent-color: #fedc00 !important;
    margin-right: 12px !important;
    cursor: pointer !important;
}

.mobile-filter-panel .flex.items-center {
    padding: 12px 0 !important;
}

.mobile-filter-panel .flex.items-center span {
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #374151 !important;
}

.mobile-filter-panel button {
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    border-radius: 12px !important;
    font-size: 14px !important;
    padding: 16px 20px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.mobile-filter-panel button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;
}

.mobile-filter-panel button:active {
    transform: translateY(0) !important;
}

.mobile-filter-panel button[style*="background-color: #fedc00"] {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    color: #1f2937 !important;
    border: none !important;
}

.mobile-filter-panel button[style*="background-color: #fedc00"]:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #fedc00 100%) !important;
    box-shadow: 0 4px 16px rgba(254, 220, 0, 0.3) !important;
}

.mobile-filter-panel .bg-gray-200 {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
    color: #4b5563 !important;
    border: 2px solid #d1d5db !important;
}

.mobile-filter-panel .bg-gray-200:hover {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    color: white !important;
    border-color: #ef4444 !important;
}

.mobile-filter-panel .flex.items-center.justify-between {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    padding: 20px !important;
    border-bottom: 2px solid rgba(0, 0, 0, 0.1) !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
    display: flex;
    gap: 170px;
}

.mobile-filter-panel h3 {
    color: #1f2937 !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    margin: 0 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
}

#close-mobile-filters {
    width: 44px !important;
    height: 44px !important;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.2) !important;
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
    color: #1f2937 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    backdrop-filter: blur(10px) !important;
}

#close-mobile-filters:hover {
    background: rgba(255, 255, 255, 0.3) !important;
    border-color: rgba(255, 255, 255, 0.5) !important;
    transform: scale(1.1) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
}

#close-mobile-filters:active {
    transform: scale(0.95) !important;
}

#close-mobile-filters svg {
    width: 24px !important;
    height: 24px !important;
    stroke: currentColor !important;
    stroke-width: 2.5 !important;
    fill: none !important;
}

.mobile-filter-panel .p-4 {
    padding: 24px !important;
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
}

.mobile-filter-panel .space-y-4 > div {
    animation: slideInUp 0.3s ease-out !important;
    animation-fill-mode: both !important;
}

.mobile-filter-panel .space-y-4 > div:nth-child(1) { animation-delay: 0.1s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(2) { animation-delay: 0.15s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(3) { animation-delay: 0.2s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(4) { animation-delay: 0.25s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(5) { animation-delay: 0.3s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(6) { animation-delay: 0.35s !important; }

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.mobile-filter-panel::-webkit-scrollbar {
    width: 6px !important;
}

.mobile-filter-panel::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05) !important;
    border-radius: 3px !important;
}

.mobile-filter-panel::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    border-radius: 3px !important;
}

.mobile-filter-panel::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #fedc00 100%) !important;
}

.mobile-filter-panel .space-y-4 > div {
    margin-bottom: 20px !important;
}

.mobile-filter-panel input,
.mobile-filter-panel select {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 12px !important;
    padding: 14px 16px !important;
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #1f2937 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.mobile-filter-panel input:focus,
.mobile-filter-panel select:focus {
    outline: none !important;
    border-color: #fedc00 !important;
    box-shadow: 0 0 0 4px rgba(254, 220, 0, 0.15), 0 4px 12px rgba(254, 220, 0, 0.1) !important;
    background: #ffffff !important;
    transform: translateY(-1px) !important;
}

.mobile-filter-panel input::placeholder {
    color: #9ca3af !important;
    font-weight: 400 !important;
}

.mobile-filter-panel select {
    cursor: pointer !important;
    background-position: right 12px center !important;
    background-repeat: no-repeat !important;
    background-size: 16px !important;
    padding-right: 40px !important;
}

.mobile-filter-panel input:hover,
.mobile-filter-panel select:hover {
    border-color: #fbbf24 !important;
    box-shadow: 0 2px 8px rgba(254, 220, 0, 0.1) !important;
}

/* Checkbox styling */
.mobile-filter-panel input[type="checkbox"] {
    width: 20px !important;
    height: 20px !important;
    accent-color: #fedc00 !important;
    margin-right: 12px !important;
    cursor: pointer !important;
}

.mobile-filter-panel .flex.items-center {
    padding: 12px 0 !important;
}

.mobile-filter-panel .flex.items-center span {
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #374151 !important;
}

/* Button styling */
.mobile-filter-panel button {
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    border-radius: 12px !important;
    font-size: 14px !important;
    padding: 16px 20px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.mobile-filter-panel button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;
}

.mobile-filter-panel button:active {
    transform: translateY(0) !important;
}

/* Apply button specific styling */
.mobile-filter-panel button[style*="background-color: #fedc00"] {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    color: #1f2937 !important;
    border: none !important;
}

.mobile-filter-panel button[style*="background-color: #fedc00"]:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #fedc00 100%) !important;
    box-shadow: 0 4px 16px rgba(254, 220, 0, 0.3) !important;
}

/* Clear button specific styling */
.mobile-filter-panel .bg-gray-200 {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
    color: #4b5563 !important;
    border: 2px solid #d1d5db !important;
}

.mobile-filter-panel .bg-gray-200:hover {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    color: white !important;
    border-color: #ef4444 !important;
}

/* Mobile Filter Panel Header */
.mobile-filter-panel .flex.items-center.justify-between {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    padding: 20px !important;
    border-bottom: 2px solid rgba(0, 0, 0, 0.1) !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
    display: flex;
    gap: 170px;
}

.mobile-filter-panel h3 {
    color: #1f2937 !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    margin: 0 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
}

/* Close button styling */
#close-mobile-filters {
    width: 40px !important;
    height: 40px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#close-mobile-filters:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    transform: scale(1.1) !important;
}

/* Mobile Filter Panel - Essential Styles Only */
.mobile-filter-panel {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
}

.mobile-filter-panel .space-y-4 > div {
    margin-bottom: 20px !important;
}

.mobile-filter-panel input,
.mobile-filter-panel select {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 12px !important;
    padding: 14px 16px !important;
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #1f2937 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.mobile-filter-panel input:focus,
.mobile-filter-panel select:focus {
    outline: none !important;
    border-color: #fedc00 !important;
    box-shadow: 0 0 0 4px rgba(254, 220, 0, 0.15), 0 4px 12px rgba(254, 220, 0, 0.1) !important;
    background: #ffffff !important;
    transform: translateY(-1px) !important;
}

.mobile-filter-panel input::placeholder {
    color: #9ca3af !important;
    font-weight: 400 !important;
}

.mobile-filter-panel select {
    cursor: pointer !important;
    background-position: right 12px center !important;
    background-repeat: no-repeat !important;
    background-size: 16px !important;
    padding-right: 40px !important;
}

.mobile-filter-panel input:hover,
.mobile-filter-panel select:hover {
    border-color: #fbbf24 !important;
    box-shadow: 0 2px 8px rgba(254, 220, 0, 0.1) !important;
}

.mobile-filter-panel input[type="checkbox"] {
    width: 20px !important;
    height: 20px !important;
    accent-color: #fedc00 !important;
    margin-right: 12px !important;
    cursor: pointer !important;
}

.mobile-filter-panel .flex.items-center {
    padding: 12px 0 !important;
}

.mobile-filter-panel .flex.items-center span {
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #374151 !important;
}

.mobile-filter-panel button {
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    border-radius: 12px !important;
    font-size: 14px !important;
    padding: 16px 20px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.mobile-filter-panel button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;
}

.mobile-filter-panel button:active {
    transform: translateY(0) !important;
}

.mobile-filter-panel button[style*="background-color: #fedc00"] {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    color: #1f2937 !important;
    border: none !important;
}

.mobile-filter-panel button[style*="background-color: #fedc00"]:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #fedc00 100%) !important;
    box-shadow: 0 4px 16px rgba(254, 220, 0, 0.3) !important;
}

.mobile-filter-panel .bg-gray-200 {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
    color: #4b5563 !important;
    border: 2px solid #d1d5db !important;
}

.mobile-filter-panel .bg-gray-200:hover {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    color: white !important;
    border-color: #ef4444 !important;
}

.mobile-filter-panel .flex.items-center.justify-between {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    padding: 20px !important;
    border-bottom: 2px solid rgba(0, 0, 0, 0.1) !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
    display: flex;
    gap: 170px;
}

.mobile-filter-panel h3 {
    color: #1f2937 !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    margin: 0 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
}

#close-mobile-filters {
    width: 44px !important;
    height: 44px !important;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.2) !important;
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
    color: #1f2937 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    backdrop-filter: blur(10px) !important;
}

#close-mobile-filters:hover {
    background: rgba(255, 255, 255, 0.3) !important;
    border-color: rgba(255, 255, 255, 0.5) !important;
    transform: scale(1.1) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
}

#close-mobile-filters:active {
    transform: scale(0.95) !important;
}

#close-mobile-filters svg {
    width: 24px !important;
    height: 24px !important;
    stroke: currentColor !important;
    stroke-width: 2.5 !important;
    fill: none !important;
}

.mobile-filter-panel .p-4 {
    padding: 24px !important;
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
}

.mobile-filter-panel .space-y-4 > div {
    animation: slideInUp 0.3s ease-out !important;
    animation-fill-mode: both !important;
}

.mobile-filter-panel .space-y-4 > div:nth-child(1) { animation-delay: 0.1s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(2) { animation-delay: 0.15s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(3) { animation-delay: 0.2s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(4) { animation-delay: 0.25s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(5) { animation-delay: 0.3s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(6) { animation-delay: 0.35s !important; }

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.mobile-filter-panel::-webkit-scrollbar {
    width: 6px !important;
}

.mobile-filter-panel::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05) !important;
    border-radius: 3px !important;
}

.mobile-filter-panel::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    border-radius: 3px !important;
}

.mobile-filter-panel::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #fedc00 100%) !important;
}

.mobile-filter-panel .space-y-4 > div {
    margin-bottom: 20px !important;
}

.mobile-filter-panel input,
.mobile-filter-panel select {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 12px !important;
    padding: 14px 16px !important;
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #1f2937 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.mobile-filter-panel input:focus,
.mobile-filter-panel select:focus {
    outline: none !important;
    border-color: #fedc00 !important;
    box-shadow: 0 0 0 4px rgba(254, 220, 0, 0.15), 0 4px 12px rgba(254, 220, 0, 0.1) !important;
    background: #ffffff !important;
    transform: translateY(-1px) !important;
}

.mobile-filter-panel input::placeholder {
    color: #9ca3af !important;
    font-weight: 400 !important;
}

.mobile-filter-panel select {
    cursor: pointer !important;
    background-position: right 12px center !important;
    background-repeat: no-repeat !important;
    background-size: 16px !important;
    padding-right: 40px !important;
}

.mobile-filter-panel input:hover,
.mobile-filter-panel select:hover {
    border-color: #fbbf24 !important;
    box-shadow: 0 2px 8px rgba(254, 220, 0, 0.1) !important;
}

/* Checkbox styling */
.mobile-filter-panel input[type="checkbox"] {
    width: 20px !important;
    height: 20px !important;
    accent-color: #fedc00 !important;
    margin-right: 12px !important;
    cursor: pointer !important;
}

.mobile-filter-panel .flex.items-center {
    padding: 12px 0 !important;
}

.mobile-filter-panel .flex.items-center span {
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #374151 !important;
}

/* Button styling */
.mobile-filter-panel button {
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    border-radius: 12px !important;
    font-size: 14px !important;
    padding: 16px 20px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.mobile-filter-panel button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;
}

.mobile-filter-panel button:active {
    transform: translateY(0) !important;
}

/* Apply button specific styling */
.mobile-filter-panel button[style*="background-color: #fedc00"] {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    color: #1f2937 !important;
    border: none !important;
}

.mobile-filter-panel button[style*="background-color: #fedc00"]:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #fedc00 100%) !important;
    box-shadow: 0 4px 16px rgba(254, 220, 0, 0.3) !important;
}

/* Clear button specific styling */
.mobile-filter-panel .bg-gray-200 {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
    color: #4b5563 !important;
    border: 2px solid #d1d5db !important;
}

.mobile-filter-panel .bg-gray-200:hover {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    color: white !important;
    border-color: #ef4444 !important;
}

/* Mobile Filter Panel Header */
.mobile-filter-panel .flex.items-center.justify-between {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    padding: 20px !important;
    border-bottom: 2px solid rgba(0, 0, 0, 0.1) !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
    display: flex;
    gap: 170px;
}

.mobile-filter-panel h3 {
    color: #1f2937 !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    margin: 0 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
}

.mobile-filter-header {
    background: linear-gradient(135deg, var(--bg-gray-50) 0%, var(--border-color) 100%) !important;
    border-bottom: 2px solid var(--border-color) !important;
    padding: 20px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0 !important;
}

.mobile-filter-title {
    font-size: 18px !important;
    font-weight: 700 !important;
    margin: 0 !important;
    color: var(--text-primary) !important;
    display: flex !important;
    align-items: center !important;
}

.mobile-filter-close {
    background: linear-gradient(135deg, var(--bg-gray-100) 0%, var(--border-color) 100%) !important;
    border: 2px solid var(--border-color) !important;
    color: var(--text-secondary) !important;
    font-size: 18px !important;
    cursor: pointer !important;
    padding: 8px !important;
    width: 36px !important;
    height: 36px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: var(--radius-lg) !important;
    transition: var(--transition) !important;
    box-shadow: var(--shadow-md) !important;
}

.mobile-filter-close:hover {
    background: linear-gradient(135deg, var(--border-color) 0%, var(--border-color) 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: var(--shadow-lg) !important;
}

.mobile-filter-content {
    padding: 20px !important;
    background: linear-gradient(135deg, var(--bg-white) 0%, var(--bg-gray-50) 100%) !important;
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
    color: var(--text-secondary) !important;
    margin-bottom: 8px !important;
    font-size: 14px !important;
}

/* Form container styling - match desktop filter form */
.mobile-filter-content .space-y-4 {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    border-radius: 12px !important;
    padding: 20px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
    border: 2px solid rgba(251, 191, 36, 0.1) !important;
}

/* IMPORTANT: Hide mobile filter button completely on desktop */
@media (min-width: 1024px) {
    .mobile-filter-toggle1,
    .mobile-filter-toggle1.lg\:hidden,
    div.mobile-filter-toggle1 {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
}

/* Show mobile filter button only on mobile/tablet */
@media (max-width: 1023px) {
    .mobile-filter-toggle1 {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: fixed !important;
        bottom: 70px !important;
        right: 20px !important;
        z-index: 1000 !important;
        pointer-events: auto !important;
    }
    
    .mobile-filter-toggle1 button {
        background: var(--primary-color) !important;
        color: var(--text-primary) !important;
        border: none !important;
        height: 50px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 4px 12px rgba(254, 220, 0, 0.4) !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
    }
    
    .mobile-filter-toggle1 button:hover {
        transform: scale(1.1) !important;
        box-shadow: 0 6px 16px rgba(254, 220, 0, 0.6) !important;
    }
}

.filter-form {
    background: linear-gradient(135deg, var(--bg-white) 0%, var(--bg-white) 100%) !important;
    border-radius: var(--radius-lg) !important;
    box-shadow: var(--shadow-lg) !important;
    padding: 20px !important;
    border: 2px solid rgba(254, 220, 0, 0.1) !important;
}

.filter-form input,
.filter-form select {
    height: 48px !important;
    border: 2px solid var(--border-color) !important;
    border-radius: var(--radius-lg) !important;
    font-size: 16px !important;
    padding: 12px 16px !important;
    transition: var(--transition) !important;
    background-color: #fdffb9 !important;
}

.filter-form input:focus,
.filter-form select:focus {
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 3px rgba(254, 220, 0, 0.1) !important;
    outline: none !important;
}

.filter-form button {
    height: 48px !important;
    border-radius: var(--radius-lg) !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    padding: 14px 24px !important;
    transition: var(--transition) !important;
    border: none !important;
    cursor: pointer !important;
}

#search-restaurants {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-color) 100%) !important;
    color: var(--text-primary) !important;
    font-weight: 700 !important;
}

#search-restaurants:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-dark) 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(254, 220, 0, 0.4) !important;
}

#clear-filters {
    background: linear-gradient(135deg, var(--bg-gray-100) 0%, var(--border-color) 100%) !important;
    color: var(--text-secondary) !important;
    border: 2px solid var(--border-color) !important;
}

.restaurant-card {
    background: linear-gradient(135deg, var(--bg-white) 0%, var(--bg-white) 100%) !important;
    border-radius: var(--radius-lg) !important;
    box-shadow: var(--shadow-md) !important;
    margin-bottom: 16px !important;
    border: 2px solid var(--bg-white) !important;
    transition: all 0.4s ease !important;
}

.restaurant-card:hover {
    box-shadow: var(--shadow-lg) !important;
    transform: translateY(-4px) scale(1.02) !important;
    border-color: var(--primary-color) !important;
}

.loading-spinner {
    border: 4px solid var(--bg-white) !important;
    border-top: 4px solid var(--primary-color) !important;
    border-right: 4px solid var(--primary-color) !important;
    border-radius: 50% !important;
    width: 32px !important;
    height: 32px !important;
    animation: spin 1.2s ease-in-out infinite !important;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    :root {
        --text-primary: #ffffff;
        --text-secondary: #d1d5db;
        --text-muted: #9ca3af;
        --bg-white:rgb(255, 255, 255);
        --bg-gray-50: #111827;
        --bg-gray-100: #374151;
        --border-color: #374151;
        --border-light: #4b5563;
    }
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

<div class="lebonresto-single-layout single-restaurant-template min-h-screen bg-gray-50">
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
        
        // Get Google API reviews for the current restaurant
        error_log('LEBONRESTO REVIEWS LOG: Starting review data fetching for restaurant ID: ' . $current_restaurant_id);
        
        $google_api_reviews = get_post_meta($current_restaurant_id, '_restaurant_google_api_reviews', true);
        error_log('LEBONRESTO REVIEWS LOG: Stored reviews from meta: ' . (is_array($google_api_reviews) ? count($google_api_reviews) . ' reviews found' : 'no reviews or not array'));
        
        if (!is_array($google_api_reviews)) {
            $google_api_reviews = array();
            error_log('LEBONRESTO REVIEWS LOG: Initialized empty reviews array');
        }
        
        // Try to get fresh Google Places data if needed
        $google_place_id = get_post_meta($current_restaurant_id, '_restaurant_google_place_id', true);
        $api_key = function_exists('lebonresto_get_google_maps_api_key') ? lebonresto_get_google_maps_api_key() : '';
        
        error_log('LEBONRESTO REVIEWS LOG: Google Place ID: ' . ($google_place_id ?: 'NOT SET'));
        error_log('LEBONRESTO REVIEWS LOG: API Key available: ' . (!empty($api_key) ? 'YES' : 'NO'));
        error_log('LEBONRESTO REVIEWS LOG: fetch function available: ' . (function_exists('lebonresto_fetch_google_places_data') ? 'YES' : 'NO'));
        
        if ($google_place_id && $api_key && function_exists('lebonresto_fetch_google_places_data')) {
            error_log('LEBONRESTO REVIEWS LOG: Attempting to fetch fresh Google Places data...');
            $places_data = lebonresto_fetch_google_places_data($google_place_id, $api_key);
            
            if ($places_data) {
                error_log('LEBONRESTO REVIEWS LOG: Google Places API returned data: ' . print_r(array_keys($places_data), true));
                
                if (isset($places_data['reviews']) && !empty($places_data['reviews'])) {
                    error_log('LEBONRESTO REVIEWS LOG: Found ' . count($places_data['reviews']) . ' reviews in API response');
                    
                    $fresh_api_reviews = array();
                    foreach ($places_data['reviews'] as $index => $review) {
                        error_log('LEBONRESTO REVIEWS LOG: Processing review ' . ($index + 1) . ': ' . print_r($review, true));
                        
                        // Ensure we have valid review data before adding
                        if (isset($review['author_name']) || isset($review['text']) || isset($review['rating'])) {
                            $processed_review = array(
                                'name' => isset($review['author_name']) ? $review['author_name'] : 'Utilisateur anonyme',
                                'author_name' => isset($review['author_name']) ? $review['author_name'] : 'Utilisateur anonyme',
                                'rating' => isset($review['rating']) ? intval($review['rating']) : 0,
                                'text' => isset($review['text']) ? $review['text'] : '',
                                'date' => isset($review['time']) ? date('Y-m-d', $review['time']) : date('Y-m-d'),
                                'time' => isset($review['time']) ? $review['time'] : time(),
                                'source' => 'google_api'
                            );
                            
                            $fresh_api_reviews[] = $processed_review;
                            error_log('LEBONRESTO REVIEWS LOG: Added processed review: ' . print_r($processed_review, true));
                        } else {
                            error_log('LEBONRESTO REVIEWS LOG: Skipped review ' . ($index + 1) . ' - invalid data');
                        }
                    }
                    
                    if (!empty($fresh_api_reviews)) {
                        $google_api_reviews = $fresh_api_reviews;
                        update_post_meta($current_restaurant_id, '_restaurant_google_api_reviews', $fresh_api_reviews);
                        error_log('LEBONRESTO REVIEWS LOG: Updated meta with ' . count($fresh_api_reviews) . ' fresh reviews');
                    } else {
                        error_log('LEBONRESTO REVIEWS LOG: No valid reviews to save after processing');
                    }
                } else {
                    error_log('LEBONRESTO REVIEWS LOG: No reviews found in API response or reviews array is empty');
                }
            } else {
                error_log('LEBONRESTO REVIEWS LOG: Google Places API returned no data or failed');
            }
        } else {
            error_log('LEBONRESTO REVIEWS LOG: Cannot fetch fresh data - missing requirements');
        }
        
        error_log('LEBONRESTO REVIEWS LOG: Final reviews count for display: ' . count($google_api_reviews));
        ?>

        <!-- Mobile Filter Toggle Button -->
        <div class="mobile-filter-toggle1 lg:hidden fixed z-9999">
        <button type="button" id="mobile-filter-btn" class="mobile-filter-button">
            <svg viewBox="0 0 24 24" width="20" height="20" class="filter-icon">
                <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"></path>
            </svg>
            <span class="filter-text">Filtres</span>
        </button>
        </div>

    <!-- Mobile Filter Overlay -->
    <div id="mobile-filter-overlay" class="mobile-filter-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" style="display: none;">
        <div class="mobile-filter-panel bg-white h-full w-80 max-w-[85vw] transform -translate-x-full transition-transform duration-300">
            <!-- Filter Header with Close Button -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-gradient-to-r from-yellow-400 to-yellow-500">
                <h3 class="text-lg font-semibold text-gray-800"><?php _e('Filtres', 'le-bon-resto'); ?></h3>
                <button type="button" id="close-mobile-filters" class="text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-all">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <div class="p-4 overflow-y-auto">
                <!-- Mobile Filter Form -->
                <div class="space-y-4">
                    <!-- Restaurant Name Search -->
                    <div>
                        <input 
                            type="text" 
                            id="mobile-restaurant-name" 
                            placeholder="<?php _e('Nom du restaurant...', 'le-bon-resto'); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        />
                    </div>
                    
                    <!-- City Filter -->
                    <div>
                        <input 
                            type="text" 
                            id="mobile-city" 
                            placeholder="<?php _e('Ville...', 'le-bon-resto'); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        />
                    </div>
                    
                    <!-- Cuisine Filter -->
                    <div>
                        <select 
                            id="mobile-cuisine"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        >
                            <option value=""><?php _e('Toutes les cuisines', 'le-bon-resto'); ?></option>
                            <option value="française"><?php _e('Française', 'le-bon-resto'); ?></option>
                            <option value="italienne"><?php _e('Italienne', 'le-bon-resto'); ?></option>
                            <option value="asiatique"><?php _e('Asiatique', 'le-bon-resto'); ?></option>
                            <option value="méditerranéenne"><?php _e('Méditerranéenne', 'le-bon-resto'); ?></option>
                            <option value="mexicaine"><?php _e('Mexicaine', 'le-bon-resto'); ?></option>
                            <option value="indienne"><?php _e('Indienne', 'le-bon-resto'); ?></option>
                        </select>
                    </div>
                    
                    <!-- Distance Filter -->
                    <div>
                        <select 
                            id="mobile-distance-filter"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            disabled
                        >
                            <option value=""><?php _e('Sélectionner la distance', 'le-bon-resto'); ?></option>
                            <option value="5">5 km</option>
                            <option value="10">10 km</option>
                            <option value="25">25 km</option>
                            <option value="50">50 km</option>
                            <option value="100">100 km</option>
                        </select>
                    </div>
                    
                    <!-- Sort Filter -->
                    <div>
                        <select 
                            id="mobile-sort"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        >
                            <option value="featured"><?php _e('Recommandés en premier', 'le-bon-resto'); ?></option>
                            <option value="newest"><?php _e('Plus récents', 'le-bon-resto'); ?></option>
                            <option value="distance"><?php _e('Distance', 'le-bon-resto'); ?></option>
                            <option value="name"><?php _e('Nom A-Z', 'le-bon-resto'); ?></option>
                        </select>
                    </div>
                    
                    <!-- Featured Only Toggle -->
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="mobile-featured-only" 
                            class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 focus:ring-2"
                        />
                        <span class="ml-2 text-sm text-gray-600">
                            <?php _e('Seulement les recommandés', 'le-bon-resto'); ?>
                        </span>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="space-y-3 pt-4">
                        <button 
                            id="mobile-apply-filters"
                            class="w-full px-4 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200 text-sm"
                            style="background-color: #fedc00;"
                        >
                            <?php _e('Appliquer les filtres', 'le-bon-resto'); ?>
                        </button>
                        
                        <button 
                            id="mobile-clear-all"
                            class="w-full px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-200 text-sm"
                        >
                            <?php _e('Effacer tout', 'le-bon-resto'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Desktop Filter Header (Hidden on Mobile, Tablet, and iPad) -->
        <div class="filter-header1 w-full bg-gray-100 border-b border-gray-200 sticky top-0 z-50">
            <div class="filter-container container mx-auto px-4 py-4">
                <div class="filter-form bg-white rounded-lg shadow-md p-2">
                    <div class="flex flex-col lg:flex-row items-center gap-4">
                        <!-- Restaurant Name Search -->
                        <div class="flex-1">
                            <input 
                                type="text" 
                                id="restaurant-name-filter" 
                                placeholder="<?php _e('Rechercher des restaurants...', 'le-bon-resto'); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            />
                        </div>
                        
                        <!-- City Filter -->
                        <div class="w-full lg:w-48">
                            <input 
                                type="text" 
                                id="city-filter" 
                                placeholder="<?php _e('Ville...', 'le-bon-resto'); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            />
                        </div>
                        
                        <!-- Cuisine Filter -->
                        <div class="w-full lg:w-48">
                            <select 
                                id="cuisine-filter"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            >
                                <option value=""><?php _e('Toutes les cuisines', 'le-bon-resto'); ?></option>
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
                                <span class="text-sm text-yellow-600 whitespace-nowrap">
                                    <i class="fas fa-star mr-1" style="color: #fedc00;"></i>
                                    <?php _e('Recommandé', 'le-bon-resto'); ?>
                            </span>
                            </label>
                        </div>

                        <!-- Search Button -->
                        <button 
                            id="search-restaurants"
                            class="w-full lg:w-auto px-6 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200"
                            style="background-color: #fedc00;"
                        >
                            <i class="fas fa-search mr-2"></i><?php _e('Rechercher', 'le-bon-resto'); ?>
                        </button>
                        
                        <!-- Clear Button -->
                        <button 
                            id="clear-filters"
                            class="w-full lg:w-auto px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-200"
                        >
                            <?php _e('Effacer', 'le-bon-resto'); ?>
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
                    <span class="tab-text"><?php _e('Visite virtuelle', 'le-bon-resto'); ?></span>
                </button>
                <button 
                    id="mobile-tab-map" 
                    class="mobile-tab-btn active"
                    data-tab="map"
                >
                    <i class="fas fa-map-marker-alt"></i>
                    <span class="tab-text"><?php _e('Carte', 'le-bon-resto'); ?></span>
                </button>
                        </div>
                </div>

        <!-- Line 2: Two Column Layout (50% each) -->
        <div class="two-column-layout flex-1 grid grid-cols-1 lg:grid-cols-2 min-h-screen">
            
            <!-- Left Column: Map + Gallery (50% width) -->
            <div class="left-column1 relative bg-white border-r border-gray-200 flex flex-col">
                <!-- Map Section -->
                <div id="restaurants-map" class="w-full flex-1" style="height: 60vh; min-height: 400px;">
                <!-- Map Controls -->
                <div class="button-center">
                    <button 
                        id="center-current-restaurant"
                        class="px-3 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-medium rounded text-sm transition duration-200"
                        style="background-color: #fedc00;"
                        title="<?php _e('Centrer sur le restaurant actuel', 'le-bon-resto'); ?>"
                    >
                        <i class="fas fa-crosshairs mr-1"></i><?php _e('Centrer', 'le-bon-resto'); ?>
                    </button>
                    </div>
                
                <!-- Results Counter -->
                <div class="results-counter">
                    <span id="map-results-count" class="px-3 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-medium rounded text-sm">
                        <?php _e('Chargement des restaurants...', 'le-bon-resto'); ?>
                    </span>
                </div>

                </div>
                
                            </div>
                            
            <!-- Right Column: All Sections Combined (50% width) -->
            <div class="right-column1 flex flex-col bg-white">
                
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
                                <h3 class="text-xl font-semibold text-gray-600 mb-2"><?php _e('Visite virtuelle', 'le-bon-resto'); ?></h3>
                                <p class="text-gray-500 mb-4"><?php _e('Aucune visite virtuelle disponible pour ce restaurant', 'le-bon-resto'); ?></p>
                            </div>
            </div>
                    <?php endif; ?>
                </div>
                

                </div>
                
        <!-- Current Restaurant Info (Hidden, used by JS) -->
        <script type="application/json" id="current-restaurant-data">
        <?php
        $restaurant_data = array(
            'id' => intval($current_restaurant_id),
            'title' => get_the_title(),
            'address' => $address ?: '',
            'city' => $city ?: '',
            'cuisine_type' => $cuisine_type ?: '',
            'description' => $description ?: '',
            'phone' => $phone ?: '',
            'email' => $email ?: '',
            'latitude' => $latitude ?: '',
            'longitude' => $longitude ?: '',
            'is_featured' => ($is_featured === '1'),
            'virtual_tour_url' => $virtual_tour_url ?: '',
            'link' => get_permalink(),
            'gallery_images' => $gallery_images ?: array()
        );
        
        echo wp_json_encode($restaurant_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        ?>
        </script>
        


    <?php endwhile; ?>
</div>
<!-- Two Column Layout: Restaurant Cards + Reviews -->
<div class="flex flex-col lg:flex-row gap-4 lg:gap-0">
    
    <!-- Right Column: Google Reviews (30%) -->
    <div class="w-full lg:w-3/10 bg-white rounded-lg shadow-lg overflow-hidden order-2 lg:order-2">
        
        <div id="google-reviews-container">
            <?php 
            // Debug information (remove in production)
            error_log('LEBONRESTO REVIEWS LOG: DISPLAY SECTION - Starting display logic');
            error_log('LEBONRESTO REVIEWS LOG: DISPLAY SECTION - Reviews array: ' . print_r($google_api_reviews, true));
            
            if (WP_DEBUG) {
                echo '<!-- DEBUG: google_api_reviews count: ' . (is_array($google_api_reviews) ? count($google_api_reviews) : 'not array') . ' -->';
                echo '<!-- DEBUG: google_place_id: ' . esc_html($google_place_id) . ' -->';
                echo '<!-- DEBUG: api_key available: ' . (!empty($api_key) ? 'yes' : 'no') . ' -->';
                if (!empty($google_api_reviews)) {
                    echo '<!-- DEBUG: First review data: ' . esc_html(print_r($google_api_reviews[0], true)) . ' -->';
                }
            }
            
            if (!empty($google_api_reviews)) {
                error_log('LEBONRESTO REVIEWS LOG: DISPLAY SECTION - Will display real reviews');
            } else {
                error_log('LEBONRESTO REVIEWS LOG: DISPLAY SECTION - Will display fallback/test review');
            }
            ?>
            <?php if (!empty($google_api_reviews)): ?>
                <?php 
                $review_count = 0;
                foreach (array_slice($google_api_reviews, 0, 5) as $index => $review): 
                    error_log('LEBONRESTO REVIEWS LOG: DISPLAY SECTION - Processing review ' . ($index + 1) . ': ' . print_r($review, true));
                ?>
                    <?php 
                    // Validate review data and provide fallbacks
                    $review_name = isset($review['name']) ? $review['name'] : (isset($review['author_name']) ? $review['author_name'] : 'Utilisateur anonyme');
                    $review_rating = isset($review['rating']) ? intval($review['rating']) : 0;
                    $review_text = isset($review['text']) ? $review['text'] : '';
                    $review_date = isset($review['date']) ? $review['date'] : date('Y-m-d');
                    
                    error_log('LEBONRESTO REVIEWS LOG: DISPLAY SECTION - Review ' . ($index + 1) . ' processed data: name=' . $review_name . ', rating=' . $review_rating . ', text_length=' . strlen($review_text));
                    
                    // Skip reviews with invalid data
                    if (empty($review_name) && empty($review_text) && $review_rating === 0) {
                        error_log('LEBONRESTO REVIEWS LOG: DISPLAY SECTION - Skipping review ' . ($index + 1) . ' - invalid data');
                        continue;
                    }
                    
                    $review_count++;
                    error_log('LEBONRESTO REVIEWS LOG: DISPLAY SECTION - Will display review ' . ($index + 1) . ' (display count: ' . $review_count . ')');
                    
                    // Get first letter of author name for avatar
                    $author_initial = !empty($review_name) ? strtoupper(substr($review_name, 0, 1)) : 'A';
                    
                    // Rating badge text
                    $rating_text = $review_rating >= 4 ? 'Excellent' : ($review_rating >= 3 ? 'Bien' : 'Moyen');
                    
                    // Format date
                    $formatted_date = date('j M Y', strtotime($review_date));
                    ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="review-author-section">
                                <div class="review-author-avatar"><?php echo esc_html($author_initial); ?></div>
                                <div class="review-author-info">
                                    <div class="review-author"><?php echo esc_html($review_name); ?></div>
                                    <div class="review-restaurant"><?php echo esc_html(get_the_title()); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-3 h-3 <?php echo $i <= $review_rating ? 'text-yellow-400' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="review-content">
                            <?php if (!empty($review_text)): ?>
                                <div class="review-text"><?php echo esc_html(wp_trim_words($review_text, 30)); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="review-footer">
                            <div class="review-date"><?php echo esc_html($formatted_date); ?></div>
                            <div class="review-badge"><?php echo esc_html($rating_text); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php error_log('LEBONRESTO REVIEWS LOG: DISPLAY SECTION - Completed real reviews display. Total displayed: ' . (isset($review_count) ? $review_count : 0)); ?>
            <?php else: ?>
                <?php
                error_log('LEBONRESTO REVIEWS LOG: DISPLAY SECTION - Showing test review (no real reviews available)');
                // Show a test review to verify the display is working (temporary for debugging)
                $test_review = array(
                    'name' => 'Test User',
                    'rating' => 5,
                    'text' => 'Ceci est un avis de test pour vérifier que l\'affichage fonctionne correctement.',
                    'date' => date('Y-m-d')
                );
                $review_name = $test_review['name'];
                $review_rating = $test_review['rating'];
                $review_text = $test_review['text'];
                $review_date = $test_review['date'];
                $author_initial = strtoupper(substr($review_name, 0, 1));
                $rating_text = $review_rating >= 4 ? 'Excellent' : ($review_rating >= 3 ? 'Bien' : 'Moyen');
                $formatted_date = date('j M Y', strtotime($review_date));
                ?>
                
                <!-- Test review for debugging -->
                <div class="review-item">
                    <div class="review-header">
                        <div class="review-author-section">
                            <div class="review-author-avatar"><?php echo esc_html($author_initial); ?></div>
                            <div class="review-author-info">
                                <div class="review-author"><?php echo esc_html($review_name); ?></div>
                                <div class="review-restaurant"><?php echo esc_html(get_the_title()); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="review-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg class="w-3 h-3 <?php echo $i <= $review_rating ? 'text-yellow-400' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="review-content">
                        <div class="review-text"><?php echo esc_html($review_text); ?></div>
                    </div>
                    
                    <div class="review-footer">
                        <div class="review-date"><?php echo esc_html($formatted_date); ?></div>
                        <div class="review-badge"><?php echo esc_html($rating_text); ?></div>
                    </div>
                </div>
                
                <div class="text-center py-4 mt-4 border-t border-gray-200">
                    <p class="text-gray-500 text-sm"><?php _e('Aucun avis réel disponible pour ce restaurant', 'le-bon-resto'); ?></p>
                    <?php if (empty($google_place_id)): ?>
                        <p class="text-xs text-gray-400 mt-2"><?php _e('Configurez un Google Place ID pour afficher les avis automatiquement', 'le-bon-resto'); ?></p>
                    <?php elseif (empty($api_key)): ?>
                        <p class="text-xs text-gray-400 mt-2"><?php _e('Configurez une clé API Google Maps pour récupérer les avis', 'le-bon-resto'); ?></p>
                    <?php else: ?>
                        <p class="text-xs text-gray-400 mt-2"><?php _e('Les avis seront récupérés automatiquement', 'le-bon-resto'); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Left Column: Restaurant Cards (70%) -->
    <div class="w-full lg:w-7/10 flex flex-col order-1 lg:order-1">
                                 <!-- Filter Section -->
                <div class="filter-section p-4 bg-gradient-to-r border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <select 
                                id="sort-restaurants"
                                class="px-2 py-1 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-1 focus:ring-yellow-400 bg-white shadow-sm" style="width: 100%;"
                            >
                        <option value="featured"><?php _e('Recommandés en premier', 'le-bon-resto'); ?></option>
                        <option value="newest"><?php _e('Plus récents', 'le-bon-resto'); ?></option>
                                <option value="distance"><?php _e('Distance', 'le-bon-resto'); ?></option>
                        <option value="name"><?php _e('Nom A-Z', 'le-bon-resto'); ?></option>
                            </select>
                        </div>
                        </div>
</div>

                <!-- Restaurant Cards Container -->
        <div id="restaurants-container" class="flex-1 overflow-y-auto">
                        <!-- Restaurant cards will be loaded here via JavaScript -->
                        <div class="text-center py-8">
                            <div class="loading-spinner mx-auto mb-3"></div>
                <p class="text-gray-500"><?php _e('Chargement des restaurants...', 'le-bon-resto'); ?></p>
                        </div>
                    </div>
                
                <!-- Pagination -->
                <div id="pagination-container" class="p-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                    <span id="pagination-info"><?php _e('Chargement...', 'le-bon-resto'); ?></span>
                        </div>
                        <div id="pagination-controls" class="flex items-center space-x-2">
                            <!-- Pagination buttons will be generated here by JavaScript -->
                        </div>
                    </div>
                </div>   
                    </div>
</div>



                
<script>
// Essential inline functions - main functionality moved to external JS
(function() {
    'use strict';
    
    // Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile filters
        if (typeof initializeMobileFilterHandlers === 'function') {
            initializeMobileFilterHandlers();
    }
    
    // Initialize mobile tabs
    if (typeof initializeMobileTabs === 'function') {
        initializeMobileTabs();
    }
    
    // Initialize location detection for distance filtering
    const distanceFilter = document.getElementById('distance-filter');
    const mobileDistanceFilter = document.getElementById('mobile-distance-filter');
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                window.userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                // Enable desktop distance filter
                if (distanceFilter) {
                    distanceFilter.disabled = false;
                    console.log('✅ Desktop distance filter enabled (first init)');
                }
                
                // Enable mobile distance filter
                if (mobileDistanceFilter) {
                    mobileDistanceFilter.disabled = false;
                    console.log('✅ Mobile distance filter enabled (first init)');
                }
            },
            function(error) {
                console.log('❌ Geolocation error (first init):', error);
                
                // Disable both distance filters on error
                if (distanceFilter) {
                    distanceFilter.disabled = true;
                }
                if (mobileDistanceFilter) {
                    mobileDistanceFilter.disabled = true;
                }
            }
        );
    } else {
        console.log('❌ Geolocation not supported (first init)');
        
        // Disable both distance filters if geolocation not supported
        if (distanceFilter) {
            distanceFilter.disabled = true;
        }
        if (mobileDistanceFilter) {
            mobileDistanceFilter.disabled = true;
        }
    }
});
})();

// Mobile filter functionality
function initializeMobileFilterHandlers() {
    console.log('Initializing mobile filter handlers...');
    
    const mobileFilterBtn = document.getElementById('mobile-filter-btn');
    const mobileFilterOverlay = document.getElementById('mobile-filter-overlay');
    const mobileFilterPanel = document.querySelector('.mobile-filter-panel');
    
    console.log('Elements found:', {
        btn: !!mobileFilterBtn,
        overlay: !!mobileFilterOverlay,
        panel: !!mobileFilterPanel
    });
    
    // Check if elements exist
    if (!mobileFilterBtn || !mobileFilterOverlay || !mobileFilterPanel) {
        console.error('Mobile filter elements not found!');
        return;
    }
    
    // Open mobile filter panel
    function openMobileFilter() {
        console.log('Opening mobile filter');
                mobileFilterOverlay.classList.remove('hidden');
        mobileFilterOverlay.classList.add('show');
        mobileFilterPanel.classList.add('show');
                mobileFilterPanel.classList.remove('-translate-x-full');
                document.body.style.overflow = 'hidden';
    }
    
    // Close mobile filter panel
    function closeMobileFilter() {
        console.log('Closing mobile filter');
        mobileFilterPanel.classList.remove('show');
        mobileFilterPanel.classList.add('-translate-x-full');
        setTimeout(() => {
            mobileFilterOverlay.classList.add('hidden');
            mobileFilterOverlay.classList.remove('show');
            document.body.style.overflow = '';
        }, 300);
    }
    
    // Mobile filter button click
    mobileFilterBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Mobile filter button clicked!');
        openMobileFilter();
    });
    
    // Close on overlay click
        mobileFilterOverlay.addEventListener('click', function(e) {
            if (e.target === mobileFilterOverlay) {
            console.log('Overlay clicked');
            closeMobileFilter();
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileFilterOverlay.classList.contains('show')) {
            console.log('Escape key pressed');
            closeMobileFilter();
        }
    });
    
    // Set up close button (it's added dynamically)
    setTimeout(() => {
        const closeMobileFilters = document.getElementById('close-mobile-filters');
        if (closeMobileFilters) {
            closeMobileFilters.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Close button clicked');
                closeMobileFilter();
            });
        }
    }, 100);
    
    // Sync mobile filters with desktop filters
    syncMobileFilters();
    
    // Mobile filter event listeners
    setupMobileFilterListeners();
}

// Sync mobile filters with desktop filters
function syncMobileFilters() {
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
    
    Object.keys(desktopFilters).forEach(key => {
        const desktopEl = document.getElementById(desktopFilters[key]);
        const mobileEl = document.getElementById(mobileFilters[key]);
        
        if (desktopEl && mobileEl) {
            // Sync desktop to mobile
            desktopEl.addEventListener('input', function() {
                if (mobileEl.type === 'checkbox') {
                    mobileEl.checked = this.checked;
                } else {
                    mobileEl.value = this.value;
                }
            });
            
            // Sync mobile to desktop
            mobileEl.addEventListener('input', function() {
                if (desktopEl.type === 'checkbox') {
                    desktopEl.checked = this.checked;
        } else {
                    desktopEl.value = this.value;
                }
            });
        }
    });
}

// Setup mobile filter event listeners
function setupMobileFilterListeners() {
    // Mobile search button
    const mobileSearchBtn = document.getElementById('mobile-search-restaurants');
    
    if (mobileSearchBtn) {
        mobileSearchBtn.addEventListener('click', function() {
            // Trigger desktop search
            const desktopSearchBtn = document.getElementById('search-restaurants');
            
            if (desktopSearchBtn) {
                desktopSearchBtn.click();
            }
            
            // Close mobile panel
            const mobileFilterPanel = document.querySelector('.mobile-filter-panel');
            const mobileFilterOverlay = document.getElementById('mobile-filter-overlay');
            
            if (mobileFilterPanel && mobileFilterOverlay) {
                mobileFilterPanel.classList.add('-translate-x-full');
                setTimeout(() => {
                    mobileFilterOverlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }, 300);
            }
        });
    }
    
    // Mobile clear button
    const mobileClearBtn = document.getElementById('mobile-clear-filters');
    
    if (mobileClearBtn) {
        mobileClearBtn.addEventListener('click', function() {
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
        } else {
                        element.value = '';
                    }
                }
            });
            
            // Trigger desktop clear
            const desktopClearBtn = document.getElementById('clear-filters');
            
            if (desktopClearBtn) {
                desktopClearBtn.click();
            }
        });
    }
}

// Handle tab click
function handleTabClick(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Only work on mobile devices
    if (window.innerWidth > 1023) {
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
    const leftColumn = document.querySelector('.left-column1');
    const rightColumn = document.querySelector('.right-column1');
    const tabButtons = document.querySelectorAll('.mobile-tab-btn');
    
    // Update button states
    tabButtons.forEach(btn => {
        btn.classList.remove('active', 'border-yellow-400', 'bg-yellow-50');
        btn.classList.add('border-transparent');
    });
    
    this.classList.add('active', 'border-yellow-400', 'bg-yellow-50');
    this.classList.remove('border-transparent');
    
    // Show/hide entire columns based on tab (mobile only)
    if (tabType === 'vr') {
        // Hide left column (map)
        if (leftColumn) {
            leftColumn.style.setProperty('display', 'none', 'important');
        }
        
        // Show right column (VR)
        if (rightColumn) {
            rightColumn.style.setProperty('display', 'flex', 'important');
            rightColumn.style.setProperty('visibility', 'visible', 'important');
            rightColumn.style.setProperty('opacity', '1', 'important');
            rightColumn.style.setProperty('width', '100%', 'important');
            rightColumn.style.setProperty('flex', '1', 'important');
        }
        
        // Also show the VR content specifically
        if (vrContent) {
            vrContent.style.setProperty('display', 'block', 'important');
            vrContent.style.setProperty('visibility', 'visible', 'important');
            vrContent.style.setProperty('opacity', '1', 'important');
            vrContent.style.setProperty('position', 'relative', 'important');
            vrContent.style.setProperty('z-index', '10', 'important');
        }
        
    } else if (tabType === 'map') {
        // Show left column (map)
        if (leftColumn) {
            leftColumn.style.setProperty('display', 'flex', 'important');
            leftColumn.style.setProperty('visibility', 'visible', 'important');
            leftColumn.style.setProperty('opacity', '1', 'important');
            leftColumn.style.setProperty('width', '100%', 'important');
            leftColumn.style.setProperty('flex', '1', 'important');
        }
        
        // Hide right column (VR)
        if (rightColumn) {
            rightColumn.style.setProperty('display', 'none', 'important');
        }
        
        // Also hide the VR content specifically
        if (vrContent) {
            vrContent.style.setProperty('display', 'none', 'important');
            vrContent.style.setProperty('visibility', 'hidden', 'important');
            vrContent.style.setProperty('opacity', '0', 'important');
        }
    }
}

// Initialize mobile tab system
function initializeMobileTabs() {
    // Wait a bit for DOM to be ready
    setTimeout(() => {
        const tabButtons = document.querySelectorAll('.mobile-tab-btn');
        const vrContent = document.querySelector('.virtual-tour-section');
        const mapContent = document.querySelector('#restaurants-map');
        const leftColumn = document.querySelector('.left-column1');
        const rightColumn = document.querySelector('.right-column1');
        
        if (tabButtons.length === 0) {
            return;
        }
    
        // Set default view to Map on mobile only
        if (window.innerWidth <= 1023) {
            
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
        
        // Test direct button access
        const vrButton = document.getElementById('mobile-tab-vr');
        const mapButton = document.getElementById('mobile-tab-map');
        
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
    const mobileDistanceFilter = document.getElementById('mobile-distance-filter');
    
    // Initialize mobile tab system with a small delay to ensure DOM is ready
    setTimeout(() => {
        initializeMobileTabs();
    }, 100);
    
    // Apply essential styles
    const rightColumn = document.querySelector('.right-column1');
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
        const rightColumn = document.querySelector('.right-column1');
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
                
                // Enable desktop distance filter
                if (distanceFilter) {
                    distanceFilter.disabled = false;
                    console.log('✅ Desktop distance filter enabled');
                }
                
                // Enable mobile distance filter
                if (mobileDistanceFilter) {
                    mobileDistanceFilter.disabled = false;
                    console.log('✅ Mobile distance filter enabled');
                }
            },
            function(error) {
                console.log('❌ Geolocation error:', error);
                
                // Disable both distance filters on error
                if (distanceFilter) {
                    distanceFilter.disabled = true;
                }
                if (mobileDistanceFilter) {
                    mobileDistanceFilter.disabled = true;
                }
            }
        );
    } else {
        console.log('❌ Geolocation not supported');
        
        // Disable both distance filters if geolocation not supported
        if (distanceFilter) {
            distanceFilter.disabled = true;
        }
        if (mobileDistanceFilter) {
            mobileDistanceFilter.disabled = true;
        }
    }
});

// Also initialize on window load as backup
window.addEventListener('load', function() {
    console.log('🔧 [MOBILE TABS] Window loaded, re-initializing mobile tabs...');
    setTimeout(() => {
        initializeMobileTabs();
    }, 200);
});

// Syntax validation check
if (typeof window !== 'undefined') {
    console.log('✅ Single restaurant JavaScript loaded successfully');
}
</script>

<?php
// Localize script data
wp_localize_script(
    'lebonresto-single-js',
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
            'emailTitle' => __('Email restaurant', 'le-bon-resto'),
            'kmAway' => __('%s km away', 'le-bon-resto'),
            'loadingRestaurants' => __('Loading restaurants...', 'le-bon-resto'),
            'restaurantsFound' => __('%s restaurants found', 'le-bon-resto'),
            'centerOnCurrent' => __('Centrer sur le restaurant actuel', 'le-bon-resto'),
            'googleReviews' => __('Avis Google', 'le-bon-resto'),
            'loadingReviews' => __('Chargement des avis...', 'le-bon-resto'),
            'noReviews' => __('Aucun avis disponible', 'le-bon-resto'),
            'reviewsFrom' => __('Avis de %s', 'le-bon-resto'),
        )
    )
);

// Google Reviews are now loaded directly via PHP for the current restaurant
// No additional JavaScript loading needed for reviews display
?>

<!-- DEBUG MARKER: Line reference for JavaScript error debugging -->

<style>
/* Clean Mobile Filter Panel CSS - No Duplicates */
/* Main Layout Styles */
.lebonresto-single-layout {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif !important;
    background: linear-gradient(135deg, var(--bg-gray-50) 0%, var(--border-color) 100%) !important;
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
    background-color: var(--bg-white) !important;
    border-right: 3px solid var(--border-color) !important;
    position: relative !important;
    z-index: 1 !important;
}

@media (max-width: 1023px) {
    #restaurants-map {
        border-right: none !important;
        border-bottom: 3px solid var(--bg-white) !important;
    }
}

/* Virtual Tour Section */
.virtual-tour-section {
    height: 64vh !important;
    border-bottom: 3px solid var(--bg-white) !important;
    background: linear-gradient(135deg, var(--bg-white) 0%, var(--bg-white) 100%) !important;
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
    background: linear-gradient(135deg, var(--bg-white) 0%, var(--bg-white) 100%) !important;
    border-radius: var(--radius-lg) !important;
    box-shadow: var(--shadow-lg) !important;
    padding: 20px !important;
    border: 2px solid rgba(254, 220, 0, 0.1) !important;
}

.filter-form input,
.filter-form select {
    height: 48px !important;
    border: 2px solid var(--border-color) !important;
    border-radius: var(--radius-lg) !important;
    font-size: 16px !important;
    padding: 12px 16px !important;
    transition: var(--transition) !important;
    background-color: #fdffb9 !important;
    color: var(--text-primary) !important;
}

.filter-form input:focus,
.filter-form select:focus {
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 3px rgba(254, 220, 0, 0.1) !important;
    outline: none !important;
    transform: translateY(-1px) !important;
}

.filter-form button {
    height: 48px !important;
    border-radius: var(--radius-lg) !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    padding: 14px 24px !important;
    transition: var(--transition) !important;
    border: none !important;
    cursor: pointer !important;
    box-shadow: var(--shadow-md) !important;
}

#search-restaurants {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-color) 100%) !important;
    color: var(--text-primary) !important;
    font-weight: 700 !important;
}

#search-restaurants:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-dark) 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(254, 220, 0, 0.4) !important;
}

#clear-filters {
    background: linear-gradient(135deg, var(--bg-gray-100) 0%, var(--border-color) 100%) !important;
    color: var(--text-secondary) !important;
    border: 2px solid var(--bg-white) !important;
}

#clear-filters:hover {
    background: linear-gradient(135deg, var(--border-color) 0%, var(--border-color) 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: var(--shadow-lg) !important;
}

/* Restaurant Cards */
.restaurant-card {
    background: linear-gradient(135deg, var(--bg-white) 0%, var(--bg-gray-50) 100%) !important;
    border-radius: var(--radius-lg) !important;
    box-shadow: var(--shadow-md) !important;
    margin-bottom: 16px !important;
    border: 2px solid var(--bg-white) !important;
    transition: all 0.4s ease !important;
    cursor: pointer !important;
    position: relative !important;
    overflow: hidden !important;
    width: 90% !important;
}

.restaurant-card:hover {
    box-shadow: var(--shadow-lg) !important;
    transform: translateY(-4px) scale(1.02) !important;
    border-color: var(--primary-color) !important;
}


/* Loading Spinner */
.loading-spinner {
    border: 4px solid var(--bg-gray-100) !important;
    border-top: 4px solid var(--primary-color) !important;
    border-right: 4px solid var(--primary-color) !important;
    border-radius: 50% !important;
    width: 32px !important;
    height: 32px !important;
    animation: spin 1.2s ease-in-out infinite !important;
    display: inline-block !important;
    box-shadow: 0 2px 8px rgba(254, 220, 0, 0.3) !important;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Map Controls */
#center-current-restaurant {
    padding: 12px 18px !important;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-color) 100%) !important;
    color: var(--text-primary) !important;
    border: none !important;
    border-radius: var(--radius-md) !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: var(--transition) !important;
    box-shadow: 0 2px 8px rgba(254, 220, 0, 0.3) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 44px !important;
}

#center-current-restaurant:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-dark) 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 16px rgba(254, 220, 0, 0.4) !important;
}

#map-results-count {
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px) !important;
    padding: 8px 16px !important;
    border-radius: var(--radius-md) !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    color: var(--text-secondary) !important;
    box-shadow: var(--shadow-lg) !important;
    border: 2px solid rgba(254, 220, 0, 0.2) !important;
}

/* Custom scrollbar */
#restaurants-list::-webkit-scrollbar {
    width: 6px;
}

#restaurants-list::-webkit-scrollbar-track {
    background: var(--bg-gray-100);
    border-radius: var(--radius-sm);
}

#restaurants-list::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: var(--radius-sm);
}

#restaurants-list::-webkit-scrollbar-thumb:hover {
    background: var(--primary-dark);
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
    color:rgb(255, 255, 255) !important;
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
    color:rgb(255, 255, 255) !important;
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
.filter-header1 {
    display: none !important;
}

@media (min-width: 1280px) {
    .filter-header1 {
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
    .left-column1 {
        position: relative !important;
    }
    
    .right-column1 {
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

.mobile-filter-toggle1 button {
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
    width: 120px !important;
    height: 48px !important;
    font-size: 18px !important;
    font-weight: bold !important;
}

.mobile-filter-toggle1 button:hover {
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

/* Mobile Filter Panel - Essential Styles Only */
.mobile-filter-panel {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
}

.mobile-filter-panel .space-y-4 > div {
    margin-bottom: 20px !important;
}

.mobile-filter-panel input,
.mobile-filter-panel select {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 12px !important;
    padding: 14px 16px !important;
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #1f2937 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.mobile-filter-panel input:focus,
.mobile-filter-panel select:focus {
    outline: none !important;
    border-color: #fedc00 !important;
    box-shadow: 0 0 0 4px rgba(254, 220, 0, 0.15), 0 4px 12px rgba(254, 220, 0, 0.1) !important;
    background: #ffffff !important;
    transform: translateY(-1px) !important;
}

.mobile-filter-panel input::placeholder {
    color: #9ca3af !important;
    font-weight: 400 !important;
}

.mobile-filter-panel select {
    cursor: pointer !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e") !important;
    background-position: right 12px center !important;
    background-repeat: no-repeat !important;
    background-size: 16px !important;
    padding-right: 40px !important;
}

.mobile-filter-panel input:hover,
.mobile-filter-panel select:hover {
    border-color: #fbbf24 !important;
    box-shadow: 0 2px 8px rgba(254, 220, 0, 0.1) !important;
}

.mobile-filter-panel input[type="checkbox"] {
    width: 20px !important;
    height: 20px !important;
    accent-color: #fedc00 !important;
    margin-right: 12px !important;
    cursor: pointer !important;
}

.mobile-filter-panel .flex.items-center {
    padding: 12px 0 !important;
}

.mobile-filter-panel .flex.items-center span {
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #374151 !important;
}

.mobile-filter-panel button {
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    border-radius: 12px !important;
    font-size: 14px !important;
    padding: 16px 20px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.mobile-filter-panel button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;
}

.mobile-filter-panel button:active {
    transform: translateY(0) !important;
}

.mobile-filter-panel button[style*="background-color: #fedc00"] {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    color: #1f2937 !important;
    border: none !important;
}

.mobile-filter-panel button[style*="background-color: #fedc00"]:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #fedc00 100%) !important;
    box-shadow: 0 4px 16px rgba(254, 220, 0, 0.3) !important;
}

.mobile-filter-panel .bg-gray-200 {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
    color: #4b5563 !important;
    border: 2px solid #d1d5db !important;
}

.mobile-filter-panel .bg-gray-200:hover {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    color: white !important;
    border-color: #ef4444 !important;
}

.mobile-filter-panel .flex.items-center.justify-between {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    padding: 20px !important;
    border-bottom: 2px solid rgba(0, 0, 0, 0.1) !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
    display: flex;
    gap: 170px;
}

.mobile-filter-panel h3 {
    color: #1f2937 !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    margin: 0 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
}

#close-mobile-filters {
    width: 44px !important;
    height: 44px !important;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.2) !important;
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
    color: #1f2937 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    backdrop-filter: blur(10px) !important;
}

#close-mobile-filters:hover {
    background: rgba(255, 255, 255, 0.3) !important;
    border-color: rgba(255, 255, 255, 0.5) !important;
    transform: scale(1.1) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
}

#close-mobile-filters:active {
    transform: scale(0.95) !important;
}

#close-mobile-filters svg {
    width: 24px !important;
    height: 24px !important;
    stroke: currentColor !important;
    stroke-width: 2.5 !important;
    fill: none !important;
}

.mobile-filter-panel .p-4 {
    padding: 24px !important;
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
}

.mobile-filter-panel .space-y-4 > div {
    animation: slideInUp 0.3s ease-out !important;
    animation-fill-mode: both !important;
}

.mobile-filter-panel .space-y-4 > div:nth-child(1) { animation-delay: 0.1s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(2) { animation-delay: 0.15s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(3) { animation-delay: 0.2s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(4) { animation-delay: 0.25s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(5) { animation-delay: 0.3s !important; }
.mobile-filter-panel .space-y-4 > div:nth-child(6) { animation-delay: 0.35s !important; }

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.mobile-filter-panel::-webkit-scrollbar {
    width: 6px !important;
}

.mobile-filter-panel::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05) !important;
    border-radius: 3px !important;
}

.mobile-filter-panel::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    border-radius: 3px !important;
}

.mobile-filter-panel::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #fedc00 100%) !important;
}

.mobile-filter-panel .space-y-4 > div {
    margin-bottom: 20px !important;
}

.mobile-filter-panel input,
.mobile-filter-panel select {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 12px !important;
    padding: 14px 16px !important;
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #1f2937 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.mobile-filter-panel input:focus,
.mobile-filter-panel select:focus {
    outline: none !important;
    border-color: #fedc00 !important;
    box-shadow: 0 0 0 4px rgba(254, 220, 0, 0.15), 0 4px 12px rgba(254, 220, 0, 0.1) !important;
    background: #ffffff !important;
    transform: translateY(-1px) !important;
}

.mobile-filter-panel input::placeholder {
    color: #9ca3af !important;
    font-weight: 400 !important;
}

.mobile-filter-panel select {
    cursor: pointer !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e") !important;
    background-position: right 12px center !important;
    background-repeat: no-repeat !important;
    background-size: 16px !important;
    padding-right: 40px !important;
}

.mobile-filter-panel input:hover,
.mobile-filter-panel select:hover {
    border-color: #fbbf24 !important;
    box-shadow: 0 2px 8px rgba(254, 220, 0, 0.1) !important;
}

/* Checkbox styling */
.mobile-filter-panel input[type="checkbox"] {
    width: 20px !important;
    height: 20px !important;
    accent-color: #fedc00 !important;
    margin-right: 12px !important;
    cursor: pointer !important;
}

.mobile-filter-panel .flex.items-center {
    padding: 12px 0 !important;
}

.mobile-filter-panel .flex.items-center span {
    font-size: 15px !important;
    font-weight: 500 !important;
    color: #374151 !important;
}

/* Button styling */
.mobile-filter-panel button {
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    border-radius: 12px !important;
    font-size: 14px !important;
    padding: 16px 20px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.mobile-filter-panel button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;
}

.mobile-filter-panel button:active {
    transform: translateY(0) !important;
}

/* Apply button specific styling */
.mobile-filter-panel button[style*="background-color: #fedc00"] {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    color: #1f2937 !important;
    border: none !important;
}

.mobile-filter-panel button[style*="background-color: #fedc00"]:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #fedc00 100%) !important;
    box-shadow: 0 4px 16px rgba(254, 220, 0, 0.3) !important;
}

/* Clear button specific styling */
.mobile-filter-panel .bg-gray-200 {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
    color: #4b5563 !important;
    border: 2px solid #d1d5db !important;
}

.mobile-filter-panel .bg-gray-200:hover {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    color: white !important;
    border-color: #ef4444 !important;
}

/* Mobile Filter Panel Header */
.mobile-filter-panel .flex.items-center.justify-between {
    background: linear-gradient(135deg, #fedc00 0%, #f59e0b 100%) !important;
    padding: 20px !important;
    border-bottom: 2px solid rgba(0, 0, 0, 0.1) !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
    display: flex;
    gap: 170px;
}

.mobile-filter-panel h3 {
    color: #1f2937 !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    margin: 0 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
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
    .filter-header1 {
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
    .right-column1 {
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
    .right-column1 * {
        overflow: visible !important;
        max-height: none !important;
    }
    
    /* Specifically target restaurants container */
    .right-column1 #restaurants-container {
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

/* Restaurant Cards Layout - Redesigned to match all restaurants page */
#restaurants-list {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
    gap: 16px !important;
    padding: 16px !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    max-height: 60vh !important;
}

/* Single restaurant page card overrides */
.restaurant-card {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    border: 2px solid #ffffff !important;
    transition: all 0.4s ease !important;
    overflow: hidden !important;
    height: auto !important;
    width: 100% !important;
    min-width: auto !important;
    max-width: none !important;
}

.restaurant-card:hover {
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-4px) scale(1.02) !important;
    border-color: #fbbf24 !important;
}

.restaurant-card.current-restaurant {
    border-color: #fbbf24 !important;
    box-shadow: 0 8px 25px rgba(251, 191, 36, 0.3) !important;
}

/* Card layout for single restaurant page */
.restaurant-card .card-layout {
    flex-direction: column !important;
    height: 100% !important;
}

.restaurant-card .card-image {
    width: 100% !important;
    height: 140px !important;
    position: relative !important;
    border-radius: 12px 12px 0 0 !important;
    overflow: hidden !important;
    background: #f3f4f6 !important;
}

.restaurant-card .restaurant-image {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    transition: transform 0.3s ease !important;
}

.restaurant-card .card-content {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    padding: 16px !important;
    justify-content: space-between !important;
}

.restaurant-card .card-actions {
    display: flex !important;
    gap: 8px !important;
    align-items: center !important;
    margin-top: auto !important;
}

/* Mobile responsive overrides for single restaurant cards */
@media (max-width: 768px) {
    #restaurants-list {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
        padding: 12px !important;
        max-height: 50vh !important;
    }
    
    .restaurant-card {
        min-height: 220px !important;
    }
    
    .restaurant-card .card-image {
        height: 120px !important;
        border-radius: 12px 12px 0 0 !important;
    }
}

/* Current restaurant highlighting */
.restaurant-card.current-restaurant .restaurant-name a {
    color: #fbbf24 !important;
    font-weight: 700 !important;
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

/* Two Column Layout Styles */
.w-7\/10 {
    width: 70% !important;
}

.w-3\/10 {
    width: 30% !important;
}

/* Google Reviews Section */
#google-reviews-container {
    max-height: 60vh !important;
    overflow-y: auto !important;
    padding: 4px !important;
}

.review-item {
    background: linear-gradient(135deg, var(--bg-white) 0%, var(--bg-gray-50) 100%) !important;
    border: 2px solid var(--bg-white) !important;
    border-radius: var(--radius-lg) !important;
    padding: 16px !important;
    margin-bottom: 16px !important;
    transition: all 0.4s ease !important;
    box-shadow: var(--shadow-md) !important;
    position: relative !important;
    overflow: hidden !important;
    cursor: pointer !important;
}

.review-item:hover {
    box-shadow: var(--shadow-lg) !important;
    transform: translateY(-4px) scale(1.02) !important;
    border-color: var(--primary-color) !important;
}

.review-item::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    height: 3px !important;
    background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
    opacity: 0 !important;
    transition: opacity 0.3s ease !important;
}

.review-item:hover::before {
    opacity: 1 !important;
}

.review-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    margin-bottom: 12px !important;
}

.review-author-section {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.review-author-avatar {
    width: 32px !important;
    height: 32px !important;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: var(--text-primary) !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    box-shadow: 0 2px 8px rgba(254, 220, 0, 0.3) !important;
}

.review-author-info {
    flex: 1 !important;
}

.review-author {
    font-weight: 600 !important;
    color: var(--text-primary) !important;
    font-size: 14px !important;
    margin-bottom: 2px !important;
    line-height: 1.2 !important;
}

.review-restaurant {
    color: var(--primary-color) !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

.review-rating {
    display: flex !important;
    align-items: center !important;
    gap: 2px !important;
    margin-bottom: 12px !important;
}

.review-rating svg {
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1)) !important;
}

.review-content {
    margin-bottom: 12px !important;
}

.review-text {
    color: var(--text-secondary) !important;
    font-size: 13px !important;
    line-height: 1.6 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 4 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    text-align: justify !important;
}

.review-footer {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding-top: 8px !important;
    border-top: 1px solid var(--border-light) !important;
}

.review-date {
    color: var(--text-muted) !important;
    font-size: 11px !important;
    font-weight: 500 !important;
}

.review-badge {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
    color: var(--text-primary) !important;
    font-size: 9px !important;
    font-weight: 700 !important;
    padding: 2px 6px !important;
    border-radius: 12px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    box-shadow: 0 1px 3px rgba(254, 220, 0, 0.3) !important;
}

/* Reviews container header */
.reviews-header {
    background: linear-gradient(135deg, var(--bg-white) 0%, var(--bg-gray-50) 100%) !important;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0 !important;
    padding: 20px !important;
    border-bottom: 2px solid var(--border-light) !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
    backdrop-filter: blur(10px) !important;
}

.reviews-title {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-bottom: 4px !important;
}

.reviews-title svg {
    filter: drop-shadow(0 2px 4px rgba(254, 220, 0, 0.3)) !important;
}

.reviews-subtitle {
    color: var(--text-muted) !important;
    font-size: 12px !important;
    font-weight: 500 !important;
}

/* Custom scrollbar for reviews */
#google-reviews-container::-webkit-scrollbar {
    width: 6px;
}

#google-reviews-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#google-reviews-container::-webkit-scrollbar-thumb {
    background: #fedc00;
    border-radius: 3px;
}

#google-reviews-container::-webkit-scrollbar-thumb:hover {
    background: #e6c200;
}

/* Two Column Layout Container */
.mobile-cards-section {
    height: calc(100vh - 200px) !important;
    overflow: hidden !important;
}

.mobile-cards-section > .flex {
    height: 100% !important;
}

/* Left Column (Restaurant Cards) */
.w-7\/10 {
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
}

/* Right Column (Reviews) */
.w-3\/10 {
    height: 100% !important;
    max-height: 100% !important;
}

/* Ensure restaurants container takes remaining space */
#restaurants-container {
    flex: 1 !important;
    overflow-y: auto !important;
    min-height: 0 !important;
}

/* Mobile Responsive Fixes */
@media (max-width: 768px) {
    /* Stack columns vertically on mobile */
    .mobile-cards-section .flex {
        flex-direction: column !important;
    }
    
    .w-7\/10, .w-3\/10 {
        width: 100% !important;
        height: auto !important;
    }
    
    /* Hide reviews section on mobile to save space */
    .w-3\/10 {
        display: none !important;
    }
    
    .mobile-cards-section {
        height: auto !important;
    }
    
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