<?php
/**
 * HTML Structure and Accessibility Optimization
 * 
 * @package LeBonResto
 */

if (!defined('ABSPATH')) {
    exit;
}

class LeBonResto_HTML_Optimization {
    
    public function __construct() {
        // Add semantic HTML improvements
        add_filter('the_content', array($this, 'optimize_restaurant_content'), 10, 1);
        add_action('wp_head', array($this, 'add_accessibility_meta'), 1);
        
        // Add ARIA labels and roles
        add_action('wp_footer', array($this, 'add_aria_improvements'));
        
        // Optimize heading structure
        add_filter('the_title', array($this, 'optimize_heading_structure'), 10, 2);
        
        // Add skip links
        add_action('wp_body_open', array($this, 'add_skip_links'));
        
        // Add language attributes
        add_filter('language_attributes', array($this, 'add_language_attributes'));
    }
    
    /**
     * Optimize restaurant content for semantic HTML
     */
    public function optimize_restaurant_content($content) {
        if (!is_singular('restaurant')) {
            return $content;
        }
        
        // Add semantic structure to restaurant content
        $content = $this->add_semantic_markup($content);
        
        return $content;
    }
    
    /**
     * Add semantic markup to content
     */
    private function add_semantic_markup($content) {
        // Wrap content in semantic elements
        $content = '<article class="restaurant-content" itemscope itemtype="https://schema.org/Restaurant">' . $content . '</article>';
        
        return $content;
    }
    
    /**
     * Add accessibility meta tags
     */
    public function add_accessibility_meta() {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return;
        }
        
        echo '<!-- Accessibility Meta Tags -->' . "\n";
        echo '<meta name="theme-color" content="#fedc00">' . "\n";
        echo '<meta name="msapplication-TileColor" content="#fedc00">' . "\n";
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">' . "\n";
    }
    
    /**
     * Add ARIA improvements
     */
    public function add_aria_improvements() {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return;
        }
        
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add ARIA labels to interactive elements
            const buttons = document.querySelectorAll('button:not([aria-label])');
            buttons.forEach(button => {
                if (!button.getAttribute('aria-label') && button.textContent.trim()) {
                    button.setAttribute('aria-label', button.textContent.trim());
                }
            });
            
            // Add ARIA labels to links
            const links = document.querySelectorAll('a:not([aria-label])');
            links.forEach(link => {
                if (!link.getAttribute('aria-label') && link.textContent.trim()) {
                    link.setAttribute('aria-label', link.textContent.trim());
                }
            });
            
            // Add role attributes to custom elements
            const customButtons = document.querySelectorAll('.action-icon-btn');
            customButtons.forEach(btn => {
                btn.setAttribute('role', 'button');
                btn.setAttribute('tabindex', '0');
            });
            
            // Add keyboard navigation support
            customButtons.forEach(btn => {
                btn.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        btn.click();
                    }
                });
            });
            
            // Add focus management for modals
            const modals = document.querySelectorAll('.modal, .popup');
            modals.forEach(modal => {
                const focusableElements = modal.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                
                if (focusableElements.length > 0) {
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];
                    
                    modal.addEventListener('keydown', function(e) {
                        if (e.key === 'Tab') {
                            if (e.shiftKey) {
                                if (document.activeElement === firstElement) {
                                    e.preventDefault();
                                    lastElement.focus();
                                }
                            } else {
                                if (document.activeElement === lastElement) {
                                    e.preventDefault();
                                    firstElement.focus();
                                }
                            }
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Optimize heading structure
     */
    public function optimize_heading_structure($title, $id) {
        if (!is_singular('restaurant')) {
            return $title;
        }
        
        // Add proper heading hierarchy
        if (is_single() && in_the_loop()) {
            return '<h1 class="restaurant-title" itemprop="name">' . $title . '</h1>';
        }
        
        return $title;
    }
    
    /**
     * Add skip links for accessibility
     */
    public function add_skip_links() {
        if (!is_singular('restaurant') && !is_page_template('templates/all-restaurants.php')) {
            return;
        }
        
        ?>
        <a class="skip-link screen-reader-text" href="#main-content">
            <?php _e('Skip to main content', 'le-bon-resto'); ?>
        </a>
        <a class="skip-link screen-reader-text" href="#restaurant-navigation">
            <?php _e('Skip to restaurant navigation', 'le-bon-resto'); ?>
        </a>
        <?php
    }
    
    /**
     * Add language attributes
     */
    public function add_language_attributes($output) {
        if (is_singular('restaurant')) {
            $output .= ' itemscope itemtype="https://schema.org/Restaurant"';
        }
        
        return $output;
    }
    
    /**
     * Generate semantic restaurant card HTML
     */
    public static function generate_restaurant_card_html($restaurant_id) {
        $restaurant = get_post($restaurant_id);
        if (!$restaurant) {
            return '';
        }
        
        $name = get_the_title($restaurant_id);
        $cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true);
        $city = get_post_meta($restaurant_id, '_restaurant_city', true);
        $address = get_post_meta($restaurant_id, '_restaurant_address', true);
        $phone = get_post_meta($restaurant_id, '_restaurant_phone', true);
        $email = get_post_meta($restaurant_id, '_restaurant_email', true);
        $description = get_post_meta($restaurant_id, '_restaurant_description', true);
        $is_featured = get_post_meta($restaurant_id, '_restaurant_is_featured', true);
        $latitude = get_post_meta($restaurant_id, '_restaurant_latitude', true);
        $longitude = get_post_meta($restaurant_id, '_restaurant_longitude', true);
        
        $html = '<article class="restaurant-card" itemscope itemtype="https://schema.org/Restaurant" role="article">';
        
        // Restaurant header
        $html .= '<header class="restaurant-header">';
        $html .= '<h2 class="restaurant-name" itemprop="name">' . esc_html($name) . '</h2>';
        
        if ($cuisine_type) {
            $html .= '<span class="restaurant-cuisine" itemprop="servesCuisine">' . esc_html($cuisine_type) . '</span>';
        }
        
        if ($is_featured) {
            $html .= '<span class="featured-badge" aria-label="Restaurant en vedette">★</span>';
        }
        
        $html .= '</header>';
        
        // Restaurant content
        $html .= '<div class="restaurant-content">';
        
        if ($description) {
            $html .= '<div class="restaurant-description" itemprop="description">';
            $html .= '<p>' . esc_html(wp_trim_words($description, 30)) . '</p>';
            $html .= '</div>';
        }
        
        // Restaurant details
        $html .= '<div class="restaurant-details">';
        
        if ($address) {
            $html .= '<div class="restaurant-address" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">';
            $html .= '<span class="address-label">Adresse:</span> ';
            $html .= '<span itemprop="streetAddress">' . esc_html($address) . '</span>';
            if ($city) {
                $html .= ', <span itemprop="addressLocality">' . esc_html($city) . '</span>';
            }
            $html .= '</div>';
        }
        
        if ($phone) {
            $html .= '<div class="restaurant-phone">';
            $html .= '<span class="phone-label">Téléphone:</span> ';
            $html .= '<a href="tel:' . esc_attr($phone) . '" itemprop="telephone" aria-label="Appeler le restaurant">' . esc_html($phone) . '</a>';
            $html .= '</div>';
        }
        
        if ($email) {
            $html .= '<div class="restaurant-email">';
            $html .= '<span class="email-label">Email:</span> ';
            $html .= '<a href="mailto:' . esc_attr($email) . '" itemprop="email" aria-label="Envoyer un email au restaurant">' . esc_html($email) . '</a>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        // Restaurant actions
        $html .= '<div class="restaurant-actions" role="group" aria-label="Actions du restaurant">';
        $html .= '<a href="' . get_permalink($restaurant_id) . '" class="btn btn-primary" aria-label="Voir les détails du restaurant ' . esc_attr($name) . '">Voir détails</a>';
        
        if ($phone) {
            $html .= '<a href="tel:' . esc_attr($phone) . '" class="btn btn-secondary" aria-label="Appeler le restaurant ' . esc_attr($name) . '">Appeler</a>';
        }
        
        $html .= '</div>';
        
        // Hidden structured data
        $html .= '<div class="restaurant-meta" style="display: none;">';
        $html .= '<span itemprop="url">' . get_permalink($restaurant_id) . '</span>';
        
        if ($latitude && $longitude) {
            $html .= '<div itemprop="geo" itemscope itemtype="https://schema.org/GeoCoordinates">';
            $html .= '<meta itemprop="latitude" content="' . esc_attr($latitude) . '">';
            $html .= '<meta itemprop="longitude" content="' . esc_attr($longitude) . '">';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</article>';
        
        return $html;
    }
    
    /**
     * Generate semantic navigation HTML
     */
    public static function generate_navigation_html($items) {
        $html = '<nav class="restaurant-navigation" role="navigation" aria-label="Navigation du restaurant">';
        $html .= '<ul class="nav-list" role="list">';
        
        foreach ($items as $item) {
            $html .= '<li class="nav-item" role="listitem">';
            $html .= '<a href="' . esc_url($item['url']) . '" class="nav-link"';
            
            if (isset($item['aria_label'])) {
                $html .= ' aria-label="' . esc_attr($item['aria_label']) . '"';
            }
            
            if (isset($item['current']) && $item['current']) {
                $html .= ' aria-current="page"';
            }
            
            $html .= '>' . esc_html($item['text']) . '</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Generate semantic form HTML
     */
    public static function generate_form_html($form_data) {
        $html = '<form class="restaurant-form" method="post" action="#" role="form" aria-label="Formulaire de contact du restaurant">';
        
        if (isset($form_data['fields'])) {
            foreach ($form_data['fields'] as $field) {
                $html .= '<div class="form-group">';
                
                if (isset($field['label'])) {
                    $html .= '<label for="' . esc_attr($field['id']) . '" class="form-label">' . esc_html($field['label']) . '</label>';
                }
                
                $html .= '<' . $field['type'] . ' ';
                $html .= 'id="' . esc_attr($field['id']) . '" ';
                $html .= 'name="' . esc_attr($field['name']) . '" ';
                $html .= 'class="form-control" ';
                
                if (isset($field['required']) && $field['required']) {
                    $html .= 'required aria-required="true" ';
                }
                
                if (isset($field['placeholder'])) {
                    $html .= 'placeholder="' . esc_attr($field['placeholder']) . '" ';
                }
                
                if (isset($field['aria_label'])) {
                    $html .= 'aria-label="' . esc_attr($field['aria_label']) . '" ';
                }
                
                $html .= '>';
                
                if ($field['type'] === 'textarea' && isset($field['content'])) {
                    $html .= esc_textarea($field['content']);
                }
                
                $html .= '</' . $field['type'] . '>';
                
                if (isset($field['help_text'])) {
                    $html .= '<div class="form-help" id="' . esc_attr($field['id']) . '-help">' . esc_html($field['help_text']) . '</div>';
                }
                
                $html .= '</div>';
            }
        }
        
        if (isset($form_data['submit_button'])) {
            $html .= '<div class="form-actions">';
            $html .= '<button type="submit" class="btn btn-primary" aria-label="' . esc_attr($form_data['submit_button']['aria_label']) . '">';
            $html .= esc_html($form_data['submit_button']['text']);
            $html .= '</button>';
            $html .= '</div>';
        }
        
        $html .= '</form>';
        
        return $html;
    }
    
    /**
     * Add performance optimizations
     */
    public static function add_performance_optimizations() {
        // Add preload hints for critical resources
        add_action('wp_head', function() {
            echo '<link rel="preload" href="' . LEBONRESTO_PLUGIN_URL . 'assets/css/restaurant-detail.css" as="style">' . "\n";
            echo '<link rel="preload" href="' . LEBONRESTO_PLUGIN_URL . 'assets/js/restaurant-detail.js" as="script">' . "\n";
        }, 1);
        
        // Add resource hints
        add_action('wp_head', function() {
            echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
            echo '<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">' . "\n";
            echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        }, 1);
    }
}

// Initialize HTML optimization
new LeBonResto_HTML_Optimization();
