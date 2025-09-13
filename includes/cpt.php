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
        'restaurant-menu',
        __('Restaurant Menu', 'le-bon-resto'),
        'lebonresto_restaurant_menu_callback',
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

    // Check if nonce is valid for menu
    $menu_nonce_valid = isset($_POST['lebonresto_restaurant_menu_nonce']) && wp_verify_nonce($_POST['lebonresto_restaurant_menu_nonce'], 'lebonresto_save_restaurant_menu');
    
    // Check if nonce is valid for blog
    $blog_nonce_valid = isset($_POST['lebonresto_restaurant_blog_nonce']) && wp_verify_nonce($_POST['lebonresto_restaurant_blog_nonce'], 'lebonresto_save_restaurant_blog');
    
    // Check if nonce is valid for options
    $options_nonce_valid = isset($_POST['lebonresto_restaurant_options_nonce']) && wp_verify_nonce($_POST['lebonresto_restaurant_options_nonce'], 'lebonresto_save_restaurant_options');
    
    // Check if nonce is valid for menus
    $menus_nonce_valid = isset($_POST['lebonresto_restaurant_menus_nonce']) && wp_verify_nonce($_POST['lebonresto_restaurant_menus_nonce'], 'lebonresto_save_restaurant_menus');

    if (!$details_nonce_valid && !$media_nonce_valid && !$menu_nonce_valid && !$blog_nonce_valid && !$options_nonce_valid && !$menus_nonce_valid) {
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
    
    // Save menu meta fields
    if ($menu_nonce_valid) {
        if (isset($_POST['restaurant_menu_image'])) {
            $menu_image = sanitize_text_field($_POST['restaurant_menu_image']);
            update_post_meta($post_id, '_restaurant_menu_image', $menu_image);
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
        'is_featured' => get_post_meta($post_id, '_restaurant_is_featured', true),
        'virtual_tour_url' => get_post_meta($post_id, '_restaurant_virtual_tour_url', true),
        'gallery' => get_post_meta($post_id, '_restaurant_gallery', true),
        'video_url' => get_post_meta($post_id, '_restaurant_video_url', true),
        'principal_image' => get_post_meta($post_id, '_restaurant_principal_image', true),
        'menu_image' => get_post_meta($post_id, '_restaurant_menu_image', true),
        'blog_title' => get_post_meta($post_id, '_restaurant_blog_title', true),
        'blog_content' => get_post_meta($post_id, '_restaurant_blog_content', true),
        'selected_options' => get_post_meta($post_id, '_restaurant_selected_options', true),
        'menus' => get_post_meta($post_id, '_restaurant_menus', true),
    );
}

/**
 * Restaurant menu meta box callback function
 */
function lebonresto_restaurant_menu_callback($post) {
    // Add nonce for security
    wp_nonce_field('lebonresto_save_restaurant_menu', 'lebonresto_restaurant_menu_nonce');

    // Get current values
    $menu_image = get_post_meta($post->ID, '_restaurant_menu_image', true);

    ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="restaurant_menu_image"><?php _e('Menu Image', 'le-bon-resto'); ?></label>
            </th>
            <td>
                <div id="restaurant-menu-image-container">
                    <input type="hidden" id="restaurant_menu_image" name="restaurant_menu_image" value="<?php echo esc_attr($menu_image); ?>" />
                    <button type="button" class="button" id="restaurant-menu-image-button">
                        <?php _e('Select Menu Image', 'le-bon-resto'); ?>
                    </button>
                    <button type="button" class="button" id="restaurant-menu-image-clear" style="margin-left: 10px;">
                        <?php _e('Clear Image', 'le-bon-resto'); ?>
                    </button>
                    <div id="restaurant-menu-image-preview" style="margin-top: 10px;">
                        <?php if ($menu_image): ?>
                            <?php lebonresto_display_menu_image_preview($menu_image); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="description"><?php _e('Upload an image of the restaurant menu or menu board.', 'le-bon-resto'); ?></p>
            </td>
        </tr>
    </table>

    <script>
    jQuery(document).ready(function($) {
        var menuImageUploader;

        // Menu Image functionality
        $('#restaurant-menu-image-button').click(function(e) {
            e.preventDefault();
            
            if (menuImageUploader) {
                menuImageUploader.open();
                return;
            }
            
            menuImageUploader = wp.media({
                title: '<?php _e('Select Menu Image', 'le-bon-resto'); ?>',
                button: {
                    text: '<?php _e('Use this image', 'le-bon-resto'); ?>'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            menuImageUploader.on('select', function() {
                var attachment = menuImageUploader.state().get('selection').first().toJSON();
                $('#restaurant_menu_image').val(attachment.id);
                updateMenuImagePreview(attachment);
            });
            
            menuImageUploader.open();
        });

        $('#restaurant-menu-image-clear').click(function(e) {
            e.preventDefault();
            $('#restaurant_menu_image').val('');
            $('#restaurant-menu-image-preview').empty();
        });

        function updateMenuImagePreview(attachment) {
            var preview = $('#restaurant-menu-image-preview');
            preview.empty();
            
            if (attachment) {
                var imageDiv = $('<div style="position: relative; width: 200px; height: 150px;"></div>');
                var img = $('<img src="' + attachment.sizes.medium.url + '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; border: 2px solid #ddd;" />');
                imageDiv.append(img);
                preview.append(imageDiv);
            }
        }
    });
    </script>
    <?php
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
 * Display menu image preview in admin
 */
function lebonresto_display_menu_image_preview($image_id) {
    if (empty($image_id)) {
        return;
    }

    $image_url = wp_get_attachment_image_url($image_id, 'medium');
    if ($image_url) {
        echo '<div style="position: relative; width: 200px; height: 150px;">';
        echo '<img src="' . esc_url($image_url) . '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; border: 2px solid #ddd;" />';
        echo '</div>';
    }
}
