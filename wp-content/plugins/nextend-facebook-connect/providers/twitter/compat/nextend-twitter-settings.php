<?php
/*
Nextend Twitter Connect Settings Page
*/

$newtwitter_status = "normal";


if (!class_exists('NextendTwitterSettings')) {

    add_action('admin_init', array(
        'NextendTwitterSettings',
        'store_settings'
    ));

    class NextendTwitterSettings {

        static function store_settings() {
            if (current_user_can('manage_options')) {
                if (isset($_POST['newtwitter_update_options']) && check_admin_referer('nextend-twitter-connect')) {
                    if ($_POST['newtwitter_update_options'] == 'Y') {
                        foreach ($_POST AS $k => $v) {
                            $_POST[$k] = stripslashes($v);
                        }
                        unset($_POST['Submit']);
                        $sanitize = array(
                            'newtwitter_update_options',
                            'twitter_consumer_key',
                            'twitter_consumer_secret',
                            'twitter_redirect',
                            'twitter_redirect_reg',
                            'twitter_load_style'
                        );
                        foreach ($sanitize AS $k) {
                            $_POST[$k] = sanitize_text_field($_POST[$k]);
                        }

                        $_POST['twitter_user_prefix'] = preg_replace("/[^A-Za-z0-9\-_ ]/", '', $_POST['twitter_user_prefix']);

                        update_option("nextend_twitter_connect", maybe_serialize($_POST));
                        $newtwitter_status = 'update_success';
                    }
                }
            }
        }

        static function NextendTwitter_Options_Page() {
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
                <div id="newtwitter-options">
                    <div id="newtwitter-title"><h2>Nextend Twitter Connect Settings</h2></div>
                    <?php
                    global $newtwitter_status;
                    if ($newtwitter_status == 'update_success') {
                        $message = 'Configuration updated' . "<br />";
                    } else if ($newtwitter_status == 'update_failed') {
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
                    <div id="newtwitter-desc">
                        <p><?php echo 'This plugins helps you create Twitter login and register buttons. The login and register process only takes one click and you can fully customize the buttons with images and other assets.'; ?></p>
                        <h3><?php echo 'Setup'; ?></h3>
                        <p>
							<?php echo '<ol><li><a href="https://dev.twitter.com/apps/new" target="_blank">Create a twitter app!</a></li>'; ?>
                            <?php echo '<li>Choose an App Name, it can be anything you like. Fill out the description and your website home page: ' . site_url() . '</li>'; ?>
                            <?php echo '<li>Callback url must be: ' . new_twitter_login_url() . '</li>'; ?>
                            <?php echo '<li>Accept the rules and Click on <b>Create your twitter application</b></li>'; ?>
                            <?php echo '<li>The next page contains the <b>Consumer key</b> and <b>Consumer secret</b> which you have to copy and past below.</li>'; ?>
                            <?php echo '<li><b>Save changes!</b></li></ol>'; ?>


                        </p>
                        <h3><?php echo 'Usage'; ?></h3>
                        <h4><?php echo 'Simple link'; ?></h4>
                        <p><?php echo '&lt;a href="' . new_twitter_login_url() . '&redirect=' . get_option('siteurl') . '" onclick="window.location = \'' . new_twitter_login_url() . '&redirect=\'+window.location.href; return false;"&gt;Click here to login or register with twitter&lt;/a&gt;'; ?></p>

                        <h4><?php echo 'Image button'; ?></h4>
                        <p><?php echo '&lt;a href="' . new_twitter_login_url() . '&redirect=' . get_option('siteurl') . '" onclick="window.location = \'' . new_twitter_login_url() . '&redirect=\'+window.location.href; return false;"&gt; &lt;img src="HereComeTheImage" /&gt; &lt;/a&gt;'; ?></p>

                        <h3><?php echo 'Note'; ?></h3>
                        <p><?php echo 'If the twitter user\'s email address already used by another member of your site, the twitter profile will be automatically linked to the existing profile!'; ?></p>

                    </div>

                    <!--right-->
                    <div class="postbox-container" style="float:right;width:30%;">
                        <div class="metabox-holder">
                            <div class="meta-box-sortables">

                                <!--about-->
                                <div id="newtwitter-about" class="postbox">
                                    <h3 class="hndle"><?php echo 'About this plugin'; ?></h3>
                                    <div class="inside">
                                        <ul>
                                            <li>
                                                <a href="http://www.nextendweb.com/social-connect-plugins-for-wordpress.html"
                                                   target="_blank"><?php echo 'Check the related <b>blog post</b>!'; ?></a>
                                            </li>
                                            <li><br></li>
                                            <li>
                                                <a href="http://wordpress.org/extend/plugins/nextend-twitter-connect/"><?php echo 'Nextend Twitter Connect'; ?></a>
                                            </li>
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
                                <div id="newtwitter-setting" class="postbox">
                                    <h3 class="hndle"><?php echo 'Settings'; ?></h3>
                                    <?php $nextend_twitter_connect = maybe_unserialize(get_option('nextend_twitter_connect')); ?>

                                    <form method="post"
                                          action="<?php echo get_bloginfo("wpurl"); ?>/wp-admin/options-general.php?page=nextend-twitter-connect">
										<?php wp_nonce_field('nextend-twitter-connect'); ?>
                                        <input type="hidden" name="newtwitter_update_options" value="Y">

                                        <table class="form-table">
                                            <tr>
                                                <th scope="row"><?php echo 'Twitter Consumer key:'; ?></th>
                                                <td>
                                                    <input type="text" name="twitter_consumer_key"
                                                           value="<?php echo esc_html($nextend_twitter_connect['twitter_consumer_key']); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Twitter Consumer secret:'; ?></th>
                                                <td>
                                                    <input type="text" name="twitter_consumer_secret"
                                                           value="<?php echo esc_html($nextend_twitter_connect['twitter_consumer_secret']); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'New user prefix:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_twitter_connect['twitter_user_prefix'])) {
                                                        $nextend_twitter_connect['twitter_user_prefix'] = 'twitter - ';
                                                    } ?>
                                                    <input type="text" name="twitter_user_prefix"
                                                           value="<?php echo esc_html($nextend_twitter_connect['twitter_user_prefix']); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Fixed redirect url for login:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_twitter_connect['twitter_redirect'])) {
                                                        $nextend_twitter_connect['twitter_redirect'] = 'auto';
                                                    } ?>
                                                    <input type="text" name="twitter_redirect"
                                                           value="<?php echo esc_html($nextend_twitter_connect['twitter_redirect']); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Fixed redirect url for register:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_twitter_connect['twitter_redirect_reg'])) {
                                                        $nextend_twitter_connect['twitter_redirect_reg'] = 'auto';
                                                    } ?>
                                                    <input type="text" name="twitter_redirect_reg"
                                                           value="<?php echo esc_html($nextend_twitter_connect['twitter_redirect_reg']); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Load button stylesheet:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_twitter_connect['twitter_load_style'])) {
                                                        $nextend_twitter_connect['twitter_load_style'] = 1;
                                                    } ?>
                                                    <input name="twitter_load_style" id="twitter_load_style_yes"
                                                           value="1"
                                                           type="radio" <?php if (isset($nextend_twitter_connect['twitter_load_style']) && $nextend_twitter_connect['twitter_load_style']) { ?> checked <?php } ?>>
                                                    Yes &nbsp;&nbsp;&nbsp;&nbsp;
                                                    <input name="twitter_load_style" id="twitter_load_style_no"
                                                           value="0"
                                                           type="radio" <?php if (isset($nextend_twitter_connect['twitter_load_style']) && $nextend_twitter_connect['twitter_load_style'] == 0) { ?> checked <?php } ?>>
                                                    No
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Login button:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_twitter_connect['twitter_login_button'])) {
                                                        $nextend_twitter_connect['twitter_login_button'] = '<div class="new-twitter-btn new-twitter-1 new-twitter-default-anim"><div class="new-twitter-1-1"><div class="new-twitter-1-1-1">CONNECT WITH</div></div></div>';
                                                    } ?>
                                                    <textarea cols="83" rows="3"
                                                              name="twitter_login_button"><?php echo esc_html($nextend_twitter_connect['twitter_login_button']); ?></textarea>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Link account button:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_twitter_connect['twitter_link_button'])) {
                                                        $nextend_twitter_connect['twitter_link_button'] = '<div class="new-twitter-btn new-twitter-1 new-twitter-default-anim"><div class="new-twitter-1-1"><div class="new-twitter-1-1-1">LINK ACCOUNT TO</div></div></div>';
                                                    } ?>
                                                    <textarea cols="83" rows="3"
                                                              name="twitter_link_button"><?php echo esc_html($nextend_twitter_connect['twitter_link_button']); ?></textarea>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php echo 'Unlink account button:'; ?></th>
                                                <td>
													<?php if (!isset($nextend_twitter_connect['twitter_unlink_button'])) {
                                                        $nextend_twitter_connect['twitter_unlink_button'] = '<div class="new-twitter-btn new-twitter-1 new-twitter-default-anim"><div class="new-twitter-1-1"><div class="new-twitter-1-1-1">UNLINK ACCOUNT</div></div></div>';
                                                    } ?>
                                                    <textarea cols="83" rows="3"
                                                              name="twitter_unlink_button"><?php echo esc_html($nextend_twitter_connect['twitter_unlink_button']); ?></textarea>
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

        function NextendTwitter_Menu() {
            add_options_page('Nextend Twitter Connect', 'Nextend Twitter Connect', 'manage_options', 'nextend-twitter-connect', array(
                'NextendTwitterSettings',
                'NextendTwitter_Options_Page'
            ));
        }

    }
}
?>
