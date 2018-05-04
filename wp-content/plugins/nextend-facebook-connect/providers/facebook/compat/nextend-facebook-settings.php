<?php
/*
Nextend FB Connect Settings Page
*/

$newfb_status = "normal";

if (!class_exists('NextendFBSettings')) {

    add_action('admin_init', array(
        'NextendFBSettings',
        'store_settings'
    ));

    class NextendFBSettings {

        static function store_settings() {
            if (current_user_can('manage_options')) {
                if (isset($_POST['newfb_update_options']) && check_admin_referer('nextend-facebook-connect')) {
                    if ($_POST['newfb_update_options'] == 'Y') {
                        foreach ($_POST AS $k => $v) {
                            $_POST[$k] = stripslashes($v);
                        }
                        unset($_POST['Submit']);
                        $sanitize = array(
                            'newfb_update_options',
                            'fb_appid',
                            'fb_secret',
                            'fb_redirect',
                            'fb_redirect_reg',
                            'fb_load_style'
                        );
                        foreach ($sanitize AS $k) {
                            $_POST[$k] = sanitize_text_field($_POST[$k]);
                        }

                        $_POST['fb_user_prefix'] = preg_replace("/[^A-Za-z0-9\-_ ]/", '', $_POST['fb_user_prefix']);
                        update_option("nextend_fb_connect", maybe_serialize($_POST));
                        $newfb_status = 'update_success';
                    }
                }
            }
        }

        static function NextendFB_Options_Page() {
            $domain = get_option('siteurl');
            $domain = str_replace(array(
                'http://',
                'https://'
            ), array(
                '',
                ''
            ), $domain);
            $domain = str_replace('www.', '', $domain);
            $a      = explode("/", $domain);
            $domain = $a[0];
            ?>

            <div class="wrap">
                <div id="newfb-options">
                    <div id="newfb-title"><h2>Nextend Facebook Connect Settings</h2></div>
                    <?php
                    global $newfb_status;
                    if ($newfb_status == 'update_success') {
                        $message = 'Configuration updated' . "<br />";
                    } else if ($newfb_status == 'update_failed') {
                        $message = 'Error while saving options' . "<br />";
                    } else {
                        $message = '';
                    }

                    if ($message != "") {
                        ?>
                        <div class="updated"><strong><p><?php
                                echo $message;
                                ?></p></strong></div><?php
                    } ?>

                    <?php
                    if (!function_exists('curl_init')) {
                        ?>
                        <div class="error"><strong><p>Facebook needs the CURL PHP extension. Contact your server
                                    adminsitrator!</p></strong></div>
                        <?php
                    } else {
                        $version       = curl_version();
                        $ssl_supported = ($version['features'] & CURL_VERSION_SSL);
                        if (!$ssl_supported) {
                            ?>
                            <div class="error"><strong><p>Protocol https not supported or disabled in libcurl. Contact
                                        your server adminsitrator!</p></strong></div>
                            <?php
                        }
                    }
                    if (!function_exists('json_decode')) {
                        ?>
                        <div class="error"><strong><p>Facebook needs the JSON PHP extension. Contact your server
                                    adminsitrator!</p></strong></div>
                        <?php
                    }
                    ?>

                    <div id="newfb-desc">
                        <p><?php echo 'This plugins helps you create Facebook login and register buttons. The login and register process only takes one click and you can fully customize the buttons with images and other assets.'; ?></p>
                        <h3><?php echo 'Setup'; ?></h3>
                        <p>
							<?php echo '<ol><li><a href="https://developers.facebook.com/apps/" target="_blank">Create a facebook app!</a></li>'; ?>
                            <?php echo '<li>Don\'t choose from the listed options, but click on <b>advanced setup</b> in the bottom.</li>'; ?>
                            <?php echo '<li>Choose an <b>app name</b>, and a <b>category</b>, then click on <b>Create App ID</b>.</li>'; ?>
                            <?php echo '<li>Pass the security check.</li>'; ?>
                            <?php echo '<li>Go to the <b>Settings</b> of the application.</li>'; ?>
                            <?php echo '<li>Click on <b>+ Add Platform</b>, and choose <b>Website</b>.</li>'; ?>
                            <?php echo '<li>Give your website\'s address at the <b>Site URL</b> field with: <b>' . get_option('siteurl') . '</b></li>'; ?>
                            <?php echo '<li>Give a <b>Contact Email</b> and click on <b>Save Changes</b>.</li>'; ?>
                            <?php echo '<li>Go to <b>Status & Review</b>, and change the availability for the general public to <b>YES</b>.</li>'; ?>
                            <?php echo '<li>Go back to the <b>Settings</b>, and copy the <b>App ID</b>, and <b>APP Secret</b>, which you have to copy and paste below.</li>'; ?>
                            <?php echo '<li><b>Save changes!</b></li></ol>'; ?>


                        </p>
                        <h3><?php echo 'Usage'; ?></h3>
                        <h4><?php echo 'Simple link'; ?></h4>
                        <p><?php echo '&lt;a href="' . new_fb_login_url() . '&redirect=' . get_option('siteurl') . '" onclick="window.location = \'' . new_fb_login_url() . '&redirect=\'+window.location.href; return false;"&gt;Click here to login or register with Facebook&lt;/a&gt;'; ?></p>

                        <h4><?php echo 'Image button'; ?></h4>
                        <p><?php echo '&lt;a href="' . new_fb_login_url() . '&redirect=' . get_option('siteurl') . '" onclick="window.location = \'' . new_fb_login_url() . '&redirect=\'+window.location.href; return false;"&gt; &lt;img src="HereComeTheImage" /&gt; &lt;/a&gt;'; ?></p>

                        <h3><?php echo 'Note'; ?></h3>
                        <p><?php echo 'If the Facebook user\'s email address already used by another member of your site, the facebook profile will be automatically linked to the existing profile!'; ?></p>

                    </div>

                    <!--right-->
                    <div class="postbox-container" style="float:right;width:30%;">
                        <div class="metabox-holder">
                            <div class="meta-box-sortables">

                                <!--about-->
                                <div id="newfb-about" class="postbox">
                                    <h3 class="hndle"><?php echo 'About this plugin'; ?></h3>
                                    <div class="inside">
                                        <ul>

                                            <li>
                                                <a href="http://www.nextendweb.com/social-connect-plugins-for-wordpress.html"
                                                   target="_blank"><?php echo 'Check the related <b>blog post</b>!'; ?></a>
                                            </li>
                                            <li><br></li>
                                            <li><a href="http://wordpress.org/extend/plugins/nextend-facebook-connect/"
                                                   target="_blank"><?php echo 'Nextend Facebook Connect'; ?></a></li>
                                            <li><br></li>
                                            <li><a href="http://profiles.wordpress.org/nextendweb"
                                                   target="_blank"><?php echo 'Nextend  plugins at WordPress.org'; ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!--about end-->

                                <!--others-->
                                <!--others end-->

                            </div>
                        </div>
                    </div>
                    <!--right end-->

                    <!--left-->
                    <div class="postbox-container" style="float:left;width: 69%;">
                        <div class="metabox-holder">
                            <div class="meta-box-sortabless">

                                <!--setting-->
                                <div id="newfb-setting" class="postbox">
                                    <h3 class="hndle"><?php echo 'Settings'; ?></h3>
                                    <?php $nextend_fb_connect = maybe_unserialize(get_option('nextend_fb_connect')); ?>

                                    <form method="post"
                                          action="<?php echo get_bloginfo("wpurl"); ?>/wp-admin/options-general.php?page=nextend-facebook-connect">
										<?php wp_nonce_field('nextend-facebook-connect'); ?>
                                        <input type="hidden" name="newfb_update_options" value="Y">

                                        <table class="form-table">
                                            <tr>
                                                <th scope="row"><?php echo 'Facebook App ID:'; ?></th>
                                                <td>
                                                    <input type="text" name="fb_appid"
                                                           value="<?php echo esc_html($nextend_fb_connect['fb_appid']); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Facebook App Secret:'; ?></th>
                                                <td>
                                                    <input type="text" name="fb_secret"
                                                           value="<?php echo esc_html($nextend_fb_connect['fb_secret']); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'New user prefix:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_fb_connect['fb_user_prefix'])) {
                                                        $nextend_fb_connect['fb_user_prefix'] = 'Facebook - ';
                                                    } ?>
                                                    <input type="text" name="fb_user_prefix"
                                                           value="<?php echo esc_html($nextend_fb_connect['fb_user_prefix']); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Fixed redirect url for login:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_fb_connect['fb_redirect'])) {
                                                        $nextend_fb_connect['fb_redirect'] = 'auto';
                                                    } ?>
                                                    <input type="text" name="fb_redirect"
                                                           value="<?php echo esc_html($nextend_fb_connect['fb_redirect']); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Fixed redirect url for register:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_fb_connect['fb_redirect_reg'])) {
                                                        $nextend_fb_connect['fb_redirect_reg'] = 'auto';
                                                    } ?>
                                                    <input type="text" name="fb_redirect_reg"
                                                           value="<?php echo esc_html($nextend_fb_connect['fb_redirect_reg']); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Load button stylesheet:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_fb_connect['fb_load_style'])) {
                                                        $nextend_fb_connect['fb_load_style'] = 1;
                                                    } ?>
                                                    <input name="fb_load_style" id="fb_load_style_yes" value="1"
                                                           type="radio" <?php if (isset($nextend_fb_connect['fb_load_style']) && $nextend_fb_connect['fb_load_style']) { ?> checked <?php } ?>>
                                                    Yes &nbsp;&nbsp;&nbsp;&nbsp;
                                                    <input name="fb_load_style" id="fb_load_style_no" value="0"
                                                           type="radio" <?php if (isset($nextend_fb_connect['fb_load_style']) && $nextend_fb_connect['fb_load_style'] == 0) { ?> checked <?php } ?>>
                                                    No
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Login button:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_fb_connect['fb_login_button'])) {
                                                        $nextend_fb_connect['fb_login_button'] = '<div class="new-fb-btn new-fb-1 new-fb-default-anim"><div class="new-fb-1-1"><div class="new-fb-1-1-1">CONNECT WITH</div></div></div>';
                                                    } ?>
                                                    <textarea cols="83" rows="3"
                                                              name="fb_login_button"><?php echo esc_html($nextend_fb_connect['fb_login_button']); ?></textarea>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Link account button:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_fb_connect['fb_link_button'])) {
                                                        $nextend_fb_connect['fb_link_button'] = '<div class="new-fb-btn new-fb-1 new-fb-default-anim"><div class="new-fb-1-1"><div class="new-fb-1-1-1">LINK ACCOUNT TO</div></div></div>';
                                                    } ?>
                                                    <textarea cols="83" rows="3"
                                                              name="fb_link_button"><?php echo esc_html($nextend_fb_connect['fb_link_button']); ?></textarea>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Unlink account button:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_fb_connect['fb_unlink_button'])) {
                                                        $nextend_fb_connect['fb_unlink_button'] = '<div class="new-fb-btn new-fb-1 new-fb-default-anim"><div class="new-fb-1-1"><div class="new-fb-1-1-1">UNLINK ACCOUNT</div></div></div>';
                                                    } ?>
                                                    <textarea cols="83" rows="3"
                                                              name="fb_unlink_button"><?php echo esc_html($nextend_fb_connect['fb_unlink_button']); ?></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"></th>
                                                <td>
                                                    <a href="http://www.nextendweb.com/social-connect-button-generator"
                                                       target="_blank"><img style="margin-left: -4px;"
                                                                            src="<?php echo plugins_url('generatorbanner.png', __FILE__); ?>"/></a>
                                                </td>
                                            </tr>
                                        </table>

                                        <p class="submit">
                                            <input style="margin-left: 10%;" type="submit" name="Submit"
                                                   value="<?php echo 'Save Changes'; ?>"/>
                                        </p>
                                    </form>
                                </div>
                                <!--setting end-->

                                <!--others-->
                                <!--others end-->

                            </div>
                        </div>
                    </div>
                    <!--left end-->

                </div>
            </div>
            <?php
        }

        function NextendFB_Menu() {
            add_options_page('Nextend FB Connect', 'Nextend FB Connect', 'manage_options', 'nextend-facebook-connect', array(
                'NextendFBSettings',
                'NextendFB_Options_Page'
            ));
        }

    }
}
?>
