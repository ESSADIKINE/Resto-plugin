<?php
/**
 * All Restaurants Page Template - Redesigned with TripAdvisor-inspired Layout
 * 
 * @package LeBonResto
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Enqueue required CSS and JS
wp_enqueue_style('tailwind-css', 'https://cdn.tailwindcss.com', array(), '3.4.0');
wp_enqueue_style('lebonresto-all-restaurants', LEBONRESTO_PLUGIN_URL . 'assets/css/all-restaurants.css', array('tailwind-css'), LEBONRESTO_PLUGIN_VERSION);

// Enqueue Leaflet CSS and JS for popup map
wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);

wp_enqueue_script('lebonresto-all-restaurants', LEBONRESTO_PLUGIN_URL . 'assets/js/all-restaurants.js', array('jquery', 'wp-api', 'leaflet-js'), LEBONRESTO_PLUGIN_VERSION, true);

// Get plugin options
$options = get_option('lebonresto_options', array());

// Localize script
wp_localize_script('lebonresto-all-restaurants', 'lebonrestoAll', array(
    'apiUrl' => home_url('/wp-json/lebonresto/v1/restaurants'),
    'cuisineTypesUrl' => home_url('/wp-json/lebonresto/v1/cuisine-types'),
    'googlePlacesUrl' => home_url('/wp-json/lebonresto/v1/google-places'),
    'nonce' => wp_create_nonce('wp_rest'),
    'perPage' => 20,
    'settings' => array(
        'defaultRadius' => isset($options['default_radius']) ? intval($options['default_radius']) : 25,
        'maxRadius' => isset($options['max_radius']) ? intval($options['max_radius']) : 100,
        'primaryColor' => isset($options['primary_color']) ? $options['primary_color'] : '#fedc00',
    ),
    'strings' => array(
        'loading' => __('Chargement...', 'le-bon-resto'),
        'noResults' => __('Aucun restaurant trouvé', 'le-bon-resto'),
        'error' => __('Erreur lors du chargement', 'le-bon-resto'),
        'tryAgain' => __('Réessayer', 'le-bon-resto'),
        'clearFilters' => __('Effacer les filtres', 'le-bon-resto'),
        'showMore' => __('Afficher plus', 'le-bon-resto'),
        'showLess' => __('Afficher moins', 'le-bon-resto'),
    )
));

// Get cuisine types
$cuisine_types = lebonresto_get_cuisine_types();
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e('Tous les Restaurants', 'le-bon-resto'); ?> - <?php bloginfo('name'); ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Découvrez les meilleurs restaurants du Maroc. Explorez notre collection complète avec filtres avancés, avis, photos et réservations en ligne.">
    <meta name="keywords" content="restaurants Maroc, guide restaurants, cuisine marocaine, réservation restaurant, avis restaurants">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Tous les Restaurants - <?php bloginfo('name'); ?>">
    <meta property="og:description" content="Découvrez les meilleurs restaurants du Maroc avec notre guide complet">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo home_url('/all'); ?>">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class('lebonresto-all-restaurants-page'); ?>>
    <?php wp_body_open(); ?>

    <div class="lebonresto-all-restaurants-redesigned">
        
        <!-- Page Header Section -->
        <div class="page-header-section">
            <div class="container mx-auto px-4 py-8">
                <div class="header-content">
                    <!-- Title Section -->
                    <div class="header-title-section">
                        <h1 class="main-title">
                            <?php _e('Meilleurs Restaurants au Maroc', 'le-bon-resto'); ?>
                        </h1>
                        <h3 class="main-subtitle">
                            <?php _e('Meilleurs restaurants au Maroc : savourez la gastronomie marocaine, tajines, couscous et spécialités locales dans des adresses incontournables.', 'le-bon-resto'); ?>
                        </h3>
                    </div>
                    
                    <!-- Search and Map Section -->
                    <div class="header-controls-section">
                        <!-- Search Input -->
                        <div class="header-search-container">
                            <div class="header-search-input-container">
                                <input type="text" id="restaurant-search" class="header-search-input" placeholder="<?php _e('Rechercher un restaurant...', 'le-bon-resto'); ?>">
                                <svg viewBox="0 0 24 24" width="20" height="20" class="header-search-icon">
                                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Map Toggle Button -->
                        <div class="map-toggle-container">
                            <button class="map-toggle-btn" type="button" aria-label="<?php _e('Voir la carte', 'le-bon-resto'); ?>">
                                <span class="map-btn-content">
                                    <svg viewBox="0 0 24 24" width="16" height="16" class="map-icon">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.25 9.799c0-4.247 3.488-7.707 7.75-7.707s7.75 3.46 7.75 7.707c0 2.28-1.138 4.477-2.471 6.323-1.31 1.813-2.883 3.388-3.977 4.483l-.083.083-.002.002-1.225 1.218-1.213-1.243-.03-.03-.012-.013c-1.1-1.092-2.705-2.687-4.035-4.53-1.324-1.838-2.452-4.024-2.452-6.293M12 3.592c-3.442 0-6.25 2.797-6.25 6.207 0 1.796.907 3.665 2.17 5.415 1.252 1.736 2.778 3.256 3.886 4.357l.043.042.16.164.148-.149.002-.002.061-.06c1.103-1.105 2.605-2.608 3.843-4.322 1.271-1.76 2.187-3.64 2.187-5.445 0-3.41-2.808-6.207-6.25-6.207m1.699 5.013a1.838 1.838 0 1 0-3.397 1.407A1.838 1.838 0 0 0 13.7 8.605m-2.976-2.38a3.338 3.338 0 1 1 2.555 6.168 3.338 3.338 0 0 1-2.555-6.169"></path>
                                    </svg>
                                    <span class="map-text"><?php _e('Carte', 'le-bon-resto'); ?></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Main Content Layout -->
        <div class="main-content-layout">
            <div class="container mx-auto px-4">
                <div class="content-grid">
                    
                    <!-- Left Sidebar - Filters -->
                    <div class="filters-sidebar">
                        <div class="filters-container">
                            
                            <!-- Distance Filter -->
                            <div class="filter-group">
                                <div class="filter-header">
                                    <h3 class="filter-title"><?php _e('Distance', 'le-bon-resto'); ?></h3>
                                    <button type="button" class="filter-toggle" aria-expanded="true">
                                        <svg viewBox="0 0 24 24" width="20" height="20" class="toggle-icon">
                                            <path d="M18.4 7.4 12 13.7 5.6 7.4 4.2 8.8l7.8 7.8 7.8-7.8z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="filter-content">
                                    <div class="location-status" id="location-status">
                                        <svg viewBox="0 0 24 24" width="16" height="16" class="location-icon">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"></path>
                                        </svg>
                                        <span class="location-text"><?php _e('Détection de votre position...', 'le-bon-resto'); ?></span>
                                    </div>
                                    <div class="distance-options">
                                        <label class="filter-option distance-option">
                                            <input type="radio" class="filter-radio" name="distance" value="5">
                                            <span class="radio-mark"></span>
                                            <span class="option-text"><?php _e('5 km', 'le-bon-resto'); ?></span>
                                        </label>
                                        <label class="filter-option distance-option">
                                            <input type="radio" class="filter-radio" name="distance" value="10">
                                            <span class="radio-mark"></span>
                                            <span class="option-text"><?php _e('10 km', 'le-bon-resto'); ?></span>
                                        </label>
                                        <label class="filter-option distance-option">
                                            <input type="radio" class="filter-radio" name="distance" value="25">
                                            <span class="radio-mark"></span>
                                            <span class="option-text"><?php _e('25 km', 'le-bon-resto'); ?></span>
                                        </label>
                                        <label class="filter-option distance-option">
                                            <input type="radio" class="filter-radio" name="distance" value="50">
                                            <span class="radio-mark"></span>
                                            <span class="option-text"><?php _e('50 km', 'le-bon-resto'); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <!-- Cuisine Type Filter -->
                            <div class="filter-group">
                                <div class="filter-header">
                                    <h3 class="filter-title"><?php _e('Type de cuisine', 'le-bon-resto'); ?></h3>
                                    <button type="button" class="filter-toggle" aria-expanded="true">
                                        <svg viewBox="0 0 24 24" width="20" height="20" class="toggle-icon">
                                            <path d="M18.4 7.4 12 13.7 5.6 7.4 4.2 8.8l7.8 7.8 7.8-7.8z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="filter-content">
                                    <div class="filter-options">
                                        <?php foreach ($cuisine_types as $cuisine): ?>
                                        <label class="filter-option">
                                            <input type="checkbox" class="filter-checkbox" name="cuisine_type" value="<?php echo esc_attr($cuisine); ?>">
                                            <span class="checkmark"></span>
                                            <span class="option-text"><?php echo esc_html(ucfirst($cuisine)); ?></span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <button class="show-more-btn" type="button">
                                        <span class="show-more-text"><?php _e('Afficher plus', 'le-bon-resto'); ?></span>
                                        <svg viewBox="0 0 24 24" width="1em" height="1em" class="show-more-icon">
                                            <path d="M18.4 7.4 12 13.7 5.6 7.4 4.2 8.8l7.8 7.8 7.8-7.8z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Price Range Filter -->
                            <div class="filter-group">
                                <div class="filter-header">
                                    <h3 class="filter-title"><?php _e('Gamme de prix', 'le-bon-resto'); ?></h3>
                                    <button type="button" class="filter-toggle" aria-expanded="true">
                                        <svg viewBox="0 0 24 24" width="20" height="20" class="toggle-icon">
                                            <path d="M18.4 7.4 12 13.7 5.6 7.4 4.2 8.8l7.8 7.8 7.8-7.8z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="filter-content">
                                    <div class="price-range-slider">
                                        <div class="price-inputs">
                                            <div class="price-input-group">
                                                <label for="min-price"><?php _e('Prix min', 'le-bon-resto'); ?></label>
                                                <input type="number" id="min-price" name="min_price" min="0" step="10" placeholder="0" class="price-input">
                                                <span class="currency-symbol">MAD</span>
                                            </div>
                                            <div class="price-separator"> </div>
                                            <div class="price-input-group">
                                                <label for="max-price"><?php _e('Prix max', 'le-bon-resto'); ?></label>
                                                <input type="number" id="max-price" name="max_price" min="0" step="10" placeholder="1000" class="price-input">
                                                <span class="currency-symbol">MAD</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Clear Filters Button -->
                            <div class="filter-actions">
                                <button type="button" class="clear-filters-btn">
                                    <svg viewBox="0 0 24 24" width="16" height="16" class="clear-icon">
                                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
                                    </svg>
                                    <?php _e('Effacer tous les filtres', 'le-bon-resto'); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Content - Restaurant Listings -->
                    <div class="restaurants-content">
                        
                        <!-- Results Header -->
                        <div class="results-header">
                            <div class="results-info">
                                <div class="results-count">
                                    <span id="results-total">0</span> <?php _e('restaurants trouvés', 'le-bon-resto'); ?>
                                </div>
                                <div class="sort-dropdown">
                                    <button type="button" class="sort-dropdown-btn">
                                        <span class="sort-text"><?php _e('Trier par', 'le-bon-resto'); ?></span>
                                        <svg viewBox="0 0 24 24" width="16" height="16" class="sort-icon">
                                            <path d="M18.4 7.4 12 13.7 5.6 7.4 4.2 8.8l7.8 7.8 7.8-7.8z"></path>
                                        </svg>
                                    </button>
                                    <div class="sort-dropdown-menu">
                                        <div class="sort-options">
                                            <button type="button" class="sort-option" data-sort="featured">
                                                <?php _e('En vedette', 'le-bon-resto'); ?>
                                            </button>
                                            <button type="button" class="sort-option" data-sort="rating">
                                                <?php _e('Note Google Maps', 'le-bon-resto'); ?>
                                            </button>
                                            <button type="button" class="sort-option" data-sort="reviews">
                                                <?php _e('Nombre d\'avis Google', 'le-bon-resto'); ?>
                                            </button>
                                            <button type="button" class="sort-option" data-sort="name">
                                                <?php _e('Nom', 'le-bon-resto'); ?>
                                            </button>
                                            <button type="button" class="sort-option" data-sort="distance">
                                                <?php _e('Distance', 'le-bon-resto'); ?>
                                            </button>
                                            <button type="button" class="sort-option" data-sort="price_low">
                                                <?php _e('Prix croissant', 'le-bon-resto'); ?>
                                            </button>
                                            <button type="button" class="sort-option" data-sort="price_high">
                                                <?php _e('Prix décroissant', 'le-bon-resto'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Restaurant Cards Container -->
                        <div class="restaurants-container">
                            <div id="restaurants-list" class="restaurants-grid">
                                <!-- Loading State -->
                                <div class="loading-state">
                                    <div class="loading-spinner"></div>
                                    <p class="loading-text"><?php _e('Chargement des restaurants...', 'le-bon-resto'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Load More Button -->
                        <div class="load-more-container">
                            <button type="button" id="load-more-btn" class="load-more-btn">
                                <span class="load-more-text"><?php _e('Afficher plus de restaurants', 'le-bon-resto'); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Filter Toggle -->
    <div class="mobile-filter-toggle lg:hidden">
        <button type="button" id="mobile-filter-btn" class="mobile-filter-button">
            <svg viewBox="0 0 24 24" width="20" height="20" class="filter-icon">
                <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"></path>
            </svg>
            <span class="filter-text"><?php _e('Filtres', 'le-bon-resto'); ?></span>
        </button>
    </div>

    <!-- Mobile Filter Overlay -->
    <div id="mobile-filter-overlay" class="mobile-filter-overlay hidden"></div>

    <!-- Restaurant Map Popup Modal -->
    <div id="restaurant-map-popup" class="restaurant-popup-modal">
        <div class="popup-overlay"></div>
        <div class="popup-container">
            <div class="popup-header">
                <h3 class="popup-title"><?php _e('Carte des Restaurants', 'le-bon-resto'); ?></h3>
                <button class="popup-close" id="close-popup">
                    <svg viewBox="0 0 24 24" width="24" height="24" class="close-icon">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.293 5.293a1 1 0 0 1 1.414 0L12 10.586l5.293-5.293a1 1 0 1 1 1.414 1.414L13.414 12l5.293 5.293a1 1 0 0 1-1.414 1.414L12 13.414l-5.293 5.293a1 1 0 0 1-1.414-1.414L10.586 12 5.293 6.707a1 1 0 0 1 0-1.414z"></path>
                    </svg>
                </button>
            </div>
            <div class="popup-content">
                <div id="popup-restaurants-map" class="popup-map-container"></div>
                <div class="popup-map-controls">
                    <button id="popup-center-current" class="popup-control-btn">
                        <svg viewBox="0 0 24 24" width="16" height="16" class="control-icon">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"></path>
                        </svg>
                        <?php _e('Centrer sur restaurant', 'le-bon-resto'); ?>
                    </button>
                    <div class="popup-results-counter">
                        <span id="popup-results-count"><?php _e('Chargement...', 'le-bon-resto'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
