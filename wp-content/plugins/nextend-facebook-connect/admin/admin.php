<?php
define('NSL_ADMIN_PATH', __FILE__);

require_once dirname(__FILE__) . '/upgrader.php';
NextendSocialUpgrader::init();

class NextendSocialLoginAdmin {

    public static function init() {
        add_action('admin_menu', 'NextendSocialLoginAdmin::admin_menu', 1);
        add_action('admin_init', 'NextendSocialLoginAdmin::admin_init');

        add_filter('plugin_action_links', 'NextendSocialLoginAdmin::plugin_action_links', 10, 2);

        add_filter('nsl_update_settings_validate_nextend_social_login', 'NextendSocialLoginAdmin::validateSettings', 10, 2);

        add_action('wp_ajax_nsl_save_review_state', 'NextendSocialLoginAdmin::save_review_state');
    }

    public static function getAdminUrl($view = 'providers') {

        return add_query_arg(array(
            'page' => 'nextend-social-login',
            'view' => $view
        ), admin_url('options-general.php'));
    }

    public static function getAdminSettingsUrl($subview = 'general') {

        return add_query_arg(array(
            'page'    => 'nextend-social-login',
            'view'    => 'global-settings',
            'subview' => $subview
        ), admin_url('options-general.php'));
    }

    public static function admin_menu() {
        $menu = add_options_page('Nextend Social Login', 'Nextend Social Login', 'manage_options', 'nextend-social-login', array(
            'NextendSocialLoginAdmin',
            'display_admin'
        ));

        add_action('admin_print_styles-' . $menu, 'NextendSocialLoginAdmin::admin_css');
    }

    public static function admin_css() {
        wp_enqueue_style('nsl-admin-stylesheet', plugins_url('/style.css?nsl-ver=' . urlencode(NextendSocialLogin::$version), NSL_ADMIN_PATH));
    }

    public static function display_admin() {
        $view = !empty($_REQUEST['view']) ? $_REQUEST['view'] : '';

        if (substr($view, 0, 9) == 'provider-') {
            $providerID = substr($view, 9);
            if (isset(NextendSocialLogin::$providers[$providerID])) {
                self::display_admin_area('provider', $providerID);

                return;
            }
        }
        switch ($view) {
            case 'fix-redirect-uri':
                self::display_admin_area('fix-redirect-uri');
                break;
            case 'global-settings':
                self::display_admin_area('global-settings');
                break;
            case 'pro-addon':
                self::display_admin_area('pro-addon');
                break;
            case 'install-pro':
                if (check_admin_referer('nextend-social-login')) {
                    self::display_admin_area('install-pro');
                } else {
                    self::display_admin_area('providers');
                }
                break;
            default:
                self::display_admin_area('providers');
                break;
        }
    }

    /**
     * @param string $view
     * @param string $currentProvider
     */
    private static function display_admin_area($view, $currentProvider = '') {
        if (empty($currentProvider)) {
            include(dirname(__FILE__) . '/templates/header.php');
            include(dirname(__FILE__) . '/templates/menu.php');

            \NSL\Notices::displayNotices();

            /** @var string $view */
            include(dirname(__FILE__) . '/templates/' . $view . '.php');
            include(dirname(__FILE__) . '/templates/footer.php');
        } else {
            include(dirname(__FILE__) . '/templates/' . $view . '.php');
        }
    }

    public static function renderProSettings() {
        include(dirname(__FILE__) . '/templates/global-settings-pro.php');
    }

    public static function admin_init() {
        if (current_user_can('manage_options')) {
            if (!isset($_GET['page']) || $_GET['page'] != 'nextend-social-login' || !isset($_GET['view']) || $_GET['view'] != 'fix-redirect-uri') {
                add_action('admin_notices', 'NextendSocialLoginAdmin::show_oauth_uri_notice');
            }

            if (!self::isPro() && NextendSocialLogin::$settings->get('woocommerce_dismissed') == 0 && class_exists('woocommerce', false) && count(NextendSocialLogin::$enabledProviders)) {
                add_action('admin_notices', 'NextendSocialLoginAdmin::show_woocommerce_notice');
            }
        }

        if (isset($_GET['page']) && $_GET['page'] == 'nextend-social-login') {
            if (!empty($_GET['view'])) {
                switch ($_GET['view']) {
                    case 'enable':
                    case 'sub-enable':
                        if (!empty($_GET['provider'])) {
                            if (check_admin_referer('nextend-social-login_enable_' . $_GET['provider'])) {
                                NextendSocialLogin::enableProvider($_GET['provider']);
                            }
                            if ($_GET['view'] == 'sub-enable') {
                                wp_redirect(NextendSocialLogin::$providers[$_GET['provider']]->getAdmin()
                                                                                             ->getUrl('settings'));
                                exit;
                            }

                            wp_redirect(self::getAdminUrl());
                            exit;
                        }
                        break;
                    case 'disable':
                    case 'sub-disable':
                        if (!empty($_GET['provider'])) {
                            if (check_admin_referer('nextend-social-login_disable_' . $_GET['provider'])) {
                                NextendSocialLogin::disableProvider($_GET['provider']);
                            }
                            if ($_GET['view'] == 'sub-disable') {
                                wp_redirect(NextendSocialLogin::$providers[$_GET['provider']]->getAdmin()
                                                                                             ->getUrl('settings'));
                                exit;
                            }

                            wp_redirect(self::getAdminUrl());
                            exit;
                        }
                        break;
                    case 'update_oauth_redirect_url':
                        if (check_admin_referer('nextend-social-login_update_oauth_redirect_url')) {
                            foreach (NextendSocialLogin::$enabledProviders AS $provider) {
                                $provider->updateOauthRedirectUrl();
                            }
                        }

                        wp_redirect(self::getAdminUrl());
                        exit;

                    case 'dismiss_woocommerce':
                        if (check_admin_referer('nsl_dismiss_woocommerce')) {
                            NextendSocialLogin::$settings->update(array(
                                'woocommerce_dismissed' => 1
                            ));

                            if (!empty($_REQUEST['redirect_to'])) {
                                wp_safe_redirect($_REQUEST['redirect_to']);
                                exit;
                            }
                        }

                        wp_redirect(self::getAdminUrl());
                        break;
                }
            }
        }
        add_action('admin_post_nextend-social-login', 'NextendSocialLoginAdmin::save_form_data');
        add_action('wp_ajax_nextend-social-login', 'NextendSocialLoginAdmin::ajax_save_form_data');


        add_action('admin_enqueue_scripts', 'NextendSocialLoginAdmin::admin_enqueue_scripts');

        if (!function_exists('json_decode')) {
            add_settings_error('nextend-social', 'settings_updated', printf(__('%s needs json_decode function.', 'nextend-facebook-connect'), 'Nextend Social Login') . ' ' . __('Please contact your server administrator and ask for solution!', 'nextend-facebook-connect'), 'error');
        }

        add_action('show_user_profile', array(
            'NextendSocialLoginAdmin',
            'showUserFields'
        ));
        add_action('edit_user_profile', array(
            'NextendSocialLoginAdmin',
            'showUserFields'
        ));
    }

    public static function save_form_data() {
        if (current_user_can('manage_options') && check_admin_referer('nextend-social-login')) {
            foreach ($_POST AS $k => $v) {
                if (is_string($v)) {
                    $_POST[$k] = stripslashes($v);
                }
            }

            $view = !empty($_REQUEST['view']) ? $_REQUEST['view'] : '';

            if ($view == 'global-settings') {

                NextendSocialLogin::$settings->update($_POST);

                \NSL\Notices::addSuccess(__('Settings saved.'));

                wp_redirect(self::getAdminSettingsUrl(!empty($_REQUEST['subview']) ? $_REQUEST['subview'] : ''));
                exit;
            } else if ($view == 'pro-addon') {

                NextendSocialLogin::$settings->update($_POST);

                if (NextendSocialLogin::$settings->get('license_key_ok') == '1') {
                    \NSL\Notices::addSuccess(__('The authorization was successful', 'nextend-facebook-connect'));
                }

                wp_redirect(self::getAdminUrl($view));
                exit;
            } else if ($view == 'pro-addon-deauthorize') {

                NextendSocialLogin::$settings->update(array(
                    'license_key' => ''
                ));

                \NSL\Notices::addSuccess(__('Deauthorize completed.', 'nextend-facebook-connect'));

                wp_redirect(self::getAdminUrl('pro-addon'));
                exit;

            } else if ($view == 'import') {
                $provider = isset($_GET['provider']) ? $_GET['provider'] : '';
                if (!empty($provider) && isset(NextendSocialLogin::$providers[$provider]) && NextendSocialLogin::$providers[$provider]->getState() == 'legacy') {
                    NextendSocialLogin::$providers[$provider]->import();

                    wp_redirect(NextendSocialLogin::$providers[$provider]->getAdmin()
                                                                         ->getUrl('settings'));
                    exit;
                }

                wp_redirect(NextendSocialLoginAdmin::getAdminUrl());
                exit;
            } else if (substr($view, 0, 9) == 'provider-') {
                $providerID = substr($view, 9);
                if (isset(NextendSocialLogin::$providers[$providerID])) {
                    NextendSocialLogin::$providers[$providerID]->settings->update($_POST);

                    \NSL\Notices::addSuccess(__('Settings saved.'));
                    wp_redirect(NextendSocialLogin::$providers[$providerID]->getAdmin()
                                                                           ->getUrl(isset($_POST['subview']) ? $_POST['subview'] : ''));
                    exit;
                }
            }
        }

        wp_redirect(self::getAdminUrl());
        exit;
    }

    public static function ajax_save_form_data() {
        check_ajax_referer('nextend-social-login');
        if (current_user_can('manage_options')) {
            $view = !empty($_POST['view']) ? $_POST['view'] : '';
            if ($view === 'orderProviders') {
                if (!empty($_POST['ordering'])) {
                    NextendSocialLogin::$settings->update(array(
                        'ordering' => $_POST['ordering']
                    ));
                }
            }
        }
    }

    public static function validateSettings($newData, $postedData) {

        if (isset($postedData['redirect'])) {
            if (isset($postedData['custom_redirect_enabled']) && $postedData['custom_redirect_enabled'] == '1') {
                $newData['redirect'] = trim(sanitize_text_field($postedData['redirect']));
            } else {
                $newData['redirect'] = '';
            }
        }

        if (isset($postedData['redirect_reg'])) {
            if (isset($postedData['custom_redirect_reg_enabled']) && $postedData['custom_redirect_reg_enabled'] == '1') {
                $newData['redirect_reg'] = trim(sanitize_text_field($postedData['redirect_reg']));
            } else {
                $newData['redirect_reg'] = '';
            }
        }

        foreach ($postedData as $key => $value) {
            switch ($key) {
                case 'debug':
                case 'avatar_store':
                    if ($value == 1) {
                        $newData[$key] = 1;
                    } else {
                        $newData[$key] = 0;
                    }
                    break;
                case 'show_login_form':
                case 'show_registration_form':
                case 'show_embedded_login_form':
                    $newData[$key] = sanitize_text_field($value);
                    break;
                case 'enabled':
                    if (is_array($value)) {
                        $newData[$key] = $value;
                    }
                    break;
                case 'ordering':
                    if (is_array($value)) {
                        $newData[$key] = $value;
                    }
                    break;
                case 'license_key':
                    $value = trim(sanitize_text_field($value));
                    if ($value != NextendSocialLogin::$settings->get('license_key') || NextendSocialLogin::$settings->get('license_key_ok') == '0') {
                        $newData['license_key_ok'] = '0';

                        if (!empty($value)) {
                            try {
                                $response = self::apiCall('test-license', array('license_key' => $value));
                                if ($response === 'OK') {
                                    $newData[$key]             = $value;
                                    $newData['license_key_ok'] = '1';
                                }
                            } catch (Exception $e) {
                                \NSL\Notices::addError($e->getMessage());
                            }
                        }
                    }
                    break;
                case 'review_state':
                case 'woocommerce_dismissed':
                    $newData[$key] = intval($value);
                    break;
            }
        }

        return $newData;
    }

    public static function plugin_action_links($links, $file) {

        if ($file != NSL_PLUGIN_BASENAME) {
            return $links;
        }
        $settings_link = '<a href="' . esc_url(menu_page_url('nextend-social-login', false)) . '">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    public static function admin_enqueue_scripts() {
        if ('settings_page_nextend-social-login' === get_current_screen()->id) {

            // Since WordPress 4.9
            if (function_exists('wp_enqueue_code_editor')) {
                // Enqueue code editor and settings for manipulating HTML.
                $settings = wp_enqueue_code_editor(array('type' => 'text/html'));

                // Bail if user disabled CodeMirror.
                if (false === $settings) {
                    return;
                }

                wp_add_inline_script('code-editor', sprintf('jQuery( function() { var settings = %s; jQuery(".nextend-html-editor").each(function(i, el){wp.codeEditor.initialize( el, settings);}); } );', wp_json_encode($settings)));

                $settings['codemirror']['readOnly'] = 'nocursor';

                wp_add_inline_script('code-editor', sprintf('jQuery( function() { var settings = %s; jQuery(".nextend-html-editor-readonly").each(function(i, el){wp.codeEditor.initialize( el, settings);}); } );', wp_json_encode($settings)));
            }

            if (isset($_GET['view']) && $_GET['view'] == 'pro-addon') {
                wp_enqueue_script('plugin-install');
                wp_enqueue_script('updates');
            }
        }
    }

    private static $endpoint = 'https://secure.nextendweb.com/wp-json/nextend-api/v2/';

    public static function getEndpoint($action = '') {
        return self::$endpoint . 'product/nsl/' . urlencode($action);
    }

    /**
     * @param       $action
     * @param array $args
     *
     * @return bool|mixed
     * @throws Exception
     */
    public static function apiCall($action, $args = array()) {

        $http_args = array(
            'timeout'    => 15,
            'user-agent' => 'WordPress',
            'body'       => array_merge(array(
                'platform'    => 'wordpress',
                'domain'      => parse_url(site_url(), PHP_URL_HOST),
                'license_key' => NextendSocialLogin::$settings->get('license_key')
            ), $args)
        );

        $request = wp_remote_get(self::getEndpoint($action), $http_args);

        if (is_wp_error($request)) {

            throw new Exception($request->get_error_message());
        } else if (wp_remote_retrieve_response_code($request) !== 200) {

            $response = json_decode(wp_remote_retrieve_body($request), true);
            if (isset($response['message'])) {
                $message = 'Nextend Social Login Pro Addon: ' . $response['message'];

                \NSL\Notices::addError($message);

                return new WP_Error('error', $message);
            }

            throw new Exception(sprintf(__('Unexpected response: %s', 'nextend-facebook-connect'), wp_remote_retrieve_body($request)));
        }

        $response = json_decode(wp_remote_retrieve_body($request), true);

        return $response;
    }

    public static function showProBox() {
        if (!self::isPro()) {
            include(dirname(__FILE__) . '/templates/pro.php');
        }
    }

    public static function getProState() {
        if (NextendSocialLogin::$settings->get('license_key_ok') == '1') {
            if (self::isPro()) {
                return 'activated';
            } else if (!current_user_can('install_plugins')) {
                return 'no-capability';
            } else {
                if (file_exists(WP_PLUGIN_DIR . '/nextend-social-login-pro/nextend-social-login-pro.php')) {
                    return 'installed';
                } else {
                    return 'not-installed';
                }
            }
        }

        return 'no-license';
    }

    public static function trackUrl($url, $source) {
        return add_query_arg(array(
            'utm_campaign' => 'nsl',
            'utm_source'   => urlencode($source),
            'utm_medium'   => 'nsl-wordpress-' . (apply_filters('nsl-pro', false) ? 'pro' : 'free')
        ), $url);
    }

    public static function save_review_state() {
        check_ajax_referer('nsl_save_review_state');
        if (isset($_POST['review_state'])) {
            $review_state = intval($_POST['review_state']);
            if ($review_state > 0) {

                NextendSocialLogin::$settings->update(array(
                    'review_state' => $review_state
                ));
            }
        }
        wp_die();
    }

    public static function show_oauth_uri_notice() {
        foreach (NextendSocialLogin::$enabledProviders AS $provider) {
            if (!$provider->checkOauthRedirectUrl()) {
                echo '<div class="error">
                        <p>' . sprintf(__('%s detected that your login url changed. You must update the Oauth redirect URIs in the related social applications.', 'nextend-facebook-connect'), '<b>Nextend Social Login</b>') . '</p>
                        <p class="submit"><a href="' . NextendSocialLoginAdmin::getAdminUrl('fix-redirect-uri') . '" class="button button-primary">' . __('Fix Error', 'nextend-facebook-connect') . ' - ' . __('Oauth Redirect URI', 'nextend-facebook-connect') . '</a></p>
                    </div>';
                break;
            }
        }
    }

    public static function show_woocommerce_notice() {
        $dismissUrl = wp_nonce_url(add_query_arg(array('redirect_to' => NextendSocialLogin::getCurrentPageURL()), NextendSocialLoginAdmin::getAdminUrl('dismiss_woocommerce')), 'nsl_dismiss_woocommerce');
        echo '<div class="notice notice-info">
            <p>' . sprintf(__('%1$s detected that %2$s installed on your site. You need the Pro Addon to display Social Login buttons in %2$s login form!', 'nextend-facebook-connect'), '<b>Nextend Social Login</b>', '<b>WooCommerce</b>') . '</p>
            <p><a href="' . NextendSocialLoginAdmin::trackUrl('https://nextendweb.com/social-login/', 'woocommerce-notice') . '" target="_blank" onclick="window.location.href=\'' . esc_url($dismissUrl) . '\';" class="button button-primary">' . __('Dismiss and check Pro Addon', 'nextend-facebook-connect') . '</a> <a href="' . esc_url($dismissUrl) . '" class="button button-secondary">' . __('Dismiss', 'nextend-facebook-connect') . '</a></p>
        </div>';
    }

    public static function isPro() {
        return apply_filters('nsl-pro', false);
    }

    public static function showUserFields($user) {
        include(dirname(__FILE__) . '/EditUser.php');
    }
}