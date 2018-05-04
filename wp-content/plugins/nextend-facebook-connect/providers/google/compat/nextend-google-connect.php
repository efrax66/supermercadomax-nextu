<?php

/*
Plugin Name: Nextend Google Connect
Plugin URI: http://nextendweb.com/
Description: Google connect
Version: 1.6.1
Author: Roland Soos, Jamie Bainbridge
License: GPL2
*/

/*  Copyright 2012  Roland Soos - Nextend  (email : roland@nextendweb.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
global $new_google_settings;

define('NEW_GOOGLE_LOGIN', 1);
if (!defined('NEW_GOOGLE_LOGIN_PLUGIN_BASENAME')) {
    define('NEW_GOOGLE_LOGIN_PLUGIN_BASENAME', plugin_basename(__FILE__));
}
$new_google_settings = maybe_unserialize(get_option('nextend_google_connect'));

if (!function_exists('nextend_uniqid')) {
    function nextend_uniqid() {
        if (isset($_COOKIE['nextend_uniqid'])) {
            if (get_site_transient('n_' . $_COOKIE['nextend_uniqid']) !== false) {
                return $_COOKIE['nextend_uniqid'];
            }
        }
        $_COOKIE['nextend_uniqid'] = uniqid('nextend', true);
        setcookie('nextend_uniqid', $_COOKIE['nextend_uniqid'], time() + 3600, '/');
        set_site_transient('n_' . $_COOKIE['nextend_uniqid'], 1, 3600);

        return $_COOKIE['nextend_uniqid'];
    }
}

/*
Loading style for buttons
*/

function nextend_google_connect_stylesheet() {
    wp_register_style('nextend_google_connect_stylesheet', plugins_url('buttons/google-btn.css', __FILE__));
    wp_enqueue_style('nextend_google_connect_stylesheet');
}

if ($new_google_settings['google_load_style']) {
    add_action('wp_enqueue_scripts', 'nextend_google_connect_stylesheet');
    add_action('login_enqueue_scripts', 'nextend_google_connect_stylesheet');
    add_action('admin_enqueue_scripts', 'nextend_google_connect_stylesheet');
}

/*
Adding query vars for the WP parser
*/

function new_google_add_query_var() {

    global $wp;
    $wp->add_query_var('editProfileRedirect');
    $wp->add_query_var('loginGoogle');
}

add_filter('init', 'new_google_add_query_var');

/* -----------------------------------------------------------------------------
Main function to handle the Sign in/Register/Linking process
----------------------------------------------------------------------------- */

/*
Compatibility for older versions
*/
add_action('parse_request', 'new_google_login_compat');

function new_google_login_compat() {

    global $wp;
    if ($wp->request == 'loginGoogle' || isset($wp->query_vars['loginGoogle'])) {
        new_google_login_action();
    }
}

/*
For login page
*/
add_action('login_init', 'new_google_login');

function new_google_login() {

    if (isset($_REQUEST['loginGoogle']) && $_REQUEST['loginGoogle'] == '1') {
        new_google_login_action();
    }
}

function new_google_login_action() {
    global $wp, $wpdb, $new_google_settings;

    if (isset($_GET['action']) && $_GET['action'] == 'unlink') {
        $user_info = wp_get_current_user();
        if ($user_info->ID) {
            $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'social_users
          WHERE ID = %d
          AND type = \'google\'', $user_info->ID));
            set_site_transient($user_info->ID . '_new_google_admin_notice', 'Your Google profile is successfully unlinked from your account.', 3600);
        }
        new_google_redirect();
    }
    include(dirname(__FILE__) . '/sdk/init.php');

    if (isset($_GET['code'])) {
        if (isset($new_google_settings['google_redirect']) && $new_google_settings['google_redirect'] != '' && $new_google_settings['google_redirect'] != 'auto') {
            $_GET['redirect'] = $new_google_settings['google_redirect'];
        }

        set_site_transient(nextend_uniqid() . '_google_r', $_GET['redirect'], 3600);

        $client->authenticate();
        $access_token = $client->getAccessToken();
        set_site_transient(nextend_uniqid() . '_google_at', $access_token, 3600);
        header('Location: ' . filter_var(new_google_login_url(), FILTER_SANITIZE_URL));
        exit;
    }

    $access_token = get_site_transient(nextend_uniqid() . '_google_at');

    if ($access_token !== false) {
        $client->setAccessToken($access_token);
    }
    if (isset($_REQUEST['logout'])) {
        delete_site_transient(nextend_uniqid() . '_google_at');
        $client->revokeToken();
    }
    if ($client->getAccessToken()) {
        $u = $oauth2->userinfo->get();

        // The access token may have been updated lazily.
        set_site_transient(nextend_uniqid() . '_google_at', $client->getAccessToken(), 3600);

        // These fields are currently filtered through the PHP sanitize filters.

        // See http://www.php.net/manual/en/filter.filters.sanitize.php

        $email = filter_var($u['email'], FILTER_SANITIZE_EMAIL);
        $ID    = $wpdb->get_var($wpdb->prepare('
      SELECT ID FROM ' . $wpdb->prefix . 'social_users WHERE type = "google" AND identifier = "%s"
    ', $u['id']));
        if (!get_user_by('id', $ID)) {
            $wpdb->query($wpdb->prepare('
        DELETE FROM ' . $wpdb->prefix . 'social_users WHERE ID = "%s"
      ', $ID));
            $ID = null;
        }
        if (!is_user_logged_in()) {
            if ($ID == null) { // Register

                $ID = email_exists($email);
                if ($ID == false) { // Real register

                    require_once(ABSPATH . WPINC . '/registration.php');
                    $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
                    if (!isset($new_google_settings['google_user_prefix'])) {
                        $new_google_settings['google_user_prefix'] = 'Google - ';
                    }
                    $sanitized_user_login = sanitize_user($new_google_settings['google_user_prefix'] . $u['name']);
                    if (!validate_username($sanitized_user_login)) {
                        $sanitized_user_login = sanitize_user('google' . $user_profile['id']);
                    }
                    $defaul_user_name = $sanitized_user_login;
                    $i                = 1;
                    while (username_exists($sanitized_user_login)) {
                        $sanitized_user_login = $defaul_user_name . $i;
                        $i++;
                    }
                    $ID = wp_create_user($sanitized_user_login, $random_password, $email);
                    if (!is_wp_error($ID)) {
                        wp_new_user_notification($ID, $random_password);
                        $user_info = get_userdata($ID);
                        wp_update_user(array(
                            'ID'           => $ID,
                            'display_name' => $u['name'],
                            'first_name'   => $u['given_name'],
                            'last_name'    => $u['family_name'],
                            'googleplus'   => $u['link']
                        ));
                        update_user_meta($ID, 'new_google_default_password', $user_info->user_pass);
                        do_action('nextend_google_user_registered', $ID, $u, $oauth2);
                    } else {
                        return;
                    }
                }
                if ($ID) {
                    $wpdb->insert($wpdb->prefix . 'social_users', array(
                        'ID'         => $ID,
                        'type'       => 'google',
                        'identifier' => $u['id']
                    ), array(
                        '%d',
                        '%s',
                        '%s'
                    ));
                }
                if (isset($new_google_settings['google_redirect_reg']) && $new_google_settings['google_redirect_reg'] != '' && $new_google_settings['google_redirect_reg'] != 'auto') {
                    set_site_transient(nextend_uniqid() . '_google_r', $new_google_settings['google_redirect_reg'], 3600);
                }
            }
            if ($ID) { // Login

                $secure_cookie = is_ssl();
                $secure_cookie = apply_filters('secure_signon_cookie', $secure_cookie, array());
                global $auth_secure_cookie; // XXX ugly hack to pass this to wp_authenticate_cookie

                $auth_secure_cookie = $secure_cookie;
                wp_set_auth_cookie($ID, true, $secure_cookie);
                $user_info = get_userdata($ID);
                do_action('wp_login', $user_info->user_login, $user_info);
                do_action('nextend_google_user_logged_in', $ID, $u, $oauth2);

                // @Jamie Bainbridge fix for Google Avatars
                $userJSON = @file_get_contents('http://picasaweb.google.com/data/entry/api/user/' . $u['id'] . '?alt=json');
                if ($userJSON) {
                    $userArray = json_decode($userJSON, true);
                    if ($userArray && isset($userArray["entry"]) && isset($userArray["entry"]["gphoto\$thumbnail"]) && isset($userArray["entry"]["gphoto\$thumbnail"]["\$t"])) {
                        update_user_meta($ID, 'google_profile_picture', $userArray["entry"]["gphoto\$thumbnail"]["\$t"]);
                    }
                }
            }
        } else {
            if (new_google_is_user_connected()) { // It was a simple login


            } elseif ($ID === null) { // Let's connect the account to the current user!

                $current_user = wp_get_current_user();
                $wpdb->insert($wpdb->prefix . 'social_users', array(
                    'ID'         => $current_user->ID,
                    'type'       => 'google',
                    'identifier' => $u['id']
                ), array(
                    '%d',
                    '%s',
                    '%s'
                ));
                do_action('nextend_google_user_account_linked', $ID, $u, $oauth2);
                $user_info = wp_get_current_user();
                set_site_transient($user_info->ID . '_new_google_admin_notice', 'Your Google profile is successfully linked with your account. Now you can sign in with Google easily.', 3600);
            } else {
                $user_info = wp_get_current_user();
                set_site_transient($user_info->ID . '_new_google_admin_notice', 'This Google profile is already linked with other account. Linking process failed!', 3600);
            }
        }
    } else {
        if (isset($new_google_settings['google_redirect']) && $new_google_settings['google_redirect'] != '' && $new_google_settings['google_redirect'] != 'auto') {
            $_GET['redirect'] = $new_google_settings['google_redirect'];
        }
        if (isset($_GET['redirect'])) {
            set_site_transient(nextend_uniqid() . '_google_r', $_GET['redirect'], 3600);
        }

        $redirect = get_site_transient(nextend_uniqid() . '_google_r');

        if ($redirect || $redirect == new_google_login_url()) {
            $redirect = site_url();
            set_site_transient(nextend_uniqid() . '_google_r', $redirect, 3600);
        }
        header('LOCATION: ' . $client->createAuthUrl());
        exit;
    }
    new_google_redirect();
}

/*
Is the current user connected the Google profile?
*/

function new_google_is_user_connected() {

    global $wpdb;
    $current_user = wp_get_current_user();
    $ID           = $wpdb->get_var($wpdb->prepare('
    SELECT identifier FROM ' . $wpdb->prefix . 'social_users WHERE type = "google" AND ID = "%d"
  ', $current_user->ID));
    if ($ID === null) {
        return false;
    }

    return $ID;
}

/*
Connect Field in the Profile page
*/

function new_add_google_connect_field() {

    global $new_is_social_header;
    if ($new_is_social_header === null) {
        ?>
        <h3>Social connect</h3>
        <?php
        $new_is_social_header = true;
    }
    ?>
    <table class="form-table">
        <tbody>
        <tr>
            <th></th>
            <td>
				<?php
                if (new_google_is_user_connected()) {
                    echo new_google_unlink_button();
                } else {
                    echo new_google_link_button();
                }
                ?>
            </td>
        </tr>
        </tbody>
    </table>
    <?php
}

add_action('profile_personal_options', 'new_add_google_connect_field');

function new_add_google_login_form() {

    ?>
    <script>
		if (jQuery.type(has_social_form) === 'undefined') {
            var has_social_form = false;
            var socialLogins = null;
        }
        jQuery(document).ready(function () {
            (function ($) {
                if (!has_social_form) {
                    has_social_form = true;
                    var loginForm = $('#loginform,#registerform,#front-login-form,#setupform');
                    socialLogins = $(
                        '<div class="newsociallogins" style="text-align: center;"><div style="clear:both;"></div></div>');
                    if (loginForm.find('input').length > 0) {
                        loginForm.prepend("<h3 style='text-align:center;'><?php echo 'OR'; ?></h3>");
                    }
                    loginForm.prepend(socialLogins);
                }
                if (!window.google_added) {
                    socialLogins.prepend(
                        '<?php echo addslashes(preg_replace('/^\s+|\n|\r|\s+$/m', '', new_google_sign_button())); ?>');
                    window.google_added = true;
                }
            }(jQuery));
        });
    </script>
    <?php
}

add_action('login_form', 'new_add_google_login_form');
add_action('register_form', 'new_add_google_login_form');
add_action('bp_sidebar_login_form', 'new_add_google_login_form');
add_filter('get_avatar', 'new_google_insert_avatar', 5, 5);

function new_google_insert_avatar($avatar = '', $id_or_email, $size = 96, $default = '', $alt = false) {

    $id = 0;
    if (is_numeric($id_or_email)) {
        $id = $id_or_email;
    } else if (is_string($id_or_email)) {
        $u  = get_user_by('email', $id_or_email);
        $id = $u->id;
    } else if (is_object($id_or_email)) {
        $id = $id_or_email->user_id;
    }
    if ($id == 0) {
        return $avatar;
    }
    $pic = get_user_meta($id, 'google_profile_picture', true);
    if (!$pic || $pic == '') {
        return $avatar;
    }
    $avatar = preg_replace('/src=("|\').*?("|\')/i', 'src=\'' . $pic . '\'', $avatar);

    return $avatar;
}

add_filter('bp_core_fetch_avatar', 'new_google_bp_insert_avatar', 3, 5);

function new_google_bp_insert_avatar($avatar = '', $params, $id) {
    if (!is_numeric($id) || strpos($avatar, 'gravatar') === false) {
        return $avatar;
    }
    $pic = get_user_meta($id, 'google_profile_picture', true);
    if (!$pic || $pic == '') {
        return $avatar;
    }
    $avatar = preg_replace('/src=("|\').*?("|\')/i', 'src=\'' . $pic . '\'', $avatar);

    return $avatar;
}

/*
Options Page
*/
require_once(trailingslashit(dirname(__FILE__)) . "nextend-google-settings.php");
if (class_exists('NextendGoogleSettings')) {
    $nextendgooglesettings = new NextendGoogleSettings();
    if (isset($nextendgooglesettings)) {
        add_action('admin_menu', array(
            &$nextendgooglesettings,
            'NextendGoogle_Menu'
        ), 1);
    }
}
add_filter('plugin_action_links', 'new_google_plugin_action_links', 10, 2);

function new_google_plugin_action_links($links, $file) {

    if ($file != NEW_GOOGLE_LOGIN_PLUGIN_BASENAME) {
        return $links;
    }
    $settings_link = '<a href="' . esc_url(menu_page_url('nextend-google-connect', false)) . '">' . esc_html('Settings') . '</a>';
    array_unshift($links, $settings_link);

    return $links;
}

/* -----------------------------------------------------------------------------
Miscellaneous functions
----------------------------------------------------------------------------- */

function new_google_sign_button() {

    global $new_google_settings;

    return '<a href="' . esc_url(new_google_login_url() . (isset($_GET['redirect_to']) ? '&redirect=' . urlencode($_GET['redirect_to']) : '')) . '" rel="nofollow">' . $new_google_settings['google_login_button'] . '</a><br />';
}

function new_google_link_button() {

    global $new_google_settings;

    return '<a href="' . esc_url(new_google_login_url() . '&redirect=' . urlencode(new_google_curPageURL())) . '">' . $new_google_settings['google_link_button'] . '</a><br />';
}

function new_google_unlink_button() {

    global $new_google_settings;

    return '<a href="' . esc_url(new_google_login_url() . '&action=unlink&redirect=' . urlencode(new_google_curPageURL())) . '">' . $new_google_settings['google_unlink_button'] . '</a><br />';
}

function new_google_curPageURL() {

    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }

    return $pageURL;
}

function new_google_login_url() {

    return site_url('wp-login.php') . '?loginGoogle=1';
}

function new_google_redirect() {

    $redirect = get_site_transient(nextend_uniqid() . '_google_r');

    if (!$redirect || $redirect == '' || $redirect == new_google_login_url()) {
        if (isset($_GET['redirect'])) {
            $redirect = $_GET['redirect'];
        } else {
            $redirect = site_url();
        }
    }
    $redirect = wp_sanitize_redirect($redirect);
    $redirect = wp_validate_redirect($redirect, site_url());
    header('LOCATION: ' . $redirect);
    delete_site_transient(nextend_uniqid() . '_google_r');
    exit;
}

function new_google_edit_profile_redirect() {

    global $wp;
    if (isset($wp->query_vars['editProfileRedirect'])) {
        if (function_exists('bp_loggedin_user_domain')) {
            header('LOCATION: ' . bp_loggedin_user_domain() . 'profile/edit/group/1/');
        } else {
            header('LOCATION: ' . self_admin_url('profile.php'));
        }
        exit;
    }
}

add_action('parse_request', 'new_google_edit_profile_redirect');

function new_google_jquery() {

    wp_enqueue_script('jquery');
}

add_action('login_form_login', 'new_google_jquery');
add_action('login_form_register', 'new_google_jquery');

/*
Session notices used in the profile settings
*/

function new_google_admin_notice() {
    $user_info = wp_get_current_user();
    $notice    = get_site_transient($user_info->ID . '_new_google_admin_notice');
    if ($notice !== false) {
        echo '<div class="updated">
       <p>' . $notice . '</p>
    </div>';
        delete_site_transient($user_info->ID . '_new_google_admin_notice');
    }
}

add_action('admin_notices', 'new_google_admin_notice');
