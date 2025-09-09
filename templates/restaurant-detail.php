<?php
/**
 * Restaurant Detail Template - VisiteMonResto.com Inspired Design
 * 
 * @package LeBonResto
 */

get_header(); 

// Enqueue Tailwind CSS
wp_enqueue_style(
    'tailwind-css',
    'https://cdn.tailwindcss.com',
    array(),
    '3.4.0'
);

// Enqueue detail page CSS
wp_enqueue_style(
    'lebonresto-detail-css',
    LEBONRESTO_PLUGIN_URL . 'assets/css/restaurant-detail.css',
    array('tailwind-css'),
    LEBONRESTO_PLUGIN_VERSION . '.' . time()
);

// Enqueue detail page JS
wp_enqueue_script(
    'lebonresto-detail-js',
    LEBONRESTO_PLUGIN_URL . 'assets/js/restaurant-detail.js',
    array('jquery'),
    LEBONRESTO_PLUGIN_VERSION . '.' . time(),
    true
);

// Get restaurant data
$restaurant_id = get_the_ID();
$address = get_post_meta($restaurant_id, '_restaurant_address', true);
$city = get_post_meta($restaurant_id, '_restaurant_city', true);
$cuisine_type = get_post_meta($restaurant_id, '_restaurant_cuisine_type', true);
$description = get_post_meta($restaurant_id, '_restaurant_description', true);
$phone = get_post_meta($restaurant_id, '_restaurant_phone', true);
$email = get_post_meta($restaurant_id, '_restaurant_email', true);
$latitude = get_post_meta($restaurant_id, '_restaurant_latitude', true);
$longitude = get_post_meta($restaurant_id, '_restaurant_longitude', true);
$is_featured = get_post_meta($restaurant_id, '_restaurant_is_featured', true);
$virtual_tour_url = get_post_meta($restaurant_id, '_restaurant_virtual_tour_url', true);
$video_url = get_post_meta($restaurant_id, '_restaurant_video_url', true);

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
        
        <!-- Property Navigation -->
        <div class="property-navigation-wrap">
            <div class="container-fluid">
                <ul class="property-navigation list-unstyled d-flex justify-content-between">
                    <li class="property-navigation-item">
                        <a class="back-top" href="#main-wrap">
                            <i class="fas fa-arrow-up"></i>
                        </a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#property-detail-wrap">Détails</a>
                    </li>
                    <?php if ($virtual_tour_url): ?>
                    <li class="property-navigation-item">
                        <a class="target" href="#property-video-wrap">Vidéo immersive</a>
                    </li>
                    <?php endif; ?>
                    <li class="property-navigation-item">
                        <a class="target" href="#property-description-wrap">Le mot du Chef</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#property-features-wrap">Options</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#property-address-wrap">Adresse</a>
                    </li>
                    <li class="property-navigation-item">
                        <a class="target" href="#property-review-wrap">Commentaires</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Page Title -->
        <div class="page-title-wrap">
            <div class="container">
                <div class="d-flex align-items-center">
                    <!-- Breadcrumb -->
                    <div class="breadcrumb-wrap">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item breadcrumb-item-back-to-search">
                                    <a href="<?php echo home_url('/restaurants/'); ?>">Afficher la recherche</a>
                                </li>
                                <li class="breadcrumb-item breadcrumb-item-home">
                                    <i class="fas fa-home"></i>
                                    <a href="<?php echo home_url(); ?>">Accueil</a>
                                </li>
                                <li class="breadcrumb-item active"><?php the_title(); ?></li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Item Tools -->
                    <ul class="item-tools">
                        <li class="item-tool houzez-favorite">
                            <span class="add-favorite-js item-tool-favorite" data-listid="<?php echo $restaurant_id; ?>">
                                <i class="fas fa-heart"></i>
                            </span>
                        </li>
                        
                        <li class="item-tool houzez-share">
                            <span class="item-tool-share dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-share"></i>
                            </span>
                            <div class="dropdown-menu dropdown-menu-right item-tool-dropdown-menu">
                                <?php if ($phone): ?>
                                <a class="dropdown-item" target="_blank" href="https://api.whatsapp.com/send?text=<?php echo urlencode(get_the_title()); ?>&nbsp;<?php echo urlencode(get_permalink()); ?>">
                                    <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                                </a>
                                <?php endif; ?>
                                
                                <a class="dropdown-item" href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink()); ?>&media=<?php echo urlencode($principal_image); ?>&description=<?php echo urlencode(get_the_title()); ?>" onclick="window.open(this.href, 'mywin','left=50,top=50,width=600,height=350,toolbar=0'); return false;">
                                    <i class="fab fa-pinterest mr-1"></i> Pinterest
                                </a>
                                
                                <a class="dropdown-item" href="https://www.facebook.com/sharer.php?u=<?php echo urlencode(get_permalink()); ?>&t=<?php echo urlencode(get_the_title()); ?>" onclick="window.open(this.href, 'mywin','left=50,top=50,width=600,height=350,toolbar=0'); return false;">
                                    <i class="fab fa-facebook mr-1"></i> Facebook
                                </a>
                                
                                <a class="dropdown-item" href="https://twitter.com/intent/tweet?text=<?php echo urlencode(get_the_title()); ?>&url=<?php echo urlencode(get_permalink()); ?>&via=<?php echo urlencode(get_bloginfo('name')); ?>" onclick="window.open(this.href, 'mywin','left=50,top=50,width=600,height=350,toolbar=0'); return false;">
                                    <i class="fab fa-twitter mr-1"></i> Twitter
                                </a>
                                
                                <a class="dropdown-item" href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode(get_the_title()); ?>&source=<?php echo urlencode(get_bloginfo('url')); ?>" onclick="window.open(this.href, 'mywin','left=50,top=50,width=600,height=350,toolbar=0'); return false;">
                                    <i class="fab fa-linkedin mr-1"></i> LinkedIn
                                </a>
                                
                                <a class="dropdown-item" href="mailto:<?php echo $email ?: 'contact@example.com'; ?>?Subject=<?php echo urlencode(get_the_title()); ?>&body=<?php echo urlencode(get_permalink()); ?>">
                                    <i class="fas fa-envelope mr-1"></i> Email
                                </a>
                            </div>
                        </li>
                        
                        <li class="item-tool houzez-print" data-propid="<?php echo $restaurant_id; ?>">
                            <span class="item-tool-compare">
                                <i class="fas fa-print"></i>
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="d-flex align-items-center property-title-price-wrap">
                    <div class="page-title">
                        <h1><?php the_title(); ?></h1>
                    </div>
                </div>

                <div class="property-labels-wrap">
                    <?php if ($cuisine_type): ?>
                    <a href="#" class="label-status label status-color-154">
                        <?php echo esc_html(ucfirst($cuisine_type)); ?>
                    </a>
                    <?php endif; ?>
                </div>

                <address class="item-address">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    <?php echo esc_html($address . ($city ? ', ' . $city : '')); ?>
                </address>
            </div>
        </div>

        <!-- Property Top Section -->
        <div class="container">
            <div class="property-top-wrap">
                <div class="property-banner">
                    <div class="container hidden-on-mobile">
                        <ul class="nav nav-pills" id="pills-tab" role="tablist">
                            <?php if ($video_url): ?>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-video-tab" data-toggle="pill" href="#pills-video" role="tab">
                                    <i class="fas fa-video"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <li class="nav-item">
                                <a class="nav-link active" id="pills-gallery-tab" data-toggle="pill" href="#pills-gallery" role="tab">
                                    <i class="fas fa-images"></i>
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link" id="pills-map-tab" data-toggle="pill" href="#pills-map" role="tab">
                                    <i class="fas fa-map"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="pills-tabContent">
                        <?php if ($video_url): ?>
                        <div class="tab-pane" id="pills-video" role="tabpanel">
                            <div class="top-gallery-section">
                                <div class="video-wrapper">
                                    <iframe src="<?php echo esc_url($video_url); ?>" 
                                            class="w-full h-96" 
                                            frameborder="0" 
                                            allowfullscreen>
                                    </iframe>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="tab-pane show active" id="pills-gallery" role="tabpanel">
                            <div class="top-gallery-section">
                                <?php if (!empty($gallery_images)): ?>
                                <div id="property-gallery-js" class="houzez-photoswipe listing-slider">
                                    <?php foreach ($gallery_images as $index => $image): ?>
                                    <div data-thumb="<?php echo esc_url($image['url']); ?>">
                                        <a rel="gallery-1" data-slider-no="<?php echo $index + 1; ?>" href="#" class="houzez-trigger-popup-slider-js swipebox" data-toggle="modal" data-target="#property-lightbox">
                                            <img class="img-fluid" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?: get_the_title()); ?>" title="<?php echo esc_attr($image['alt'] ?: get_the_title()); ?>">
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="tab-pane" id="pills-map" role="tabpanel">
                            <div class="map-wrap">
                                <div id="houzez-single-listing-map" class="w-full h-96"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 col-md-12 bt-content-wrap">
                    <div class="property-view">
                        
                        <!-- Mobile Top Wrap -->
                        <div class="visible-on-mobile">
                            <div class="mobile-top-wrap">
                                <div class="mobile-property-tools houzez-media-tabs-5 clearfix">
                                    <ul class="nav nav-pills" id="pills-tab-mobile" role="tablist">
                                        <?php if ($video_url): ?>
                                        <li class="nav-item">
                                            <a class="nav-link" id="pills-video-tab-mobile" data-toggle="pill" href="#pills-video-mobile" role="tab">
                                                <i class="fas fa-video"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <li class="nav-item">
                                            <a class="nav-link active" id="pills-gallery-tab-mobile" data-toggle="pill" href="#pills-gallery-mobile" role="tab">
                                                <i class="fas fa-images"></i>
                                            </a>
                                        </li>
                                        
                                        <li class="nav-item">
                                            <a class="nav-link" id="pills-map-tab-mobile" data-toggle="pill" href="#pills-map-mobile" role="tab">
                                                <i class="fas fa-map"></i>
                                            </a>
                                        </li>
                                    </ul>

                                    <ul class="item-tools">
                                        <li class="item-tool houzez-favorite">
                                            <span class="add-favorite-js item-tool-favorite" data-listid="<?php echo $restaurant_id; ?>">
                                                <i class="fas fa-heart"></i>
                                            </span>
                                        </li>
                                        
                                        <li class="item-tool houzez-share">
                                            <span class="item-tool-share dropdown-toggle" data-toggle="dropdown">
                                                <i class="fas fa-share"></i>
                                            </span>
                                            <div class="dropdown-menu dropdown-menu-right item-tool-dropdown-menu">
                                                <!-- Same sharing options as desktop -->
                                            </div>
                                        </li>
                                        
                                        <li class="item-tool houzez-print" data-propid="<?php echo $restaurant_id; ?>">
                                            <span class="item-tool-compare">
                                                <i class="fas fa-print"></i>
                                            </span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="mobile-property-title clearfix">
                                    <span class="labels-wrap labels-right">
                                        <?php if ($cuisine_type): ?>
                                        <a href="#" class="label-status label status-color-154">
                                            <?php echo esc_html(ucfirst($cuisine_type)); ?>
                                        </a>
                                        <?php endif; ?>
                                    </span>

                                    <div class="page-title">
                                        <span class="item-title property-title-mobile"><?php the_title(); ?></span>
                                    </div>

                                    <address class="item-address">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?php echo esc_html($address . ($city ? ', ' . $city : '')); ?>
                                    </address>
                                </div>
                            </div>
                        </div>

                        <!-- Property Details Section -->
                        <div class="property-detail-wrap property-section-wrap" id="property-detail-wrap">
                            <div class="block-wrap">
                                <div class="block-title-wrap d-flex justify-content-between align-items-center">
                                    <h2>Détails</h2>
                                    <span class="small-text grey">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Mis à jour le <?php echo date('j F Y', strtotime(get_the_modified_date())); ?> à <?php echo get_the_modified_time('g:i a'); ?>
                                    </span>
                                </div>
                                <div class="block-content-wrap">
                                    <div class="detail-wrap">
                                        <ul class="list-2-cols list-unstyled">
                                            <li>
                                                <strong>Référence du resto:</strong>
                                                <span>LBR<?php echo str_pad($restaurant_id, 5, '0', STR_PAD_LEFT); ?></span>
                                            </li>
                                            <li class="prop_status">
                                                <strong>Type de resto:</strong>
                                                <span><?php echo esc_html(ucfirst($cuisine_type ?: 'Non spécifié')); ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Virtual Tour Section -->
                        <?php if ($virtual_tour_url): ?>
                        <div class="property-video-wrap property-section-wrap" id="property-video-wrap">
                            <div class="block-wrap">
                                <div class="block-title-wrap d-flex justify-content-between align-items-center">
                                    <h2>Vidéo immersive</h2>
                                </div>
                                <div class="block-content-wrap">
                                    <div class="block-video-wrap">
                                        <div class="video-wrapper">
                                            <iframe src="<?php echo esc_url($virtual_tour_url); ?>" 
                                                    class="w-full h-96" 
                                                    frameborder="0" 
                                                    allowfullscreen>
                                            </iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Chef Description Section -->
                        <div class="property-description-wrap property-section-wrap" id="property-description-wrap">
                            <div class="block-wrap">
                                <div class="block-title-wrap">
                                    <h2>Le mot du Chef</h2>
                                </div>
                                <div class="block-content-wrap">
                                    <?php if ($description): ?>
                                        <div class="prose prose-lg max-w-none">
                                            <?php echo wpautop(esc_html($description)); ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-gray-500 italic">Aucune description du chef disponible pour le moment.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Features Section -->
                        <div class="property-features-wrap property-section-wrap" id="property-features-wrap">
                            <div class="block-wrap">
                                <div class="block-title-wrap d-flex justify-content-between align-items-center">
                                    <h2>Options</h2>
                                </div>
                                <div class="block-content-wrap">
                                    <ul class="list-3-cols list-unstyled">
                                        <li><i class="fas fa-check-circle mr-2"></i><a href="#">Accès PMR (Personnes à Mobilité Réduite)</a></li>
                                        <li><i class="fas fa-check-circle mr-2"></i><a href="#">Chauffage</a></li>
                                        <li><i class="fas fa-check-circle mr-2"></i><a href="#">Climatisation</a></li>
                                        <li><i class="fas fa-check-circle mr-2"></i><a href="#">Équipements écologiques</a></li>
                                        <li><i class="fas fa-check-circle mr-2"></i><a href="#">Parking gratuit</a></li>
                                        <li><i class="fas fa-check-circle mr-2"></i><a href="#">Proximité avec les transports en commun</a></li>
                                        <li><i class="fas fa-check-circle mr-2"></i><a href="#">Salle à manger privée</a></li>
                                        <li><i class="fas fa-check-circle mr-2"></i><a href="#">Salle de réception privatisable</a></li>
                                        <li><i class="fas fa-check-circle mr-2"></i><a href="#">Salles insonorisées</a></li>
                                        <li><i class="fas fa-check-circle mr-2"></i><a href="#">Système de ventilation efficace</a></li>
                                        <li><i class="fas fa-check-circle mr-2"></i><a href="#">Wi-Fi gratuit</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Address Section -->
                        <div class="property-address-wrap property-section-wrap" id="property-address-wrap">
                            <div class="block-wrap">
                                <div class="block-title-wrap d-flex justify-content-between align-items-center">
                                    <h2>Adresse</h2>
                                    <a class="btn btn-primary btn-slim" href="http://maps.google.com/?q=<?php echo urlencode($address . ($city ? ', ' . $city : '')); ?>" target="_blank">
                                        <i class="fas fa-map-marker-alt mr-1"></i> Ouvrir sur Google Maps
                                    </a>
                                </div>
                                <div class="block-content-wrap">
                                    <ul class="list-2-cols list-unstyled">
                                        <li class="detail-address">
                                            <strong>Adresse</strong>
                                            <span><?php echo esc_html($address); ?></span>
                                        </li>
                                        <li class="detail-city">
                                            <strong>Ville</strong>
                                            <span><?php echo esc_html($city); ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Reviews Section -->
                        <div class="property-review-wrap property-section-wrap" id="property-review-wrap">
                            <div class="block-title-wrap review-title-wrap d-flex align-items-center">
                                <h2>0 Avis</h2>
                                <div class="rating-score-wrap flex-grow-1"></div>
                                
                                <div class="sort-by">
                                    <div class="d-flex align-items-center">
                                        <div class="sort-by-title">Trier par:</div>
                                        <select id="sort_review" class="form-control">
                                            <option value="">Ordre par défaut</option>
                                            <option value="a_date">Date ancienne à nouvelle</option>
                                            <option value="d_date">Date nouvelle à ancienne</option>
                                            <option value="a_rating">Note (de faible à élevé)</option>
                                            <option value="d_rating">Prix (élevé à bas)</option>
                                        </select>
                                    </div>
                                </div>
                                <a class="btn btn-primary btn-slim" href="#property-review-form">Mettre un commentaire</a>
                            </div>

                            <ul id="houzez_reviews_container" class="review-list-wrap list-unstyled"></ul>

                            <div class="block-wrap" id="property-review-form">
                                <div class="block-title-wrap">
                                    <h3>Mettre un commentaire</h3>
                                </div>
                                
                                <div class="block-content-wrap">
                                    <form method="post">
                                        <div class="form_messages"></div>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input class="form-control" name="review_email" placeholder="Saisissez votre Email" type="email">
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-group">
                                                    <label>Titre</label>
                                                    <input class="form-control" name="review_title" placeholder="Donner un titre à votre avis" type="text">
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-group">
                                                    <label>Note</label>
                                                    <select name="review_stars" class="form-control">
                                                        <option value="">Sélectionner</option>
                                                        <option value="1">1 étoile - Médiocre</option>
                                                        <option value="2">2 étoiles - Passable</option>
                                                        <option value="3">3 étoiles - Moyen</option>
                                                        <option value="4">4 étoiles - Bon</option>
                                                        <option value="5">5 étoiles - Excellent</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-sm-12 col-xs-12">
                                                <div class="form-group form-group-textarea">
                                                    <label>Avis</label>
                                                    <textarea class="form-control" name="review" rows="5" placeholder="Mettre un commentaire"></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="col-sm-12 col-xs-12">
                                                <button id="submit-review" class="btn btn-secondary btn-sm-full-width">
                                                    <span class="btn-loader"></span>
                                                    Poster votre avis
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4 col-md-12 bt-sidebar-wrap houzez_sticky">
                    <aside id="sidebar" class="sidebar-wrap">
                        <div class="property-form-wrap">
                            <div class="property-form clearfix">
                                <form method="post" action="#">
                                    <div class="agent-details">
                                        <div class="d-flex align-items-center">
                                            <div class="agent-image">
                                                <img class="rounded" src="<?php echo $principal_image ?: 'https://via.placeholder.com/70x70'; ?>" alt="<?php the_title(); ?>" width="70" height="70">
                                            </div>
                                            <ul class="agent-information list-unstyled">
                                                <li class="agent-name">
                                                    <i class="fas fa-user mr-1"></i>
                                                    <?php the_title(); ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <input class="form-control" name="name" type="text" placeholder="Prénom">
                                    </div>
                                    
                                    <div class="form-group">
                                        <input class="form-control" name="mobile" type="text" placeholder="Téléphone">
                                    </div>
                                    
                                    <div class="form-group">
                                        <input class="form-control" name="email" type="email" placeholder="Email">
                                    </div>
                                    
                                    <div class="form-group form-group-textarea">
                                        <textarea class="form-control hz-form-message" name="message" rows="4" placeholder="Message">Bonjour, [<?php the_title(); ?>]</textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-full-width">
                                        <i class="fas fa-paper-plane mr-1"></i>
                                        Envoyer
                                    </button>
                                    
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
    // Smooth scrolling for navigation links
    document.querySelectorAll('.property-navigation .target').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Gallery lightbox functionality
    const galleryData = JSON.parse(document.getElementById('gallery-data').textContent);
    const lightboxGallery = document.getElementById('lightbox-gallery');
    
    document.querySelectorAll('.houzez-trigger-popup-slider-js').forEach((link, index) => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const image = galleryData[index];
            if (image) {
                lightboxGallery.innerHTML = `
                    <img src="${image.url}" alt="${image.alt || 'Gallery Image'}" class="img-fluid">
                `;
                $('#property-lightbox').modal('show');
            }
        });
    });

    // Map initialization (if coordinates are available)
    <?php if ($latitude && $longitude): ?>
    if (typeof L !== 'undefined') {
        const map = L.map('houzez-single-listing-map').setView([<?php echo $latitude; ?>, <?php echo $longitude; ?>], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        L.marker([<?php echo $latitude; ?>, <?php echo $longitude; ?>]).addTo(map)
            .bindPopup('<?php echo esc_js(get_the_title()); ?>')
            .openPopup();
    }
    <?php endif; ?>
});
</script>

<?php get_footer(); ?>