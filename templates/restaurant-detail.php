<?php
/**
 * Restaurant Detail Template - VisiteMonResto.com Inspired Design
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
$seo_description = "Découvrez {$restaurant_name} à {$city}, Maroc. Restaurant spécialisé en {$cuisine_type} avec visite virtuelle 360°, ambiance authentique. Réservation en ligne, menus, photos, tour virtuel et avis clients. Le meilleur de la gastronomie marocaine à {$city}.";

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

// Enqueue Tailwind CSS
wp_enqueue_style(
    'tailwind-css',
    'https://cdn.tailwindcss.com',
    array(),
    '3.4.0'
);

// Enqueue Leaflet CSS
wp_enqueue_style(
    'leaflet-css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
    array(),
    '1.9.4'
);

// Enqueue detail page CSS
wp_enqueue_style(
    'lebonresto-detail-css',
    LEBONRESTO_PLUGIN_URL . 'assets/css/restaurant-detail.css',
    array('tailwind-css', 'leaflet-css'),
    LEBONRESTO_PLUGIN_VERSION . '.' . time()
);

// Enqueue Bootstrap CSS
wp_enqueue_style(
    'bootstrap-css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    array(),
    '5.3.0'
);

// Enqueue Leaflet JS
wp_enqueue_script(
    'leaflet-js',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
    array(),
    '1.9.4',
    true
);

// Enqueue Bootstrap JS
wp_enqueue_script(
    'bootstrap-js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    array(),
    '5.3.0',
    true
);

// Enqueue detail page JS
wp_enqueue_script(
    'lebonresto-detail-js',
    LEBONRESTO_PLUGIN_URL . 'assets/js/restaurant-detail.js',
    array('jquery', 'leaflet-js', 'bootstrap-js'),
    LEBONRESTO_PLUGIN_VERSION . '.' . time(),
    true
);

// Get restaurant data
$restaurant_id = get_the_ID();
$address = get_post_meta($restaurant_id, '_restaurant_address', true);
$city = get_post_meta($restaurant_id, '_restaurant_city', true);
$cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true);
$description = get_post_meta($restaurant_id, '_restaurant_description', true);

// Get selected options
$selected_options = get_post_meta($restaurant_id, '_restaurant_selected_options', true);
if (!is_array($selected_options)) {
    $selected_options = array();
}
$phone = get_post_meta($restaurant_id, '_restaurant_phone', true);
$email = get_post_meta($restaurant_id, '_restaurant_email', true);
$latitude = get_post_meta($restaurant_id, '_restaurant_latitude', true);
$longitude = get_post_meta($restaurant_id, '_restaurant_longitude', true);
$google_maps_link = get_post_meta($restaurant_id, '_restaurant_google_maps_link', true);
$is_featured = get_post_meta($restaurant_id, '_restaurant_is_featured', true);
$virtual_tour_url = get_post_meta($restaurant_id, '_restaurant_virtual_tour_url', true);
$video_url = get_post_meta($restaurant_id, '_restaurant_video_url', true);
$menu_image = get_post_meta($restaurant_id, '_restaurant_menu_image', true);
$menus = get_post_meta($restaurant_id, '_restaurant_menus', true);
if (!is_array($menus)) {
    $menus = array();
}
$blog_title = get_post_meta($restaurant_id, '_restaurant_blog_title', true);
$blog_content = get_post_meta($restaurant_id, '_restaurant_blog_content', true);
$selected_options = get_post_meta($restaurant_id, '_restaurant_selected_options', true);
if (!is_array($selected_options)) {
    $selected_options = array();
}

// Get gallery images
if (function_exists('lebonresto_get_gallery_images')) {
    $gallery_images = lebonresto_get_gallery_images($restaurant_id);
} else {
    $gallery_ids = get_post_meta($restaurant_id, '_restaurant_gallery', true);
    $gallery_images = array();
    
    if ($gallery_ids) {
        $image_ids = explode(',', $gallery_ids);
        foreach ($image_ids as $image_id) {
            $image_id = intval($image_id);
            if ($image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'large');
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

// Get principal image
$principal_image = get_post_meta($restaurant_id, '_restaurant_principal_image', true);
if (!$principal_image && !empty($gallery_images)) {
    $principal_image = $gallery_images[0]['url'];
}

?>

<div class="lebonresto-detail-layout">
    <?php while (have_posts()) : the_post(); ?>
        
        <!-- Header Navigation -->
        <div class="property-navigation-wrap">
            <div class="container-fluid">
                <ul class="property-navigation list-unstyled d-flex justify-content-between">
                    <li class="property-navigation-item">
                        <a class="target" href="#details-section">Détails</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#chef-section">Le mot du Chef</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#options-section">Options</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#address-section">Adresse</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#menu-section">Menu</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Blog Information Section -->
        <div class="blog-info-wrap" style="width: 100%; background: var(--bg-primary); padding: 2rem 0; border-bottom: 1px solid var(--border-color);">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="blog-title" style="font-size: 2.5rem; font-weight: 800; margin: 0; color: var(--text-primary);">
                            <?php echo $blog_title ?: get_the_title(); ?>
                        </h1>
                        <p class="blog-path" style="color: var(--text-secondary); margin: 0.5rem 0; font-size: 1.1rem;">
                            <i class="fas fa-home"></i> 
                            <a href="<?php echo home_url(); ?>" style="color: var(--primary-color); text-decoration: none;">Accueil</a> 
                            <i class="fas fa-chevron-right mx-2"></i>
                            <a href="<?php echo home_url('/restaurants/'); ?>" style="color: var(--primary-color); text-decoration: none;">Restaurants</a>
                            <i class="fas fa-chevron-right mx-2"></i>
                            <span style="color: var(--text-secondary);"><?php the_title(); ?></span>
                        </p>
                        <p class="blog-date" style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                            <i class="fas fa-calendar"></i> Créé le <?php echo date('j F Y', strtotime(get_the_date())); ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <?php if ($cuisine_type): ?>
                        <span class="cuisine-badge" style="background: var(--gradient-primary); color: var(--bg-primary); padding: 0.5rem 1.5rem; border-radius: var(--radius-full); font-weight: 600; display: inline-block;">
                            <?php echo esc_html(ucfirst($cuisine_type)); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Container with 4 Sections -->
        <div class="container" style="margin-top: 2rem;">
            <div class="property-top-wrap">
                <div class="property-banner" style="position: relative;">
                    <!-- Section Switcher Icons -->
                    <div class="section-switcher" style="position: absolute; top: 20px; right: 50px; z-index: 1000;">
                        <div class="switcher-icons" style="display: inline-flex; background: var(--bg-primary); padding: 0.5rem; border-radius: var(--radius-full); box-shadow: var(--shadow-lg); gap: 0.5rem;">
                            <button class="section-btn active" data-section="map" style="padding: 1rem; border: none; background: var(--gradient-primary); color: var(--bg-primary); border-radius: var(--radius-full); cursor: pointer; transition: all var(--transition-normal);" title="Carte">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                            </button>
                            <button class="section-btn" data-section="virtual-tour" style="padding: 1rem; border: none; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: var(--radius-full); cursor: pointer; transition: all var(--transition-normal);" title="Visite Virtuelle">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </button>
                            <button class="section-btn" data-section="video" style="padding: 1rem; border: none; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: var(--radius-full); cursor: pointer; transition: all var(--transition-normal);" title="Vidéo">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
                                </svg>
                            </button>
                            <button class="section-btn" data-section="images" style="padding: 1rem; border: none; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: var(--radius-full); cursor: pointer; transition: all var(--transition-normal);" title="Images">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content Sections -->
                    <div class="content-sections" style="position: relative;">
                        <!-- Map Section (Default) -->
                        <div class="content-section active" id="map-section" style="width: 100%; height: 600px; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg);">
                            <div id="restaurant-map" style="width: 100%; height: 100%;">
                                <?php if (!$latitude || !$longitude): ?>
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: var(--bg-tertiary); color: var(--text-muted);">
                                    <div style="text-align: center;">
                                        <i class="fas fa-map-marker-alt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                        <p>Coordonnées non disponibles</p>
                                        <small>Veuillez ajouter la latitude et longitude dans l'admin</small>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Virtual Tour Section -->
                        <?php if ($virtual_tour_url): ?>
                        <div class="content-section" id="virtual-tour-section" style="width: 100%; height: 600px; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg); display: none;">
                            <iframe src="<?php echo esc_url($virtual_tour_url); ?>" 
                                    style="width: 100%; height: 100%; border: none;" 
                                    frameborder="0" 
                                    allowfullscreen>
                            </iframe>
                </div>
                <?php endif; ?>
                    
                    <!-- Video Section -->
                    <?php if ($video_url): ?>
                    <div class="content-section" id="video-section" style="width: 100%; height: 600px; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg); display: none;">
                        <?php
                        // Convert video URL to embed format
                        $embed_url = $video_url;
                        
                        // YouTube URL conversion
                        if (strpos($video_url, 'youtube.com/watch') !== false) {
                            $video_id = '';
                            if (preg_match('/v=([^&]+)/', $video_url, $matches)) {
                                $video_id = $matches[1];
                            }
                            if ($video_id) {
                                $embed_url = 'https://www.youtube.com/embed/' . $video_id . '?rel=0&modestbranding=1&showinfo=0';
                            }
                        } elseif (strpos($video_url, 'youtu.be/') !== false) {
                            $video_id = '';
                            if (preg_match('/youtu\.be\/([^?]+)/', $video_url, $matches)) {
                                $video_id = $matches[1];
                            }
                            if ($video_id) {
                                $embed_url = 'https://www.youtube.com/embed/' . $video_id . '?rel=0&modestbranding=1&showinfo=0';
                            }
                        }
                        // Vimeo URL conversion
                        elseif (strpos($video_url, 'vimeo.com/') !== false) {
                            $video_id = '';
                            if (preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches)) {
                                $video_id = $matches[1];
                            }
                            if ($video_id) {
                                $embed_url = 'https://player.vimeo.com/video/' . $video_id . '?title=0&byline=0&portrait=0';
                            }
                        }
                        // Direct video file - use as is
                        elseif (preg_match('/\.(mp4|webm|ogg)$/i', $video_url)) {
                            $embed_url = $video_url;
                        }
                        ?>
                        <?php if (preg_match('/\.(mp4|webm|ogg)$/i', $video_url)): ?>
                        <!-- Direct video file -->
                        <video controls style="width: 100%; height: 100%; object-fit: cover;">
                            <source src="<?php echo esc_url($embed_url); ?>" type="video/<?php echo pathinfo($video_url, PATHINFO_EXTENSION); ?>">
                            <?php _e('Your browser does not support the video tag.', 'le-bon-resto'); ?>
                        </video>
                        <?php else: ?>
                        <!-- Embedded video (YouTube, Vimeo) -->
                        <iframe src="<?php echo esc_url($embed_url); ?>" 
                                style="width: 100%; height: 100%; border: none;" 
                                    frameborder="0" 
                                    allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                            </iframe>
                        <?php endif; ?>
                        <div class="video-fallback" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-tertiary); display: none; align-items: center; justify-content: center; flex-direction: column; color: var(--text-muted);">
                            <i class="fas fa-video" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Vidéo non disponible</p>
                            <a href="<?php echo esc_url($video_url); ?>" target="_blank" style="color: var(--primary-color); text-decoration: none; margin-top: 1rem;">
                                <i class="fas fa-external-link-alt" style="margin-right: 0.5rem;"></i>
                                Ouvrir la vidéo dans un nouvel onglet
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                        <!-- Images Section -->
                        <div class="content-section" id="images-section" style="width: 100%; height: 600px; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg); display: none;">
                    <?php if (!empty($gallery_images)): ?>
                                <div class="image-slider" style="width: 100%; height: 100%; position: relative;">
                                    <div class="slider-container" style="width: 100%; height: 100%; position: relative; overflow: hidden;">
                        <?php foreach ($gallery_images as $index => $image): ?>
                                            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: <?php echo $index === 0 ? '1' : '0'; ?>; transition: opacity var(--transition-normal);">
                            <img src="<?php echo esc_url($image['url']); ?>" 
                                 alt="<?php echo esc_attr($image['alt'] ?: get_the_title()); ?>" 
                                                     style="width: 100%; height: 100%; object-fit: cover;" />
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="slider-controls" style="position: absolute; bottom: 1rem; left: 50%; transform: translateX(-50%); display: flex; gap: 0.5rem;">
                                        <?php foreach ($gallery_images as $index => $image): ?>
                                            <button class="slider-dot <?php echo $index === 0 ? 'active' : ''; ?>" 
                                                    data-slide="<?php echo $index; ?>" 
                                                    style="width: 12px; height: 12px; border-radius: 50%; border: none; background: <?php echo $index === 0 ? 'var(--primary-color)' : 'rgba(255,255,255,0.5)'; ?>; cursor: pointer; transition: all var(--transition-normal);">
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: var(--bg-tertiary); color: var(--text-muted);">
                                    <div style="text-align: center;">
                                        <i class="fas fa-images" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                        <p>Aucune image disponible</p>
                                    </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="row" style="margin-top: 3rem;">
                <!-- Left Column - 70% -->
                <div class="col-lg-8 col-md-12 bt-content-wrap" id="main-content">
                    <!-- Détails Section -->
                    <div class="property-section-wrap" id="details-section" style="margin-bottom: 2rem;">
                        <div class="block-wrap">
                            <div class="block-title-wrap">
                                <h2>Détails</h2>
                            </div>
                            <div class="block-content-wrap">
                                <div class="detail-wrap">
                                    <ul class="list-2-cols list-unstyled">
                                        <li>
                                            <strong>Type de cuisine:</strong>
                                            <span><?php echo esc_html(ucfirst($cuisine_type ?: 'Non spécifié')); ?></span>
                                        </li>
                                        <li>
                                            <strong>Ville:</strong>
                                            <span><?php echo esc_html($city); ?></span>
                                        </li>
                                        <?php if ($phone): ?>
                                        <li>
                                            <strong>Téléphone:</strong>
                                            <span><a href="tel:<?php echo esc_attr($phone); ?>" style="color: var(--primary-color);"><?php echo esc_html($phone); ?></a></span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if ($email): ?>
                                        <li>
                                            <strong>Email:</strong>
                                            <span><a href="mailto:<?php echo esc_attr($email); ?>" style="color: var(--primary-color);"><?php echo esc_html($email); ?></a></span>
                                        </li>
                <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Le mot du Chef Section -->
                    <div class="property-section-wrap" id="chef-section" style="margin-bottom: 2rem;">
                        <div class="block-wrap">
                            <div class="block-title-wrap">
                                <h2>Le mot du Chef</h2>
                            </div>
                            <div class="block-content-wrap">
                                <div class="blog-content" style="font-size: 1.1rem; line-height: 1.8; color: var(--text-primary);">
                                    <?php if ($blog_content && $blog_content !== 'Hello world!'): ?>
                                        <?php echo wpautop($blog_content); ?>
                                    <?php else: ?>
                                        <p>Bienvenue chez <strong><?php echo esc_html(get_the_title()); ?></strong> !</p>
                                        <p>Nous sommes ravis de vous accueillir dans notre établissement. Découvrez notre cuisine authentique et notre ambiance chaleureuse.</p>
                                        <?php if ($description): ?>
                                            <p><?php echo esc_html($description); ?></p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                            
                    <!-- Options Section -->
                    <?php if (!empty($selected_options)): ?>
                    <div class="property-section-wrap" id="options-section" style="margin-bottom: 2rem;">
                        <div class="block-wrap">
                            <div class="block-title-wrap">
                                <h2>Options</h2>
                            </div>
                            <div class="block-content-wrap">
                                <div class="options-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                                    <?php foreach ($selected_options as $option): ?>
                                    <div class="option-item" style="display: flex; align-items: center; padding: 1rem; background: var(--bg-secondary); border-radius: var(--radius-lg); border-left: 4px solid var(--primary-color);">
                                        <i class="fas fa-check-circle" style="color: var(--primary-color); margin-right: 0.75rem; font-size: 1.2rem;"></i>
                                        <span style="font-weight: 500; color: var(--text-primary);"><?php echo esc_html($option); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                    <!-- Adresse Section -->
                    <div class="property-section-wrap" id="address-section" style="margin-bottom: 2rem;">
                        <div class="block-wrap">
                            <div class="block-title-wrap">
                                <h2>Adresse</h2>
                            </div>
                            <div class="block-content-wrap">
                                <div class="address-info" style="padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius-lg);">
                                    <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                                        <i class="fas fa-map-marker-alt" style="color: var(--primary-color); margin-right: 1rem; font-size: 1.5rem;"></i>
                                <div>
                                            <strong style="display: block; color: var(--text-primary);"><?php echo esc_html($address); ?></strong>
                                            <span style="color: var(--text-secondary);"><?php echo esc_html($city); ?></span>
                                        </div>
                                    </div>
                                    <?php 
                                    // Use Google Maps link if available, otherwise use coordinates
                                    $maps_url = '';
                                    if ($google_maps_link) {
                                        $maps_url = $google_maps_link;
                                    } elseif ($latitude && $longitude) {
                                        $maps_url = 'https://www.google.com/maps?q=' . $latitude . ',' . $longitude;
                                    }
                                    ?>
                                    <?php if ($maps_url): ?>
                                    <div style="margin-top: 1rem;">
                                        <a href="<?php echo esc_url($maps_url); ?>" 
                                           target="_blank" 
                                           style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: var(--gradient-primary); color: var(--bg-primary); text-decoration: none; border-radius: var(--radius-full); font-weight: 600;">
                                            <i class="fas fa-external-link-alt" style="margin-right: 0.5rem;"></i>
                                            Voir sur Google Maps
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Menu Section -->
                    <?php if (!empty($menus) || $menu_image): ?>
                    <div class="property-section-wrap" id="menu-section" style="margin-bottom: 2rem;">
                        <div class="block-wrap">
                            <div class="block-title-wrap">
                                <h2>Menus</h2>
                            </div>
                            <div class="block-content-wrap">
                                <?php if (!empty($menus)): ?>
                                    <div class="menus-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                                        <?php foreach ($menus as $index => $menu): ?>
                                            <div class="menu-item1" id="menu-<?php echo $index; ?>" style="border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem; background: var(--bg-primary); box-shadow: var(--shadow-sm); transition: var(--transition);">
                                                <h3 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 1.25rem; font-weight: 600; cursor: pointer;" 
                                                    class="menu-title" 
                                                    data-menu-index="<?php echo $index; ?>"
                                                    onclick="openMenuPopup(<?php echo $index; ?>, '<?php echo esc_js($menu['name']); ?>', '<?php echo esc_js($menu['file_url']); ?>')">
                                                    <?php echo esc_html($menu['name']); ?>
                                                    <i class="fas fa-eye" style="margin-left: 0.5rem; font-size: 0.875rem; opacity: 0.7;"></i>
                                                </h3>
                                                <div style="text-align: center;">
                                                    <a href="<?php echo esc_url($menu['file_url']); ?>" 
                                                       target="_blank" 
                                                       style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: var(--gradient-primary); color: var(--bg-primary); text-decoration: none; border-radius: var(--radius-full); font-weight: 600; transition: var(--transition);">
                                                        <i class="fas fa-download" style="margin-right: 0.5rem;"></i>
                                                        Télécharger le menu
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                                <?php if ($menu_image): ?>
                                <div class="menu-image-container" style="text-align: center;">
                                    <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Menu Principal</h3>
                                    <?php 
                                    $menu_image_url = wp_get_attachment_image_url($menu_image, 'large');
                                    if ($menu_image_url): ?>
                                        <img src="<?php echo esc_url($menu_image_url); ?>" 
                                             alt="Menu du restaurant <?php the_title(); ?>" 
                                             style="max-width: 100%; height: auto; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); cursor: pointer;"
                                             onclick="openImageModal('<?php echo esc_url($menu_image_url); ?>', 'Menu du restaurant <?php the_title(); ?>')" />
                                    <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                            
                </div>
                
                <!-- Right Column - 30% -->
                <div class="col-lg-4 col-md-12 bt-sidebar-wrap" id="contact-form">
                    <aside id="sidebar" class="sidebar-wrap">
                        <div class="property-form-wrap" id="contact-form-wrapper">
                            <div class="property-form clearfix">
                                <form id="restaurant-contact-form" method="post" action="#">
                                    <?php wp_nonce_field('lebonresto_contact_form', 'contact_nonce'); ?>
                                    <input type="hidden" name="restaurant_id" value="<?php echo esc_attr($restaurant_id); ?>">
                                    
                                    <div class="agent-details">
                                        <div class="d-flex align-items-center">
                                            <div class="agent-image">
                                                <?php 
                                                $restaurant_image_url = '';
                                                if ($principal_image) {
                                                    $restaurant_image_url = wp_get_attachment_image_url($principal_image, 'thumbnail');
                                                }
                                                if (!$restaurant_image_url) {
                                                    $restaurant_image_url = 'https://via.placeholder.com/70x70/FFC107/FFFFFF?text=' . urlencode(substr(get_the_title(), 0, 2));
                                                }
                                                ?>
                                                <img class="rounded" src="<?php echo esc_url($restaurant_image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" width="70" height="70">
                                            </div>
                                            <ul class="agent-information list-unstyled">
                                                <li class="agent-name">
                                                    <i class="fas fa-user mr-1"></i>
                                                    <?php echo $blog_title ?: get_the_title(); ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <input class="form-control" name="name" type="text" placeholder="Prénom *" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <input class="form-control" name="mobile" type="text" placeholder="Téléphone">
                                    </div>
                                    
                                    <div class="form-group">
                                        <input class="form-control" name="email" type="email" placeholder="Email *" required>
                                    </div>
                                    
                                    <div class="form-group form-group-textarea">
                                        <textarea class="form-control hz-form-message" name="message" rows="4" placeholder="Message *" required>Bonjour, [<?php the_title(); ?>]</textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-full-width">
                                        <i class="fas fa-paper-plane mr-1"></i>
                                        <span class="btn-text">Envoyer</span>
                                        <span class="btn-loading" style="display: none;">
                                            <i class="fas fa-spinner fa-spin mr-1"></i>
                                            Envoi en cours...
                                        </span>
                                    </button>
                                    
                                    <div id="form-message" class="form-message" style="margin-top: 15px; padding: 10px; border-radius: 5px; display: none;"></div>
                                    
                                    <?php if ($phone): ?>
                                    <a href="tel:<?php echo esc_attr($phone); ?>" class="btn btn-success btn-full-width mt-2">
                                        <i class="fas fa-phone mr-1"></i>
                                        Appeler <?php echo esc_html($phone); ?>
                                    </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

        </div>
        
    <?php endwhile; ?>
</div>

<!-- Gallery Lightbox Modal -->
<div id="property-lightbox" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Galerie</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
        </button>
            </div>
            <div class="modal-body">
                <div id="lightbox-gallery"></div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="gallery-data">
<?php echo wp_json_encode($gallery_images); ?>
</script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Section switching functionality
        const sectionButtons = document.querySelectorAll('.section-btn');
        const contentSections = document.querySelectorAll('.content-section');
        
        sectionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetSection = this.getAttribute('data-section');
                
                // Update button states
                sectionButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.style.background = 'var(--bg-tertiary)';
                    btn.style.color = 'var(--text-secondary)';
                });
                
                this.classList.add('active');
                this.style.background = 'var(--gradient-primary)';
                this.style.color = 'var(--bg-primary)';
                
                // Update content sections
                contentSections.forEach(section => {
                    section.style.display = 'none';
                    section.classList.remove('active');
                });
                
                const targetElement = document.getElementById(targetSection + '-section');
                if (targetElement) {
                    targetElement.style.display = 'block';
                    targetElement.classList.add('active');
                }
            });
        });

        // Image slider functionality
        const sliderDots = document.querySelectorAll('.slider-dot');
        const slides = document.querySelectorAll('.slide');
        let currentSlide = 0;
        
        sliderDots.forEach((dot, index) => {
            dot.addEventListener('click', function() {
                // Update dots
                sliderDots.forEach(d => {
                    d.classList.remove('active');
                    d.style.background = 'rgba(255,255,255,0.5)';
                });
                
                this.classList.add('active');
                this.style.background = 'var(--primary-color)';
                
                // Update slides
                slides.forEach(slide => {
                    slide.classList.remove('active');
                    slide.style.opacity = '0';
                });
                
                slides[index].classList.add('active');
                slides[index].style.opacity = '1';
                
                currentSlide = index;
            });
        });

        // Auto-advance slider
        if (slides.length > 1) {
            setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;
                
                // Update dots
                sliderDots.forEach((dot, index) => {
                    dot.classList.remove('active');
                    dot.style.background = index === currentSlide ? 'var(--primary-color)' : 'rgba(255,255,255,0.5)';
                });
                
                // Update slides
                slides.forEach((slide, index) => {
                    slide.classList.remove('active');
                    slide.style.opacity = index === currentSlide ? '1' : '0';
                });
            }, 5000);
        }

        // Map initialization (if coordinates are available)
        <?php if ($latitude && $longitude && is_numeric($latitude) && is_numeric($longitude)): ?>
        function initializeMap() {
            console.log('initializeMap called');
            console.log('Leaflet available:', typeof L !== 'undefined');
            console.log('Coordinates:', <?php echo $latitude; ?>, <?php echo $longitude; ?>);
            
            if (typeof L === 'undefined') {
                console.error('Leaflet library not loaded');
                return;
            }
            
            // Clear any existing content
            const mapContainer = document.getElementById('restaurant-map');
            console.log('Map container found:', !!mapContainer);
            
            if (!mapContainer) {
                console.error('Map container not found');
                return;
            }
            
            // Clear existing map if it exists
            if (window.restaurantMap) {
                window.restaurantMap.remove();
            }
            
            mapContainer.innerHTML = '';
            
            // Ensure container has proper dimensions
            mapContainer.style.width = '100%';
            mapContainer.style.height = '500px';
            
            // Force a reflow to ensure dimensions are applied
            mapContainer.offsetHeight;
            
            try {
                // Initialize the map
                window.restaurantMap = L.map('restaurant-map', {
                    center: [<?php echo $latitude; ?>, <?php echo $longitude; ?>],
                    zoom: 15,
                    zoomControl: true
                });
                
                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(window.restaurantMap);
                
                // Create custom SVG icon for restaurant
                const restaurantIcon = L.divIcon({
                    html: `
                        <div style="
                            width: 40px;
                            height: 40px;
                            background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%);
                            border: 3px solid #ffffff;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                            animation: pulse 2s infinite;
                        ">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                        </div>
                    `,
                    className: 'custom-restaurant-marker',
                    iconSize: [40, 40],
                    iconAnchor: [20, 20],
                    popupAnchor: [0, -20]
                });
                
                // Add custom marker
                const marker = L.marker([<?php echo $latitude; ?>, <?php echo $longitude; ?>], {
                    icon: restaurantIcon
                }).addTo(window.restaurantMap);
                
                // Create detailed popup content
                const popupContent = `
                    <div class="restaurant-popup-content" style="
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        min-width: 250px;
                        max-width: 300px;
                        padding: 0;
                    ">
                        <div style="
                            display: flex;
                            gap: 12px;
                            align-items: flex-start;
                        ">
                            <div style="flex-shrink: 0;">
                                <img src="<?php echo $principal_image ? wp_get_attachment_image_url($principal_image, 'thumbnail') : 'https://via.placeholder.com/60x60'; ?>" 
                                     alt="<?php echo esc_attr(get_the_title()); ?>" 
                                     style="
                                        width: 60px;
                                        height: 60px;
                                        object-fit: cover;
                                        border-radius: 8px;
                                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                                     " />
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h3 style="
                                    font-size: 16px;
                                    font-weight: 600;
                                    margin: 0 0 4px 0;
                                    line-height: 1.3;
                                    color: #1f2937;
                                "><?php echo esc_html(get_the_title()); ?></h3>
                                <p style="
                                    margin: 0 0 4px 0;
                                    font-size: 13px;
                                    line-height: 1.4;
                                    color: #6b7280;
                                "><?php echo esc_html($address); ?></p>
                                <p style="
                                    margin: 0 0 8px 0;
                                    font-size: 13px;
                                    line-height: 1.4;
                                    color: #6b7280;
                                "><?php echo esc_html($city); ?></p>
                                <div style="
                                    display: flex;
                                    gap: 8px;
                                    flex-wrap: wrap;
                                    justify-content: center;
                                ">
                                    <?php if ($phone): ?>
                                    <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $phone)); ?>" 
                                       target="_blank"
                                       title="WhatsApp"
                                       style="
                                           display: inline-flex;
                                           align-items: center;
                                           justify-content: center;
                                           padding: 8px;
                                           background: #25D366;
                                           color: white;
                                           text-decoration: none;
                                           border-radius: 50%;
                                           width: 32px;
                                           height: 32px;
                                           transition: all 0.2s ease;
                                       "
                                       onmouseover="this.style.background='#128C7E'; this.style.transform='scale(1.1)'"
                                       onmouseout="this.style.background='#25D366'; this.style.transform='scale(1)'">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                        </svg>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($google_maps_link): ?>
                                    <a href="<?php echo esc_url($google_maps_link); ?>" 
                                       target="_blank"
                                       title="Google Maps"
                                       style="
                                           display: inline-flex;
                                           align-items: center;
                                           justify-content: center;
                                           padding: 8px;
                                           background: #4285F4;
                                           color: white;
                                           text-decoration: none;
                                           border-radius: 50%;
                                           width: 32px;
                                           height: 32px;
                                           transition: all 0.2s ease;
                                       "
                                       onmouseover="this.style.background='#3367D6'; this.style.transform='scale(1.1)'"
                                       onmouseout="this.style.background='#4285F4'; this.style.transform='scale(1)'">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                    </a>
                                    <?php elseif ($latitude && $longitude): ?>
                                    <a href="https://www.google.com/maps?q=<?php echo esc_attr($latitude); ?>,<?php echo esc_attr($longitude); ?>" 
                                       target="_blank"
                                       title="Google Maps"
                                       style="
                                           display: inline-flex;
                                           align-items: center;
                                           justify-content: center;
                                           padding: 8px;
                                           background: #4285F4;
                                           color: white;
                                           text-decoration: none;
                                           border-radius: 50%;
                                           width: 32px;
                                           height: 32px;
                                           transition: all 0.2s ease;
                                       "
                                       onmouseover="this.style.background='#3367D6'; this.style.transform='scale(1.1)'"
                                       onmouseout="this.style.background='#4285F4'; this.style.transform='scale(1)'">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?php 
                                        $restaurant_name = $blog_title ?: get_the_title();
                                        $restaurant_slug = sanitize_title($restaurant_name);
                                        // Debug: Uncomment the line below to see what values we're getting
                                        // echo '<!-- Debug: blog_title=' . $blog_title . ', get_the_title()=' . get_the_title() . ', restaurant_name=' . $restaurant_name . ', slug=' . $restaurant_slug . ' -->';
                                        echo esc_url(home_url('/details/' . $restaurant_slug . '/#details-section')); 
                                    ?>" 
                                       title="Voir détails"
                                       style="
                                           display: inline-flex;
                                           align-items: center;
                                           justify-content: center;
                                           padding: 8px;
                                           background: linear-gradient(135deg, #fedc00 0%, #fedc00 100%);
                                           color: white;
                                           text-decoration: none;
                                           border-radius: 50%;
                                           width: 32px;
                                           height: 32px;
                                           transition: all 0.2s ease;
                                       "
                                       onmouseover="this.style.transform='scale(1.1)'"
                                       onmouseout="this.style.transform='scale(1)'">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Bind popup with custom content
                marker.bindPopup(popupContent, {
                    className: 'restaurant-popup',
                    closeButton: true,
                    autoClose: false,
                    closeOnClick: false
                }).openPopup();
                
                // Add pulse animation CSS
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes pulse {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.1); }
                        100% { transform: scale(1); }
                    }
                    .custom-restaurant-marker {
                        animation: pulse 2s infinite;
                    }
                    .restaurant-popup .leaflet-popup-content-wrapper {
                        border-radius: 12px;
                        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
                        border: 2px solid #f3f4f6;
                        transition: all 0.3s ease;
                    }
                    .restaurant-popup .leaflet-popup-content-wrapper:hover {
                        border-color: #fedc00;
                        box-shadow: 0 12px 32px rgba(255, 193, 7, 0.3);
                    }
                `;
                document.head.appendChild(style);
                
                // Invalidate size after a short delay to ensure proper rendering
                setTimeout(function() {
                    if (window.restaurantMap) {
                        window.restaurantMap.invalidateSize();
                    }
                }, 100);
                
                console.log('Map initialized successfully with custom marker and popup');
            } catch (error) {
                console.error('Error initializing map:', error);
                mapContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; background: var(--bg-tertiary); color: var(--text-muted);"><div style="text-align: center;"><i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i><p>Erreur lors du chargement de la carte</p></div></div>';
            }
        }
        
        // Initialize map when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for Leaflet to be available
            function waitForLeaflet() {
                if (typeof L !== 'undefined') {
                    initializeMap();
                } else {
                    setTimeout(waitForLeaflet, 100);
                }
            }
            waitForLeaflet();
        });
        
        // Also try on window load as backup
        window.addEventListener('load', function() {
            if (typeof L !== 'undefined') {
                setTimeout(initializeMap, 100);
            }
        });
        
        // Re-initialize map when switching to map section
        document.addEventListener('click', function(e) {
            if (e.target.closest('.section-btn[data-section="map"]')) {
                // Wait for section to become visible, then initialize map
                setTimeout(function() {
                    const mapSection = document.getElementById('map-section');
                    if (mapSection && mapSection.style.display !== 'none') {
                        initializeMap();
                    }
                }, 200);
            }
        });
        <?php else: ?>
        console.log('No valid coordinates available for map');
        // Show message in map container
        document.addEventListener('DOMContentLoaded', function() {
            const mapContainer = document.getElementById('restaurant-map');
            if (mapContainer) {
                mapContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; background: var(--bg-tertiary); color: var(--text-muted);"><div style="text-align: center;"><i class="fas fa-map-marker-alt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i><p>Coordonnées non disponibles</p><small>Veuillez ajouter la latitude et longitude dans l\'admin</small></div></div>';
            }
        });
        <?php endif; ?>

        // Smooth scrolling for navigation links
        document.querySelectorAll('.property-navigation .target').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Handle video loading errors
        const videoIframe = document.querySelector('#video-section iframe');
        if (videoIframe) {
            videoIframe.addEventListener('error', function() {
                const fallback = document.querySelector('.video-fallback');
                if (fallback) {
                    fallback.style.display = 'flex';
                    videoIframe.style.display = 'none';
                }
            });
            
            // Also check if iframe loads but shows error content
            videoIframe.addEventListener('load', function() {
                setTimeout(() => {
                    try {
                        // Try to access iframe content to check if it loaded properly
                        const iframeDoc = videoIframe.contentDocument || videoIframe.contentWindow.document;
                        if (iframeDoc && iframeDoc.body && iframeDoc.body.innerHTML.includes('error') || 
                            iframeDoc && iframeDoc.body && iframeDoc.body.innerHTML.includes('not available')) {
                            const fallback = document.querySelector('.video-fallback');
                            if (fallback) {
                                fallback.style.display = 'flex';
                                videoIframe.style.display = 'none';
                            }
                        }
                    } catch (e) {
                        // Cross-origin error, which is normal for YouTube
                        // Video should load fine in this case
                    }
                }, 2000);
            });
        }
    });

    // Image modal functionality
    function openImageModal(imageUrl, imageAlt) {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        `;
        
        const img = document.createElement('img');
        img.src = imageUrl;
        img.alt = imageAlt;
        img.style.cssText = `
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 8px;
        `;
        
        modal.appendChild(img);
        document.body.appendChild(modal);
        
        modal.addEventListener('click', function() {
            document.body.removeChild(modal);
        });
    }
</script>

<!-- Menu Popup Modal -->
<div id="menu-popup" class="menu-popup" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 10000; backdrop-filter: blur(5px);">
    <div class="menu-popup-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: var(--bg-primary); border-radius: var(--radius-xl); padding: 2rem; max-width: 90vw; max-height: 90vh; box-shadow: var(--shadow-2xl); overflow: hidden;">
        <div class="menu-popup-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
            <h3 id="menu-popup-title" style="margin: 0; color: var(--text-primary); font-size: 1.5rem; font-weight: 600;"></h3>
            <button id="menu-popup-close" style="background: none; border: none; font-size: 1.5rem; color: var(--text-secondary); cursor: pointer; padding: 0.5rem; border-radius: var(--radius-md); transition: var(--transition);">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="menu-popup-body" style="text-align: center;">
            <div id="menu-popup-image-container" style="max-height: 70vh; overflow: auto;">
                <!-- Menu image will be loaded here -->
            </div>
            <div class="menu-popup-actions" style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center;">
                <a id="menu-popup-download" href="#" target="_blank" style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: var(--gradient-primary); color: var(--bg-primary); text-decoration: none; border-radius: var(--radius-full); font-weight: 600; transition: var(--transition);">
                    <i class="fas fa-download" style="margin-right: 0.5rem;"></i>
                    Télécharger
                </a>
                <button id="menu-popup-close-btn" style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: var(--radius-full); font-weight: 600; cursor: pointer; transition: var(--transition);">
                    <i class="fas fa-times" style="margin-right: 0.5rem;"></i>
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Menu popup functionality
function openMenuPopup(menuIndex, menuName, fileUrl) {
    const popup = document.getElementById('menu-popup');
    const title = document.getElementById('menu-popup-title');
    const imageContainer = document.getElementById('menu-popup-image-container');
    const downloadLink = document.getElementById('menu-popup-download');
    
    // Set title
    title.textContent = menuName;
    
    // Set download link
    downloadLink.href = fileUrl;
    
    // Check if file is an image
    const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(fileUrl);
    
    if (isImage) {
        // Display image
        imageContainer.innerHTML = `
            <img src="${fileUrl}" 
                 alt="${menuName}" 
                 style="max-width: 100%; height: auto; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);" 
                 onload="this.style.opacity='1'" 
                 style="opacity: 0; transition: opacity 0.3s ease;" />
        `;
    } else {
        // Display PDF or other file
        imageContainer.innerHTML = `
            <div style="padding: 2rem; text-align: center;">
                <i class="fas fa-file-pdf" style="font-size: 4rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">Ce fichier ne peut pas être affiché en aperçu.</p>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Cliquez sur "Télécharger" pour ouvrir le fichier.</p>
            </div>
        `;
    }
    
    // Show popup
    popup.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Add animation
    popup.style.opacity = '0';
    setTimeout(() => {
        popup.style.opacity = '1';
    }, 10);
}

// Close popup functions
function closeMenuPopup() {
    const popup = document.getElementById('menu-popup');
    popup.style.opacity = '0';
    setTimeout(() => {
        popup.style.display = 'none';
        document.body.style.overflow = 'auto';
    }, 300);
}

// Event listeners
document.getElementById('menu-popup-close').addEventListener('click', closeMenuPopup);
document.getElementById('menu-popup-close-btn').addEventListener('click', closeMenuPopup);

// Close on background click
document.getElementById('menu-popup').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMenuPopup();
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMenuPopup();
    }
});

        // Smooth scrolling for menu links
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                    // Highlight the target menu
                    targetElement.style.border = '2px solid var(--primary-color)';
                    targetElement.style.boxShadow = 'var(--shadow-lg)';
                    setTimeout(() => {
                        targetElement.style.border = '1px solid var(--border-color)';
                        targetElement.style.boxShadow = 'var(--shadow-sm)';
                    }, 2000);
                }
            });
        });


        // Clean sticky contact form implementation
        function initCleanStickyContact() {
            const contactForm = document.getElementById('contact-form-wrapper');
            const contactContainer = document.getElementById('contact-form');
            
            if (!contactForm || !contactContainer) {
                console.log('Contact form elements not found');
                return;
            }

            // Only on desktop
            if (window.innerWidth < 992) {
                return;
            }

            let originalPosition = null;
            let isSticky = false;

            function handleScroll() {
                if (window.innerWidth < 992) return;

                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Get original position only once
                if (!originalPosition) {
                    const rect = contactContainer.getBoundingClientRect();
                    originalPosition = {
                        top: rect.top + scrollTop,
                        left: rect.left,
                        width: rect.width
                    };
                    console.log('Original position captured:', originalPosition);
                }

                // Check if we should be sticky (when scrolled past the form)
                const shouldStick = scrollTop > (originalPosition.top - 50);

                if (shouldStick && !isSticky) {
                    console.log('Making contact form sticky');
                    isSticky = true;
                    contactForm.style.cssText = `
                        position: fixed !important;
                        top: 20px !important;
                        left: ${originalPosition.left}px !important;
                        width: ${originalPosition.width}px !important;
                        z-index: 1000 !important;
                        background: white !important;
                        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
                        border-radius: 12px !important;
                        transition: all 0.3s ease !important;
                    `;
                } else if (!shouldStick && isSticky) {
                    console.log('Removing sticky from contact form');
                    isSticky = false;
                    contactForm.style.cssText = `
                        position: relative !important;
                        top: auto !important;
                        left: auto !important;
                        width: 100% !important;
                        z-index: auto !important;
                        background: transparent !important;
                        box-shadow: none !important;
                        border-radius: 0 !important;
                    `;
                }
            }

            // Wait for page to fully load before capturing position
            setTimeout(() => {
                handleScroll();
            }, 500);

            // Listen to scroll events
            window.addEventListener('scroll', handleScroll);
            
            // Reset on window resize
            window.addEventListener('resize', function() {
                originalPosition = null;
                isSticky = false;
                contactForm.style.cssText = '';
                setTimeout(handleScroll, 100);
            });
        }

        // Initialize clean sticky contact form
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing clean sticky contact form...');
            setTimeout(initCleanStickyContact, 1000);
            
            // Initialize contact form
            initContactForm();
        });
        
        // Contact form functionality
        function initContactForm() {
            const form = document.getElementById('restaurant-contact-form');
            const submitBtn = form.querySelector('button[type="submit"]');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            const formMessage = document.getElementById('form-message');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                submitBtn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                
                // Hide previous messages
                formMessage.style.display = 'none';
                
                // Get form data
                const formData = new FormData(form);
                formData.append('action', 'lebonresto_send_contact_email');
                formData.append('nonce', '<?php echo wp_create_nonce('lebonresto_contact_form'); ?>');
                
                // Send AJAX request
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading state
                    submitBtn.disabled = false;
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    
                    // Show message
                    formMessage.style.display = 'block';
                    formMessage.className = 'form-message';
                    
                    if (data.success) {
                        formMessage.style.backgroundColor = '#d4edda';
                        formMessage.style.color = '#155724';
                        formMessage.style.border = '1px solid #c3e6cb';
                        formMessage.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                        
                        // Reset form
                        form.reset();
                    } else {
                        formMessage.style.backgroundColor = '#f8d7da';
                        formMessage.style.color = '#721c24';
                        formMessage.style.border = '1px solid #f5c6cb';
                        formMessage.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + data.message;
                    }
                    
                    // Scroll to message
                    formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Hide loading state
                    submitBtn.disabled = false;
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    
                    // Show error message
                    formMessage.style.display = 'block';
                    formMessage.className = 'form-message';
                    formMessage.style.backgroundColor = '#f8d7da';
                    formMessage.style.color = '#721c24';
                    formMessage.style.border = '1px solid #f5c6cb';
                    formMessage.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Une erreur s\'est produite. Veuillez réessayer.';
                });
            });
        }
</script>

<?php get_footer(); ?>