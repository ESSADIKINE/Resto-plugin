<?php
/**
 * Advanced SEO Optimization for Le Bon Resto Plugin
 * 
 * @package LeBonResto
 */

if (!defined('ABSPATH')) {
    exit;
}

class LeBonResto_Advanced_SEO {
    
    private $plugin_version;
    
    public function __construct() {
        $this->plugin_version = LEBONRESTO_PLUGIN_VERSION;
        
        // Initialize SEO features
        add_action('init', array($this, 'init_seo_features'));
        add_action('wp_head', array($this, 'add_advanced_meta_tags'), 1);
        add_action('wp_head', array($this, 'add_structured_data'), 2);
        add_action('wp_head', array($this, 'add_social_media_tags'), 3);
        
        // XML Sitemap
        add_action('init', array($this, 'add_sitemap_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_sitemap_query_vars'));
        add_action('template_redirect', array($this, 'handle_sitemap_request'));
        
        // Image optimization
        add_filter('wp_get_attachment_image_attributes', array($this, 'optimize_image_attributes'), 10, 3);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_lazy_loading_script'));
        
        // Meta title optimization
        add_filter('wp_title', array($this, 'optimize_page_title'), 10, 3);
        add_filter('document_title_parts', array($this, 'optimize_document_title'), 10, 1);
        
        // Breadcrumb schema
        add_action('wp_head', array($this, 'add_breadcrumb_schema'), 4);
        
        // Performance optimization
        add_action('wp_enqueue_scripts', array($this, 'optimize_scripts_loading'));
    }
    
    /**
     * Initialize SEO features
     */
    public function init_seo_features() {
        // Add custom meta boxes for SEO
        add_action('add_meta_boxes', array($this, 'add_seo_meta_boxes'));
        add_action('save_post', array($this, 'save_seo_meta_data'));
        
        // Add REST API endpoints for SEO data
        add_action('rest_api_init', array($this, 'register_seo_rest_routes'));
    }
    
    /**
     * Add advanced meta tags
     */
    public function add_advanced_meta_tags() {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return;
        }
        
        $this->add_meta_title();
        $this->add_meta_description();
        $this->add_meta_keywords();
        $this->add_canonical_url();
        $this->add_robots_meta();
        $this->add_geo_meta();
        $this->add_alternate_language_tags();
    }
    
    /**
     * Add structured data (Schema.org JSON-LD)
     */
    public function add_structured_data() {
        if (!is_singular('restaurant')) {
            return;
        }
        
        $restaurant_id = get_the_ID();
        $structured_data = $this->generate_restaurant_structured_data($restaurant_id);
        
        if ($structured_data) {
            echo '<script type="application/ld+json">' . "\n";
            echo wp_json_encode($structured_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            echo "\n" . '</script>' . "\n";
        }
    }
    
    /**
     * Add social media tags (Open Graph, Twitter Cards)
     */
    public function add_social_media_tags() {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return;
        }
        
        $this->add_open_graph_tags();
        $this->add_twitter_card_tags();
        $this->add_facebook_tags();
    }
    
    /**
     * Generate comprehensive restaurant structured data
     */
    private function generate_restaurant_structured_data($restaurant_id) {
        $restaurant = get_post($restaurant_id);
        if (!$restaurant || $restaurant->post_type !== 'restaurant') {
            return null;
        }
        
        // Get restaurant meta data
        $name = get_the_title($restaurant_id);
        $description = get_post_meta($restaurant_id, '_restaurant_description', true);
        $address = get_post_meta($restaurant_id, '_restaurant_address', true);
        $city = get_post_meta($restaurant_id, '_restaurant_city', true);
        $phone = get_post_meta($restaurant_id, '_restaurant_phone', true);
        $email = get_post_meta($restaurant_id, '_restaurant_email', true);
        $cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true);
        $latitude = get_post_meta($restaurant_id, '_restaurant_latitude', true);
        $longitude = get_post_meta($restaurant_id, '_restaurant_longitude', true);
        $is_featured = get_post_meta($restaurant_id, '_restaurant_is_featured', true);
        $price_range = get_post_meta($restaurant_id, '_restaurant_price_range', true);
        $opening_hours = get_post_meta($restaurant_id, '_restaurant_opening_hours', true);
        $menu_url = get_post_meta($restaurant_id, '_restaurant_menu_url', true);
        $virtual_tour_url = get_post_meta($restaurant_id, '_restaurant_virtual_tour_url', true);
        
        // Get images
        $images = $this->get_restaurant_images($restaurant_id);
        $logo = $this->get_restaurant_logo($restaurant_id);
        
        // Get reviews and ratings
        $reviews = $this->get_restaurant_reviews($restaurant_id);
        $aggregate_rating = $this->calculate_aggregate_rating($reviews);
        
        // Base structured data
        $structured_data = array(
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $name,
            'description' => $description ?: $this->generate_seo_description($restaurant_id),
            'url' => get_permalink($restaurant_id),
            'image' => $images,
            'logo' => $logo,
            'telephone' => $phone,
            'email' => $email,
            'servesCuisine' => $cuisine_type ?: 'Moroccan cuisine',
            'priceRange' => $price_range ?: '$$',
            'address' => array(
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
                'addressLocality' => $city ?: 'Casablanca',
                'addressRegion' => 'Casablanca-Settat',
                'addressCountry' => 'MA',
                'postalCode' => ''
            ),
            'geo' => array(
                '@type' => 'GeoCoordinates',
                'latitude' => $latitude,
                'longitude' => $longitude
            ),
            'openingHoursSpecification' => $this->format_opening_hours($opening_hours),
            'hasMenu' => $menu_url ? array(
                '@type' => 'Menu',
                'url' => $menu_url
            ) : null,
            'sameAs' => $this->get_social_media_links($restaurant_id),
            'potentialAction' => array(
                '@type' => 'ReserveAction',
                'target' => array(
                    '@type' => 'EntryPoint',
                    'urlTemplate' => get_permalink($restaurant_id) . '#reservation',
                    'actionPlatform' => array('http://schema.org/DesktopWebPlatform', 'http://schema.org/MobileWebPlatform')
                )
            )
        );
        
        // Add virtual tour if available
        if ($virtual_tour_url) {
            $structured_data['hasVirtualTour'] = array(
                '@type' => 'VirtualTour',
                'url' => $virtual_tour_url
            );
        }
        
        // Add reviews and ratings
        if (!empty($reviews)) {
            $structured_data['review'] = $reviews;
            if ($aggregate_rating) {
                $structured_data['aggregateRating'] = $aggregate_rating;
            }
        }
        
        // Add featured status
        if ($is_featured) {
            $structured_data['additionalProperty'] = array(
                '@type' => 'PropertyValue',
                'name' => 'Featured',
                'value' => 'true'
            );
        }
        
        // Add offers if available
        $offers = $this->get_restaurant_offers($restaurant_id);
        if (!empty($offers)) {
            $structured_data['offers'] = $offers;
        }
        
        // Clean up null values
        $structured_data = array_filter($structured_data, function($value) {
            return $value !== null && $value !== '';
        });
        
        return $structured_data;
    }
    
    /**
     * Get restaurant images for structured data
     */
    private function get_restaurant_images($restaurant_id) {
        $images = array();
        
        // Get gallery images
        $gallery_ids = get_post_meta($restaurant_id, '_restaurant_gallery', true);
        if ($gallery_ids) {
            $image_ids = explode(',', $gallery_ids);
            foreach ($image_ids as $image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'large');
                if ($image_url) {
                    $images[] = array(
                        '@type' => 'ImageObject',
                        'url' => $image_url,
                        'caption' => get_post_meta($image_id, '_wp_attachment_image_alt', true)
                    );
                }
            }
        }
        
        // Get principal image
        $principal_image = get_post_meta($restaurant_id, '_restaurant_principal_image', true);
        if ($principal_image) {
            $image_url = wp_get_attachment_image_url($principal_image, 'large');
            if ($image_url) {
                array_unshift($images, array(
                    '@type' => 'ImageObject',
                    'url' => $image_url,
                    'caption' => get_post_meta($principal_image, '_wp_attachment_image_alt', true)
                ));
            }
        }
        
        return $images;
    }
    
    /**
     * Get restaurant logo
     */
    private function get_restaurant_logo($restaurant_id) {
        $logo_id = get_post_meta($restaurant_id, '_restaurant_logo', true);
        if ($logo_id) {
            $logo_url = wp_get_attachment_image_url($logo_id, 'medium');
            if ($logo_url) {
                return array(
                    '@type' => 'ImageObject',
                    'url' => $logo_url
                );
            }
        }
        
        // Fallback to site logo
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo_url = wp_get_attachment_image_url($custom_logo_id, 'medium');
            if ($logo_url) {
                return array(
                    '@type' => 'ImageObject',
                    'url' => $logo_url
                );
            }
        }
        
        return null;
    }
    
    /**
     * Get restaurant reviews for structured data
     */
    private function get_restaurant_reviews($restaurant_id) {
        // This would integrate with your review system
        // For now, return empty array
        return array();
    }
    
    /**
     * Calculate aggregate rating
     */
    private function calculate_aggregate_rating($reviews) {
        if (empty($reviews)) {
            return null;
        }
        
        $total_rating = 0;
        $review_count = count($reviews);
        
        foreach ($reviews as $review) {
            $total_rating += $review['ratingValue'];
        }
        
        $average_rating = $total_rating / $review_count;
        
        return array(
            '@type' => 'AggregateRating',
            'ratingValue' => round($average_rating, 1),
            'reviewCount' => $review_count,
            'bestRating' => 5,
            'worstRating' => 1
        );
    }
    
    /**
     * Format opening hours for structured data
     */
    private function format_opening_hours($opening_hours) {
        if (!$opening_hours) {
            return null;
        }
        
        $formatted_hours = array();
        $days = array(
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday'
        );
        
        foreach ($days as $day_key => $day_name) {
            if (isset($opening_hours[$day_key]) && !empty($opening_hours[$day_key]['open'])) {
                $formatted_hours[] = array(
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => $day_name,
                    'opens' => $opening_hours[$day_key]['open'],
                    'closes' => $opening_hours[$day_key]['close']
                );
            }
        }
        
        return $formatted_hours;
    }
    
    /**
     * Get social media links
     */
    private function get_social_media_links($restaurant_id) {
        $social_links = array();
        
        $facebook = get_post_meta($restaurant_id, '_restaurant_facebook', true);
        $instagram = get_post_meta($restaurant_id, '_restaurant_instagram', true);
        $twitter = get_post_meta($restaurant_id, '_restaurant_twitter', true);
        $website = get_post_meta($restaurant_id, '_restaurant_website', true);
        
        if ($facebook) $social_links[] = $facebook;
        if ($instagram) $social_links[] = $instagram;
        if ($twitter) $social_links[] = $twitter;
        if ($website) $social_links[] = $website;
        
        return $social_links;
    }
    
    /**
     * Get restaurant offers
     */
    private function get_restaurant_offers($restaurant_id) {
        $offers = array();
        
        // Get special offers from meta
        $special_offers = get_post_meta($restaurant_id, '_restaurant_special_offers', true);
        if ($special_offers) {
            foreach ($special_offers as $offer) {
                $offers[] = array(
                    '@type' => 'Offer',
                    'name' => $offer['name'],
                    'description' => $offer['description'],
                    'price' => $offer['price'],
                    'priceCurrency' => 'MAD',
                    'availability' => 'https://schema.org/InStock',
                    'validFrom' => $offer['valid_from'],
                    'validThrough' => $offer['valid_through']
                );
            }
        }
        
        return $offers;
    }
    
    /**
     * Add meta title
     */
    private function add_meta_title() {
        $title = $this->generate_meta_title();
        if ($title) {
            echo '<title>' . esc_html($title) . '</title>' . "\n";
        }
    }
    
    /**
     * Generate optimized meta title
     */
    private function generate_meta_title() {
        if (is_singular('restaurant')) {
            $restaurant_id = get_the_ID();
            $name = get_the_title($restaurant_id);
            $city = get_post_meta($restaurant_id, '_restaurant_city', true) ?: 'Casablanca';
            $cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true) ?: 'cuisine marocaine';
            
            // Check for custom SEO title
            $custom_title = get_post_meta($restaurant_id, '_seo_title', true);
            if ($custom_title) {
                return $custom_title;
            }
            
            return "{$name} - {$cuisine_type} à {$city}, Maroc | Restaurant avec Visite Virtuelle";
        } elseif (is_page_template('templates/all-restaurants.php')) {
            return "Restaurants Casablanca - Guide Complet avec Visites Virtuelles 360° | Le Bon Resto";
        }
        
        return null;
    }
    
    /**
     * Add meta description
     */
    private function add_meta_description() {
        $description = $this->generate_meta_description();
        if ($description) {
            echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        }
    }
    
    /**
     * Generate optimized meta description
     */
    private function generate_meta_description() {
        if (is_singular('restaurant')) {
            $restaurant_id = get_the_ID();
            
            // Check for custom SEO description
            $custom_description = get_post_meta($restaurant_id, '_seo_description', true);
            if ($custom_description) {
                return $custom_description;
            }
            
            return $this->generate_seo_description($restaurant_id);
        } elseif (is_page_template('templates/all-restaurants.php')) {
            return "Découvrez les meilleurs restaurants à Casablanca avec visites virtuelles 360°. Guide complet avec photos, menus, avis et réservations en ligne. Cuisine marocaine, internationale et gastronomie fine.";
        }
        
        return null;
    }
    
    /**
     * Generate SEO description for restaurant
     */
    private function generate_seo_description($restaurant_id) {
        $name = get_the_title($restaurant_id);
        $city = get_post_meta($restaurant_id, '_restaurant_city', true) ?: 'Casablanca';
        $cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true) ?: 'cuisine marocaine';
        $description = get_post_meta($restaurant_id, '_restaurant_description', true);
        
        if ($description) {
            $description = wp_trim_words($description, 20, '...');
        } else {
            $description = "Restaurant spécialisé en {$cuisine_type}";
        }
        
        return "Découvrez {$name} à {$city}, Maroc. {$description} avec visite virtuelle 360°, ambiance authentique. Réservation en ligne, menus, photos et avis clients.";
    }
    
    /**
     * Add meta keywords
     */
    private function add_meta_keywords() {
        $keywords = $this->generate_meta_keywords();
        if ($keywords) {
            echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
        }
    }
    
    /**
     * Generate meta keywords
     */
    private function generate_meta_keywords() {
        $keywords = array();
        
        if (is_singular('restaurant')) {
            $restaurant_id = get_the_ID();
            $name = get_the_title($restaurant_id);
            $city = get_post_meta($restaurant_id, '_restaurant_city', true) ?: 'Casablanca';
            $cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true) ?: 'cuisine marocaine';
            
            $keywords = array(
                "restaurant {$name}",
                "{$cuisine_type} {$city}",
                "restaurant {$city}",
                "visite virtuelle restaurant",
                "tour 360 restaurant",
                "gastronomie Maroc",
                "réservation restaurant {$city}",
                "restaurant halal {$city}",
                "meilleur restaurant {$city}",
                "restaurant pas cher {$city}"
            );
        } elseif (is_page_template('templates/all-restaurants.php')) {
            $keywords = array(
                "restaurants Casablanca",
                "cuisine marocaine",
                "gastronomie Maroc",
                "visite virtuelle restaurant",
                "tour 360 restaurant",
                "guide restaurants Casablanca",
                "réservation restaurant",
                "restaurant halal Casablanca",
                "meilleur restaurant Casablanca",
                "restaurant pas cher Casablanca"
            );
        }
        
        return implode(', ', $keywords);
    }
    
    /**
     * Add canonical URL
     */
    private function add_canonical_url() {
        $canonical_url = $this->get_canonical_url();
        if ($canonical_url) {
            echo '<link rel="canonical" href="' . esc_url($canonical_url) . '">' . "\n";
        }
    }
    
    /**
     * Get canonical URL
     */
    private function get_canonical_url() {
        if (is_singular('restaurant')) {
            return get_permalink();
        } elseif (is_page_template('templates/all-restaurants.php')) {
            return home_url('/restaurants/');
        }
        
        return null;
    }
    
    /**
     * Add robots meta
     */
    private function add_robots_meta() {
        $robots = $this->get_robots_meta();
        if ($robots) {
            echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";
        }
    }
    
    /**
     * Get robots meta content
     */
    private function get_robots_meta() {
        if (is_singular('restaurant')) {
            $noindex = get_post_meta(get_the_ID(), '_seo_noindex', true);
            if ($noindex) {
                return 'noindex, nofollow';
            }
        }
        
        return 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';
    }
    
    /**
     * Add geo meta tags
     */
    private function add_geo_meta() {
        if (!is_singular('restaurant')) {
            return;
        }
        
        $restaurant_id = get_the_ID();
        $latitude = get_post_meta($restaurant_id, '_restaurant_latitude', true);
        $longitude = get_post_meta($restaurant_id, '_restaurant_longitude', true);
        
        if ($latitude && $longitude) {
            echo '<meta name="geo.region" content="MA-CAS">' . "\n";
            echo '<meta name="geo.placename" content="Casablanca">' . "\n";
            echo '<meta name="geo.position" content="' . esc_attr($latitude) . ';' . esc_attr($longitude) . '">' . "\n";
            echo '<meta name="ICBM" content="' . esc_attr($latitude) . ', ' . esc_attr($longitude) . '">' . "\n";
        }
    }
    
    /**
     * Add alternate language tags
     */
    private function add_alternate_language_tags() {
        $current_url = get_permalink();
        
        echo '<link rel="alternate" hreflang="fr" href="' . esc_url($current_url . '?lang=fr') . '">' . "\n";
        echo '<link rel="alternate" hreflang="ar" href="' . esc_url($current_url . '?lang=ar') . '">' . "\n";
        echo '<link rel="alternate" hreflang="en" href="' . esc_url($current_url . '?lang=en') . '">' . "\n";
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($current_url) . '">' . "\n";
    }
    
    /**
     * Add Open Graph tags
     */
    private function add_open_graph_tags() {
        $og_data = $this->get_open_graph_data();
        
        if ($og_data['title']) {
            echo '<meta property="og:title" content="' . esc_attr($og_data['title']) . '">' . "\n";
        }
        if ($og_data['description']) {
            echo '<meta property="og:description" content="' . esc_attr($og_data['description']) . '">' . "\n";
        }
        if ($og_data['image']) {
            echo '<meta property="og:image" content="' . esc_url($og_data['image']) . '">' . "\n";
            echo '<meta property="og:image:width" content="1200">' . "\n";
            echo '<meta property="og:image:height" content="630">' . "\n";
        }
        if ($og_data['url']) {
            echo '<meta property="og:url" content="' . esc_url($og_data['url']) . '">' . "\n";
        }
        if ($og_data['type']) {
            echo '<meta property="og:type" content="' . esc_attr($og_data['type']) . '">' . "\n";
        }
        if ($og_data['site_name']) {
            echo '<meta property="og:site_name" content="' . esc_attr($og_data['site_name']) . '">' . "\n";
        }
        if ($og_data['locale']) {
            echo '<meta property="og:locale" content="' . esc_attr($og_data['locale']) . '">' . "\n";
        }
    }
    
    /**
     * Get Open Graph data
     */
    private function get_open_graph_data() {
        $data = array(
            'title' => '',
            'description' => '',
            'image' => '',
            'url' => '',
            'type' => 'website',
            'site_name' => get_bloginfo('name'),
            'locale' => 'fr_FR'
        );
        
        if (is_singular('restaurant')) {
            $restaurant_id = get_the_ID();
            $data['title'] = get_the_title($restaurant_id) . ' - Restaurant à Casablanca, Maroc';
            $data['description'] = $this->generate_seo_description($restaurant_id);
            $data['url'] = get_permalink($restaurant_id);
            $data['type'] = 'restaurant';
            
            // Get restaurant image
            $principal_image = get_post_meta($restaurant_id, '_restaurant_principal_image', true);
            if ($principal_image) {
                $image_url = wp_get_attachment_image_url($principal_image, 'large');
                if ($image_url) {
                    $data['image'] = $image_url;
                }
            }
        } elseif (is_page_template('templates/all-restaurants.php')) {
            $data['title'] = 'Restaurants Casablanca - Guide Complet avec Visites Virtuelles';
            $data['description'] = 'Découvrez les meilleurs restaurants à Casablanca avec visites virtuelles 360°. Guide complet avec photos, menus, avis et réservations en ligne.';
            $data['url'] = home_url('/restaurants/');
        }
        
        return $data;
    }
    
    /**
     * Add Twitter Card tags
     */
    private function add_twitter_card_tags() {
        $twitter_data = $this->get_twitter_card_data();
        
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        if ($twitter_data['title']) {
            echo '<meta name="twitter:title" content="' . esc_attr($twitter_data['title']) . '">' . "\n";
        }
        if ($twitter_data['description']) {
            echo '<meta name="twitter:description" content="' . esc_attr($twitter_data['description']) . '">' . "\n";
        }
        if ($twitter_data['image']) {
            echo '<meta name="twitter:image" content="' . esc_url($twitter_data['image']) . '">' . "\n";
        }
        if ($twitter_data['site']) {
            echo '<meta name="twitter:site" content="' . esc_attr($twitter_data['site']) . '">' . "\n";
        }
    }
    
    /**
     * Get Twitter Card data
     */
    private function get_twitter_card_data() {
        $og_data = $this->get_open_graph_data();
        
        return array(
            'title' => $og_data['title'],
            'description' => $og_data['description'],
            'image' => $og_data['image'],
            'site' => '@' . get_bloginfo('name')
        );
    }
    
    /**
     * Add Facebook specific tags
     */
    private function add_facebook_tags() {
        if (is_singular('restaurant')) {
            $restaurant_id = get_the_ID();
            $facebook_app_id = get_option('facebook_app_id');
            
            if ($facebook_app_id) {
                echo '<meta property="fb:app_id" content="' . esc_attr($facebook_app_id) . '">' . "\n";
            }
        }
    }
    
    /**
     * Add breadcrumb schema
     */
    public function add_breadcrumb_schema() {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return;
        }
        
        $breadcrumbs = $this->generate_breadcrumb_schema();
        if ($breadcrumbs) {
            echo '<script type="application/ld+json">' . "\n";
            echo wp_json_encode($breadcrumbs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            echo "\n" . '</script>' . "\n";
        }
    }
    
    /**
     * Generate breadcrumb schema
     */
    private function generate_breadcrumb_schema() {
        $breadcrumbs = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array()
        );
        
        $position = 1;
        
        // Home
        $breadcrumbs['itemListElement'][] = array(
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Accueil',
            'item' => home_url()
        );
        
        // Restaurants
        $breadcrumbs['itemListElement'][] = array(
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Restaurants',
            'item' => home_url('/restaurants/')
        );
        
        // Current page
        if (is_singular('restaurant')) {
            $restaurant_id = get_the_ID();
            $breadcrumbs['itemListElement'][] = array(
                '@type' => 'ListItem',
                'position' => $position,
                'name' => get_the_title($restaurant_id),
                'item' => get_permalink($restaurant_id)
            );
        }
        
        return $breadcrumbs;
    }
    
    /**
     * Add sitemap rewrite rules
     */
    public function add_sitemap_rewrite_rules() {
        add_rewrite_rule(
            '^sitemap-restaurants\.xml$',
            'index.php?lebonresto_sitemap=restaurants',
            'top'
        );
        add_rewrite_rule(
            '^sitemap-cuisines\.xml$',
            'index.php?lebonresto_sitemap=cuisines',
            'top'
        );
    }
    
    /**
     * Add sitemap query vars
     */
    public function add_sitemap_query_vars($vars) {
        $vars[] = 'lebonresto_sitemap';
        return $vars;
    }
    
    /**
     * Handle sitemap request
     */
    public function handle_sitemap_request() {
        $sitemap_type = get_query_var('lebonresto_sitemap');
        
        if ($sitemap_type) {
            $this->generate_xml_sitemap($sitemap_type);
            exit;
        }
    }
    
    /**
     * Generate XML sitemap
     */
    private function generate_xml_sitemap($type) {
        header('Content-Type: application/xml; charset=utf-8');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
        
        if ($type === 'restaurants') {
            $this->add_restaurants_to_sitemap();
        } elseif ($type === 'cuisines') {
            $this->add_cuisines_to_sitemap();
        }
        
        echo '</urlset>';
    }
    
    /**
     * Add restaurants to sitemap
     */
    private function add_restaurants_to_sitemap() {
        $restaurants = get_posts(array(
            'post_type' => 'restaurant',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query' => array(
                array(
                    'key' => '_seo_noindex',
                    'value' => '1',
                    'compare' => '!='
                )
            )
        ));
        
        foreach ($restaurants as $restaurant) {
            $lastmod = get_the_modified_date('c', $restaurant->ID);
            $priority = get_post_meta($restaurant->ID, '_restaurant_is_featured', true) ? '0.9' : '0.8';
            
            echo '<url>' . "\n";
            echo '<loc>' . get_permalink($restaurant->ID) . '</loc>' . "\n";
            echo '<lastmod>' . $lastmod . '</lastmod>' . "\n";
            echo '<changefreq>weekly</changefreq>' . "\n";
            echo '<priority>' . $priority . '</priority>' . "\n";
            
            // Add images
            $this->add_restaurant_images_to_sitemap($restaurant->ID);
            
            echo '</url>' . "\n";
        }
    }
    
    /**
     * Add cuisines to sitemap
     */
    private function add_cuisines_to_sitemap() {
        $cuisines = get_terms(array(
            'taxonomy' => 'cuisine_type',
            'hide_empty' => true
        ));
        
        foreach ($cuisines as $cuisine) {
            echo '<url>' . "\n";
            echo '<loc>' . get_term_link($cuisine) . '</loc>' . "\n";
            echo '<lastmod>' . current_time('c') . '</lastmod>' . "\n";
            echo '<changefreq>monthly</changefreq>' . "\n";
            echo '<priority>0.7</priority>' . "\n";
            echo '</url>' . "\n";
        }
    }
    
    /**
     * Add restaurant images to sitemap
     */
    private function add_restaurant_images_to_sitemap($restaurant_id) {
        $gallery_ids = get_post_meta($restaurant_id, '_restaurant_gallery', true);
        if ($gallery_ids) {
            $image_ids = explode(',', $gallery_ids);
            foreach ($image_ids as $image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'large');
                if ($image_url) {
                    echo '<image:image>' . "\n";
                    echo '<image:loc>' . $image_url . '</image:loc>' . "\n";
                    echo '<image:title>' . get_the_title($image_id) . '</image:title>' . "\n";
                    echo '<image:caption>' . get_post_meta($image_id, '_wp_attachment_image_alt', true) . '</image:caption>' . "\n";
                    echo '</image:image>' . "\n";
                }
            }
        }
    }
    
    /**
     * Optimize image attributes for SEO
     */
    public function optimize_image_attributes($attr, $attachment, $size) {
        if (is_singular('restaurant')) {
            // Add lazy loading
            $attr['loading'] = 'lazy';
            
            // Optimize alt text
            if (empty($attr['alt'])) {
                $restaurant_name = get_the_title();
                $attr['alt'] = "Image du restaurant {$restaurant_name} à Casablanca, Maroc";
            }
            
            // Add decoding attribute
            $attr['decoding'] = 'async';
        }
        
        return $attr;
    }
    
    /**
     * Enqueue lazy loading script
     */
    public function enqueue_lazy_loading_script() {
        if (is_singular('restaurant') || is_page_template('templates/all-restaurants.php')) {
            wp_enqueue_script(
                'lebonresto-lazy-loading',
                LEBONRESTO_PLUGIN_URL . 'assets/js/lazy-loading.js',
                array(),
                $this->plugin_version,
                true
            );
        }
    }
    
    /**
     * Optimize page title
     */
    public function optimize_page_title($title, $sep, $seplocation) {
        if (is_singular('restaurant')) {
            $restaurant_id = get_the_ID();
            $custom_title = get_post_meta($restaurant_id, '_seo_title', true);
            
            if ($custom_title) {
                return $custom_title;
            }
            
            $name = get_the_title($restaurant_id);
            $city = get_post_meta($restaurant_id, '_restaurant_city', true) ?: 'Casablanca';
            
            return "{$name} - Restaurant à {$city}, Maroc";
        }
        
        return $title;
    }
    
    /**
     * Optimize document title
     */
    public function optimize_document_title($title) {
        if (is_singular('restaurant')) {
            $restaurant_id = get_the_ID();
            $custom_title = get_post_meta($restaurant_id, '_seo_title', true);
            
            if ($custom_title) {
                return array('title' => $custom_title);
            }
        }
        
        return $title;
    }
    
    /**
     * Optimize scripts loading
     */
    public function optimize_scripts_loading() {
        if (is_singular('restaurant') || is_page_template('templates/all-restaurants.php')) {
            // Defer non-critical scripts
            add_filter('script_loader_tag', array($this, 'defer_scripts'), 10, 2);
        }
    }
    
    /**
     * Defer scripts for better performance
     */
    public function defer_scripts($tag, $handle) {
        $defer_scripts = array(
            'lebonresto-lazy-loading',
            'lebonresto-all-restaurants',
            'lebonresto-detail-js'
        );
        
        if (in_array($handle, $defer_scripts)) {
            return str_replace('<script ', '<script defer ', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Add SEO meta boxes
     */
    public function add_seo_meta_boxes() {
        add_meta_box(
            'lebonresto_seo_meta',
            'SEO Settings',
            array($this, 'seo_meta_box_callback'),
            'restaurant',
            'normal',
            'high'
        );
    }
    
    /**
     * SEO meta box callback
     */
    public function seo_meta_box_callback($post) {
        wp_nonce_field('lebonresto_seo_meta', 'lebonresto_seo_nonce');
        
        $seo_title = get_post_meta($post->ID, '_seo_title', true);
        $seo_description = get_post_meta($post->ID, '_seo_description', true);
        $seo_keywords = get_post_meta($post->ID, '_seo_keywords', true);
        $noindex = get_post_meta($post->ID, '_seo_noindex', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="seo_title">SEO Title</label></th>
                <td>
                    <input type="text" id="seo_title" name="seo_title" value="<?php echo esc_attr($seo_title); ?>" class="regular-text" />
                    <p class="description">Custom title for search engines (leave empty for auto-generated)</p>
                </td>
            </tr>
            <tr>
                <th><label for="seo_description">SEO Description</label></th>
                <td>
                    <textarea id="seo_description" name="seo_description" rows="3" class="large-text"><?php echo esc_textarea($seo_description); ?></textarea>
                    <p class="description">Custom meta description (leave empty for auto-generated)</p>
                </td>
            </tr>
            <tr>
                <th><label for="seo_keywords">SEO Keywords</label></th>
                <td>
                    <input type="text" id="seo_keywords" name="seo_keywords" value="<?php echo esc_attr($seo_keywords); ?>" class="large-text" />
                    <p class="description">Comma-separated keywords</p>
                </td>
            </tr>
            <tr>
                <th><label for="noindex">No Index</label></th>
                <td>
                    <label>
                        <input type="checkbox" id="noindex" name="noindex" value="1" <?php checked($noindex, '1'); ?> />
                        Prevent this page from being indexed by search engines
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save SEO meta data
     */
    public function save_seo_meta_data($post_id) {
        if (!isset($_POST['lebonresto_seo_nonce']) || !wp_verify_nonce($_POST['lebonresto_seo_nonce'], 'lebonresto_seo_meta')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $fields = array('_seo_title', '_seo_description', '_seo_keywords');
        foreach ($fields as $field) {
            if (isset($_POST[str_replace('_', '', $field)])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[str_replace('_', '', $field)]));
            }
        }
        
        $noindex = isset($_POST['noindex']) ? '1' : '0';
        update_post_meta($post_id, '_seo_noindex', $noindex);
    }
    
    /**
     * Register REST API routes for SEO data
     */
    public function register_seo_rest_routes() {
        register_rest_route('lebonresto/v1', '/seo/restaurant/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_restaurant_seo_data'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * Get restaurant SEO data via REST API
     */
    public function get_restaurant_seo_data($request) {
        $restaurant_id = $request['id'];
        $restaurant = get_post($restaurant_id);
        
        if (!$restaurant || $restaurant->post_type !== 'restaurant') {
            return new WP_Error('restaurant_not_found', 'Restaurant not found', array('status' => 404));
        }
        
        return array(
            'title' => $this->generate_meta_title(),
            'description' => $this->generate_meta_description(),
            'keywords' => $this->generate_meta_keywords(),
            'structured_data' => $this->generate_restaurant_structured_data($restaurant_id)
        );
    }
}

// Initialize advanced SEO
new LeBonResto_Advanced_SEO();
