<?php
/**
 * Custom Post Type Registration
 * 
 * @package LeBonResto
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Restaurant Custom Post Type
 */
function lebonresto_register_restaurant_cpt() {
    $labels = array(
        'name'                  => _x('Restaurants', 'Post type general name', 'le-bon-resto'),
        'singular_name'         => _x('Restaurant', 'Post type singular name', 'le-bon-resto'),
        'menu_name'             => _x('Restaurants', 'Admin Menu text', 'le-bon-resto'),
        'name_admin_bar'        => _x('Restaurant', 'Add New on Toolbar', 'le-bon-resto'),
        'add_new'               => __('Add New', 'le-bon-resto'),
        'add_new_item'          => __('Add New Restaurant', 'le-bon-resto'),
        'new_item'              => __('New Restaurant', 'le-bon-resto'),
        'edit_item'             => __('Edit Restaurant', 'le-bon-resto'),
        'view_item'             => __('View Restaurant', 'le-bon-resto'),
        'all_items'             => __('All Restaurants', 'le-bon-resto'),
        'search_items'          => __('Search Restaurants', 'le-bon-resto'),
        'parent_item_colon'     => __('Parent Restaurants:', 'le-bon-resto'),
        'not_found'             => __('No restaurants found.', 'le-bon-resto'),
        'not_found_in_trash'    => __('No restaurants found in Trash.', 'le-bon-resto'),
        'featured_image'        => _x('Restaurant Cover Image', 'Overrides the "Featured Image" phrase', 'le-bon-resto'),
        'set_featured_image'    => _x('Set cover image', 'Overrides the "Set featured image" phrase', 'le-bon-resto'),
        'remove_featured_image' => _x('Remove cover image', 'Overrides the "Remove featured image" phrase', 'le-bon-resto'),
        'use_featured_image'    => _x('Use as cover image', 'Overrides the "Use as featured image" phrase', 'le-bon-resto'),
        'archives'              => _x('Restaurant archives', 'The post type archive label', 'le-bon-resto'),
        'insert_into_item'      => _x('Insert into restaurant', 'Overrides the "Insert into post" phrase', 'le-bon-resto'),
        'uploaded_to_this_item' => _x('Uploaded to this restaurant', 'Overrides the "Uploaded to this post" phrase', 'le-bon-resto'),
        'filter_items_list'     => _x('Filter restaurants list', 'Screen reader text for the filter links', 'le-bon-resto'),
        'items_list_navigation' => _x('Restaurants list navigation', 'Screen reader text for the pagination', 'le-bon-resto'),
        'items_list'            => _x('Restaurants list', 'Screen reader text for the items list', 'le-bon-resto'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => 'lebonresto', // Show under our custom menu
        'show_in_rest'       => true, // Enable REST API support
        'rest_base'          => 'restaurant', // Custom REST API endpoint
        'query_var'          => true,
        'rewrite'            => array('slug' => 'restaurant'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-store',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
    );

    register_post_type('restaurant', $args);
}

// Register the custom post type with higher priority to ensure it loads early
add_action('init', 'lebonresto_register_restaurant_cpt', 5);

/**
 * Debug function to check if post type is registered
 */
function lebonresto_debug_post_type() {
    if (current_user_can('manage_options') && isset($_GET['lebonresto_debug'])) {
        $post_types = get_post_types(array(), 'objects');
        if (isset($post_types['restaurant'])) {
            wp_die('Restaurant post type is registered successfully. Labels: ' . print_r($post_types['restaurant']->labels, true));
        } else {
            wp_die('Restaurant post type is NOT registered!');
        }
    }
}
add_action('admin_init', 'lebonresto_debug_post_type');

/**
 * Add custom meta boxes for restaurant fields
 */
function lebonresto_add_restaurant_meta_boxes() {
    add_meta_box(
        'restaurant-details',
        __('Restaurant Details', 'le-bon-resto'),
        'lebonresto_restaurant_details_callback',
        'restaurant',
        'normal',
        'high'
    );
    
    add_meta_box(
        'restaurant-media',
        __('Restaurant Media', 'le-bon-resto'),
        'lebonresto_restaurant_media_callback',
        'restaurant',
        'normal',
        'high'
    );
    
    
    add_meta_box(
        'restaurant-blog',
        __('Restaurant Blog Content', 'le-bon-resto'),
        'lebonresto_restaurant_blog_callback',
        'restaurant',
        'normal',
        'high'
    );
    
    add_meta_box(
        'restaurant-options',
        __('Restaurant Options', 'le-bon-resto'),
        'lebonresto_restaurant_options_callback',
        'restaurant',
        'side',
        'default'
    );
    
    add_meta_box(
        'restaurant-menus',
        __('Restaurant Menus', 'le-bon-resto'),
        'lebonresto_restaurant_menus_callback',
        'restaurant',
        'normal',
        'high'
    );
    
    add_meta_box(
        'restaurant-reviews',
        __('Restaurant Reviews', 'le-bon-resto'),
        'lebonresto_restaurant_reviews_callback',
        'restaurant',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'lebonresto_add_restaurant_meta_boxes');

/**
 * Meta box callback function
 */
function lebonresto_restaurant_details_callback($post) {
    // Add nonce for security
    wp_nonce_field('lebonresto_save_restaurant_data', 'lebonresto_restaurant_nonce');

    // Get current values
    $description = get_post_meta($post->ID, '_restaurant_description', true);
    $address = get_post_meta($post->ID, '_restaurant_address', true);
    $city = get_post_meta($post->ID, '_restaurant_city', true);
    $latitude = get_post_meta($post->ID, '_restaurant_latitude', true);
    $longitude = get_post_meta($post->ID, '_restaurant_longitude', true);
    $google_maps_link = get_post_meta($post->ID, '_restaurant_google_maps_link', true);
    $cuisine_type = get_post_meta($post->ID, '_restaurant_cuisine_type', true);
    $is_featured = get_post_meta($post->ID, '_restaurant_is_featured', true);
    $phone = get_post_meta($post->ID, '_restaurant_phone', true);
    $email = get_post_meta($post->ID, '_restaurant_email', true);
    $virtual_tour_url = get_post_meta($post->ID, '_restaurant_virtual_tour_url', true);
    $min_price = get_post_meta($post->ID, '_restaurant_min_price', true);
    $max_price = get_post_meta($post->ID, '_restaurant_max_price', true);
    $currency = get_post_meta($post->ID, '_restaurant_currency', true);

    ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="restaurant_description"><?php _e('Description', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <textarea id="restaurant_description" name="restaurant_description" rows="4" cols="50" class="large-text"><?php echo esc_textarea($description); ?></textarea>
                <p class="description"><?php _e('Detailed description of the restaurant.', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_address"><?php _e('Address', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="text" id="restaurant_address" name="restaurant_address" value="<?php echo esc_attr($address); ?>" class="regular-text" />
                <p class="description"><?php _e('Street address of the restaurant.', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_city"><?php _e('City', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="text" id="restaurant_city" name="restaurant_city" value="<?php echo esc_attr($city); ?>" class="regular-text" />
                <p class="description"><?php _e('City where the restaurant is located.', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_latitude"><?php _e('Latitude', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="number" id="restaurant_latitude" name="restaurant_latitude" value="<?php echo esc_attr($latitude); ?>" step="any" class="regular-text" />
                <p class="description"><?php _e('Latitude coordinate (e.g., 48.8566).', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_longitude"><?php _e('Longitude', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="number" id="restaurant_longitude" name="restaurant_longitude" value="<?php echo esc_attr($longitude); ?>" step="any" class="regular-text" />
                <p class="description"><?php _e('Longitude coordinate (e.g., 2.3522).', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_google_maps_link"><?php _e('Google Maps Link', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="url" id="restaurant_google_maps_link" name="restaurant_google_maps_link" value="<?php echo esc_attr($google_maps_link); ?>" class="large-text" />
                <p class="description"><?php _e('Google Maps link (e.g., https://maps.google.com/...). If provided, this will be used instead of coordinates for the map link.', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_cuisine_type"><?php _e('Cuisine Type', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <select id="restaurant_cuisine_type" name="restaurant_cuisine_type" class="regular-text">
                    <option value=""><?php _e('Select Cuisine Type', 'le-bon-resto'); ?></option>
                    <option value="french" <?php selected($cuisine_type, 'french'); ?>><?php _e('French', 'le-bon-resto'); ?></option>
                    <option value="italian" <?php selected($cuisine_type, 'italian'); ?>><?php _e('Italian', 'le-bon-resto'); ?></option>
                    <option value="japanese" <?php selected($cuisine_type, 'japanese'); ?>><?php _e('Japanese', 'le-bon-resto'); ?></option>
                    <option value="chinese" <?php selected($cuisine_type, 'chinese'); ?>><?php _e('Chinese', 'le-bon-resto'); ?></option>
                    <option value="indian" <?php selected($cuisine_type, 'indian'); ?>><?php _e('Indian', 'le-bon-resto'); ?></option>
                    <option value="mexican" <?php selected($cuisine_type, 'mexican'); ?>><?php _e('Mexican', 'le-bon-resto'); ?></option>
                    <option value="mediterranean" <?php selected($cuisine_type, 'mediterranean'); ?>><?php _e('Mediterranean', 'le-bon-resto'); ?></option>
                    <option value="american" <?php selected($cuisine_type, 'american'); ?>><?php _e('American', 'le-bon-resto'); ?></option>
                    <option value="other" <?php selected($cuisine_type, 'other'); ?>><?php _e('Other', 'le-bon-resto'); ?></option>
                </select>
                <p class="description"><?php _e('Type of cuisine served at the restaurant.', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_phone"><?php _e('Phone Number', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="tel" id="restaurant_phone" name="restaurant_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text" />
                <p class="description"><?php _e('Restaurant contact phone number.', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_email"><?php _e('Email Address', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="email" id="restaurant_email" name="restaurant_email" value="<?php echo esc_attr($email); ?>" class="regular-text" />
                <p class="description"><?php _e('Restaurant contact email address.', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_min_price"><?php _e('Minimum Price', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="number" id="restaurant_min_price" name="restaurant_min_price" value="<?php echo esc_attr($min_price); ?>" min="0" step="0.01" class="regular-text" />
                <p class="description"><?php _e('Minimum price per person (e.g., 15.50).', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_max_price"><?php _e('Maximum Price', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="number" id="restaurant_max_price" name="restaurant_max_price" value="<?php echo esc_attr($max_price); ?>" min="0" step="0.01" class="regular-text" />
                <p class="description"><?php _e('Maximum price per person (e.g., 45.00).', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_currency"><?php _e('Currency', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <select id="restaurant_currency" name="restaurant_currency" class="regular-text">
                    <option value="MAD" <?php selected($currency, 'MAD'); ?>>MAD - Moroccan Dirham (د.م.)</option>
                    <option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR - Euro (€)</option>
                    <option value="USD" <?php selected($currency, 'USD'); ?>>USD - US Dollar ($)</option>
                </select>
                <p class="description"><?php _e('Select the currency for this restaurant\'s prices.', 'le-bon-resto'); ?></p>
            </td>
        </tr>
                    <tr>
                <th scope="row">
                    <label for="restaurant_virtual_tour_url"><?php _e('Virtual Tour URL', 'le-bon-resto'); ?></label>
                </th>
                <td>
                    <input type="url" id="restaurant_virtual_tour_url" name="restaurant_virtual_tour_url" value="<?php echo esc_attr($virtual_tour_url); ?>" class="regular-text" />
                    <p class="description"><?php _e('Enter the URL for the 360° virtual tour (e.g., from 3DVista, Matterport, etc.).', 'le-bon-resto'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="restaurant_is_featured"><?php _e('Featured Restaurant', 'le-bon-resto'); ?></label>
                </th>
                <td>
                    <label for="restaurant_is_featured">
                        <input type="checkbox" id="restaurant_is_featured" name="restaurant_is_featured" value="1" <?php checked($is_featured, '1'); ?> />
                        <?php _e('Mark this restaurant as featured (will appear first in search results)', 'le-bon-resto'); ?>
                    </label>
                    <p class="description"><?php _e('Featured restaurants get priority placement in listings and search results.', 'le-bon-resto'); ?></p>
                </td>
            </tr>
    </table>
    <?php
}

/**
 * Restaurant media meta box callback function
 */
function lebonresto_restaurant_media_callback($post) {
    // Add nonce for security
    wp_nonce_field('lebonresto_save_restaurant_media', 'lebonresto_restaurant_media_nonce');

    // Get current values
    $principal_image = get_post_meta($post->ID, '_restaurant_principal_image', true);
    $gallery = get_post_meta($post->ID, '_restaurant_gallery', true);
    $video_url = get_post_meta($post->ID, '_restaurant_video_url', true);
    $virtual_tour_url = get_post_meta($post->ID, '_restaurant_virtual_tour_url', true);

    ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="restaurant_principal_image"><?php _e('Principal Image', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <div id="restaurant-principal-image-container">
                    <input type="hidden" id="restaurant_principal_image" name="restaurant_principal_image" value="<?php echo esc_attr($principal_image); ?>" />
                    <button type="button" class="button" id="restaurant-principal-image-button">
                        <?php _e('Select Principal Image', 'le-bon-resto'); ?>
                    </button>
                    <button type="button" class="button" id="restaurant-principal-image-clear" style="margin-left: 10px;">
                        <?php _e('Clear Image', 'le-bon-resto'); ?>
                    </button>
                    <div id="restaurant-principal-image-preview" style="margin-top: 10px;">
                        <?php if ($principal_image): ?>
                            <?php lebonresto_display_principal_image_preview($principal_image); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="description"><?php _e('Select the main image for this restaurant (will be displayed prominently).', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_gallery"><?php _e('Gallery Images', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <div id="restaurant-gallery-container">
                    <input type="hidden" id="restaurant_gallery" name="restaurant_gallery" value="<?php echo esc_attr($gallery); ?>" />
                    <button type="button" class="button" id="restaurant-gallery-button">
                        <?php _e('Select Images', 'le-bon-resto'); ?>
                    </button>
                    <button type="button" class="button" id="restaurant-gallery-clear" style="margin-left: 10px;">
                        <?php _e('Clear Gallery', 'le-bon-resto'); ?>
                    </button>
                    <div id="restaurant-gallery-preview" style="margin-top: 10px;">
                        <?php if ($gallery): ?>
                            <?php lebonresto_display_gallery_preview($gallery); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="description"><?php _e('Select multiple images for the restaurant gallery.', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_video_url"><?php _e('Video URL', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="url" id="restaurant_video_url" name="restaurant_video_url" value="<?php echo esc_attr($video_url); ?>" class="large-text" />
                <div class="video-url-help" style="background: #f0f8ff; border: 1px solid #b3d9ff; border-radius: 4px; padding: 10px; margin-top: 5px;">
                    <p style="margin: 0 0 8px 0; font-weight: 600; color: #0066cc;"><?php _e('Supported Video Formats:', 'le-bon-resto'); ?></p>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li><strong>YouTube:</strong> <?php _e('https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID', 'le-bon-resto'); ?></li>
                        <li><strong>Vimeo:</strong> <?php _e('https://vimeo.com/VIDEO_ID', 'le-bon-resto'); ?></li>
                        <li><strong>Direct Video:</strong> <?php _e('https://example.com/video.mp4 (MP4, WebM, OGG)', 'le-bon-resto'); ?></li>
                    </ul>
                    <p style="margin: 8px 0 0 0; font-size: 12px; color: #666;">
                        <i class="dashicons dashicons-info"></i>
                        <?php _e('YouTube videos will be automatically converted to embed format. If embedding fails, users can click to open the video in a new tab.', 'le-bon-resto'); ?>
                    </p>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_virtual_tour_url"><?php _e('Virtual Tour', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="text" id="restaurant_virtual_tour_url" name="restaurant_virtual_tour_url" value="<?php echo esc_attr($virtual_tour_url); ?>" class="large-text" />
                <p class="description"><?php _e('Virtual tour iframe URL or local path to index.html project (e.g., /virtual-tours/restaurant-name/index.html).', 'le-bon-resto'); ?></p>
            </td>
        </tr>
    </table>

    <script>
    jQuery(document).ready(function($) {
        var mediaUploader;
        var principalImageUploader;

        // Principal Image functionality
        $('#restaurant-principal-image-button').click(function(e) {
            e.preventDefault();
            
            if (principalImageUploader) {
                principalImageUploader.open();
                return;
            }
            
            principalImageUploader = wp.media({
                title: '<?php _e('Select Principal Image', 'le-bon-resto'); ?>',
                button: {
                    text: '<?php _e('Use this image', 'le-bon-resto'); ?>'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            principalImageUploader.on('select', function() {
                var attachment = principalImageUploader.state().get('selection').first().toJSON();
                $('#restaurant_principal_image').val(attachment.id);
                updatePrincipalImagePreview(attachment);
            });
            
            principalImageUploader.open();
        });

        $('#restaurant-principal-image-clear').click(function(e) {
            e.preventDefault();
            $('#restaurant_principal_image').val('');
            $('#restaurant-principal-image-preview').empty();
        });

        function updatePrincipalImagePreview(attachment) {
            var preview = $('#restaurant-principal-image-preview');
            preview.empty();
            
            if (attachment) {
                var imageDiv = $('<div style="position: relative; width: 150px; height: 150px;"></div>');
                var img = $('<img src="' + attachment.sizes.medium.url + '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; border: 2px solid #ddd;" />');
                imageDiv.append(img);
                preview.append(imageDiv);
            }
        }

        // Gallery functionality
        $('#restaurant-gallery-button').click(function(e) {
            e.preventDefault();
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: '<?php _e('Select Images for Gallery', 'le-bon-resto'); ?>',
                button: {
                    text: '<?php _e('Use these images', 'le-bon-resto'); ?>'
                },
                multiple: true,
                library: {
                    type: 'image'
                }
            });
            
            mediaUploader.on('select', function() {
                var attachments = mediaUploader.state().get('selection').toJSON();
                var imageIds = attachments.map(function(attachment) {
                    return attachment.id;
                });
                
                $('#restaurant_gallery').val(imageIds.join(','));
                updateGalleryPreview(attachments);
            });
            
            mediaUploader.open();
        });

        $('#restaurant-gallery-clear').click(function(e) {
            e.preventDefault();
            $('#restaurant_gallery').val('');
            $('#restaurant-gallery-preview').empty();
        });

        function updateGalleryPreview(attachments) {
            var preview = $('#restaurant-gallery-preview');
            preview.empty();
            
            if (attachments.length > 0) {
                var container = $('<div style="display: flex; flex-wrap: wrap; gap: 10px;"></div>');
                
                attachments.forEach(function(attachment) {
                    var imageDiv = $('<div style="position: relative; width: 100px; height: 100px;"></div>');
                    var img = $('<img src="' + attachment.sizes.thumbnail.url + '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;" />');
                    imageDiv.append(img);
                    container.append(imageDiv);
                });
                
                preview.append(container);
            }
        }
    });
    </script>
    <?php
}

/**
 * Display gallery preview in admin
 */
function lebonresto_display_gallery_preview($gallery) {
    if (empty($gallery)) {
        return;
    }
    
    $image_ids = explode(',', $gallery);
    if (empty($image_ids)) {
        return;
    }
    
    echo '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
    foreach ($image_ids as $image_id) {
        $image_id = intval($image_id);
        if ($image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
            if ($image_url) {
                echo '<div style="position: relative; width: 100px; height: 100px;">';
                echo '<img src="' . esc_url($image_url) . '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;" />';
                echo '</div>';
            }
        }
    }
    echo '</div>';
}



/**
 * Display principal image preview in admin
 */
function lebonresto_display_principal_image_preview($image_id) {
    if (empty($image_id)) {
        return;
    }

    $image_url = wp_get_attachment_image_url($image_id, 'medium'); // Use medium size for preview
    if ($image_url) {
        echo '<div style="position: relative; width: 150px; height: 150px;">';
        echo '<img src="' . esc_url($image_url) . '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; border: 2px solid #ddd;" />';
        echo '</div>';
    }
}

/**
 * Save restaurant meta data
 */
function lebonresto_save_restaurant_data($post_id) {
    // Check if nonce is valid for details
    $details_nonce_valid = isset($_POST['lebonresto_restaurant_nonce']) && wp_verify_nonce($_POST['lebonresto_restaurant_nonce'], 'lebonresto_save_restaurant_data');
    
    // Check if nonce is valid for media
    $media_nonce_valid = isset($_POST['lebonresto_restaurant_media_nonce']) && wp_verify_nonce($_POST['lebonresto_restaurant_media_nonce'], 'lebonresto_save_restaurant_media');

    
    // Check if nonce is valid for blog
    $blog_nonce_valid = isset($_POST['lebonresto_restaurant_blog_nonce']) && wp_verify_nonce($_POST['lebonresto_restaurant_blog_nonce'], 'lebonresto_save_restaurant_blog');
    
    // Check if nonce is valid for options
    $options_nonce_valid = isset($_POST['lebonresto_restaurant_options_nonce']) && wp_verify_nonce($_POST['lebonresto_restaurant_options_nonce'], 'lebonresto_save_restaurant_options');
    
    // Check if nonce is valid for menus
    $menus_nonce_valid = isset($_POST['lebonresto_restaurant_menus_nonce']) && wp_verify_nonce($_POST['lebonresto_restaurant_menus_nonce'], 'lebonresto_save_restaurant_menus');
    
    // Check if nonce is valid for reviews
    $reviews_nonce_valid = isset($_POST['lebonresto_reviews_nonce']) && wp_verify_nonce($_POST['lebonresto_reviews_nonce'], 'lebonresto_save_restaurant_data');

    if (!$details_nonce_valid && !$media_nonce_valid && !$blog_nonce_valid && !$options_nonce_valid && !$menus_nonce_valid && !$reviews_nonce_valid) {
        return;
    }

    // Check if user has permissions to save data
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if not an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Save details meta fields
    if ($details_nonce_valid) {
        $detail_fields = array(
            'restaurant_description' => '_restaurant_description',
            'restaurant_address' => '_restaurant_address',
            'restaurant_city' => '_restaurant_city',
            'restaurant_latitude' => '_restaurant_latitude',
            'restaurant_longitude' => '_restaurant_longitude',
            'restaurant_google_maps_link' => '_restaurant_google_maps_link',
            'restaurant_cuisine_type' => '_restaurant_cuisine_type',
            'restaurant_phone' => '_restaurant_phone',
            'restaurant_email' => '_restaurant_email',
            'restaurant_min_price' => '_restaurant_min_price',
            'restaurant_max_price' => '_restaurant_max_price',
            'restaurant_currency' => '_restaurant_currency',
        );

        foreach ($detail_fields as $field => $meta_key) {
            if (isset($_POST[$field])) {
                $value = sanitize_text_field($_POST[$field]);
                if ($field === 'restaurant_description') {
                    $value = sanitize_textarea_field($_POST[$field]);
                } elseif ($field === 'restaurant_email') {
                    $value = sanitize_email($_POST[$field]);
                } elseif ($field === 'restaurant_google_maps_link') {
                    $value = esc_url_raw($_POST[$field]);
                } elseif (in_array($field, ['restaurant_min_price', 'restaurant_max_price'])) {
                    $value = floatval($_POST[$field]);
                }
                update_post_meta($post_id, $meta_key, $value);
            }
        }
        
        // Handle featured checkbox separately
        $is_featured = isset($_POST['restaurant_is_featured']) ? '1' : '0';
        update_post_meta($post_id, '_restaurant_is_featured', $is_featured);
    }

    // Save media meta fields
    if ($media_nonce_valid) {
        $media_fields = array(
            'restaurant_principal_image' => '_restaurant_principal_image',
            'restaurant_gallery' => '_restaurant_gallery',
            'restaurant_video_url' => '_restaurant_video_url',
            'restaurant_virtual_tour_url' => '_restaurant_virtual_tour_url',
        );

        foreach ($media_fields as $field => $meta_key) {
            if (isset($_POST[$field])) {
                $value = sanitize_text_field($_POST[$field]);
                if ($field === 'restaurant_video_url' || $field === 'restaurant_virtual_tour_url') {
                    $value = esc_url_raw($_POST[$field]);
                }
                update_post_meta($post_id, $meta_key, $value);
            }
        }
    }
    
    
    // Save blog meta fields
    if ($blog_nonce_valid) {
        if (isset($_POST['restaurant_blog_title'])) {
            $blog_title = sanitize_text_field($_POST['restaurant_blog_title']);
            update_post_meta($post_id, '_restaurant_blog_title', $blog_title);
        }
        
        if (isset($_POST['restaurant_blog_content'])) {
            $blog_content = wp_kses_post($_POST['restaurant_blog_content']);
            update_post_meta($post_id, '_restaurant_blog_content', $blog_content);
        }
    }
    
    // Save options meta fields
    if ($options_nonce_valid) {
        if (isset($_POST['restaurant_selected_options']) && is_array($_POST['restaurant_selected_options'])) {
            $selected_options = array_map('sanitize_text_field', $_POST['restaurant_selected_options']);
            update_post_meta($post_id, '_restaurant_selected_options', $selected_options);
        } else {
            update_post_meta($post_id, '_restaurant_selected_options', array());
        }
    }
    
    // Save menus meta fields
    if ($menus_nonce_valid) {
        if (isset($_POST['restaurant_menus']) && is_array($_POST['restaurant_menus'])) {
            $menus = array();
            foreach ($_POST['restaurant_menus'] as $menu) {
                if (!empty($menu['name']) && !empty($menu['file_id'])) {
                    $menus[] = array(
                        'name' => sanitize_text_field($menu['name']),
                        'file_id' => intval($menu['file_id']),
                        'file_url' => esc_url_raw($menu['file_url'])
                    );
                }
            }
            update_post_meta($post_id, '_restaurant_menus', $menus);
        } else {
            update_post_meta($post_id, '_restaurant_menus', array());
        }
    }
    
    // Save reviews meta fields
    if ($reviews_nonce_valid) {
        // Save Google Place ID for automatic review fetching
        if (isset($_POST['restaurant_google_place_id'])) {
            $google_place_id = sanitize_text_field($_POST['restaurant_google_place_id']);
            update_post_meta($post_id, '_restaurant_google_place_id', $google_place_id);
        }
        
    }
}
add_action('save_post', 'lebonresto_save_restaurant_data');

/**
 * Add custom fields to REST API response
 */
function lebonresto_add_restaurant_fields_to_rest() {
    register_rest_field('restaurant', 'restaurant_meta', array(
        'get_callback' => 'lebonresto_get_restaurant_meta_for_rest',
        'schema' => array(
            'description' => __('Restaurant meta data', 'le-bon-resto'),
            'type' => 'object',
        ),
    ));
}
add_action('rest_api_init', 'lebonresto_add_restaurant_fields_to_rest');

/**
 * Get restaurant meta data for REST API
 */
function lebonresto_get_restaurant_meta_for_rest($object) {
    $post_id = $object['id'];
    
    return array(
        'description' => get_post_meta($post_id, '_restaurant_description', true),
        'address' => get_post_meta($post_id, '_restaurant_address', true),
        'city' => get_post_meta($post_id, '_restaurant_city', true),
        'latitude' => get_post_meta($post_id, '_restaurant_latitude', true),
        'longitude' => get_post_meta($post_id, '_restaurant_longitude', true),
        'google_maps_link' => get_post_meta($post_id, '_restaurant_google_maps_link', true),
        'cuisine_type' => get_post_meta($post_id, '_restaurant_cuisine_type', true),
        'phone' => get_post_meta($post_id, '_restaurant_phone', true),
        'email' => get_post_meta($post_id, '_restaurant_email', true),
        'min_price' => get_post_meta($post_id, '_restaurant_min_price', true),
        'max_price' => get_post_meta($post_id, '_restaurant_max_price', true),
        'currency' => get_post_meta($post_id, '_restaurant_currency', true),
        'is_featured' => get_post_meta($post_id, '_restaurant_is_featured', true),
        'virtual_tour_url' => get_post_meta($post_id, '_restaurant_virtual_tour_url', true),
        'gallery' => get_post_meta($post_id, '_restaurant_gallery', true),
        'video_url' => get_post_meta($post_id, '_restaurant_video_url', true),
        'principal_image' => get_post_meta($post_id, '_restaurant_principal_image', true),
        'blog_title' => get_post_meta($post_id, '_restaurant_blog_title', true),
        'blog_content' => get_post_meta($post_id, '_restaurant_blog_content', true),
        'selected_options' => get_post_meta($post_id, '_restaurant_selected_options', true),
        'menus' => get_post_meta($post_id, '_restaurant_menus', true),
        'google_place_id' => get_post_meta($post_id, '_restaurant_google_place_id', true),
    );
}


/**
 * Get currency symbol based on selected currency
 */
function lebonresto_get_currency_symbol($currency = null) {
    if (!$currency) {
        $options = get_option('lebonresto_options', array());
        $currency = isset($options['currency']) ? $options['currency'] : 'MAD';
    }
    
    switch ($currency) {
        case 'EUR':
            return '€';
        case 'USD':
            return '$';
        case 'MAD':
        default:
            return 'د.م.';
    }
}

/**
 * Get currency code based on selected currency
 */
function lebonresto_get_currency_code($currency = null) {
    if (!$currency) {
        $options = get_option('lebonresto_options', array());
        $currency = isset($options['currency']) ? $options['currency'] : 'MAD';
    }
    
    return $currency;
}

/**
 * Restaurant blog content meta box callback function
 */
function lebonresto_restaurant_blog_callback($post) {
    // Add nonce for security
    wp_nonce_field('lebonresto_save_restaurant_blog', 'lebonresto_restaurant_blog_nonce');

    // Get current values
    $blog_content = get_post_meta($post->ID, '_restaurant_blog_content', true);
    $blog_title = get_post_meta($post->ID, '_restaurant_blog_title', true);

    ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="restaurant_blog_title"><?php _e('Blog Title', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="text" id="restaurant_blog_title" name="restaurant_blog_title" value="<?php echo esc_attr($blog_title); ?>" class="large-text" />
                <p class="description"><?php _e('Title for the restaurant blog page (defaults to restaurant name if empty).', 'le-bon-resto'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="restaurant_blog_content"><?php _e('Blog Content', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <?php
                wp_editor($blog_content, 'restaurant_blog_content', array(
                    'textarea_name' => 'restaurant_blog_content',
                    'media_buttons' => true,
                    'textarea_rows' => 10,
                    'teeny' => false,
                    'tinymce' => true,
                    'quicktags' => true,
                ));
                ?>
                <p class="description"><?php _e('Write the blog content for this restaurant. This will be displayed on the restaurant detail page.', 'le-bon-resto'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Restaurant options meta box callback function
 */
function lebonresto_restaurant_options_callback($post) {
    // Add nonce for security
    wp_nonce_field('lebonresto_save_restaurant_options', 'lebonresto_restaurant_options_nonce');

    // Get current values
    $selected_options = get_post_meta($post->ID, '_restaurant_selected_options', true);
    if (!is_array($selected_options)) {
        $selected_options = array();
    }

    // Get available options from settings
    $admin_options = new LeBonResto_Admin();
    $options = $admin_options->get_options();
    $available_options = isset($options['restaurant_options']) ? $options['restaurant_options'] : array();

    ?>
    <div class="restaurant-options-container">
        <p class="description"><?php _e('Select the options available at this restaurant.', 'le-bon-resto'); ?></p>
        
        <?php if (!empty($available_options)): ?>
            <?php foreach ($available_options as $option): ?>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="checkbox" 
                           name="restaurant_selected_options[]" 
                           value="<?php echo esc_attr($option); ?>" 
                           <?php checked(in_array($option, $selected_options)); ?> 
                           style="margin-right: 8px;" />
                    <?php echo esc_html($option); ?>
                </label>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #d63638; font-style: italic;">
                <?php _e('No options available. Please configure restaurant options in the plugin settings.', 'le-bon-resto'); ?>
            </p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Restaurant menus meta box callback
 */
function lebonresto_restaurant_menus_callback($post) {
    // Add nonce for security
    wp_nonce_field('lebonresto_save_restaurant_menus', 'lebonresto_restaurant_menus_nonce');

    // Get current menus
    $menus = get_post_meta($post->ID, '_restaurant_menus', true);
    if (!is_array($menus)) {
        $menus = array();
    }

    ?>
    <div class="restaurant-menus-container">
        <p class="description"><?php _e('Add multiple menus for this restaurant. Each menu can have a name and a file.', 'le-bon-resto'); ?></p>
        
        <div id="menus-list">
            <?php if (!empty($menus)): ?>
                <?php foreach ($menus as $index => $menu): ?>
                    <div class="menu-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">
                            <label style="flex: 1;">
                                <strong><?php _e('Menu Name:', 'le-bon-resto'); ?></strong><br>
                                <input type="text" 
                                       name="restaurant_menus[<?php echo $index; ?>][name]" 
                                       value="<?php echo esc_attr($menu['name']); ?>" 
                                       class="regular-text" 
                                       placeholder="<?php _e('e.g., Breakfast Menu, Lunch Menu', 'le-bon-resto'); ?>" />
                            </label>
                            <button type="button" class="button remove-menu" style="color: #d63638;">
                                <?php _e('Remove', 'le-bon-resto'); ?>
                            </button>
                        </div>
                        
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <div style="flex: 1;">
                                <strong><?php _e('Menu File:', 'le-bon-resto'); ?></strong><br>
                                <input type="hidden" 
                                       name="restaurant_menus[<?php echo $index; ?>][file_id]" 
                                       value="<?php echo esc_attr($menu['file_id']); ?>" 
                                       class="menu-file-id" />
                                <input type="text" 
                                       name="restaurant_menus[<?php echo $index; ?>][file_url]" 
                                       value="<?php echo esc_attr($menu['file_url']); ?>" 
                                       class="menu-file-url regular-text" 
                                       readonly />
                                <button type="button" class="button upload-menu-file">
                                    <?php _e('Upload File', 'le-bon-resto'); ?>
                                </button>
                            </div>
                        </div>
                        
                        <?php if (!empty($menu['file_url'])): ?>
                            <div style="margin-top: 10px;">
                                <a href="<?php echo esc_url($menu['file_url']); ?>" target="_blank" class="button">
                                    <?php _e('View File', 'le-bon-resto'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <button type="button" id="add-menu" class="button button-secondary">
            <?php _e('Add Menu', 'le-bon-resto'); ?>
        </button>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var menuIndex = <?php echo count($menus); ?>;
        
        // Add new menu
        $('#add-menu').on('click', function() {
            var menuHtml = '<div class="menu-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">' +
                '<div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">' +
                    '<label style="flex: 1;">' +
                        '<strong><?php _e('Menu Name:', 'le-bon-resto'); ?></strong><br>' +
                        '<input type="text" name="restaurant_menus[' + menuIndex + '][name]" class="regular-text" placeholder="<?php _e('e.g., Breakfast Menu, Lunch Menu', 'le-bon-resto'); ?>" />' +
                    '</label>' +
                    '<button type="button" class="button remove-menu" style="color: #d63638;"><?php _e('Remove', 'le-bon-resto'); ?></button>' +
                '</div>' +
                '<div style="display: flex; gap: 10px; align-items: center;">' +
                    '<div style="flex: 1;">' +
                        '<strong><?php _e('Menu File:', 'le-bon-resto'); ?></strong><br>' +
                        '<input type="hidden" name="restaurant_menus[' + menuIndex + '][file_id]" class="menu-file-id" />' +
                        '<input type="text" name="restaurant_menus[' + menuIndex + '][file_url]" class="menu-file-url regular-text" readonly />' +
                        '<button type="button" class="button upload-menu-file"><?php _e('Upload File', 'le-bon-resto'); ?></button>' +
                    '</div>' +
                '</div>' +
            '</div>';
            
            $('#menus-list').append(menuHtml);
            menuIndex++;
        });
        
        // Remove menu
        $(document).on('click', '.remove-menu', function() {
            $(this).closest('.menu-item').remove();
        });
        
        // Upload file
        $(document).on('click', '.upload-menu-file', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var fileInput = button.siblings('.menu-file-id');
            var fileUrl = button.siblings('.menu-file-url');
            
            var mediaUploader = wp.media({
                title: '<?php _e('Select Menu File', 'le-bon-resto'); ?>',
                button: {
                    text: '<?php _e('Use this file', 'le-bon-resto'); ?>'
                },
                multiple: false,
                library: {
                    type: ['application/pdf', 'image/jpeg', 'image/png', 'image/gif']
                }
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                fileInput.val(attachment.id);
                fileUrl.val(attachment.url);
            });
            
            mediaUploader.open();
        });
    });
    </script>
    <?php
}


/**
 * Add Google Maps API key setting to admin
 */
function lebonresto_add_google_maps_api_setting() {
    add_settings_field(
        'google_maps_api_key',
        'Google Maps API Key',
        'lebonresto_google_maps_api_key_callback',
        'general',
        'default',
        array('label_for' => 'google_maps_api_key')
    );
    register_setting('general', 'google_maps_api_key');
}
add_action('admin_init', 'lebonresto_add_google_maps_api_setting');

function lebonresto_google_maps_api_key_callback() {
    $api_key = get_option('google_maps_api_key', '');
    echo '<input type="text" id="google_maps_api_key" name="google_maps_api_key" value="' . esc_attr($api_key) . '" class="regular-text" placeholder="AIzaSyDXSSijLxRtL9tz7FbYqvnB3eWwTojpNlI" />';
    echo '<p class="description">Enter your Google Maps API key with Places API enabled. This will be used to automatically fetch restaurant reviews and ratings.</p>';
    echo '<div style="background: #e7f3ff; padding: 1rem; border-radius: 4px; margin-top: 0.5rem; border-left: 4px solid #2196F3;">';
    echo '<strong>Current API Key:</strong> ' . ($api_key ? esc_html($api_key) : 'Not set - using default key');
    echo '</div>';
}

/**
 * Get Google Maps API key
 */
function lebonresto_get_google_maps_api_key() {
    // First try to get from plugin settings
    $plugin_options = get_option('lebonresto_options', array());
    if (isset($plugin_options['google_maps_api_key']) && !empty($plugin_options['google_maps_api_key'])) {
        return $plugin_options['google_maps_api_key'];
    }
    
    // Fallback to WordPress general settings
    $wp_api_key = get_option('google_maps_api_key', '');
    if (!empty($wp_api_key)) {
        return $wp_api_key;
    }
    
    // Final fallback to default key
    return 'AIzaSyDXSSijLxRtL9tz7FbYqvnB3eWwTojpNlI';
}

/**
 * Extract place ID from Google Maps URL
 */
function lebonresto_extract_place_id_from_url($google_maps_url) {
    if (empty($google_maps_url)) {
        return false;
    }
    
    // Pattern 1: maps.google.com/maps/place/...
    if (preg_match('/maps\.google\.com\/maps\/place\/([^\/\?]+)/', $google_maps_url, $matches)) {
        return $matches[1];
    }
    
    // Pattern 2: goo.gl/maps/... or maps.app.goo.gl/...
    if (preg_match('/(?:goo\.gl|maps\.app\.goo\.gl)\/maps\/([^\/\?]+)/', $google_maps_url, $matches)) {
        return $matches[1];
    }
    
    // Pattern 3: Direct place ID in URL
    if (preg_match('/place_id=([^&]+)/', $google_maps_url, $matches)) {
        return $matches[1];
    }
    
    // Pattern 4: @lat,lng,zoom format
    if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*),(\d+\.?\d*)z/', $google_maps_url, $matches)) {
        // For @lat,lng format, we need to use the coordinates to find the place
        return 'coordinates_' . $matches[1] . '_' . $matches[2];
    }
    
    // Pattern 5: maps.google.com/?q=... format
    if (preg_match('/maps\.google\.com\/\?q=([^&]+)/', $google_maps_url, $matches)) {
        return urldecode($matches[1]);
    }
    
    // Pattern 6: maps.google.com/maps?q=... format
    if (preg_match('/maps\.google\.com\/maps\?q=([^&]+)/', $google_maps_url, $matches)) {
        return urldecode($matches[1]);
    }
    
    // Pattern 7: Any URL with /place/ in it
    if (preg_match('/\/place\/([^\/\?&]+)/', $google_maps_url, $matches)) {
        return $matches[1];
    }
    
    return false;
}

/**
 * Search for a place by name using Google Places API
 */
function lebonresto_search_place_by_name($place_name, $api_key) {
    if (empty($place_name) || empty($api_key)) {
        return false;
    }
    
    // Check cache first
    $cache_key = 'google_place_search_' . md5($place_name);
    $cached_data = get_transient($cache_key);
    
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // Google Places API search endpoint
    $url = 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json';
    $params = array(
        'input' => $place_name,
        'inputtype' => 'textquery',
        'fields' => 'place_id,name,rating,user_ratings_total,formatted_address',
        'key' => $api_key
    );
    
    $request_url = $url . '?' . http_build_query($params);
    
    // Make API request
    $response = wp_remote_get($request_url, array(
        'timeout' => 15,
        'headers' => array(
            'User-Agent' => 'WordPress/' . get_bloginfo('version')
        )
    ));
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (!$data || $data['status'] !== 'OK' || empty($data['candidates'])) {
        return false;
    }
    
    $candidate = $data['candidates'][0]; // Take the first result
    $place_id = $candidate['place_id'];
    
    // Now fetch detailed data using the place ID
    return lebonresto_fetch_google_places_data($place_id, $api_key);
}

/**
 * Fetch Google Places data using API
 */
function lebonresto_fetch_google_places_data($place_id, $api_key) {
    if (empty($place_id) || empty($api_key)) {
        return false;
    }
    
    // Check cache first
    $cache_key = 'google_places_' . md5($place_id);
    $cached_data = get_transient($cache_key);
    
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // Google Places API endpoint
    $url = 'https://maps.googleapis.com/maps/api/place/details/json';
    $params = array(
        'place_id' => $place_id,
        'fields' => 'rating,user_ratings_total,reviews,name,formatted_address,opening_hours,current_opening_hours',
        'key' => $api_key
    );
    
    $request_url = $url . '?' . http_build_query($params);
    
    // Make API request
    $response = wp_remote_get($request_url, array(
        'timeout' => 15,
        'headers' => array(
            'User-Agent' => 'WordPress/' . get_bloginfo('version')
        )
    ));
    
    if (is_wp_error($response)) {
        error_log('LeBonResto: Google Places API request failed: ' . $response->get_error_message());
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (!$data) {
        error_log('LeBonResto: Google Places API response could not be decoded');
        return false;
    }
    
    if ($data['status'] !== 'OK') {
        error_log('LeBonResto: Google Places API returned status: ' . $data['status'] . 
                  (isset($data['error_message']) ? ' - ' . $data['error_message'] : ''));
        return false;
    }
    
    $result = $data['result'];
    $places_data = array(
        'rating' => isset($result['rating']) ? floatval($result['rating']) : null,
        'review_count' => isset($result['user_ratings_total']) ? intval($result['user_ratings_total']) : null,
        'name' => isset($result['name']) ? $result['name'] : null,
        'address' => isset($result['formatted_address']) ? $result['formatted_address'] : null,
        'opening_hours' => isset($result['opening_hours']) ? $result['opening_hours'] : null,
        'current_opening_hours' => isset($result['current_opening_hours']) ? $result['current_opening_hours'] : null,
        'reviews' => array()
    );
    
    // Extract individual reviews if available
    if (isset($result['reviews']) && is_array($result['reviews'])) {
        foreach ($result['reviews'] as $review) {
            // Validate review data before adding
            if (is_array($review) && (!empty($review['author_name']) || !empty($review['text']) || isset($review['rating']))) {
                $places_data['reviews'][] = array(
                    'author_name' => isset($review['author_name']) && !empty($review['author_name']) ? sanitize_text_field($review['author_name']) : 'Anonymous',
                    'rating' => isset($review['rating']) && is_numeric($review['rating']) ? intval($review['rating']) : 0,
                    'text' => isset($review['text']) && !empty($review['text']) ? sanitize_textarea_field($review['text']) : '',
                    'time' => isset($review['time']) && is_numeric($review['time']) ? intval($review['time']) : time(),
                    'relative_time_description' => isset($review['relative_time_description']) ? sanitize_text_field($review['relative_time_description']) : ''
                );
            }
        }
    }
    
    // Cache for 6 hours to avoid excessive API calls
    set_transient($cache_key, $places_data, 6 * HOUR_IN_SECONDS);
    
    return $places_data;
}

/**
 * Extract Google Maps data from URL (fallback function)
 */
function lebonresto_extract_google_maps_data($google_maps_url) {
    if (empty($google_maps_url)) {
        return false;
    }
    
    $data = array();
    
    // Extract place ID from various Google Maps URL formats
    if (preg_match('/place\/([^\/]+)/', $google_maps_url, $matches)) {
        $data['place_id'] = $matches[1];
    } elseif (preg_match('/maps\/place\/([^\/]+)/', $google_maps_url, $matches)) {
        $data['place_id'] = $matches[1];
    }
    
    return $data;
}

/**
 * Get Google Maps rating from URL (simplified version)
 */
function lebonresto_get_google_rating_from_url($google_maps_url) {
    // This is a simplified approach - in reality, you'd need to scrape the page
    // or use a service that can extract this data
    
    // For demonstration, we'll return some sample data
    // In production, you might want to:
    // 1. Use a web scraping service
    // 2. Use a third-party API
    // 3. Store the data manually in the admin
    
    return array(
        'rating' => null, // Would be extracted from the page
        'review_count' => null, // Would be extracted from the page
        'place_id' => null // Would be extracted from URL
    );
}

/**
 * Restaurant reviews meta box callback function
 */
function lebonresto_restaurant_reviews_callback($post) {
    // Add nonce for security
    wp_nonce_field('lebonresto_save_restaurant_data', 'lebonresto_reviews_nonce');

    // Get current values
    $google_place_id = get_post_meta($post->ID, '_restaurant_google_place_id', true);

    ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="restaurant_google_place_id"><?php _e('Google Place ID', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <input type="text" id="restaurant_google_place_id" name="restaurant_google_place_id" value="<?php echo esc_attr($google_place_id); ?>" class="regular-text" placeholder="ChIJN1t_tDeuEmsRUsoyG83frY4" />
                <p class="description">
                    <strong><?php _e('Automatic Review Fetching', 'le-bon-resto'); ?></strong><br>
                    <?php _e('Enter the Google Place ID to automatically fetch reviews, ratings, and review counts from Google Places API.', 'le-bon-resto'); ?><br>
                    <em><?php _e('You can find this in the Google Maps URL or use the Google Places API to search for your restaurant.', 'le-bon-resto'); ?></em>
                </p>
                <div style="background: #e7f3ff; padding: 1rem; border-radius: 4px; margin-top: 0.5rem; border-left: 4px solid #2196F3;">
                    <strong><?php _e('How to get Google Place ID:', 'le-bon-resto'); ?></strong>
                    <ol style="margin: 0.5rem 0 0 1.5rem;">
                        <li><?php _e('Go to Google Maps and search for your restaurant', 'le-bon-resto'); ?></li>
                        <li><?php _e('Click on your restaurant in the results', 'le-bon-resto'); ?></li>
                        <li><?php _e('Copy the URL - the Place ID is in the URL after /place/', 'le-bon-resto'); ?></li>
                        <li><?php _e('Example: maps.google.com/maps/place/Restaurant+Name/@lat,lng,zoom/data=!3m1!4b1!4m5!3m4!1s0x1234567890abcdef:0x1234567890abcdef!8m2!3d40.7128!4d-74.0060', 'le-bon-resto'); ?></li>
                    </ol>
                </div>
            </td>
        </tr>
    </table>

    <?php
}
