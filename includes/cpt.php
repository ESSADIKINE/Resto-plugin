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
                <p class="description"><?php _e('YouTube or Vimeo video URL (e.g., https://www.youtube.com/watch?v=XXXXXX).', 'le-bon-resto'); ?></p>
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

    if (!$details_nonce_valid && !$media_nonce_valid) {
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
        'cuisine_type' => get_post_meta($post_id, '_restaurant_cuisine_type', true),
        'phone' => get_post_meta($post_id, '_restaurant_phone', true),
        'email' => get_post_meta($post_id, '_restaurant_email', true),
        'is_featured' => get_post_meta($post_id, '_restaurant_is_featured', true),
        'virtual_tour_url' => get_post_meta($post_id, '_restaurant_virtual_tour_url', true),
        'gallery' => get_post_meta($post_id, '_restaurant_gallery', true),
        'video_url' => get_post_meta($post_id, '_restaurant_video_url', true),
        'principal_image' => get_post_meta($post_id, '_restaurant_principal_image', true),
    );
}
