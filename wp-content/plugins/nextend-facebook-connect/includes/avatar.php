<?php

class NextendSocialLoginAvatar {

    public function __construct() {
        if (NextendSocialLogin::$settings->get('avatar_store')) {
            add_action('nsl_update_avatar', array(
                $this,
                'updateAvatar'
            ), 10, 3);

            // WP User Avatar https://wordpress.org/plugins/wp-user-avatar/
            if (!defined('WPUA_VERSION')) {
                add_filter('get_avatar', array(
                    $this,
                    'renderAvatar'
                ), 5, 6);

                add_filter('bp_core_fetch_avatar', array(
                    $this,
                    'renderAvatarBP'
                ), 3, 2);

                add_filter('bp_core_fetch_avatar_url', array(
                    $this,
                    'renderAvatarBPUrl'
                ), 3, 2);

            }

            add_filter('post_mime_types', array(
                $this,
                'addPostMimeTypeAvatar'
            ));

            add_filter('ajax_query_attachments_args', array(
                $this,
                'modifyQueryAttachmentsArgs'
            ));
        }
    }

    public function addPostMimeTypeAvatar($types) {
        $types['avatar'] = array(
            __('Avatar', 'nextend-facebook-connect'),
            __('Manage Avatar', 'nextend-facebook-connect'),
            _n_noop('Avatar <span class="count">(%s)</span>', 'Avatar <span class="count">(%s)</span>', 'nextend-facebook-connect')
        );

        return $types;
    }

    public function modifyQueryAttachmentsArgs($query) {
        if (!isset($query['meta_query']) || !is_array($query['meta_query'])) {
            $query['meta_query'] = array();
        }
        if ($query['post_mime_type'] === 'avatar') {
            $query['post_mime_type']         = 'image';
            $query['meta_query']['relation'] = 'AND';
            $query['meta_query'][]           = array(
                'key'     => '_wp_attachment_wp_user_avatar',
                'compare' => 'EXISTS'
            );
        } else {
            $query['meta_query']['relation'] = 'AND';
            $query['meta_query'][]           = array(
                'key'     => '_wp_attachment_wp_user_avatar',
                'compare' => 'NOT EXISTS'
            );
        }

        return $query;
    }

    /**
     * @param NextendSocialProvider $provider
     * @param                       $user_id
     * @param                       $avatarUrl
     */
    public function updateAvatar($provider, $user_id, $avatarUrl) {
        global $blog_id, $wpdb;
        if (!empty($avatarUrl)) {

            $original_attachment_id = get_user_meta($user_id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', true);
            if ($original_attachment_id && !get_attached_file($original_attachment_id)) {
                $original_attachment_id = false;
            }
            $overwriteAttachment = false;
            if ($original_attachment_id && get_post_meta($original_attachment_id, $provider->getId() . '_avatar', true)) {
                $overwriteAttachment = true;
            }

            if (!$original_attachment_id) {
                /**
                 * If the user unlink and link the social provider back the original avatar will be used.
                 */
                $args  = array(
                    'post_type'   => 'attachment',
                    'post_status' => 'inherit',
                    'meta_query'  => array(
                        array(
                            'key'   => $provider->getId() . '_avatar',
                            'value' => $provider->getAuthUserData('id')
                        )
                    )
                );
                $query = new WP_Query($args);
                if ($query->post_count > 0) {
                    $original_attachment_id = $query->posts[0]->ID;
                    $overwriteAttachment    = true;
                    update_user_meta($user_id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', $original_attachment_id);
                }
            }

            if (!$original_attachment_id || $overwriteAttachment === true) {
                require_once(ABSPATH . '/wp-admin/includes/file.php');

                $avatarTempPath = download_url($avatarUrl);
                if (!is_wp_error($avatarTempPath)) {
                    $mime        = wp_get_image_mime($avatarTempPath);
                    $mime_to_ext = apply_filters('getimagesize_mimes_to_exts', array(
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/gif'  => 'gif',
                        'image/bmp'  => 'bmp',
                        'image/tiff' => 'tif',
                    ));

                    if (isset($mime_to_ext[$mime])) {

                        $wp_upload_dir = wp_upload_dir();
                        $filename      = 'user-' . $user_id . '.' . $mime_to_ext[$mime];

                        $filename = wp_unique_filename($wp_upload_dir['path'], $filename);

                        $newAvatarPath = trailingslashit($wp_upload_dir['path']) . $filename;
                        $newFile       = @copy($avatarTempPath, $newAvatarPath);
                        @unlink($avatarTempPath);

                        if (false !== $newFile) {
                            $url = $wp_upload_dir['url'] . '/' . basename($filename);

                            if ($overwriteAttachment) {
                                $originalAvatarImage = get_attached_file($original_attachment_id);

                                // we got the same image, so we do not want to store it
                                if (md5_file($originalAvatarImage) === md5_file($newAvatarPath)) {
                                    @unlink($newAvatarPath);
                                } else {
                                    // Store the new avatar and remove the old one
                                    @unlink($originalAvatarImage);
                                    update_attached_file($original_attachment_id, $newAvatarPath);

                                    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                                    require_once(ABSPATH . 'wp-admin/includes/image.php');

                                    wp_update_attachment_metadata($original_attachment_id, wp_generate_attachment_metadata($original_attachment_id, $newAvatarPath));

                                    update_user_meta($user_id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', $original_attachment_id);
                                }
                            } else {
                                $attachment = array(
                                    'guid'           => $url,
                                    'post_mime_type' => $mime,
                                    'post_title'     => '',
                                    'post_content'   => '',
                                    'post_status'    => 'private',
                                );

                                $new_attachment_id = wp_insert_attachment($attachment, $newAvatarPath);
                                if (!is_wp_error($new_attachment_id)) {

                                    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                                    require_once(ABSPATH . 'wp-admin/includes/image.php');

                                    wp_update_attachment_metadata($new_attachment_id, wp_generate_attachment_metadata($new_attachment_id, $newAvatarPath));

                                    update_post_meta($new_attachment_id, $provider->getId() . '_avatar', $provider->getAuthUserData('id'));
                                    update_post_meta($new_attachment_id, '_wp_attachment_wp_user_avatar', $user_id);

                                    update_user_meta($user_id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', $new_attachment_id);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function renderAvatar($avatar = '', $id_or_email, $size = 96, $default = '', $alt = false, $args = array()) {
        global $blog_id, $wpdb;

        $id = 0;

        if (is_numeric($id_or_email)) {
            $id = $id_or_email;
        } else if (is_string($id_or_email)) {
            $user = get_user_by('email', $id_or_email);
            if ($user) {
                $id = $user->ID;
            }
        } else if (is_object($id_or_email)) {
            if (!empty($id_or_email->comment_author_email)) {
                $user = get_user_by('email', $id_or_email->comment_author_email);
                if ($user) {
                    $id = $user->ID;
                }
            } else if (!empty($id_or_email->user_id)) {
                $id = $id_or_email->user_id;
            }
        }
        if ($id == 0) {
            return $avatar;
        }

        $url = '';

        $attachment_id = get_user_meta($id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', true);
        if (wp_attachment_is_image($attachment_id)) {
            $get_size        = is_numeric($size) ? array(
                $size,
                $size
            ) : $size;
            $image_src_array = wp_get_attachment_image_src($attachment_id, $get_size);

            $url = $image_src_array[0];

            if (is_numeric($size)) {
                $args['width']  = $image_src_array[1];
                $args['height'] = $image_src_array[2];
            }
        }

        if (empty($url)) {
            $url = NextendSocialLogin::getAvatar($id);
        }

        if (!$url) {
            return $avatar;
        }

        if (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) {
            add_filter('user_profile_picture_description', array(
                $this,
                'removeProfilePictureGravatarDescription'
            ));
        }

        $class = array(
            'avatar',
            'avatar-' . (int)$args['size'],
            'photo'
        );

        if ($args['class']) {
            if (is_array($args['class'])) {
                $class = array_merge($class, $args['class']);
            } else {
                $class[] = $args['class'];
            }
        }

        return sprintf("<img alt='%s' src='%s' class='%s' height='%d' width='%d' %s/>", esc_attr($args['alt']), esc_url($url), esc_attr(join(' ', $class)), (int)$args['height'], (int)$args['width'], $args['extra_attr']);
    }

    public function renderAvatarBP($avatar, $params) {

        if (strpos($avatar, 'gravatar.com', 0) > -1) {

            $avatar = $this->renderAvatar($avatar, ($params['object'] == 'user') ? $params['item_id'] : '', ($params['object'] == 'user') ? (($params['type'] == 'thumb') ? 50 : 150) : 50, '', '');
        }

        return $avatar;
    }

    public function renderAvatarBPUrl($avatar, $params) {

        if (strpos($avatar, 'gravatar.com', 0) > -1) {

            $avatar = $this->renderAvatar($avatar, ($params['object'] == 'user') ? $params['item_id'] : '', ($params['object'] == 'user') ? (($params['type'] == 'thumb') ? 50 : 150) : 50, '', '');
        }

        return $avatar;
    }

    public function removeProfilePictureGravatarDescription($description) {
        if (strpos($description, 'Gravatar') !== false) {
            return '';
        }

        return $description;
    }
}

new NextendSocialLoginAvatar();