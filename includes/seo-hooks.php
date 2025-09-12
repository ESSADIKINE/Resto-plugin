<?php
/**
 * SEO Hooks and Filters for Extensibility
 * 
 * @package LeBonResto
 */

if (!defined('ABSPATH')) {
    exit;
}

class LeBonResto_SEO_Hooks {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize all SEO hooks and filters
     */
    private function init_hooks() {
        // Meta title hooks
        add_filter('lebonresto_meta_title', array($this, 'filter_meta_title'), 10, 2);
        add_filter('lebonresto_meta_description', array($this, 'filter_meta_description'), 10, 2);
        add_filter('lebonresto_meta_keywords', array($this, 'filter_meta_keywords'), 10, 2);
        
        // Structured data hooks
        add_filter('lebonresto_structured_data', array($this, 'filter_structured_data'), 10, 2);
        add_action('lebonresto_before_structured_data', array($this, 'before_structured_data'), 10, 1);
        add_action('lebonresto_after_structured_data', array($this, 'after_structured_data'), 10, 1);
        
        // Social media hooks
        add_filter('lebonresto_open_graph_data', array($this, 'filter_open_graph_data'), 10, 2);
        add_filter('lebonresto_twitter_card_data', array($this, 'filter_twitter_card_data'), 10, 2);
        
        // Image optimization hooks
        add_filter('lebonresto_image_alt_text', array($this, 'filter_image_alt_text'), 10, 3);
        add_filter('lebonresto_lazy_loading_attributes', array($this, 'filter_lazy_loading_attributes'), 10, 2);
        
        // Sitemap hooks
        add_filter('lebonresto_sitemap_urls', array($this, 'filter_sitemap_urls'), 10, 2);
        add_action('lebonresto_before_sitemap', array($this, 'before_sitemap_generation'), 10, 1);
        add_action('lebonresto_after_sitemap', array($this, 'after_sitemap_generation'), 10, 1);
        
        // Content optimization hooks
        add_filter('lebonresto_content_optimization', array($this, 'filter_content_optimization'), 10, 2);
        add_filter('lebonresto_heading_structure', array($this, 'filter_heading_structure'), 10, 2);
        
        // Performance hooks
        add_filter('lebonresto_critical_css', array($this, 'filter_critical_css'), 10, 1);
        add_filter('lebonresto_defer_scripts', array($this, 'filter_defer_scripts'), 10, 1);
        
        // Custom field hooks
        add_filter('lebonresto_custom_seo_fields', array($this, 'filter_custom_seo_fields'), 10, 2);
        add_action('lebonresto_save_seo_fields', array($this, 'save_custom_seo_fields'), 10, 2);
    }
    
    /**
     * Filter meta title
     */
    public function filter_meta_title($title, $restaurant_id = null) {
        // Allow plugins to modify meta title
        return apply_filters('lebonresto_meta_title_custom', $title, $restaurant_id);
    }
    
    /**
     * Filter meta description
     */
    public function filter_meta_description($description, $restaurant_id = null) {
        // Allow plugins to modify meta description
        return apply_filters('lebonresto_meta_description_custom', $description, $restaurant_id);
    }
    
    /**
     * Filter meta keywords
     */
    public function filter_meta_keywords($keywords, $restaurant_id = null) {
        // Allow plugins to modify meta keywords
        return apply_filters('lebonresto_meta_keywords_custom', $keywords, $restaurant_id);
    }
    
    /**
     * Filter structured data
     */
    public function filter_structured_data($structured_data, $restaurant_id = null) {
        // Allow plugins to modify structured data
        return apply_filters('lebonresto_structured_data_custom', $structured_data, $restaurant_id);
    }
    
    /**
     * Before structured data action
     */
    public function before_structured_data($restaurant_id) {
        do_action('lebonresto_before_structured_data_custom', $restaurant_id);
    }
    
    /**
     * After structured data action
     */
    public function after_structured_data($restaurant_id) {
        do_action('lebonresto_after_structured_data_custom', $restaurant_id);
    }
    
    /**
     * Filter Open Graph data
     */
    public function filter_open_graph_data($og_data, $restaurant_id = null) {
        // Allow plugins to modify Open Graph data
        return apply_filters('lebonresto_open_graph_data_custom', $og_data, $restaurant_id);
    }
    
    /**
     * Filter Twitter Card data
     */
    public function filter_twitter_card_data($twitter_data, $restaurant_id = null) {
        // Allow plugins to modify Twitter Card data
        return apply_filters('lebonresto_twitter_card_data_custom', $twitter_data, $restaurant_id);
    }
    
    /**
     * Filter image alt text
     */
    public function filter_image_alt_text($alt_text, $image_id, $restaurant_id = null) {
        // Allow plugins to modify image alt text
        return apply_filters('lebonresto_image_alt_text_custom', $alt_text, $image_id, $restaurant_id);
    }
    
    /**
     * Filter lazy loading attributes
     */
    public function filter_lazy_loading_attributes($attributes, $image_id) {
        // Allow plugins to modify lazy loading attributes
        return apply_filters('lebonresto_lazy_loading_attributes_custom', $attributes, $image_id);
    }
    
    /**
     * Filter sitemap URLs
     */
    public function filter_sitemap_urls($urls, $sitemap_type) {
        // Allow plugins to add custom URLs to sitemap
        return apply_filters('lebonresto_sitemap_urls_custom', $urls, $sitemap_type);
    }
    
    /**
     * Before sitemap generation action
     */
    public function before_sitemap_generation($sitemap_type) {
        do_action('lebonresto_before_sitemap_custom', $sitemap_type);
    }
    
    /**
     * After sitemap generation action
     */
    public function after_sitemap_generation($sitemap_type) {
        do_action('lebonresto_after_sitemap_custom', $sitemap_type);
    }
    
    /**
     * Filter content optimization
     */
    public function filter_content_optimization($content, $restaurant_id = null) {
        // Allow plugins to modify content optimization
        return apply_filters('lebonresto_content_optimization_custom', $content, $restaurant_id);
    }
    
    /**
     * Filter heading structure
     */
    public function filter_heading_structure($heading, $restaurant_id = null) {
        // Allow plugins to modify heading structure
        return apply_filters('lebonresto_heading_structure_custom', $heading, $restaurant_id);
    }
    
    /**
     * Filter critical CSS
     */
    public function filter_critical_css($critical_css) {
        // Allow plugins to modify critical CSS
        return apply_filters('lebonresto_critical_css_custom', $critical_css);
    }
    
    /**
     * Filter defer scripts
     */
    public function filter_defer_scripts($scripts) {
        // Allow plugins to modify defer scripts list
        return apply_filters('lebonresto_defer_scripts_custom', $scripts);
    }
    
    /**
     * Filter custom SEO fields
     */
    public function filter_custom_seo_fields($fields, $restaurant_id = null) {
        // Allow plugins to add custom SEO fields
        return apply_filters('lebonresto_custom_seo_fields_custom', $fields, $restaurant_id);
    }
    
    /**
     * Save custom SEO fields action
     */
    public function save_custom_seo_fields($restaurant_id, $fields) {
        do_action('lebonresto_save_custom_seo_fields', $restaurant_id, $fields);
    }
}

/**
 * SEO Helper Functions
 */

/**
 * Get restaurant SEO data
 */
function lebonresto_get_restaurant_seo_data($restaurant_id) {
    $data = array(
        'title' => get_post_meta($restaurant_id, '_seo_title', true),
        'description' => get_post_meta($restaurant_id, '_seo_description', true),
        'keywords' => get_post_meta($restaurant_id, '_seo_keywords', true),
        'noindex' => get_post_meta($restaurant_id, '_seo_noindex', true)
    );
    
    return apply_filters('lebonresto_restaurant_seo_data', $data, $restaurant_id);
}

/**
 * Update restaurant SEO data
 */
function lebonresto_update_restaurant_seo_data($restaurant_id, $data) {
    $fields = array('_seo_title', '_seo_description', '_seo_keywords', '_seo_noindex');
    
    foreach ($fields as $field) {
        $key = str_replace('_', '', $field);
        if (isset($data[$key])) {
            update_post_meta($restaurant_id, $field, sanitize_text_field($data[$key]));
        }
    }
    
    do_action('lebonresto_restaurant_seo_data_updated', $restaurant_id, $data);
}

/**
 * Get restaurant structured data
 */
function lebonresto_get_restaurant_structured_data($restaurant_id) {
    $structured_data = array(
        '@context' => 'https://schema.org',
        '@type' => 'Restaurant',
        'name' => get_the_title($restaurant_id),
        'url' => get_permalink($restaurant_id)
    );
    
    return apply_filters('lebonresto_restaurant_structured_data', $structured_data, $restaurant_id);
}

/**
 * Add custom structured data
 */
function lebonresto_add_custom_structured_data($data) {
    add_action('wp_head', function() use ($data) {
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }, 5);
}

/**
 * Get restaurant Open Graph data
 */
function lebonresto_get_restaurant_og_data($restaurant_id) {
    $og_data = array(
        'title' => get_the_title($restaurant_id),
        'description' => get_post_meta($restaurant_id, '_restaurant_description', true),
        'url' => get_permalink($restaurant_id),
        'type' => 'restaurant'
    );
    
    return apply_filters('lebonresto_restaurant_og_data', $og_data, $restaurant_id);
}

/**
 * Add custom meta tag
 */
function lebonresto_add_custom_meta_tag($name, $content, $property = false) {
    add_action('wp_head', function() use ($name, $content, $property) {
        $attr = $property ? 'property' : 'name';
        echo '<meta ' . $attr . '="' . esc_attr($name) . '" content="' . esc_attr($content) . '">' . "\n";
    }, 1);
}

/**
 * Get restaurant sitemap priority
 */
function lebonresto_get_restaurant_sitemap_priority($restaurant_id) {
    $is_featured = get_post_meta($restaurant_id, '_restaurant_is_featured', true);
    $priority = $is_featured ? 0.9 : 0.8;
    
    return apply_filters('lebonresto_restaurant_sitemap_priority', $priority, $restaurant_id);
}

/**
 * Get restaurant sitemap changefreq
 */
function lebonresto_get_restaurant_sitemap_changefreq($restaurant_id) {
    $changefreq = 'weekly';
    
    return apply_filters('lebonresto_restaurant_sitemap_changefreq', $changefreq, $restaurant_id);
}

/**
 * Add custom sitemap URL
 */
function lebonresto_add_sitemap_url($url, $lastmod, $changefreq = 'monthly', $priority = 0.5) {
    add_filter('lebonresto_sitemap_urls', function($urls) use ($url, $lastmod, $changefreq, $priority) {
        $urls[] = array(
            'loc' => $url,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority
        );
        return $urls;
    });
}

/**
 * Get restaurant images for sitemap
 */
function lebonresto_get_restaurant_sitemap_images($restaurant_id) {
    $images = array();
    
    $gallery_ids = get_post_meta($restaurant_id, '_restaurant_gallery', true);
    if ($gallery_ids) {
        $image_ids = explode(',', $gallery_ids);
        foreach ($image_ids as $image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'large');
            if ($image_url) {
                $images[] = array(
                    'loc' => $image_url,
                    'title' => get_the_title($image_id),
                    'caption' => get_post_meta($image_id, '_wp_attachment_image_alt', true)
                );
            }
        }
    }
    
    return apply_filters('lebonresto_restaurant_sitemap_images', $images, $restaurant_id);
}

/**
 * Add custom CSS for SEO
 */
function lebonresto_add_seo_css($css) {
    add_action('wp_head', function() use ($css) {
        echo '<style type="text/css">' . $css . '</style>' . "\n";
    }, 20);
}

/**
 * Add custom JavaScript for SEO
 */
function lebonresto_add_seo_js($js) {
    add_action('wp_footer', function() use ($js) {
        echo '<script type="text/javascript">' . $js . '</script>' . "\n";
    }, 20);
}

// Initialize SEO hooks
new LeBonResto_SEO_Hooks();
