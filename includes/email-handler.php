<?php
/**
 * Email Handler for Le Bon Resto Plugin
 * 
 * @package LeBonResto
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LeBonResto_Email_Handler {
    
    /**
     * Initialize email handler
     */
    public function __construct() {
        add_action('init', array($this, 'init_smtp'));
        
        // Add SEO meta descriptions
        add_action('wp_head', array($this, 'add_seo_meta_descriptions'));
    }
    
    /**
     * Initialize SMTP configuration
     */
    public function init_smtp() {
        $options = get_option('lebonresto_options', array());
        
        if (isset($options['smtp_enabled']) && $options['smtp_enabled'] === '1') {
            // Force WordPress to use SMTP
            add_action('phpmailer_init', array($this, 'configure_smtp'));
            
            // Override wp_mail to use SMTP
            add_filter('wp_mail', array($this, 'override_wp_mail'), 1);
        }
    }
    
    /**
     * Configure SMTP settings
     */
    public function configure_smtp($phpmailer) {
        $options = get_option('lebonresto_options', array());
        
        if (!isset($options['smtp_enabled']) || $options['smtp_enabled'] !== '1') {
            return;
        }
        
        $phpmailer->isSMTP();
        $phpmailer->Host = isset($options['smtp_host']) ? $options['smtp_host'] : 'smtp.gmail.com';
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = isset($options['smtp_port']) ? intval($options['smtp_port']) : 587;
        $phpmailer->Username = isset($options['smtp_username']) ? $options['smtp_username'] : '';
        $phpmailer->Password = isset($options['smtp_password']) ? $options['smtp_password'] : '';
        
        // Set encryption
        $encryption = isset($options['smtp_encryption']) ? $options['smtp_encryption'] : 'tls';
        if ($encryption === 'ssl') {
            $phpmailer->SMTPSecure = 'ssl';
        } elseif ($encryption === 'tls') {
            $phpmailer->SMTPSecure = 'tls';
        }
        
        // Set from email and name
        $from_email = isset($options['smtp_from_email']) ? $options['smtp_from_email'] : get_option('admin_email');
        $from_name = isset($options['smtp_from_name']) ? $options['smtp_from_name'] : get_bloginfo('name');
        
        $phpmailer->setFrom($from_email, $from_name);
    }
    
    /**
     * Override wp_mail to ensure SMTP is used
     */
    public function override_wp_mail($args) {
        // Force WordPress to use PHPMailer with SMTP
        add_action('phpmailer_init', array($this, 'configure_smtp'), 999);
        return $args;
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
            echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
            
            // Add Open Graph meta tags
            echo '<meta property="og:description" content="' . esc_attr($meta_description) . '">' . "\n";
            echo '<meta property="og:type" content="website">' . "\n";
            echo '<meta property="og:locale" content="' . $this->get_og_locale($current_lang) . '">' . "\n";
            
            // Add Twitter Card meta tags
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
            echo '<meta name="twitter:description" content="' . esc_attr($meta_description) . '">' . "\n";
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
        $description = get_post_meta(get_the_ID(), '_restaurant_description', true);
        
        // Use city from meta or default to Casablanca
        $city = $city ?: 'Casablanca';
        $cuisine_type = $cuisine_type ?: 'cuisine marocaine';
        
        $meta_descriptions = [
            'fr' => "Découvrez {$restaurant_name} à {$city}, Maroc. Restaurant spécialisé en {$cuisine_type} avec une ambiance authentique. Réservation en ligne, menus, photos et avis clients. Le meilleur de la gastronomie marocaine à {$city}.",
            'ar' => "اكتشف مطعم {$restaurant_name} في الدار البيضاء، المغرب. مطعم متخصص في {$cuisine_type} مع أجواء أصيلة. حجز عبر الإنترنت، قوائم الطعام، الصور وآراء العملاء. أفضل المأكولات المغربية في الدار البيضاء.",
            'en' => "Discover {$restaurant_name} restaurant in {$city}, Morocco. Specialized in {$cuisine_type} with authentic atmosphere. Online booking, menus, photos and customer reviews. The best of Moroccan gastronomy in {$city}."
        ];
        
        return $meta_descriptions[$lang] ?? $meta_descriptions['fr'];
    }
    
    /**
     * Get all restaurants page meta description
     */
    private function get_all_restaurants_meta($lang) {
        $meta_descriptions = [
            'fr' => "Guide complet des meilleurs restaurants à Casablanca, Maroc. Découvrez plus de 500 restaurants, cafés et bars avec photos, menus, avis et réservations en ligne. Cuisine marocaine, internationale, fast-food et gastronomie fine.",
            'ar' => "دليل شامل لأفضل المطاعم في الدار البيضاء، المغرب. اكتشف أكثر من 500 مطعم ومقهى وبار مع الصور وقوائم الطعام والآراء والحجز عبر الإنترنت. المأكولات المغربية والدولية والوجبات السريعة والمأكولات الفاخرة.",
            'en' => "Complete guide to the best restaurants in Casablanca, Morocco. Discover over 500 restaurants, cafes and bars with photos, menus, reviews and online booking. Moroccan, international, fast-food and fine dining cuisine."
        ];
        
        return $meta_descriptions[$lang] ?? $meta_descriptions['fr'];
    }
    
    /**
     * Get single restaurant page meta description
     */
    private function get_single_restaurant_meta($lang) {
        $meta_descriptions = [
            'fr' => "Restaurant d'exception à Casablanca, Maroc. Cuisine authentique, ambiance chaleureuse et service impeccable. Découvrez nos spécialités culinaires, réservez votre table et vivez une expérience gastronomique unique au cœur de la capitale économique.",
            'ar' => "مطعم استثنائي في الدار البيضاء، المغرب. مطبخ أصيل وأجواء دافئة وخدمة لا تشوبها شائبة. اكتشف تخصصاتنا الطهوية واحجز طاولتك واستمتع بتجربة طهوية فريدة في قلب العاصمة الاقتصادية.",
            'en' => "Exceptional restaurant in Casablanca, Morocco. Authentic cuisine, warm atmosphere and impeccable service. Discover our culinary specialties, book your table and experience a unique gastronomic journey in the heart of the economic capital."
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
    
}

// Initialize email handler
new LeBonResto_Email_Handler();
