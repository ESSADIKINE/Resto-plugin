<?php
/**
 * SEO Meta Descriptions for Le Bon Resto Plugin
 * Multi-language support for French, Arabic, and English
 * 
 * @package LeBonResto
 */

if (!defined('ABSPATH')) {
    exit;
}

class LeBonResto_SEO_Meta {
    
    public function __construct() {
        add_action('wp_head', array($this, 'add_seo_meta_descriptions'), 1);
    }
    
    /**
     * Add SEO meta descriptions for restaurant pages
     */
    public function add_seo_meta_descriptions() {
        // Only add meta descriptions on restaurant-related pages
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php') && !is_page_template('templates/single-restaurant.php')) {
            return;
        }
        
        // Get current language (default to French)
        $current_lang = $this->get_current_language();
        
        // Get meta description based on page type and language
        $meta_description = $this->get_meta_description($current_lang);
        
        if ($meta_description) {
            echo '<!-- Le Bon Resto SEO Meta Tags -->' . "\n";
            echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
            
            // Add Open Graph meta tags
            echo '<meta property="og:description" content="' . esc_attr($meta_description) . '">' . "\n";
            echo '<meta property="og:type" content="website">' . "\n";
            echo '<meta property="og:locale" content="' . $this->get_og_locale($current_lang) . '">' . "\n";
            
            // Add Twitter Card meta tags
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
            echo '<meta name="twitter:description" content="' . esc_attr($meta_description) . '">' . "\n";
            
            // Add hreflang tags for multi-language support
            $this->add_hreflang_tags();
        }
    }
    
    /**
     * Get current language
     */
    private function get_current_language() {
        // Check if WPML or Polylang is active
        if (function_exists('pll_current_language')) {
            return pll_current_language();
        }
        
        if (function_exists('wpml_get_current_language')) {
            return wpml_get_current_language();
        }
        
        // Check URL parameters for language
        if (isset($_GET['lang'])) {
            $lang = sanitize_text_field($_GET['lang']);
            if (in_array($lang, ['fr', 'ar', 'en'])) {
                return $lang;
            }
        }
        
        // Check browser language
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (in_array($browser_lang, ['fr', 'ar', 'en'])) {
                return $browser_lang;
            }
        }
        
        // Default to French for Morocco
        return 'fr';
    }
    
    /**
     * Get meta description based on page type and language
     */
    private function get_meta_description($lang) {
        if (is_singular('restaurant')) {
            return $this->get_restaurant_detail_meta($lang);
        } elseif (is_page_template('templates/all-restaurants.php')) {
            return $this->get_all_restaurants_meta($lang);
        } elseif (is_page_template('templates/single-restaurant.php')) {
            return $this->get_single_restaurant_meta($lang);
        }
        
        return '';
    }
    
    /**
     * Get restaurant detail page meta description
     */
    private function get_restaurant_detail_meta($lang) {
        $restaurant_name = get_the_title();
        $cuisine_type = get_post_meta(get_the_ID(), '_restaurant_cuisine_type', true);
        $city = get_post_meta(get_the_ID(), '_restaurant_city', true);
        
        // Use city from meta or default to Casablanca
        $city = $city ?: 'Casablanca';
        $cuisine_type = $cuisine_type ?: 'cuisine marocaine';
        
        $meta_descriptions = [
            'fr' => "Découvrez {$restaurant_name} à {$city}, Maroc. Restaurant spécialisé en {$cuisine_type} avec visite virtuelle 360°, ambiance authentique. Réservation en ligne, menus, photos, tour virtuel et avis clients. Le meilleur de la gastronomie marocaine à {$city}.",
            'ar' => "اكتشف مطعم {$restaurant_name} في الدار البيضاء، المغرب. مطعم متخصص في {$cuisine_type} مع جولة افتراضية 360 درجة وأجواء أصيلة. حجز عبر الإنترنت، قوائم الطعام، الصور، جولة افتراضية وآراء العملاء. أفضل المأكولات المغربية في الدار البيضاء.",
            'en' => "Discover {$restaurant_name} restaurant in {$city}, Morocco. Specialized in {$cuisine_type} with 360° virtual tour and authentic atmosphere. Online booking, menus, photos, virtual tour and customer reviews. The best of Moroccan gastronomy in {$city}."
        ];
        
        return $meta_descriptions[$lang] ?? $meta_descriptions['fr'];
    }
    
    /**
     * Get all restaurants page meta description
     */
    private function get_all_restaurants_meta($lang) {
        $meta_descriptions = [
            'fr' => "Guide complet des meilleurs restaurants à Casablanca, Maroc. Découvrez plus de 500 restaurants, cafés et bars avec visites virtuelles 360°, photos, menus, avis et réservations en ligne. Cuisine marocaine, internationale, fast-food et gastronomie fine avec tours virtuels.",
            'ar' => "دليل شامل لأفضل المطاعم في الدار البيضاء، المغرب. اكتشف أكثر من 500 مطعم ومقهى وبار مع جولات افتراضية 360 درجة، الصور وقوائم الطعام والآراء والحجز عبر الإنترنت. المأكولات المغربية والدولية والوجبات السريعة والمأكولات الفاخرة مع جولات افتراضية.",
            'en' => "Complete guide to the best restaurants in Casablanca, Morocco. Discover over 500 restaurants, cafes and bars with 360° virtual tours, photos, menus, reviews and online booking. Moroccan, international, fast-food and fine dining cuisine with virtual experiences."
        ];
        
        return $meta_descriptions[$lang] ?? $meta_descriptions['fr'];
    }
    
    /**
     * Get single restaurant page meta description
     */
    private function get_single_restaurant_meta($lang) {
        $meta_descriptions = [
            'fr' => "Restaurant d'exception à Casablanca, Maroc. Cuisine authentique, ambiance chaleureuse et service impeccable. Découvrez nos spécialités culinaires avec visite virtuelle 360°, réservez votre table et vivez une expérience gastronomique unique au cœur de la capitale économique.",
            'ar' => "مطعم استثنائي في الدار البيضاء، المغرب. مطبخ أصيل وأجواء دافئة وخدمة لا تشوبها شائبة. اكتشف تخصصاتنا الطهوية مع جولة افتراضية 360 درجة واحجز طاولتك واستمتع بتجربة طهوية فريدة في قلب العاصمة الاقتصادية.",
            'en' => "Exceptional restaurant in Casablanca, Morocco. Authentic cuisine, warm atmosphere and impeccable service. Discover our culinary specialties with 360° virtual tour, book your table and experience a unique gastronomic journey in the heart of the economic capital."
        ];
        
        return $meta_descriptions[$lang] ?? $meta_descriptions['fr'];
    }
    
    /**
     * Get Open Graph locale
     */
    private function get_og_locale($lang) {
        $locales = [
            'fr' => 'fr_FR',
            'ar' => 'ar_MA',
            'en' => 'en_US'
        ];
        
        return $locales[$lang] ?? 'fr_FR';
    }
    
    /**
     * Add hreflang tags for multi-language support
     */
    private function add_hreflang_tags() {
        $current_url = get_permalink();
        $base_url = home_url();
        
        // Add hreflang tags for each language
        echo '<link rel="alternate" hreflang="fr" href="' . esc_url($current_url . '?lang=fr') . '">' . "\n";
        echo '<link rel="alternate" hreflang="ar" href="' . esc_url($current_url . '?lang=ar') . '">' . "\n";
        echo '<link rel="alternate" hreflang="en" href="' . esc_url($current_url . '?lang=en') . '">' . "\n";
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($current_url) . '">' . "\n";
    }
    
    /**
     * Get comprehensive SEO keywords for each language
     */
    public static function get_seo_keywords($lang = 'fr') {
        $keywords = [
            'fr' => [
                'restaurants Casablanca',
                'cuisine marocaine',
                'gastronomie Maroc',
                'réservation restaurant',
                'guide restaurants',
                'cafés Casablanca',
                'bars Casablanca',
                'restaurant halal',
                'cuisine traditionnelle marocaine',
                'restaurant Casablanca centre ville',
                'meilleur restaurant Casablanca',
                'restaurant pas cher Casablanca',
                'restaurant romantique Casablanca',
                'restaurant famille Casablanca',
                'visite virtuelle restaurant',
                'tour virtuel restaurant',
                'visite 360 restaurant',
                'tour 360 restaurant',
                'visite immersive restaurant',
                'découverte virtuelle restaurant',
                'exploration virtuelle restaurant',
                'visite en ligne restaurant',
                'découverte interactive restaurant',
                'expérience virtuelle restaurant',
                'visite numérique restaurant',
                'tour digital restaurant',
                'visite à distance restaurant',
                'découverte en ligne restaurant',
                'restaurant avec visite virtuelle',
                'restaurant tour virtuel',
                'restaurant 360 degrés',
                'restaurant visite immersive'
            ],
            'ar' => [
                'مطاعم الدار البيضاء',
                'المأكولات المغربية',
                'الطبخ المغربي',
                'حجز مطعم',
                'دليل المطاعم',
                'مقاهي الدار البيضاء',
                'بارات الدار البيضاء',
                'مطعم حلال',
                'المطبخ المغربي التقليدي',
                'مطعم الدار البيضاء وسط المدينة',
                'أفضل مطعم الدار البيضاء',
                'مطعم رخيص الدار البيضاء',
                'مطعم رومانسي الدار البيضاء',
                'مطعم عائلي الدار البيضاء',
                'جولة افتراضية مطعم',
                'زيارة افتراضية مطعم',
                'جولة 360 مطعم',
                'زيارة 360 مطعم',
                'جولة تفاعلية مطعم',
                'استكشاف افتراضي مطعم',
                'تجربة افتراضية مطعم',
                'زيارة رقمية مطعم',
                'جولة رقمية مطعم',
                'زيارة عن بُعد مطعم',
                'استكشاف عبر الإنترنت مطعم',
                'جولة غامرة مطعم',
                'مطعم بجولة افتراضية',
                'مطعم زيارة 360',
                'مطعم جولة تفاعلية',
                'مطعم استكشاف افتراضي',
                'مطعم تجربة رقمية'
            ],
            'en' => [
                'restaurants Casablanca',
                'Moroccan cuisine',
                'Morocco gastronomy',
                'restaurant booking',
                'restaurant guide',
                'cafes Casablanca',
                'bars Casablanca',
                'halal restaurant',
                'traditional Moroccan cuisine',
                'restaurant Casablanca city center',
                'best restaurant Casablanca',
                'cheap restaurant Casablanca',
                'romantic restaurant Casablanca',
                'family restaurant Casablanca',
                'virtual tour restaurant',
                '360 tour restaurant',
                'virtual visit restaurant',
                '360 visit restaurant',
                'immersive tour restaurant',
                'virtual exploration restaurant',
                'interactive tour restaurant',
                'online tour restaurant',
                'digital tour restaurant',
                'virtual experience restaurant',
                '360 experience restaurant',
                'virtual discovery restaurant',
                'online exploration restaurant',
                'restaurant virtual tour',
                'restaurant 360 tour',
                'restaurant virtual visit',
                'restaurant immersive experience',
                'restaurant digital tour',
                'restaurant virtual exploration',
                'restaurant interactive experience'
            ]
        ];
        
        return $keywords[$lang] ?? $keywords['fr'];
    }
    
    /**
     * Get structured data for restaurants
     */
    public static function get_restaurant_structured_data($restaurant_id) {
        $restaurant_name = get_the_title($restaurant_id);
        $cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true);
        $city = get_post_meta($restaurant_id, '_restaurant_city', true);
        $address = get_post_meta($restaurant_id, '_restaurant_address', true);
        $phone = get_post_meta($restaurant_id, '_restaurant_phone', true);
        $email = get_post_meta($restaurant_id, '_restaurant_email', true);
        $latitude = get_post_meta($restaurant_id, '_restaurant_latitude', true);
        $longitude = get_post_meta($restaurant_id, '_restaurant_longitude', true);
        
        $structured_data = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $restaurant_name,
            'description' => get_post_meta($restaurant_id, '_restaurant_description', true),
            'url' => get_permalink($restaurant_id),
            'servesCuisine' => $cuisine_type ?: 'Moroccan cuisine',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
                'addressLocality' => $city ?: 'Casablanca',
                'addressCountry' => 'MA'
            ]
        ];
        
        if ($phone) {
            $structured_data['telephone'] = $phone;
        }
        
        if ($email) {
            $structured_data['email'] = $email;
        }
        
        if ($latitude && $longitude) {
            $structured_data['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $latitude,
                'longitude' => $longitude
            ];
        }
        
        return $structured_data;
    }
}

// Initialize SEO Meta
new LeBonResto_SEO_Meta();
