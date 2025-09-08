<?php
/**
 * Restaurant Detail Template - Elegant Tailwind UI Design
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

<div class="lebonresto-detail-layout min-h-screen bg-gray-50">
    <?php while (have_posts()) : the_post(); ?>
        
        <!-- Hero Section with Restaurant Info -->
        <div class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 overflow-hidden">
            <!-- Background Image -->
            <?php if ($principal_image): ?>
            <div class="absolute inset-0 z-0">
                <img src="<?php echo esc_url($principal_image); ?>" 
                     alt="<?php echo esc_attr(get_the_title()); ?>" 
                     class="w-full h-full object-cover opacity-40">
                <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-black/40 to-black/60"></div>
            </div>
            <?php endif; ?>
            
            <!-- Content -->
            <div class="relative z-10 container mx-auto px-4 py-16 lg:py-24">
                <div class="max-w-4xl mx-auto text-center">
                    <!-- Featured Badge -->
                    <?php if ($is_featured === '1'): ?>
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-yellow-400 text-yellow-900 text-sm font-semibold mb-6">
                        <i class="fas fa-star mr-2"></i>
                        <?php _e('Restaurant en Vedette', 'le-bon-resto'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Restaurant Title -->
                    <h1 class="text-4xl lg:text-6xl font-bold text-white mb-6">
                        <?php the_title(); ?>
                    </h1>
                    
                    <!-- Restaurant Meta Info -->
                    <div class="flex flex-wrap justify-center items-center gap-6 text-gray-300 mb-8">
                        <?php if ($city): ?>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-yellow-400 mr-2"></i>
                            <span><?php echo esc_html($city); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($cuisine_type): ?>
                        <div class="flex items-center">
                            <i class="fas fa-utensils text-yellow-400 mr-2"></i>
                            <span><?php echo esc_html(ucfirst($cuisine_type)); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($phone): ?>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-yellow-400 mr-2"></i>
                            <a href="tel:<?php echo esc_attr($phone); ?>" class="hover:text-yellow-400 transition-colors">
                                <?php echo esc_html($phone); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap justify-center gap-4">
                        <?php if ($phone): ?>
                        <a href="tel:<?php echo esc_attr($phone); ?>" 
                           class="inline-flex items-center px-6 py-3 bg-yellow-400 text-yellow-900 font-semibold rounded-lg hover:bg-yellow-500 transition-colors">
                            <i class="fas fa-phone mr-2"></i>
                            <?php _e('Call Now', 'le-bon-resto'); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($email): ?>
                        <a href="mailto:<?php echo esc_attr($email); ?>" 
                           class="inline-flex items-center px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm">
                            <i class="fas fa-envelope mr-2"></i>
                            <?php _e('Envoyer un Email', 'le-bon-resto'); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($virtual_tour_url): ?>
                        <a href="<?php echo esc_url($virtual_tour_url); ?>" 
                           target="_blank"
                           class="inline-flex items-center px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm">
                            <i class="fas fa-vr-cardboard mr-2"></i>
                            <?php _e('Visite Virtuelle', 'le-bon-resto'); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="container mx-auto px-4 py-12">
            <div class="max-w-6xl mx-auto">
                
                <!-- Restaurant Description -->
                <?php if ($description): ?>
                <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">
                        <i class="fas fa-info-circle text-yellow-400 mr-3"></i>
                        <?php _e('À Propos de Ce Restaurant', 'le-bon-resto'); ?>
                    </h2>
                    <div class="prose prose-lg max-w-none text-gray-700">
                        <?php echo wpautop(esc_html($description)); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Gallery Section -->
                <?php if (!empty($gallery_images) || $video_url): ?>
                <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-8">
                        <i class="fas fa-images text-yellow-400 mr-3"></i>
                        <?php _e('Galerie & Médias', 'le-bon-resto'); ?>
                    </h2>
                    
                    <!-- Video Section -->
                    <?php if ($video_url): ?>
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Vidéo du Restaurant', 'le-bon-resto'); ?></h3>
                        <div class="relative aspect-video rounded-xl overflow-hidden shadow-lg">
                            <iframe src="<?php echo esc_url($video_url); ?>" 
                                    class="w-full h-full" 
                                    frameborder="0" 
                                    allowfullscreen
                                    loading="lazy">
                            </iframe>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Image Gallery Grid -->
                    <?php if (!empty($gallery_images)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="gallery-grid">
                        <?php foreach ($gallery_images as $index => $image): ?>
                        <div class="group relative overflow-hidden rounded-xl shadow-lg cursor-pointer gallery-item" 
                             data-index="<?php echo $index; ?>">
                            <img src="<?php echo esc_url($image['url']); ?>" 
                                 alt="<?php echo esc_attr($image['alt'] ?: get_the_title()); ?>" 
                                 class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 flex items-center justify-center">
                                <i class="fas fa-search-plus text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Restaurant Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                    <!-- Contact Information -->
                    <div class="bg-white rounded-2xl shadow-xl p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">
                            <i class="fas fa-address-book text-yellow-400 mr-3"></i>
                            <?php _e('Informations de Contact', 'le-bon-resto'); ?>
                        </h3>
                        
                        <div class="space-y-4">
                            <?php if ($address): ?>
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-yellow-400 mt-1 mr-3"></i>
                                <div>
                                    <p class="font-semibold text-gray-900"><?php _e('Adresse', 'le-bon-resto'); ?></p>
                                    <p class="text-gray-700"><?php echo esc_html($address); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($phone): ?>
                            <div class="flex items-center">
                                <i class="fas fa-phone text-yellow-400 mr-3"></i>
                                <div>
                                    <p class="font-semibold text-gray-900"><?php _e('Téléphone', 'le-bon-resto'); ?></p>
                                    <a href="tel:<?php echo esc_attr($phone); ?>" class="text-yellow-600 hover:text-yellow-700">
                                        <?php echo esc_html($phone); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($email): ?>
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-yellow-400 mr-3"></i>
                                <div>
                                    <p class="font-semibold text-gray-900"><?php _e('Email', 'le-bon-resto'); ?></p>
                                    <a href="mailto:<?php echo esc_attr($email); ?>" class="text-yellow-600 hover:text-yellow-700">
                                        <?php echo esc_html($email); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Restaurant Features -->
                    <div class="bg-white rounded-2xl shadow-xl p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">
                            <i class="fas fa-star text-yellow-400 mr-3"></i>
                            <?php _e('Caractéristiques du Restaurant', 'le-bon-resto'); ?>
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <i class="fas fa-utensils text-yellow-400 mr-3"></i>
                                <span class="text-gray-700">
                                    <strong><?php _e('Type de Cuisine:', 'le-bon-resto'); ?></strong> 
                                    <?php echo esc_html(ucfirst($cuisine_type ?: __('Not specified', 'le-bon-resto'))); ?>
                                </span>
                            </div>
                            
                            <?php if ($is_featured === '1'): ?>
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-400 mr-3"></i>
                                <span class="text-gray-700">
                                    <strong><?php _e('Restaurant en Vedette', 'le-bon-resto'); ?></strong>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($virtual_tour_url): ?>
                            <div class="flex items-center">
                                <i class="fas fa-vr-cardboard text-yellow-400 mr-3"></i>
                                <span class="text-gray-700">
                                    <strong><?php _e('Visite Virtuelle Disponible', 'le-bon-resto'); ?></strong>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($video_url): ?>
                            <div class="flex items-center">
                                <i class="fas fa-video text-yellow-400 mr-3"></i>
                                <span class="text-gray-700">
                                    <strong><?php _e('Vidéo du Restaurant Disponible', 'le-bon-resto'); ?></strong>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Back to Restaurants -->
                <div class="text-center">
                    <a href="<?php echo home_url('/restaurants/'); ?>" 
                       class="inline-flex items-center px-8 py-4 bg-yellow-400 text-yellow-900 font-semibold rounded-lg hover:bg-yellow-500 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <?php _e('Retour à Tous les Restaurants', 'le-bon-resto'); ?>
                    </a>
                </div>
            </div>
        </div>
        
    <?php endwhile; ?>
</div>

<!-- Gallery Lightbox Modal -->
<div id="gallery-lightbox" class="fixed inset-0 bg-black/90 z-50 hidden items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button id="close-lightbox" class="absolute top-4 right-4 text-white text-2xl hover:text-yellow-400 z-10">
            <i class="fas fa-times"></i>
        </button>
        <button id="prev-image" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-2xl hover:text-yellow-400 z-10">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button id="next-image" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-2xl hover:text-yellow-400 z-10">
            <i class="fas fa-chevron-right"></i>
        </button>
        <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
        <div id="image-counter" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white text-sm bg-black/50 px-3 py-1 rounded-full"></div>
    </div>
</div>

<script type="application/json" id="gallery-data">
<?php echo wp_json_encode($gallery_images); ?>
</script>

<?php get_footer(); ?>
