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
    
    <!-- Enhanced SEO Meta Tags -->
    <title>Restaurants Maroc | Guide Complet des Meilleurs Restaurants | <?php bloginfo('name'); ?></title>
    <meta name="description" content="Découvrez les meilleurs restaurants du Maroc avec notre guide complet. Plus de 500+ restaurants sélectionnés : cuisine marocaine, française, italienne, asiatique, sushi Casablanca. Filtres avancés, avis clients, photos, réservations en ligne. Trouvez votre restaurant près de moi à Casablanca, Rabat, Marrakech, Fès et dans toutes les villes du Maroc.">
    <meta name="keywords" content="restaurants Maroc, guide restaurants Maroc, cuisine marocaine, restaurants Casablanca, sushi Casablanca, restaurants sushi Maroc, restaurant près de moi, restaurant près de chez moi, restaurants Rabat, restaurants Marrakech, restaurants Fès, réservation restaurant, avis restaurants, gastronomie marocaine, tajine, couscous, restaurants halal, restaurants végétariens, restaurants de luxe, restaurants pas chers, restaurants romantiques, restaurants en famille, restaurants japonais Casablanca, cuisine japonaise Maroc, sushi delivery Casablanca, restaurant asiatique Casablanca">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="author" content="<?php bloginfo('name'); ?>">
    <meta name="publisher" content="<?php bloginfo('name'); ?>">
    <meta name="copyright" content="<?php echo date('Y'); ?> <?php bloginfo('name'); ?>">
    <meta name="language" content="fr">
    <meta name="geo.region" content="MA">
    <meta name="geo.country" content="Maroc">
    <meta name="geo.placename" content="Maroc">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo home_url('/all'); ?>">
    
    <!-- Enhanced Open Graph Meta Tags -->
    <meta property="og:title" content="Restaurants Maroc | Guide Complet des Meilleurs Restaurants | <?php bloginfo('name'); ?>">
    <meta property="og:description" content="Découvrez les meilleurs restaurants du Maroc avec notre guide complet. Plus de 500+ restaurants sélectionnés : sushi Casablanca, restaurants près de moi, cuisine marocaine. Filtres avancés, avis clients et réservations en ligne.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo home_url('/all'); ?>">
    <meta property="og:site_name" content="<?php bloginfo('name'); ?>">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:image" content="<?php echo LEBONRESTO_PLUGIN_URL; ?>assets/images/restaurants-maroc-og.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Guide des meilleurs restaurants du Maroc">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Restaurants Maroc | Guide Complet des Meilleurs Restaurants">
    <meta name="twitter:description" content="Découvrez les meilleurs restaurants du Maroc avec notre guide complet. Plus de 500+ restaurants sélectionnés : sushi Casablanca, restaurants près de moi, cuisine marocaine. Filtres avancés et réservations en ligne.">
    <meta name="twitter:image" content="<?php echo LEBONRESTO_PLUGIN_URL; ?>assets/images/restaurants-maroc-twitter.jpg">
    <meta name="twitter:image:alt" content="Guide des meilleurs restaurants du Maroc">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="theme-color" content="#fedc00">
    <meta name="msapplication-TileColor" content="#fedc00">
    <meta name="apple-mobile-web-app-title" content="Restaurants Maroc">
    
    <!-- Preconnect to external domains for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    
    <!-- DNS Prefetch for better performance -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//unpkg.com">
    <link rel="dns-prefetch" href="//cdn.tailwindcss.com">
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?php bloginfo('name'); ?>",
        "description": "Guide complet des meilleurs restaurants du Maroc",
        "url": "<?php echo home_url('/all'); ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?php echo home_url('/all'); ?>?search={search_term_string}",
            "query-input": "required name=search_term_string"
        },
        "publisher": {
            "@type": "Organization",
            "name": "<?php bloginfo('name'); ?>",
            "url": "<?php echo home_url(); ?>",
            "logo": {
                "@type": "ImageObject",
                "url": "<?php echo LEBONRESTO_PLUGIN_URL; ?>assets/images/logo.png"
            }
        },
        "mainEntity": {
            "@type": "ItemList",
            "name": "Restaurants du Maroc",
            "description": "Liste complète des meilleurs restaurants du Maroc",
            "url": "<?php echo home_url('/all'); ?>",
            "numberOfItems": "500+",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Restaurants Casablanca - Sushi & Cuisine Japonaise",
                    "url": "<?php echo home_url('/all'); ?>?city=casablanca&cuisine=sushi"
                },
                {
                    "@type": "ListItem", 
                    "position": 2,
                    "name": "Restaurants Rabat",
                    "url": "<?php echo home_url('/all'); ?>?city=rabat"
                },
                {
                    "@type": "ListItem",
                    "position": 3,
                    "name": "Restaurants Marrakech", 
                    "url": "<?php echo home_url('/all'); ?>?city=marrakech"
                },
                {
                    "@type": "ListItem",
                    "position": 4,
                    "name": "Restaurants Fès",
                    "url": "<?php echo home_url('/all'); ?>?city=fes"
                }
            ]
        },
        "breadcrumb": {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Accueil",
                    "item": "<?php echo home_url(); ?>"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "Restaurants",
                    "item": "<?php echo home_url('/all'); ?>"
                }
            ]
        }
    }
    </script>
    
    <!-- Restaurant Collection Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "Guide des Restaurants du Maroc",
        "description": "Découvrez les meilleurs restaurants du Maroc avec notre guide complet. Filtres avancés, avis clients, photos et réservations en ligne.",
        "url": "<?php echo home_url('/all'); ?>",
        "mainEntity": {
            "@type": "ItemList",
            "name": "Restaurants du Maroc",
            "description": "Collection des meilleurs restaurants du Maroc",
            "numberOfItems": "500+"
        },
        "about": {
            "@type": "Place",
            "name": "Maroc",
            "description": "Royaume du Maroc",
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "MA"
            }
        },
        "keywords": "restaurants Maroc, cuisine marocaine, gastronomie, guide restaurants, réservation restaurant, avis restaurants, sushi Casablanca, restaurants sushi Maroc, restaurant près de moi, cuisine japonaise Maroc",
        "inLanguage": "fr",
        "isAccessibleForFree": true,
        "license": "<?php echo home_url(); ?>/terms",
        "dateModified": "<?php echo date('c'); ?>"
    }
    </script>
    
    <!-- Local Business Schema for Morocco -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Country",
        "name": "Maroc",
        "alternateName": "Morocco",
        "description": "Royaume du Maroc, destination gastronomique d'exception",
        "url": "https://fr.wikipedia.org/wiki/Maroc",
        "sameAs": [
            "https://fr.wikipedia.org/wiki/Maroc",
            "https://www.visitmorocco.com"
        ]
    }
    </script>

    <?php wp_head(); ?>
</head>

<body <?php body_class('lebonresto-all-restaurants-page'); ?>>
    <?php wp_body_open(); ?>

    <div class="lebonresto-all-restaurants-redesigned">
        
        <!-- Breadcrumb Navigation -->
        <nav class="breadcrumb-navigation" style="background: #f8f9fa; padding: 1rem 0; border-bottom: 1px solid #e5e7eb;">
            <div class="container mx-auto px-4">
                <ol class="breadcrumb-list" style="display: flex; align-items: center; gap: 0.5rem; margin: 0; padding: 0; list-style: none; font-size: 0.875rem;">
                    <li class="breadcrumb-item">
                        <a href="<?php echo home_url(); ?>" style="color: #6b7280; text-decoration: none; transition: color 0.3s ease;" onmouseover="this.style.color='#fedc00';" onmouseout="this.style.color='#6b7280';">
                            <i class="fas fa-home" style="margin-right: 0.25rem;"></i>Accueil
                        </a>
                    </li>
                    <li class="breadcrumb-separator" style="color: #9ca3af;">›</li>
                    <li class="breadcrumb-item active" style="color: #1f2937; font-weight: 500;">
                        Guide des Restaurants du Maroc
                    </li>
                </ol>
            </div>
        </nav>
        
        <!-- Page Header Section -->
        <div class="page-header-section">
            <div class="container mx-auto px-4 py-8">
                <div class="header-content">
                    <!-- Title Section -->
                    <div class="header-title-section">
                    <h1 class="main-title">
                        <?php _e('Guide Complet des Meilleurs Restaurants du Maroc 2024', 'le-bon-resto'); ?>
                    </h1>
                        <h2 class="main-subtitle">
                            <?php _e('Découvrez plus de 500+ restaurants sélectionnés au Maroc : cuisine marocaine authentique, sushi Casablanca, restaurants français, italiens, asiatiques et internationaux. Filtres avancés, avis clients vérifiés, photos HD et réservations en ligne. Trouvez votre restaurant près de moi à Casablanca, Rabat, Marrakech, Fès, Agadir et dans toutes les villes du Maroc.', 'le-bon-resto'); ?>
                        </h2>
                        
                        <!-- SEO-optimized content section -->
                        <div class="seo-content-section" style="margin-top: 2rem; padding: 1.5rem; background: rgba(255,255,255,0.95); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937;">Pourquoi choisir notre guide des restaurants du Maroc ?</h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span style="color: #fedc00; font-size: 1.5rem;">✓</span>
                                    <span style="color: #374151; font-size: 0.9rem;"><strong>500+ restaurants vérifiés</strong> dans tout le Maroc</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span style="color: #fedc00; font-size: 1.5rem;">✓</span>
                                    <span style="color: #374151; font-size: 0.9rem;"><strong>Avis clients authentiques</strong> et notes Google</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span style="color: #fedc00; font-size: 1.5rem;">✓</span>
                                    <span style="color: #374151; font-size: 0.9rem;"><strong>Filtres avancés</strong> par cuisine, prix, localisation</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span style="color: #fedc00; font-size: 1.5rem;">✓</span>
                                    <span style="color: #374151; font-size: 0.9rem;"><strong>Réservations en ligne</strong> gratuites et sécurisées</span>
                                </div>
                            </div>
                            
                            <!-- Popular cities for SEO -->
                            <div style="margin-top: 1rem;">
                                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; color: #1f2937;">Restaurants populaires par ville :</h4>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                                    <a href="<?php echo home_url('/all'); ?>?city=casablanca" style="display: inline-block; padding: 0.5rem 1rem; background: #fedc00; color: #1f2937; text-decoration: none; border-radius: 20px; font-size: 0.875rem; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.background='#fbbf24'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#fedc00'; this.style.transform='translateY(0)';">🍽️ Restaurants Casablanca</a>
                                    <a href="<?php echo home_url('/all'); ?>?city=rabat" style="display: inline-block; padding: 0.5rem 1rem; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 20px; font-size: 0.875rem; transition: all 0.3s ease;" onmouseover="this.style.background='#fedc00'; this.style.color='#1f2937';" onmouseout="this.style.background='#f3f4f6'; this.style.color='#374151';">Restaurants Rabat</a>
                                    <a href="<?php echo home_url('/all'); ?>?city=marrakech" style="display: inline-block; padding: 0.5rem 1rem; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 20px; font-size: 0.875rem; transition: all 0.3s ease;" onmouseover="this.style.background='#fedc00'; this.style.color='#1f2937';" onmouseout="this.style.background='#f3f4f6'; this.style.color='#374151';">Restaurants Marrakech</a>
                                    <a href="<?php echo home_url('/all'); ?>?city=fes" style="display: inline-block; padding: 0.5rem 1rem; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 20px; font-size: 0.875rem; transition: all 0.3s ease;" onmouseover="this.style.background='#fedc00'; this.style.color='#1f2937';" onmouseout="this.style.background='#f3f4f6'; this.style.color='#374151';">Restaurants Fès</a>
                                    <a href="<?php echo home_url('/all'); ?>?city=agadir" style="display: inline-block; padding: 0.5rem 1rem; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 20px; font-size: 0.875rem; transition: all 0.3s ease;" onmouseover="this.style.background='#fedc00'; this.style.color='#1f2937';" onmouseout="this.style.background='#f3f4f6'; this.style.color='#374151';">Restaurants Agadir</a>
                                    <a href="<?php echo home_url('/all'); ?>?city=tanger" style="display: inline-block; padding: 0.5rem 1rem; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 20px; font-size: 0.875rem; transition: all 0.3s ease;" onmouseover="this.style.background='#fedc00'; this.style.color='#1f2937';" onmouseout="this.style.background='#f3f4f6'; this.style.color='#374151';">Restaurants Tanger</a>
                                </div>
                                
                                <!-- Specialized cuisine links -->
                                <div style="margin-top: 1rem;">
                                    <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; color: #1f2937;">Cuisines spécialisées à Casablanca :</h4>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                        <a href="<?php echo home_url('/all'); ?>?city=casablanca&cuisine=sushi" style="display: inline-block; padding: 0.5rem 1rem; background: #e3f2fd; color: #1976d2; text-decoration: none; border-radius: 20px; font-size: 0.875rem; font-weight: 500; transition: all 0.3s ease;" onmouseover="this.style.background='#1976d2'; this.style.color='white';" onmouseout="this.style.background='#e3f2fd'; this.style.color='#1976d2';">🍣 Sushi Casablanca</a>
                                        <a href="<?php echo home_url('/all'); ?>?city=casablanca&cuisine=japonais" style="display: inline-block; padding: 0.5rem 1rem; background: #e8f5e8; color: #2e7d32; text-decoration: none; border-radius: 20px; font-size: 0.875rem; font-weight: 500; transition: all 0.3s ease;" onmouseover="this.style.background='#2e7d32'; this.style.color='white';" onmouseout="this.style.background='#e8f5e8'; this.style.color='#2e7d32';">🍱 Restaurants Japonais</a>
                                        <a href="<?php echo home_url('/all'); ?>?city=casablanca&cuisine=asiatique" style="display: inline-block; padding: 0.5rem 1rem; background: #fff3e0; color: #f57c00; text-decoration: none; border-radius: 20px; font-size: 0.875rem; font-weight: 500; transition: all 0.3s ease;" onmouseover="this.style.background='#f57c00'; this.style.color='white';" onmouseout="this.style.background='#fff3e0'; this.style.color='#f57c00';">🥢 Cuisine Asiatique</a>
                                        <a href="<?php echo home_url('/all'); ?>?near_me=1" style="display: inline-block; padding: 0.5rem 1rem; background: #f3e5f5; color: #7b1fa2; text-decoration: none; border-radius: 20px; font-size: 0.875rem; font-weight: 500; transition: all 0.3s ease;" onmouseover="this.style.background='#7b1fa2'; this.style.color='white';" onmouseout="this.style.background='#f3e5f5'; this.style.color='#7b1fa2';">📍 Restaurant près de moi</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search and Map Section -->
                    <div class="header-controls-section">
                        <!-- Search Input -->
                        <div class="header-search-container">
                            <div class="header-search-input-container">
                                <input type="text" id="restaurant-search" class="header-search-input">
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

    <div class="mobile-filter-toggle lg:hidden">
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
                            id="mobile-distance"
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
                        <span class="ml-2 text-sm text-gray-700">
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
        
        <!-- SEO FAQ Section -->
        <section class="seo-faq-section" style="background: #f8f9fa; padding: 4rem 0; margin-top: 3rem;">
            <div class="container mx-auto px-4">
                <div class="faq-content" style="max-width: 800px; margin: 0 auto;">
                    <h2 style="font-size: 2rem; font-weight: 700; text-align: center; margin-bottom: 3rem; color: #1f2937;">
                        Questions Fréquentes sur les Restaurants du Maroc
                    </h2>
                    
                    <div class="faq-list" style="display: grid; gap: 1.5rem;">
                        <div class="faq-item" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937;">
                                Où trouver les meilleurs restaurants sushi à Casablanca ?
                            </h3>
                            <p style="color: #6b7280; line-height: 1.6; margin: 0;">
                                Casablanca offre une excellente sélection de restaurants sushi et japonais. Notre guide présente 
                                les meilleures adresses de sushi à Casablanca, des restaurants traditionnels aux établissements 
                                modernes, tous soigneusement sélectionnés pour leur fraîcheur et leur authenticité. 
                                Trouvez facilement un restaurant sushi près de vous à Casablanca.
                            </p>
                        </div>
                        
                        <div class="faq-item" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937;">
                                Comment trouver un restaurant près de moi au Maroc ?
                            </h3>
                            <p style="color: #6b7280; line-height: 1.6; margin: 0;">
                                Utilisez notre fonction "Restaurant près de moi" pour découvrir les meilleurs restaurants 
                                à proximité de votre localisation. Notre système de géolocalisation vous propose les 
                                restaurants les plus proches avec leurs avis, photos et options de réservation en ligne.
                            </p>
                        </div>
                        
                        <div class="faq-item" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937;">
                                Quels sont les meilleurs restaurants de Casablanca ?
                            </h3>
                            <p style="color: #6b7280; line-height: 1.6; margin: 0;">
                                Casablanca abrite une scène gastronomique diversifiée avec des restaurants de renommée internationale. 
                                Notre guide présente les meilleures adresses, des restaurants traditionnels marocains aux établissements 
                                français, italiens, asiatiques et japonais, tous soigneusement sélectionnés pour leur qualité et leur authenticité.
                            </p>
                        </div>
                        
                        <div class="faq-item" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937;">
                                Les restaurants marocains sont-ils halal ?
                            </h3>
                            <p style="color: #6b7280; line-height: 1.6; margin: 0;">
                                La majorité des restaurants au Maroc servent de la cuisine halal. Notre guide indique clairement 
                                les options halal disponibles et vous permet de filtrer spécifiquement les restaurants 
                                respectant ces critères alimentaires.
                            </p>
                        </div>
                        
                        <div class="faq-item" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937;">
                                Y a-t-il des restaurants sushi halal à Casablanca ?
                            </h3>
                            <p style="color: #6b7280; line-height: 1.6; margin: 0;">
                                Oui, plusieurs restaurants sushi à Casablanca proposent des options halal. Notre guide 
                                indique clairement les restaurants japonais et sushi qui respectent les critères halal, 
                                avec des ingrédients certifiés et des méthodes de préparation conformes.
                            </p>
                        </div>
                        
                        <div class="faq-item" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937;">
                                Comment choisir un restaurant selon mon budget ?
                            </h3>
                            <p style="color: #6b7280; line-height: 1.6; margin: 0;">
                                Notre système de filtres avancés vous permet de rechercher par fourchette de prix : 
                                restaurants économiques, milieu de gamme, ou établissements de luxe. Chaque restaurant 
                                affiche sa gamme de prix pour vous aider à faire le bon choix selon votre budget.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Additional SEO Content -->
        <section class="seo-content-section" style="background: white; padding: 4rem 0;">
            <div class="container mx-auto px-4">
                <div class="seo-content" style="max-width: 1000px; margin: 0 auto;">
                    <h2 style="font-size: 2rem; font-weight: 700; text-align: center; margin-bottom: 2rem; color: #1f2937;">
                        Découvrez la Gastronomie Marocaine
                    </h2>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
                        <div class="cuisine-card" style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 12px;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🍽️</div>
                            <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937;">Cuisine Traditionnelle</h3>
                            <p style="color: #6b7280; line-height: 1.6;">
                                Tajines parfumés, couscous aux légumes, pastilla sucrée-salée, et autres spécialités 
                                marocaines authentiques dans les meilleurs restaurants du pays.
                            </p>
                        </div>
                        
                        <div class="cuisine-card" style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 12px;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🍣</div>
                            <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937;">Sushi & Cuisine Japonaise</h3>
                            <p style="color: #6b7280; line-height: 1.6;">
                                Découvrez les meilleurs restaurants sushi à Casablanca et dans tout le Maroc. 
                                Cuisine japonaise authentique, sushi frais, et restaurants halal pour tous les goûts.
                            </p>
                        </div>
                        
                        <div class="cuisine-card" style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 12px;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
                            <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937;">Restaurants de Luxe</h3>
                            <p style="color: #6b7280; line-height: 1.6;">
                                Établissements haut de gamme avec chefs étoilés, ambiance raffinée et menus 
                                d'exception pour des moments inoubliables.
                            </p>
                        </div>
                    </div>
                    
                    <div style="text-align: center; padding: 2rem; background: linear-gradient(135deg, #fedc00 0%, #fbbf24 100%); border-radius: 12px; color: #1f2937;">
                        <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">
                            Prêt à découvrir les meilleurs restaurants du Maroc ?
                        </h3>
                        <p style="margin-bottom: 1.5rem; font-size: 1.1rem;">
                            Utilisez nos filtres avancés pour trouver le restaurant parfait selon vos goûts, 
                            votre budget et votre localisation.
                        </p>
                        <a href="#restaurants-container" style="display: inline-block; padding: 1rem 2rem; background: white; color: #1f2937; text-decoration: none; border-radius: 25px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';">
                            Commencer la recherche
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
