<?php
/**
 * All Restaurants Page Template
 * 
 * @package LeBonResto
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

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
        'perPage' => 12,
        'showPagination' => true,
        'showSorting' => true,
        'showFilters' => true,
        'settings' => array(
            'defaultRadius' => isset($options['default_radius']) ? intval($options['default_radius']) : 25,
            'maxRadius' => isset($options['max_radius']) ? intval($options['max_radius']) : 100,
            'primaryColor' => isset($options['primary_color']) ? $options['primary_color'] : '#fedc00',
        ),
    )
);

// Get cuisine types
$cuisine_types = lebonresto_get_cuisine_types();
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e('Tous les Restaurants', 'le-bon-resto'); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-gray-50'); ?>>
    <?php wp_body_open(); ?>

    <div class="lebonresto-all-restaurants bg-gray-50 min-h-screen">
        <div class="container mx-auto px-4 py-8" style="max-width: 1400px;">
            
            <!-- Mobile Filter Toggle Button (Single Button) -->
            <div class="mobile-filter-toggle lg:hidden fixed top-4 right-4 z-50">
                <button 
                    id="mobile-filter-btn"
                    class="bg-yellow-400 hover:bg-yellow-500 text-gray-800 p-3 rounded-full shadow-lg transition-all duration-300 transform hover:scale-105"
                    style="background-color: #fedc00;"
                    title="Basculer les Filtres"
                >
                    <!-- Menu Icon (Default) -->
                    <div class="menu-icon">
                        <span style="font-size: 18px; font-weight: bold;">☰</span>
                    </div>
                    <!-- Close Icon (When Panel Open) -->
                    <div class="close-icon">
                        <span style="font-size: 18px; font-weight: bold;">✕</span>
                    </div>
                </button>
            </div>

            <!-- Mobile Filter Overlay -->
            <div id="mobile-filter-overlay" class="mobile-filter-overlay hidden"></div>

            <!-- Mobile Filter Panel -->
            <div id="mobile-filter-panel" class="mobile-filter-panel bg-white h-full w-80 transform transition-transform duration-300 -translate-x-full">
                <div class="p-4">
                    <!-- Mobile Filter Form -->
                    <div class="space-y-4" style="text-align: center;">
                        <!-- Restaurant Name Search -->
                        <div>
                            <input type="text" id="mobile-restaurant-name-filter" placeholder="Rechercher des restaurants..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                        </div>
                        
                        <!-- City Filter -->
                        <div>
                            <input type="text" id="mobile-city-filter" placeholder="Ville..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                        </div>
                        
                        <!-- Cuisine Filter -->
                        <div>
                            <select id="mobile-cuisine-filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                                <option value="">Toutes les Cuisines</option>
                                <?php foreach ($cuisine_types as $cuisine): ?>
                                    <option value="<?php echo esc_attr($cuisine); ?>">
                                        <?php echo esc_html(ucfirst($cuisine)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Distance Filter -->
                        <div>
                            <select id="mobile-distance-filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent" disabled>
                                <option value="">Sélectionner la distance</option>
                                <option value="5">5 km</option>
                                <option value="10">10 km</option>
                                <option value="25">25 km</option>
                                <option value="50">50 km</option>
                                <option value="100">100 km</option>
                            </select>
                        </div>
                        
                        <!-- Featured Only Toggle -->
                        <div class="flex items-center">
                            <input type="checkbox" id="mobile-featured-only" class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 focus:ring-2">
                            <span class="ml-2 text-sm text-gray-700">
                                En Vedette Seulement
                            </span>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-3 pt-4">
                            <button id="mobile-search-restaurants" class="w-full px-4 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200 text-sm" style="background-color: #fedc00;">
                                Appliquer les Filtres
                            </button>
                            
                            <button id="mobile-clear-filters" class="w-full px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-200 text-sm">
                                Tout Effacer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout: Left Filters + Right Cards -->
            <div class="two-column-layout">
                <!-- Left Column - Filters (30%) -->
                <div class="left-column">
                    <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                        
                        
                        <!-- Restaurant Name Search -->
                        <div class="form-group-floating">
                            <input 
                                type="text" 
                                id="restaurant-name-filter" 
                                class="floating-input"
                                required
                            />
                            <label for="restaurant-name-filter" class="floating-label">
                                <i class="fas fa-search mr-2"></i>
                                <?php _e('Rechercher des Restaurants', 'le-bon-resto'); ?>
                            </label>
                        </div>
                        
                        <!-- City Filter -->
                        <div class="form-group-floating">
                            <input 
                                type="text" 
                                id="city-filter" 
                                class="floating-input"
                                required
                            />
                            <label for="city-filter" class="floating-label">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <?php _e('Ville', 'le-bon-resto'); ?>
                            </label>
                        </div>
                        
                        <!-- Cuisine Filter -->
                        <div class="form-group-floating">
                            <div class="select-container">
                                <select id="cuisine-filter" class="floating-select" multiple>
                                    <?php foreach ($cuisine_types as $cuisine): ?>
                                        <option value="<?php echo esc_attr($cuisine); ?>">
                                            <?php echo esc_html(ucfirst($cuisine)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="cuisine-filter" class="floating-select-label">
                                    <i class="fas fa-utensils mr-2"></i>
                                    <?php _e('Types de Cuisine', 'le-bon-resto'); ?>
                                </label>
                                <div class="select-arrow">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Distance Filter -->
                        <div class="form-group-floating">
                            <div class="distance-filter-container">
                                <label class="distance-filter-label">
                                    <i class="fas fa-route mr-2"></i>
                                    <?php _e('Plage de Distance', 'le-bon-resto'); ?>
                                    <span id="location-status" class="ml-2 text-xs text-gray-500">
                                        <i class="fas fa-spinner fa-spin"></i> Détection de la localisation...
                                    </span>
                                </label>
                                <div class="distance-buttons">
                                    <button type="button" class="distance-btn" data-distance="1">1 km</button>
                                    <button type="button" class="distance-btn" data-distance="5">5 km</button>
                                    <button type="button" class="distance-btn" data-distance="10">10 km</button>
                                    <button type="button" class="distance-btn" data-distance="25">25 km</button>
                                    <button type="button" class="distance-btn" data-distance="50">50 km</button>
                                    <button type="button" class="distance-btn" data-distance="100">100 km</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Featured Only Toggle -->
                        <div class="form-group-small">
                            <label class="checkbox-label-small">
                                <input type="checkbox" id="featured-only" />
                                <i class="fas fa-star"></i>
                                <span><?php _e('En Vedette Seulement', 'le-bon-resto'); ?></span>
                            </label>
                        </div>
                        
                        <!-- Clear Button -->
                        <div class="button-group">
                            <button id="clear-filters" class="btn-secondary">
                                <i class="fas fa-eraser mr-2"></i>
                                <?php _e('Effacer les Filtres', 'le-bon-resto'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Cards (70%) -->
                <div class="right-column">
                    
                    <!-- Fixed Filter Section -->
                    <div class="filter-section sticky top-0 z-40 bg-white border-b border-gray-200 p-4 mb-4">
                <!-- Sorting Icon with Dropdown -->
                        <div class="relative">                         
                         <!-- Dropdown Menu -->
                            <div id="sort-dropdown-menu" class="absolute right-0 top-12 z-50 hidden bg-white border border-gray-300 rounded-lg shadow-lg">
                                <select id="sort-select" class="w-full px-3 py-2 text-sm text-gray-700 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
                                 <option value="featured">En Vedette d'Abord</option>
                                 <option value="newest">Plus Récent</option>
                                 <option value="distance">Distance</option>
                                 <option value="name">Nom A-Z</option>
                             </select>
                            </div>
                         </div>
                     </div>
                    
                    <!-- Restaurant Cards List with Scroll -->
                    <div class="restaurants-container">
                        <div id="restaurants-list" class="space-y-4">
                        <!-- Cards will be loaded here via JavaScript -->
                        <div class="text-center py-12">
                            <div class="loading-spinner mx-auto mb-4"></div>
                            <p class="text-gray-500"><?php _e('Chargement des restaurants...', 'le-bon-resto'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fixed Pagination Bar -->
                    <div id="pagination-container" class="pagination-bar sticky bottom-0 z-40 bg-white border-t border-gray-200 p-4">
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
        </div>
    </div>

    <style>
        /* FontAwesome CDN */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
        
        /* Two Column Layout */
        .two-column-layout {
            display: flex !important;
            gap: 2rem !important;
            width: 100% !important;
        }

        .left-column {
            width: 30% !important;
            flex-shrink: 0 !important;
            height: 90vh !important;
            margin-top: 20px !important;

        }

        .right-column {
            width: 70% !important;
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            height: 100vh !important;
            position: relative !important;
        }

        /* Mobile Filter Styles */
        .mobile-filter-toggle {
            position: fixed !important;
            top: 22px !important;
            right: 15px !important;
            z-index: 50 !important;
            display: block !important;
            transition: all 0.3s ease !important;
        }

        /* Icon Styles */
        .menu-icon, .close-icon {
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            height: 100% !important;
        }

        .menu-icon {
            display: flex !important;
        }

        .close-icon {
            display: none !important;
        }

        .mobile-filter-toggle.open .menu-icon {
            display: none !important;
        }

        .mobile-filter-toggle.open .close-icon {
            display: flex !important;
        }

        /* Text Icon Styles */
        .menu-icon span, .close-icon span {
            font-size: 18px !important;
            font-weight: bold !important;
            color: currentColor !important;
            display: block !important;
            line-height: 1 !important;
        }

        .mobile-filter-toggle button {
            position: relative !important;
            width: 48px !important;
            height: 48px !important;
            margin: 0 !important;
            padding: 0 !important;
            background: rgba(255, 255, 255, 0.95) !important;
            border: 2px solid #fedc00 !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            transition: all 0.3s ease !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .mobile-filter-toggle button:hover {
            background: #fedc00 !important;
            transform: scale(1.05) !important;
        }

        /* Filter Icon Animation */
        .filter-icon {
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: center !important;
            width: 20px !important;
            height: 20px !important;
        }

        .filter-line {
            width: 18px !important;
            height: 2px !important;
            background-color: #1f2937 !important;
            margin: 2px 0 !important;
            transition: all 0.3s ease !important;
            border-radius: 1px !important;
        }

        .filter-text {
            transition: all 0.3s ease !important;
        }

        /* Icon states */
        .mobile-filter-toggle button.open .filter-icon {
            display: flex !important;
        }

        .mobile-filter-toggle button.open .filter-text {
            display: none !important;
        }

        .mobile-filter-toggle button.open .filter-line:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px) !important;
        }

        .mobile-filter-toggle button.open .filter-line:nth-child(2) {
            opacity: 0 !important;
        }

        .mobile-filter-toggle button.open .filter-line:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px) !important;
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
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            width: 320px !important;
            background: white !important;
            transform: translateX(-100%) !important;
            transition: transform 0.3s ease !important;
            overflow-y: auto !important;
            z-index: 41 !important;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1) !important;
            justify-items: center !important;
            align-content: center;

        }

        .mobile-filter-panel:not(.-translate-x-full) {
            transform: translateX(0) !important;
        }

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

        /* Mobile Form Elements - Add margin-y to all */
        .mobile-filter-panel .space-y-4 > div {
            margin-top: 12px !important;
            margin-bottom: 12px !important;
        }

        /* Mobile Checkbox Styling */
        .mobile-filter-panel .flex.items-center {
            display: flex !important;
            align-items: center !important;
            margin-top: 12px !important;
            margin-bottom: 12px !important;
        }

        .mobile-filter-panel input[type="checkbox"] {
            width: 20px !important;
            height: 20px !important;
            border-radius: 6px !important;
            border: 2px solid #d1d5db !important;
            background-color: #ffffff !important;
            cursor: pointer !important;
            margin-right: 8px !important;
        }

        .mobile-filter-panel input[type="checkbox"]:checked {
            background-color: #fedc00 !important;
            border-color: #fedc00 !important;
        }

        .mobile-filter-panel .flex.items-center span {
            font-size: 14px !important;
            color: #374151 !important;
            font-weight: 500 !important;
        }

        /* Mobile Button Styling */
        .mobile-filter-panel .space-y-3 {
            margin-top: 16px !important;
            margin-bottom: 12px !important;
        }

        .mobile-filter-panel .space-y-3 button {
            width: 100% !important;
            padding: 12px 16px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            transition: all 0.2s ease !important;
            border: none !important;
            cursor: pointer !important;
            margin-top: 8px !important;
            margin-bottom: 8px !important;
        }

        .mobile-filter-panel #mobile-search-restaurants {
            background-color: #fedc00 !important;
            color: #1f2937 !important;
        }

        .mobile-filter-panel #mobile-search-restaurants:hover {
            background-color: #fedc00 !important;
        }

        .mobile-filter-panel #mobile-clear-filters {
            background-color: #e5e7eb !important;
            color: #374151 !important;
        }

        .mobile-filter-panel #mobile-clear-filters:hover {
            background-color: #d1d5db !important;
        }

        /* Mobile filter panel scrollbar */
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
        }

        @media (max-width: 768px) {
            .two-column-layout {
                flex-direction: column !important;
                gap: 1rem !important;
            }
            
            .left-column {
                display: none !important;
            }
            
            .right-column {
                width: 100% !important;
                height: auto !important;
            }
            
            .lebonresto-all-restaurants .restaurants-container {
                margin-bottom: 60px !important;
            }
            
            .lebonresto-all-restaurants .pagination-bar {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 50 !important;
            }
        }

        @media (min-width: 1024px) {
            .mobile-filter-toggle {
                display: none !important;
            }
        }
        
        /* All Restaurants Page Styles */
        .lebonresto-all-restaurants {
            background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%) !important;
            min-height: 100vh !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif !important;
            line-height: 1.6 !important;
        }

        .lebonresto-all-restaurants h1 {
            font-size: 3rem !important;
            font-weight: 800 !important;
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            background-clip: text !important;
            margin-bottom: 1rem !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }

        .lebonresto-all-restaurants .container {
            max-width: 1400px !important;
            margin: 0 auto !important;
            padding: 0 1rem !important;
        }

        /* Grid Layout */
        .lebonresto-all-restaurants .grid {
            display: grid !important;
        }

        .lebonresto-all-restaurants .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
        }

        @media (min-width: 1024px) {
            .lebonresto-all-restaurants .lg\\:grid-cols-10 {
                grid-template-columns: 3fr 7fr !important;
                gap: 2rem !important;
            }
        }

        @media (min-width: 768px) {
            .lebonresto-all-restaurants .md\\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media (min-width: 1280px) {
            .lebonresto-all-restaurants .xl\\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }

        /* Left Column - Filters Styling */
        .lebonresto-all-restaurants .left-column > div {
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
            border-radius: 16px !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
            padding: 24px !important;
            border: 2px solid rgba(255, 193, 7, 0.1) !important;
            position: sticky !important;
            top: 0 !important;
            height: 85vh !important;
            overflow-y: auto !important;
        }

        .lebonresto-all-restaurants .left-column h2 {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #1f2937 !important;
            margin-bottom: 1.5rem !important;
            display: flex !important;
            align-items: center !important;
        }

        .lebonresto-all-restaurants .left-column h2 i {
            margin-right: 8px !important;
            color: #fedc00 !important;
        }

        /* Website Logo Styling */
        .lebonresto-all-restaurants .logo-container {
            padding: 20px 0 !important;
            border-bottom: 2px solid rgba(251, 191, 36, 0.1) !important;
            margin-bottom: 24px !important;
        }

        .lebonresto-all-restaurants .website-logo {
            max-width: 200px !important;
            max-height: 60px !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
        }

        .lebonresto-all-restaurants .logo-fallback h1 {
            font-size: 2rem !important;
            font-weight: 800 !important;
            color: #fedc00 !important;
            margin-bottom: 8px !important;
        }

        .lebonresto-all-restaurants .logo-fallback p {
            font-size: 0.875rem !important;
            color: #6b7280 !important;
            margin: 0 !important;
        }

        /* Small Form Groups */
        .lebonresto-all-restaurants .form-group-small {
            margin-bottom: 16px !important;
            position: relative !important;
        }

        /* Floating Label Form Groups */
        .lebonresto-all-restaurants .form-group-floating {
            position: relative !important;
            margin-bottom: 24px !important;
        }

        /* Floating Input Styling */
        .lebonresto-all-restaurants .floating-input {
            width: 100% !important;
            padding: 20px 12px 8px 12px !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            background: #ffffff !important;
            color: #1f2937 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
            min-height: 60px !important;
            outline: none !important;
        }

        .lebonresto-all-restaurants .floating-input:focus {
            border-color: #fedc00 !important;
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-1px) !important;
        }

        .lebonresto-all-restaurants .floating-input:focus + .floating-label,
        .lebonresto-all-restaurants .floating-input:not(:placeholder-shown) + .floating-label {
            transform: translateY(-12px) scale(0.85) !important;
            color: #fedc00 !important;
            background: #ffffff !important;
            padding: 0 4px !important;
        }

        /* Floating Label Styling */
        .lebonresto-all-restaurants .floating-label {
            position: absolute !important;
            top: 20px !important;
            left: 12px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #6b7280 !important;
            pointer-events: none !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            transform-origin: left top !important;
            display: flex !important;
            align-items: center !important;
            z-index: 1 !important;
        }

        .lebonresto-all-restaurants .floating-label i {
            margin-right: 6px !important;
            font-size: 12px !important;
            color: #9ca3af !important;
        }

        .lebonresto-all-restaurants .floating-input:focus + .floating-label i,
        .lebonresto-all-restaurants .floating-input:not(:placeholder-shown) + .floating-label i {
            color: #fedc00 !important;
        }

        /* Active Floating Label State */
        .lebonresto-all-restaurants .floating-label-active {
            transform: translateY(-12px) scale(0.85) !important;
            color: #fedc00 !important;
            background: #ffffff !important;
            padding: 0 4px !important;
        }

        .lebonresto-all-restaurants .floating-label-active i {
            color: #fedc00 !important;
        }

        /* Modern Select Design */
        .lebonresto-all-restaurants .select-container {
            position: relative !important;
            width: 100% !important;
        }

        .lebonresto-all-restaurants .floating-select {
            width: 100% !important;
            padding: 20px 40px 8px 12px !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            background: #ffffff !important;
            color: #1f2937 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
            min-height: 40px !important;
            outline: none !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            cursor: pointer !important;
            padding-bottom: 0px !important;
        }

        .lebonresto-all-restaurants .floating-select:focus {
            border-color: #fedc00 !important;
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-1px) !important;
        }

        .lebonresto-all-restaurants .floating-select:focus + .floating-select-label,
        .lebonresto-all-restaurants .floating-select:not([value=""]) + .floating-select-label {
            transform: translateY(-12px) scale(0.85) !important;
            color: #fedc00 !important;
            background: #ffffff !important;
            padding: 0 4px !important;
        }

        .lebonresto-all-restaurants .floating-select-label {
            position: absolute !important;
            top: 20px !important;
            left: 12px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #6b7280 !important;
            pointer-events: none !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            transform-origin: left top !important;
            display: flex !important;
            align-items: center !important;
            z-index: 1 !important;
        }

        .lebonresto-all-restaurants .floating-select-label i {
            margin-right: 6px !important;
            font-size: 12px !important;
            color: #9ca3af !important;
        }

        .lebonresto-all-restaurants .floating-select:focus + .floating-select-label i,
        .lebonresto-all-restaurants .floating-select:not([value=""]) + .floating-select-label i {
            color: #fedc00 !important;
        }

        .lebonresto-all-restaurants .select-arrow {
            position: absolute !important;
            top: 50% !important;
            right: 12px !important;
            transform: translateY(-50%) !important;
            pointer-events: none !important;
            z-index: 2 !important;
            transition: all 0.3s ease !important;
        }

        .lebonresto-all-restaurants .floating-select:focus + .floating-select-label + .select-arrow {
            transform: translateY(-50%) rotate(180deg) !important;
        }

        .lebonresto-all-restaurants .select-arrow i {
            font-size: 12px !important;
            color: #6b7280 !important;
        }

        .lebonresto-all-restaurants .floating-select:focus + .floating-select-label + .select-arrow i {
            color: #fedc00 !important;
        }

        /* Multi-select styling */
        .lebonresto-all-restaurants .floating-select[multiple] {
            padding: 25px 40px 0px 12px !important;
            min-height: 40px !important;
        }

        .lebonresto-all-restaurants .floating-select[multiple] option {
            padding: 8px 12px !important;
            margin: 2px 0 !important;
            border-radius: 4px !important;
            background: #ffffff !important;
            color: #374151 !important;
            transition: all 0.2s ease !important;
        }

        .lebonresto-all-restaurants .floating-select[multiple] option:hover {
            background: #FFF8E1 !important;
            color: #1f2937 !important;
        }

        .lebonresto-all-restaurants .floating-select[multiple] option:checked {
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }

        /* Distance Filter Buttons */
        .lebonresto-all-restaurants .distance-filter-container {
            width: 100% !important;
        }

        .lebonresto-all-restaurants .distance-filter-label {
            display: flex !important;
            align-items: center !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #6b7280 !important;
            margin-bottom: 12px !important;
        }

        .lebonresto-all-restaurants .distance-filter-label i {
            margin-right: 6px !important;
            font-size: 12px !important;
            color: #9ca3af !important;
        }

        .lebonresto-all-restaurants .distance-buttons {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 6px !important;
            width: 100% !important;
            overflow-x: auto !important;
        }

        .lebonresto-all-restaurants .distance-btn {
            padding: 6px 8px !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 6px !important;
            background: #ffffff !important;
            color: #374151 !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
            text-align: center !important;
            min-height: 32px !important;
            min-width: 50px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
        }

        .lebonresto-all-restaurants .distance-btn.location-disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            background: #f3f4f6 !important;
            color: #9ca3af !important;
            border-color: #d1d5db !important;
        }

        .lebonresto-all-restaurants .distance-btn:hover {
            border-color: #fedc00 !important;
            background: #FFF8E1 !important;
            color: #1f2937 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.2) !important;
        }

        .lebonresto-all-restaurants .distance-btn.active {
            border-color: #fedc00 !important;
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4) !important;
        }

        .lebonresto-all-restaurants .distance-btn:active {
            transform: translateY(0) !important;
        }

        .lebonresto-all-restaurants .distance-btn:disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            background: #f3f4f6 !important;
            color: #9ca3af !important;
            border-color: #d1d5db !important;
        }

        .lebonresto-all-restaurants .distance-btn:disabled:hover {
            transform: none !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
            background: #f3f4f6 !important;
            border-color: #d1d5db !important;
        }

        /* Location Message Animation */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .location-message {
            animation: slideInRight 0.3s ease-out;
        }

        .location-permission-popup {
            animation: fadeIn 0.3s ease-out;
        }

        .location-permission-popup > div {
            animation: slideInUp 0.3s ease-out;
        }

        /* All Filter Inputs - Identical Styling */
        .lebonresto-all-restaurants .filter-input-small,
        .lebonresto-all-restaurants .filter-select-small {
            width: 100% !important;
            padding: 6px 10px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 4px !important;
            font-size: 12px !important;
            font-weight: 500 !important;
            background: #ffffff !important;
            color: #1f2937 !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            min-height: 28px !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
        }

        .lebonresto-all-restaurants .filter-select-small {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 8px center !important;
            background-size: 12px !important;
            padding-right: 28px !important;
        }

        .lebonresto-all-restaurants .filter-input-small:hover,
        .lebonresto-all-restaurants .filter-select-small:hover {
            border-color: #fedc00 !important;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.15) !important;
        }

        .lebonresto-all-restaurants .filter-input-small:focus,
        .lebonresto-all-restaurants .filter-select-small:focus {
            border-color: #fedc00 !important;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.1) !important;
            outline: none !important;
        }

        .lebonresto-all-restaurants .filter-input-small::placeholder {
            color: #9ca3af !important;
            font-size: 11px !important;
        }

        /* Small Checkbox Label */
        .lebonresto-all-restaurants .checkbox-label-small {
            display: flex !important;
            align-items: center !important;
            cursor: pointer !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #374151 !important;
            padding: 8px 0 !important;
            transition: color 0.3s ease !important;
        }

        .lebonresto-all-restaurants .checkbox-label-small:hover {
            color: #fedc00 !important;
        }

        .lebonresto-all-restaurants .checkbox-label-small input[type="checkbox"] {
            width: 16px !important;
            height: 16px !important;
            margin-right: 8px !important;
            accent-color: #fedc00 !important;
        }

        .lebonresto-all-restaurants .checkbox-label-small i {
            margin-right: 6px !important;
            color: #fedc00 !important;
            font-size: 12px !important;
        }

        /* Clear Filter Button */
        .lebonresto-all-restaurants .btn-secondary {
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
            color: #ffffff !important;
            padding: 12px 24px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            border: 1px solid #fedc00 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3) !important;
            position: relative !important;
            overflow: hidden !important;
            width: 100% !important;
        }

        .lebonresto-all-restaurants .btn-secondary:hover {
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.4) !important;
        }

        .lebonresto-all-restaurants .btn-secondary:active {
            transform: translateY(0) !important;
        }

        .lebonresto-all-restaurants .btn-secondary i {
            margin-right: 8px !important;
        }

        /* Form Inputs */
        .lebonresto-all-restaurants input[type="text"],
        .lebonresto-all-restaurants input[type="email"],
        .lebonresto-all-restaurants select {
            width: 91% !important;
            border: 2px solid #d1d5db !important;
            border-radius: 12px !important;
            font-size: 16px !important;
            font-weight: 500 !important;
            background-color: #ffffff !important;
            color: #1f2937 !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
            min-height: 48px !important;
            padding-bottom: 0px !important;
        }

        .lebonresto-all-restaurants input:focus,
        .lebonresto-all-restaurants select:focus {
            border-color: #fedc00 !important;
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1) !important;
            outline: none !important;
            transform: translateY(-1px) !important;
        }

        /* Buttons */
        .lebonresto-all-restaurants button {
            padding: 14px 24px !important;
            border: none !important;
            border-radius: 12px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            min-height: 50px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-decoration: none !important;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
        }

        .lebonresto-all-restaurants button[style*="background-color: #fedc00"],
        .lebonresto-all-restaurants .bg-yellow-400 {
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
            color: #1f2937 !important;
            font-weight: 700 !important;
        }

        .lebonresto-all-restaurants button:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(255, 193, 7, 0.4) !important;
        }

        /* Checkbox styling */
        .lebonresto-all-restaurants input[type="checkbox"] {
            width: 20px !important;
            height: 20px !important;
            border-radius: 6px !important;
            border: 2px solid #d1d5db !important;
            background-color: #ffffff !important;
            cursor: pointer !important;
        }

        .lebonresto-all-restaurants input[type="checkbox"]:checked {
            background-color: #fedc00 !important;
            border-color: #fedc00 !important;
        }

        /* Results Header */
        .lebonresto-all-restaurants .right-column > div:first-child {
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
            padding: 20px !important;
            margin-bottom: 24px !important;
            border: 2px solid rgba(255, 193, 7, 0.1) !important;
        }

        /* Filter Section - Fixed */
        .lebonresto-all-restaurants .filter-section {
            position: sticky !important;
            top: 0 !important;
            z-index: 40 !important;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            border-bottom: 1px solid #e5e7eb !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
        }

        /* Restaurants Container - Scrollable */
        .lebonresto-all-restaurants .restaurants-container {
            flex: 1 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding: 0 16px !important;
        }

        /* Restaurant Cards List */
        .lebonresto-all-restaurants #restaurants-list {
            display: flex !important;
            flex-direction: column !important;
            gap: 16px !important;
            padding-bottom: 16px !important;
        }

        /* Pagination Bar - Fixed */
        .lebonresto-all-restaurants .pagination-bar {
            position: sticky !important;
            bottom: 0 !important;
            z-index: 40 !important;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            border-top: 1px solid #e5e7eb !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
        }

        /* Pagination Styles */
        .lebonresto-all-restaurants #pagination-container {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
            border-top: 2px solid #e5e7eb !important;
            padding: 16px !important;
            flex-shrink: 0 !important;
        }

        .lebonresto-all-restaurants #pagination-controls {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .lebonresto-all-restaurants .pagination-btn {
            padding: 8px 12px !important;
            border: 1px solid #d1d5db !important;
            background: white !important;
            color: #374151 !important;
            border-radius: 6px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 36px !important;
            height: 36px !important;
        }

        .lebonresto-all-restaurants .pagination-btn:hover {
            background: #f3f4f6 !important;
            border-color: #9ca3af !important;
            transform: translateY(-1px) !important;
        }

        .lebonresto-all-restaurants .pagination-btn.active {
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
            color: white !important;
            border-color: #fedc00 !important;
            font-weight: 600 !important;
        }

        .lebonresto-all-restaurants .pagination-btn:disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            transform: none !important;
        }

        .lebonresto-all-restaurants .pagination-btn:disabled:hover {
            background: white !important;
            border-color: #d1d5db !important;
            transform: none !important;
        }

        .lebonresto-all-restaurants #pagination-info {
            font-size: 14px !important;
            color: #6b7280 !important;
            font-weight: 500 !important;
        }

        /* Custom Scrollbar for Restaurants Container */
        .lebonresto-all-restaurants .restaurants-container::-webkit-scrollbar {
            width: 8px !important;
        }

        .lebonresto-all-restaurants .restaurants-container::-webkit-scrollbar-track {
            background: #f1f5f9 !important;
            border-radius: 4px !important;
        }

        .lebonresto-all-restaurants .restaurants-container::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
            border-radius: 4px !important;
        }

        .lebonresto-all-restaurants .restaurants-container::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
        }

        /* Restaurant Cards - New Layout */
        .lebonresto-all-restaurants .restaurant-card {
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
            padding: 20px !important;
            border: 2px solid #f3f4f6 !important;
            cursor: pointer !important;
            transition: all 0.4s ease !important;
            position: relative !important;
            overflow: hidden !important;
            height: 20vh !important;
            width: 90% !important;
            display: flex !important;
            align-items: center !important;
        }

        .lebonresto-all-restaurants .restaurant-card:hover {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-2px) !important;
            border-color: #fedc00 !important;
        }

        /* Card Content Layout - Three Columns */
        .lebonresto-all-restaurants .restaurant-card .card-content {
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
            height: 100% !important;
            gap: 20px !important;
        }

        /* Left Column - Image Slider */
        .lebonresto-all-restaurants .restaurant-card .image-slider {
            width: 35% !important;
            height: 100% !important;
            position: relative !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            background: #f3f4f6 !important;
        }

        .lebonresto-all-restaurants .restaurant-card .slider-container {
            position: relative !important;
            width: 100% !important;
            height: 100% !important;
        }

        .lebonresto-all-restaurants .restaurant-card .slider-images {
            display: flex !important;
            width: 100% !important;
            height: 100% !important;
            transition: transform 0.5s ease !important;
        }

        .lebonresto-all-restaurants .restaurant-card .slider-image {
            min-width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 12px !important;
        }

        .lebonresto-all-restaurants .restaurant-card .slider-nav {
            position: absolute !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            background: rgba(0, 0, 0, 0.5) !important;
            color: white !important;
            border: none !important;
            width: 30px !important;
            height: 30px !important;
            border-radius: 50% !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 14px !important;
            z-index: 10 !important;
        }

        .lebonresto-all-restaurants .restaurant-card .slider-nav.prev {
            left: 8px !important;
        }

        .lebonresto-all-restaurants .restaurant-card .slider-nav.next {
            right: 8px !important;
        }

        .lebonresto-all-restaurants .restaurant-card .slider-dots {
            position: absolute !important;
            bottom: 8px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            display: flex !important;
            gap: 4px !important;
            z-index: 10 !important;
        }

        .lebonresto-all-restaurants .restaurant-card .slider-dot {
            width: 6px !important;
            height: 6px !important;
            border-radius: 50% !important;
            background: rgba(255, 255, 255, 0.5) !important;
            cursor: pointer !important;
            transition: background 0.3s ease !important;
        }

        .lebonresto-all-restaurants .restaurant-card .slider-dot.active {
            background: white !important;
        }

        /* Middle Column - Restaurant Info */
        .lebonresto-all-restaurants .restaurant-card .restaurant-info {
            width: 60% !important;
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            padding-left: 10px !important;
        }

        /* Right Column - Action Icons */
        .lebonresto-all-restaurants .restaurant-card .action-icons-column {
            width: 5% !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 10px !important;
        }

        .lebonresto-all-restaurants .restaurant-card .action-icons-container {
            display: flex !important;
            flex-direction: column !important;
            gap: 12px !important;
            width: 100% !important;
        }

        .lebonresto-all-restaurants .restaurant-card .action-icon-btn {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 32px !important;
            height: 32px !important;
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
            border: none !important;
            border-radius: 50% !important;
            color: #ffffff !important;
            text-decoration: none !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 2px 6px rgba(255, 193, 7, 0.3) !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .lebonresto-all-restaurants .restaurant-card .action-icon-btn::before {
            content: '' !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) scale(0) !important;
            width: 100% !important;
            height: 100% !important;
            background: rgba(255, 255, 255, 0.2) !important;
            border-radius: 50% !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            z-index: 1 !important;
        }

        .lebonresto-all-restaurants .restaurant-card .action-icon-btn:hover {
            background: #ffffff !important;
            color: #fedc00 !important;
            transform: scale(1.15) translateY(-2px) !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2) !important;
            text-decoration: none !important;
        }

        .lebonresto-all-restaurants .restaurant-card .action-icon-btn:hover::before {
            transform: translate(-50%, -50%) scale(1) !important;
            background: rgba(255, 143, 0, 0.1) !important;
        }

        .lebonresto-all-restaurants .restaurant-card .action-icon-btn i {
            font-size: 14px !important;
            color: inherit !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            position: relative !important;
            z-index: 2 !important;
        }

        .lebonresto-all-restaurants .restaurant-card .action-icon-btn:hover i {
            transform: scale(1.1) rotate(5deg) !important;
        }

        /* WhatsApp Button - Same Color as Other Icons */
        .lebonresto-all-restaurants .restaurant-card .whatsapp-btn {
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
            box-shadow: 0 2px 6px rgba(255, 193, 7, 0.3) !important;
            width: 32px !important;
            height: 32px !important;
        }

        .lebonresto-all-restaurants .restaurant-card .whatsapp-btn:hover {
            background: #ffffff !important;
            color: #fedc00 !important;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2) !important;
        }

        .lebonresto-all-restaurants .restaurant-card .whatsapp-btn:hover::before {
            background: rgba(255, 143, 0, 0.1) !important;
        }

        .lebonresto-all-restaurants .restaurant-card .whatsapp-btn i {
            font-size: 14px !important;
        }

        .lebonresto-all-restaurants .restaurant-card .restaurant-name {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #1f2937 !important;
            margin-bottom: 8px !important;
            margin-top: 0px !important;
            line-height: 1.3 !important;
        }

        .lebonresto-all-restaurants .restaurant-card .restaurant-category {
            font-size: 0.875rem !important;
            color: #fedc00 !important;
            font-weight: 600 !important;
            margin-bottom: 8px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }

        .lebonresto-all-restaurants .restaurant-card .restaurant-description {
            font-size: 0.875rem !important;
            color: #6b7280 !important;
            line-height: 1.4 !important;
            margin-bottom: 6px !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 3 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
        }


        /* Featured Badge */
        .lebonresto-all-restaurants .restaurant-card .featured-badge {
            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%) !important;
            color: #1f2937 !important;
            padding: 2px 6px !important;
            border-radius: 8px !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            margin-left: 8px !important;
            display: inline-block !important;
        }

        /* Restaurant Meta Items */
        .lebonresto-all-restaurants .restaurant-card .restaurant-meta {
            margin-top: 8px !important;
        }

        .lebonresto-all-restaurants .restaurant-card .meta-item {
            display: flex !important;
            align-items: center !important;
            font-size: 0.75rem !important;
            color: #6b7280 !important;
            margin-bottom: 4px !important;
        }

        .lebonresto-all-restaurants .restaurant-card .meta-item i {
            margin-right: 6px !important;
            color: #9ca3af !important;
            width: 12px !important;
            font-size: 10px !important;
        }

        /* Loading spinner */
        .loading-spinner {
            border: 4px solid #f3f4f6 !important;
            border-top: 4px solid #fedc00 !important;
            border-right: 4px solid #fedc00 !important;
            border-radius: 50% !important;
            width: 32px !important;
            height: 32px !important;
            animation: spin 1.2s ease-in-out infinite !important;
            display: inline-block !important;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3) !important;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }



        /* Responsive Design */
        @media (max-width: 768px) {
            .lebonresto-all-restaurants h1 {
                font-size: 2rem !important;
            }
            
            .lebonresto-all-restaurants .container {
                padding: 0 12px !important;
            }
            
            .lebonresto-all-restaurants .left-column > div {
                position: static !important;
                margin-bottom: 24px !important;
                padding: 20px !important;
                height: auto !important;
                overflow-y: visible !important;
            }
            
            .lebonresto-all-restaurants .restaurant-card {
                padding: 16px !important;
                height: auto !important;
                min-height: 200px !important;
            }
            
            .lebonresto-all-restaurants .restaurant-card .card-content {
                flex-direction: column !important;
                gap: 16px !important;
            }
            
            .lebonresto-all-restaurants .restaurant-card .image-slider {
                width: 100% !important;
                height: 120px !important;
            }
            
            .lebonresto-all-restaurants .restaurant-card .restaurant-info {
                width: 100% !important;
                padding-left: 0 !important;
            }
            
            .lebonresto-all-restaurants .restaurant-card .action-icons-column {
                width: 100% !important;
                padding: 0 !important;
            }
            
            .lebonresto-all-restaurants .restaurant-card .action-icons-container {
                flex-direction: row !important;
                justify-content: space-around !important;
                gap: 8px !important;
            }
            
            .lebonresto-all-restaurants .restaurant-card .action-icon-btn {
                width: 30px !important;
                height: 30px !important;
            }
            
            .lebonresto-all-restaurants .restaurant-card .action-icon-btn i {
                font-size: 12px !important;
            }
        }

        @media (max-width: 480px) {
            .lebonresto-all-restaurants .left-column > div {
                padding: 16px !important;
            }
            
            .lebonresto-all-restaurants .restaurant-card {
                padding: 16px !important;
            }
            
            .lebonresto-all-restaurants .restaurant-card h3 {
                font-size: 1.125rem !important;
            }
        }
    </style>

    <script>
        // Initialize floating labels
        jQuery(document).ready(function($) {
            // Handle floating label behavior for inputs
            $('.floating-input').on('focus blur input', function() {
                const input = $(this);
                const label = input.next('.floating-label');
                
                if (input.val() !== '' || input.is(':focus')) {
                    label.addClass('floating-label-active');
                } else {
                    label.removeClass('floating-label-active');
                }
            });

            // Handle floating label behavior for selects
            $('.floating-select').on('focus blur change', function() {
                const select = $(this);
                const label = select.next('.floating-select-label');
                
                if (select.val() !== '' && select.val() !== null || select.is(':focus')) {
                    label.addClass('floating-label-active');
                } else {
                    label.removeClass('floating-label-active');
                }
            });

            // Initialize labels on page load
            $('.floating-input').each(function() {
                const input = $(this);
                const label = input.next('.floating-label');
                
                if (input.val() !== '') {
                    label.addClass('floating-label-active');
                }
            });

            // Initialize select labels on page load
            $('.floating-select').each(function() {
                const select = $(this);
                const label = select.next('.floating-select-label');
                
                if (select.val() !== '' && select.val() !== null) {
                    label.addClass('floating-label-active');
                }
            });

            // Handle multi-select functionality
            $('.floating-select[multiple]').on('change', function() {
                const select = $(this);
                const selectedOptions = select.find('option:selected');
                
                // Update label to show selected count
                const label = select.next('.floating-select-label');
                const originalText = label.find('.checkbox-text').text() || label.text();
                
                if (selectedOptions.length > 0) {
                    label.find('.checkbox-text').text(`${originalText} (${selectedOptions.length} selected)`);
                } else {
                    label.find('.checkbox-text').text(originalText);
                }
            });

            // Handle distance button clicks
            $('.distance-btn').on('click', function() {
                const button = $(this);
                const distance = button.data('distance');
                
                // Remove active class from all buttons
                $('.distance-btn').removeClass('active');
                
                // Add active class to clicked button
                button.addClass('active');
                
                // Trigger filter change
                if (typeof handleFilterChange === 'function') {
                    handleFilterChange();
                }
            });
        });
    </script>

    <?php wp_footer(); ?>
</body>
</html>
