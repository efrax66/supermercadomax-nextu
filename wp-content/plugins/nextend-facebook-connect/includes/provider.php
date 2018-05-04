<?php

require_once dirname(__FILE__) . '/provider-admin.php';
require_once dirname(__FILE__) . '/provider-dummy.php';
require_once dirname(__FILE__) . '/user.php';

abstract class NextendSocialProvider extends NextendSocialProviderDummy {

    protected $dbID;
    protected $optionKey;

    protected $enabled = false;

    /** @var NextendSocialAuth */
    protected $client;

    protected $authUserData = array();

    protected $requiredFields = array();

    protected $svg = '';

    protected $sync_fields = array();

    public function __construct($defaultSettings) {

        if (empty($this->dbID)) {
            $this->dbID = $this->id;
        }

        $this->optionKey = 'nsl_' . $this->id;

        do_action('nsl_provider_init', $this);

        $this->sync_fields = apply_filters('nsl_' . $this->getId() . '_sync_fields', $this->sync_fields);

        $extraSettings = apply_filters('nsl_' . $this->getId() . '_extra_settings', array(
            'ask_email'      => 'when-empty',
            'ask_user'       => 'never',
            'ask_password'   => 'never',
            'auto_link'      => 'email',
            'disabled_roles' => array(),
            'register_roles' => array(
                'default'
            )
        ));

        foreach ($this->getSyncFields() AS $field_name => $fieldData) {

            $extraSettings['sync_fields/fields/' . $field_name . '/enabled']  = 0;
            $extraSettings['sync_fields/fields/' . $field_name . '/meta_key'] = $field_name;
        }

        $this->settings = new NextendSocialLoginSettings($this->optionKey, array_merge(array(
            'settings_saved'        => '0',
            'tested'                => '0',
            'custom_default_button' => '',
            'custom_icon_button'    => '',
            'login_label'           => '',
            'link_label'            => '',
            'unlink_label'          => '',
            'user_prefix'           => '',
            'user_fallback'         => '',
            'oauth_redirect_url'    => '',

            'sync_fields/link'  => 0,
            'sync_fields/login' => 0
        ), $extraSettings, $defaultSettings));

        $this->admin = new NextendSocialProviderAdmin($this);

    }

    public function getOptionKey() {
        return $this->optionKey;
    }

    public function getRawDefaultButton() {
        return '<span class="nsl-button nsl-button-default nsl-button-' . $this->id . '" style="background-color:' . $this->color . ';">' . $this->svg . '<span>{{label}}</span></span>';
    }

    public function getRawIconButton() {
        return '<span class="nsl-button nsl-button-icon nsl-button-' . $this->id . '" style="background-color:' . $this->color . ';">' . $this->svg . '</span>';
    }

    public function getDefaultButton($label) {
        $button = $this->settings->get('custom_default_button');
        if (!empty($button)) {
            return str_replace('{{label}}', __($label, 'nextend-facebook-connect'), $button);
        }

        return str_replace('{{label}}', __($label, 'nextend-facebook-connect'), $this->getRawDefaultButton());
    }

    public function getIconButton() {
        $button = $this->settings->get('custom_icon_button');
        if (!empty($button)) {
            return $button;
        }

        return $this->getRawIconButton();
    }

    public function getLoginUrl() {
        $args = array('loginSocial' => $this->getId());

        if (isset($_REQUEST['interim-login'])) {
            $args['interim-login'] = 1;
        }

        return add_query_arg($args, site_url('wp-login.php'));
    }

    public function needPro() {
        return false;
    }

    public function enable() {
        $this->enabled = true;

        do_action('nsl_' . $this->getId() . '_enabled');

        return true;
    }

    public function isEnabled() {
        return $this->enabled;
    }

    public function isTested() {
        return !!$this->settings->get('tested');
    }

    public function checkOauthRedirectUrl() {
        $oauth_redirect_url = $this->settings->get('oauth_redirect_url');
        if (empty($oauth_redirect_url) || $oauth_redirect_url == $this->getLoginUrl()) {
            return true;
        }

        return false;
    }

    public function updateOauthRedirectUrl() {
        $this->settings->update(array(
            'oauth_redirect_url' => $this->getLoginUrl()
        ));
    }

    /**
     * @return array
     */
    public function getRequiredFields() {
        return $this->requiredFields;
    }

    public function getState() {
        foreach ($this->requiredFields AS $name => $label) {
            $value = $this->settings->get($name);
            if (empty($value)) {
                return 'not-configured';
            }
        }
        if (!$this->isTested()) {
            return 'not-tested';
        }

        if (!$this->isEnabled()) {
            return 'disabled';
        }

        return 'enabled';
    }

    public function connect() {
        try {
            $this->doAuthenticate();
        } catch (NSLContinuePageRenderException $e) {
            // This is not an error. We allow the page to continue the normal display flow and later we inject our things.
            // Used by Theme my login function where we override the shortcode and we display our email request.
        } catch (Exception $e) {
            $this->onError($e);
        }
    }

    /**
     * @return NextendSocialAuth
     */
    protected abstract function getClient();

    /**
     * @throws NSLContinuePageRenderException
     */
    protected function doAuthenticate() {

        if (!headers_sent()) {
            //All In One WP Security sets a LOCATION header, so we need to remove it to do a successful test.
            if (function_exists('header_remove')) {
                header_remove("LOCATION");
            } else {
                header('LOCATION:', true); //Under PHP 5.3
            }
        }

        if (!$this->isTest()) {
            add_action($this->id . '_login_action_before', array(
                $this,
                'liveConnectBefore'
            ));
            add_action($this->id . '_login_action_redirect', array(
                $this,
                'liveConnectRedirect'
            ));
            add_action($this->id . '_login_action_get_user_profile', array(
                $this,
                'liveConnectGetUserProfile'
            ));

            $interim_login = isset($_REQUEST['interim-login']);
            if ($interim_login) {
                \NSL\Persistent\Persistent::set($this->id . '_interim_login', 1);
            }

            $display = isset($_REQUEST['display']);
            if ($display && $_REQUEST['display'] == 'popup') {
                \NSL\Persistent\Persistent::set($this->id . '_display', 'popup');
            }

        } else {
            add_action($this->id . '_login_action_get_user_profile', array(
                $this,
                'testConnectGetUserProfile'
            ));
        }


        do_action($this->id . '_login_action_before', $this);

        $client = $this->getClient();

        $accessTokenData = $this->getAnonymousAccessToken();

        $client->checkError();

        do_action($this->id . '_login_action_redirect', $this);

        if (!$accessTokenData && !$client->hasAuthenticateData()) {

            header('LOCATION: ' . $client->createAuthUrl());
            exit;

        } else {

            if (!$accessTokenData) {

                $accessTokenData = $client->authenticate();

                $accessTokenData = $this->requestLongLivedToken($accessTokenData);

                $this->setAnonymousAccessToken($accessTokenData);
            } else {
                $client->setAccessTokenData($accessTokenData);
            }
            if (\NSL\Persistent\Persistent::get($this->id . '_display') == 'popup') {
                \NSL\Persistent\Persistent::delete($this->id . '_display');
                ?>
                <!doctype html>
                <html lang=en>
                <head>
                    <meta charset=utf-8>
                    <title><?php _e('Authentication successful', 'nextend-facebook-connect'); ?></title>
                    <script type="text/javascript">
						try {
                            if (window.opener !== null) {
                                window.opener.location = <?php echo wp_json_encode($this->getLoginUrl()); ?>;
                                window.close();
                            } else {
                                window.location.reload(true);
                            }
                        }
                        catch (e) {
                            window.location.reload(true);
                        }
                    </script>
                </head>
                <body><a href="<?php echo esc_url($this->getLoginUrl()); ?>"><?php echo 'Continue...'; ?></a></body>
                </html>
                <?php
                exit;
            }

            $this->authUserData = $this->getCurrentUserInfo();

            do_action($this->id . '_login_action_get_user_profile', $accessTokenData);
        }
    }

    public function liveConnectGetUserProfile($access_token) {

        $socialUser = new NextendSocialUser($this, $access_token);
        $socialUser->liveConnectGetUserProfile();

        $this->deleteLoginPersistentData();
        $this->redirectToLastLocationOther();
    }

    /**
     * @param $user_id
     * @param $providerIdentifier
     *
     * @return bool
     */
    public function linkUserToProviderIdentifier($user_id, $providerIdentifier) {
        /** @var $wpdb WPDB */
        global $wpdb;

        $connectedProviderID = $this->getProviderIdentifierByUserID($user_id);

        if ($connectedProviderID !== null) {
            if ($connectedProviderID == $providerIdentifier) {
                // This provider already linked to this user
                return true;
            }

            // User already have this provider attached to his account with different provider id.
            return false;
        }

        $wpdb->insert($wpdb->prefix . 'social_users', array(
            'ID'         => $user_id,
            'type'       => $this->dbID,
            'identifier' => $providerIdentifier
        ), array(
            '%d',
            '%s',
            '%s'
        ));

        do_action('nsl_' . $this->getId() . '_link_user', $user_id, $this->getId());

        return true;
    }

    public function getUserIDByProviderIdentifier($identifier) {
        /** @var $wpdb WPDB */
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare('SELECT ID FROM `' . $wpdb->prefix . 'social_users` WHERE type = %s AND identifier = %s', array(
            $this->dbID,
            $identifier
        )));
    }

    protected function getProviderIdentifierByUserID($user_id) {
        /** @var $wpdb WPDB */
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare('SELECT identifier FROM `' . $wpdb->prefix . 'social_users` WHERE type = %s AND ID = %s', array(
            $this->dbID,
            $user_id
        )));
    }

    public function removeConnectionByUserID($user_id) {
        /** @var $wpdb WPDB */
        global $wpdb;

        $wpdb->query($wpdb->prepare('DELETE FROM `' . $wpdb->prefix . 'social_users` WHERE type = %s AND ID = %d', array(
            $this->dbID,
            $user_id
        )));
    }

    protected function unlinkUser() {
        $user_info = wp_get_current_user();
        if ($user_info->ID) {
            $this->removeConnectionByUserID($user_info->ID);

            return true;
        }

        return false;
    }

    public function isCurrentUserConnected() {
        /** @var $wpdb WPDB */
        global $wpdb;

        $current_user = wp_get_current_user();
        $ID           = $wpdb->get_var($wpdb->prepare('SELECT identifier FROM `' . $wpdb->prefix . 'social_users` WHERE type LIKE %s AND ID = %d', array(
            $this->dbID,
            $current_user->ID
        )));
        if ($ID === null) {
            return false;
        }

        return $ID;
    }

    public function isUserConnected($user_id) {
        /** @var $wpdb WPDB */
        global $wpdb;

        $ID = $wpdb->get_var($wpdb->prepare('SELECT identifier FROM `' . $wpdb->prefix . 'social_users` WHERE type LIKE %s AND ID = %d', array(
            $this->dbID,
            $user_id
        )));
        if ($ID === null) {
            return false;
        }

        return $ID;
    }

    public function findUserByAccessToken($access_token) {
        return $this->getUserIDByProviderIdentifier($this->findSocialIDByAccessToken($access_token));
    }

    public function findSocialIDByAccessToken($access_token) {
        $client = $this->getClient();
        $client->setAccessTokenData($access_token);
        $this->authUserData = $this->getCurrentUserInfo();

        return $this->getAuthUserData('id');
    }

    public function getConnectButton($buttonStyle = 'default', $redirectTo = null, $trackerData = false) {
        $arg = array();
        if (!empty($redirectTo)) {
            $arg['redirect'] = urlencode($redirectTo);
        } else if (!empty($_GET['redirect_to'])) {
            $arg['redirect'] = urlencode($_GET['redirect_to']);
        }

        if ($trackerData !== false) {
            $arg['trackerdata']      = urlencode($trackerData);
            $arg['trackerdata_hash'] = urlencode(wp_hash($trackerData));

        }

        switch ($buttonStyle) {
            case 'icon':

                $button = $this->getIconButton();
                break;
            default:

                $button = $this->getDefaultButton($this->settings->get('login_label'));
                break;
        }

        return '<a href="' . esc_url(add_query_arg($arg, $this->getLoginUrl())) . '" rel="nofollow" aria-label="' . esc_attr__($this->settings->get('login_label')) . '" data-plugin="nsl" data-action="connect" data-provider="' . esc_attr($this->getId()) . '" data-popupwidth="' . $this->getPopupWidth() . '" data-popupheight="' . $this->getPopupHeight() . '">' . $button . '</a>';
    }

    public function getLinkButton() {

        $args = array(
            'action' => 'link'
        );

        $redirect = NextendSocialLogin::getCurrentPageURL();
        if ($redirect !== false) {
            $args['redirect'] = urlencode($redirect);
        }

        return '<a href="' . esc_url(add_query_arg($args, $this->getLoginUrl())) . '" style="text-decoration:none;display:inline-block;box-shadow:none;" data-plugin="nsl" data-action="link" data-provider="' . esc_attr($this->getId()) . '" data-popupwidth="' . $this->getPopupWidth() . '" data-popupheight="' . $this->getPopupHeight() . '" aria-label="' . esc_attr__($this->settings->get('link_label')) . '">' . $this->getDefaultButton($this->settings->get('link_label')) . '</a>';
    }

    public function getUnLinkButton() {

        $args = array(
            'action' => 'unlink'
        );

        $redirect = NextendSocialLogin::getCurrentPageURL();
        if ($redirect !== false) {
            $args['redirect'] = urlencode($redirect);
        }

        return '<a href="' . esc_url(add_query_arg($args, $this->getLoginUrl())) . '" style="text-decoration:none;display:inline-block;box-shadow:none;" data-plugin="nsl" data-action="unlink" data-provider="' . esc_attr($this->getId()) . '" aria-label="' . esc_attr__($this->settings->get('unlink_label')) . '">' . $this->getDefaultButton($this->settings->get('unlink_label')) . '</a>';
    }

    public function redirectToLoginForm() {
        self::redirect(__('Authentication error', 'nextend-facebook-connect'), site_url('wp-login.php'));
    }

    public function liveConnectBefore() {

        if (is_user_logged_in() && $this->isCurrentUserConnected()) {

            if (isset($_GET['action']) && $_GET['action'] == 'unlink') {
                if ($this->unlinkUser()) {
                    \NSL\Notices::addSuccess(__('Unlink successful.', 'nextend-facebook-connect'));
                }
            }

            $this->redirectToLastLocationOther();
            exit;
        }

        if (isset($_GET['action']) && $_GET['action'] == 'link') {
            \NSL\Persistent\Persistent::set($this->id . '_action', 'link');
        }

        if (is_user_logged_in() && \NSL\Persistent\Persistent::get($this->id . '_action') != 'link') {
            $this->deleteLoginPersistentData();

            $this->redirectToLastLocationOther();
            exit;
        }
    }

    public function liveConnectRedirect() {
        if (!empty($_GET['trackerdata']) && !empty($_GET['trackerdata_hash'])) {
            if (wp_hash($_GET['trackerdata']) === $_GET['trackerdata_hash']) {
                \NSL\Persistent\Persistent::set('trackerdata', $_GET['trackerdata']);
            }
        }

        if (!is_user_logged_in()) {
            $redirectToLogin = NextendSocialLogin::$settings->get('redirect');
            if (!empty($redirectToLogin)) {
                $_GET['redirect'] = $redirectToLogin;
            }
        }

        if (!empty($_GET['redirect'])) {
            \NSL\Persistent\Persistent::set('redirect', $_GET['redirect']);
        }
    }

    public function redirectToLastLocation() {

        if (\NSL\Persistent\Persistent::get($this->id . '_interim_login') == 1) {
            $this->deleteLoginPersistentData();

            $url = add_query_arg('interim_login', 'nsl', site_url('wp-login.php', 'login'));
            ?>
            <!doctype html>
            <html lang=en>
            <head>
                <meta charset=utf-8>
                <title><?php _e('Authentication successful', 'nextend-facebook-connect'); ?></title>
                <script type="text/javascript">
					window.location = <?php echo wp_json_encode($url); ?>;
                </script>
                <meta http-equiv="refresh" content="0;<?php echo esc_attr($url); ?>">
            </head>
            </html>
            <?php
            exit;
        }

        self::redirect(__('Authentication successful', 'nextend-facebook-connect'), $this->getLastLocationRedirectTo());
    }

    protected function redirectToLastLocationOther() {
        $this->redirectToLastLocation();
    }

    protected function validateRedirect($location) {
        $location = wp_sanitize_redirect($location);

        return wp_validate_redirect($location, apply_filters('wp_safe_redirect_fallback', admin_url(), 302));
    }

    protected function getLastLocationRedirectTo() {
        $fixedRedirect = '';

        if (strpos(NextendSocialLogin::$currentWPLoginAction, 'register') === 0) {

            $fixedRedirect = NextendSocialLogin::$settings->get('redirect_reg');

            $fixedRedirect = apply_filters($this->id . '_register_redirect_url', $fixedRedirect, $this);

        } else if (NextendSocialLogin::$currentWPLoginAction == 'login') {

            $fixedRedirect = NextendSocialLogin::$settings->get('redirect');
            $fixedRedirect = apply_filters($this->id . '_login_redirect_url', $fixedRedirect, $this);

        }

        if (!empty($fixedRedirect)) {
            $redirect_to = $fixedRedirect;

            \NSL\Persistent\Persistent::delete('redirect');
        } else {
            $requested_redirect_to = \NSL\Persistent\Persistent::get('redirect');

            if (empty($requested_redirect_to) || !NextendSocialLogin::isAllowedRedirectUrl($requested_redirect_to)) {
                if (!empty($_GET['redirect']) && NextendSocialLogin::isAllowedRedirectUrl($_GET['redirect'])) {
                    $requested_redirect_to = $_GET['redirect'];
                } else {
                    $requested_redirect_to = '';
                }
            }

            if (empty($requested_redirect_to)) {
                $redirect_to = site_url();
            } else {
                $redirect_to = $requested_redirect_to;
            }
            $redirect_to = wp_sanitize_redirect($redirect_to);
            $redirect_to = wp_validate_redirect($redirect_to, site_url());

            \NSL\Persistent\Persistent::delete('redirect');

            $redirect_to = $this->validateRedirect($redirect_to);
        }


        if ($redirect_to == '' || $redirect_to == $this->getLoginUrl()) {
            $redirect_to = site_url();
        }

        return apply_filters('nsl_' . $this->getId() . 'last_location_redirect', $redirect_to, $requested_redirect_to);
    }

    /**
     * @param $user_id
     * @param $provider     NextendSocialProvider
     * @param $access_token string
     */
    public function syncProfile($user_id, $provider, $access_token) {
    }

    public function isTest() {
        if (is_user_logged_in() && current_user_can('manage_options')) {
            if (isset($_REQUEST['test'])) {
                \NSL\Persistent\Persistent::set('test', 1);

                return true;
            } else if (\NSL\Persistent\Persistent::get('test') == 1) {
                return true;
            }
        }

        return false;
    }

    public function testConnectGetUserProfile() {

        $this->deleteLoginPersistentData();

        $this->settings->update(array(
            'tested'             => 1,
            'oauth_redirect_url' => $this->getLoginUrl()
        ));

        \NSL\Notices::addSuccess(__('The test was successful', 'nextend-facebook-connect'));

        ?>
        <!doctype html>
        <html lang=en>
        <head>
            <meta charset=utf-8>
            <title><?php _e('The test was successful', 'nextend-facebook-connect'); ?></title>
            <script type="text/javascript">
				window.opener.location.reload(true);
                window.close();
            </script>
        </head>
        </html>
        <?php
        exit;
    }

    protected function setAnonymousAccessToken($accessToken) {
        \NSL\Persistent\Persistent::set($this->id . '_at', $accessToken);
    }

    protected function getAnonymousAccessToken() {
        return \NSL\Persistent\Persistent::get($this->id . '_at');
    }

    public function deleteLoginPersistentData() {
        \NSL\Persistent\Persistent::delete($this->id . '_at');
        \NSL\Persistent\Persistent::delete($this->id . '_interim_login');
        \NSL\Persistent\Persistent::delete($this->id . '_display');
        \NSL\Persistent\Persistent::delete($this->id . '_action');
        \NSL\Persistent\Persistent::delete('test');
    }

    /**
     * @param $e Exception
     */
    protected function onError($e) {
        if (NextendSocialLogin::$settings->get('debug') == 1 || $this->isTest()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $e->getMessage() . "\n";
        } else {
            //@TODO we might need to make difference between user cancelled auth and error and redirect the user based on that.
            $url = $this->getLastLocationRedirectTo();
            ?>
            <!doctype html>
            <html lang=en>
            <head>
                <meta charset=utf-8>
                <title><?php echo __('Authentication failed', 'nextend-facebook-connect'); ?></title>
                <script type="text/javascript">
					try {
                        if (window.opener !== null) {
                            window.close();
                        }
                    }
                    catch (e) {
                    }
                    window.location = <?php echo wp_json_encode($url); ?>;
                </script>
                <meta http-equiv="refresh" content="0;<?php echo esc_attr($url); ?>">
            </head>
            <body>
            </body>
            </html>
            <?php
        }
        $this->deleteLoginPersistentData();
        exit;
    }

    protected function saveUserData($user_id, $key, $data) {
        update_user_meta($user_id, $this->id . '_' . $key, $data);
    }

    protected function getUserData($user_id, $key) {
        return get_user_meta($user_id, $this->id . '_' . $key, true);
    }

    public function getAccessToken($user_id) {
        return $this->getUserData($user_id, 'access_token');
    }

    /**
     * @deprecated
     *
     * @param $user_id
     *
     * @return bool
     */
    public function getAvatar($user_id) {

        return false;
    }

    /**
     * @return array
     */
    protected function getCurrentUserInfo() {
        return array();
    }

    protected function requestLongLivedToken($accessTokenData) {
        return $accessTokenData;
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function getAuthUserData($key) {
        return '';
    }

    public static function redirect($title, $url) {
        ?>
        <!doctype html>
        <html lang=en>
        <head>
            <meta charset=utf-8>
            <title><?php echo $title; ?></title>
            <script type="text/javascript">
				try {
                    if (window.opener !== null) {
                        window.opener.location = <?php echo wp_json_encode($url); ?>;
                        window.close();
                    }
                }
                catch (e) {
                }
                window.location = <?php echo wp_json_encode($url); ?>;
            </script>
            <meta http-equiv="refresh" content="0;<?php echo esc_attr($url); ?>">
        </head>
        <body>
        </body>
        </html>
        <?php
        exit;
    }

    public function getSyncFields() {
        return $this->sync_fields;
    }

    public function hasSyncFields() {
        return !empty($this->sync_fields);
    }

    public function validateSettings($newData, $postedData) {

        return $newData;
    }

    protected function needUpdateAvatar($user_id) {
        return apply_filters('nsl_avatar_store', NextendSocialLogin::$settings->get('avatar_store'), $user_id, $this);
    }

    protected function updateAvatar($user_id, $url) {
        do_action('nsl_update_avatar', $this, $user_id, $url);
    }
}