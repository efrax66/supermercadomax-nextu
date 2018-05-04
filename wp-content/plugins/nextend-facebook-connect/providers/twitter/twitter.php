<?php

class NextendSocialProviderTwitter extends NextendSocialProvider {

    /** @var NextendSocialProviderTwitterClient */
    protected $client;

    protected $color = '#4ab3f4';

    protected $svg = '<svg xmlns="http://www.w3.org/2000/svg"><path fill="#fff" d="M16.327 3.007A5.07 5.07 0 0 1 20.22 4.53a8.207 8.207 0 0 0 2.52-.84l.612-.324a4.78 4.78 0 0 1-1.597 2.268 2.356 2.356 0 0 1-.54.384v.012A9.545 9.545 0 0 0 24 5.287v.012a7.766 7.766 0 0 1-1.67 1.884l-.768.612a13.896 13.896 0 0 1-9.874 13.848c-2.269.635-4.655.73-6.967.276a16.56 16.56 0 0 1-2.895-.936 10.25 10.25 0 0 1-1.394-.708L0 20.023a8.44 8.44 0 0 0 1.573.06c.48-.084.96-.06 1.405-.156a10.127 10.127 0 0 0 2.956-1.056 5.41 5.41 0 0 0 1.333-.852 4.44 4.44 0 0 1-1.465-.264 4.9 4.9 0 0 1-3.12-3.108c.73.134 1.482.1 2.198-.096a3.457 3.457 0 0 1-1.609-.636A4.651 4.651 0 0 1 .953 9.763c.168.072.336.156.504.24.334.127.68.22 1.033.276.216.074.447.095.673.06H3.14c-.248-.288-.653-.468-.901-.78a4.91 4.91 0 0 1-1.105-4.404 5.62 5.62 0 0 1 .528-1.26c.008 0 .017.012.024.012.13.182.28.351.445.504a8.88 8.88 0 0 0 1.465 1.38 14.43 14.43 0 0 0 6.018 2.868 9.065 9.065 0 0 0 2.21.288 4.448 4.448 0 0 1 .025-2.28 4.771 4.771 0 0 1 2.786-3.252 5.9 5.9 0 0 1 1.093-.336l.6-.072z"/></svg>';

    public function __construct() {
        $this->id    = 'twitter';
        $this->label = 'Twitter';

        $this->path = dirname(__FILE__);

        $this->requiredFields = array(
            'consumer_key'    => 'Consumer Key',
            'consumer_secret' => 'Consumer Secret'
        );

        parent::__construct(array(
            'consumer_key'    => '',
            'consumer_secret' => '',
            'login_label'     => 'Continue with <b>Twitter</b>',
            'link_label'      => 'Link account with <b>Twitter</b>',
            'unlink_label'    => 'Unlink account from <b>Twitter</b>',
            'legacy'          => 0
        ));

        if ($this->settings->get('legacy') == 1) {
            $this->loadCompat();
        }
    }

    protected function forTranslation() {
        __('Continue with <b>Twitter</b>', 'nextend-facebook-connect');
        __('Link account with <b>Twitter</b>', 'nextend-facebook-connect');
        __('Unlink account from <b>Twitter</b>', 'nextend-facebook-connect');
    }

    public function validateSettings($newData, $postedData) {
        $newData = parent::validateSettings($newData, $postedData);

        foreach ($postedData AS $key => $value) {

            switch ($key) {
                case 'legacy':
                    if ($postedData['legacy'] == 1) {
                        $newData['legacy'] = 1;
                    } else {
                        $newData['legacy'] = 0;
                    }
                    break;
                case 'tested':
                    if ($postedData[$key] == '1' && (!isset($newData['tested']) || $newData['tested'] != '0')) {
                        $newData['tested'] = 1;
                    } else {
                        $newData['tested'] = 0;
                    }
                    break;
                case 'consumer_key':
                case 'consumer_secret':
                    $newData[$key] = trim(sanitize_text_field($value));
                    if ($this->settings->get($key) !== $newData[$key]) {
                        $newData['tested'] = 0;
                    }

                    if (empty($newData[$key])) {
                        \NSL\Notices::addError(sprintf(__('The %1$s entered did not appear to be a valid. Please enter a valid %2$s.', 'nextend-facebook-connect'), $this->requiredFields[$key], $this->requiredFields[$key]));
                    }
                    break;
            }
        }

        return $newData;
    }

    /**
     * @return NextendSocialProviderTwitterClient
     */
    public function getClient() {
        if ($this->client === null) {

            require_once dirname(__FILE__) . '/twitter-client.php';

            $this->client = new NextendSocialProviderTwitterClient($this->id, $this->settings->get('consumer_key'), $this->settings->get('consumer_secret'));

            $this->client->setRedirectUri($this->getLoginUrl());
        }

        return $this->client;
    }

    /**
     * @return array|mixed|object
     * @throws Exception
     */
    protected function getCurrentUserInfo() {

        return $this->getClient()
                    ->get('account/verify_credentials', array(
                        'include_email'    => 'true',
                        'include_entities' => 'false',
                        'skip_status'      => 'true'
                    ));
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function getAuthUserData($key) {

        switch ($key) {
            case 'id':
                return $this->authUserData['id'];
            case 'email':
                return !empty($this->authUserData['email']) ? $this->authUserData['email'] : '';
            case 'name':
                return $this->authUserData['name'];
            case 'secondary_name':
                return $this->authUserData['screen_name'];
            case 'first_name':
                $name = explode(' ', $this->getAuthUserData('name'), 2);

                return isset($name[0]) ? $name[0] : '';
            case 'last_name':
                $name = explode(' ', $this->getAuthUserData('name'), 2);

                return isset($name[1]) ? $name[1] : '';
        }

        return parent::getAuthUserData($key);
    }

    public function syncProfile($user_id, $provider, $access_token) {

        if ($this->needUpdateAvatar($user_id)) {
            $this->updateAvatar($user_id, $this->authUserData['profile_image_url_https']);
        }

        $this->saveUserData($user_id, 'access_token', $access_token);
    }

    public function getState() {
        if ($this->settings->get('legacy') == 1) {
            return 'legacy';
        }

        return parent::getState();
    }

    public function loadCompat() {
        if (!is_admin()) {
            require_once(dirname(__FILE__) . '/compat/nextend-twitter-connect.php');
        } else {
            if (basename($_SERVER['PHP_SELF']) !== 'plugins.php') {
                require_once(dirname(__FILE__) . '/compat/nextend-twitter-connect.php');
            } else {

                add_action('admin_menu', array(
                    $this,
                    'loadCompatMenu'
                ), 1);
            }
        }
    }

    public function loadCompatMenu() {
        add_options_page('Nextend Twitter Connect', 'Nextend Twitter Connect', 'manage_options', 'nextend-twitter-connect', array(
            'NextendTwitterSettings',
            'NextendTwitter_Options_Page'
        ));
    }

    public function import() {
        $oldSettings = maybe_unserialize(get_option('nextend_twitter_connect'));
        if ($oldSettings === false) {
            $newSettings['legacy'] = 0;
            $this->settings->update($newSettings);
        } else if (!empty($oldSettings['twitter_consumer_key']) && !empty($oldSettings['twitter_consumer_secret'])) {
            $newSettings = array(
                'consumer_key'    => $oldSettings['twitter_consumer_key'],
                'consumer_secret' => $oldSettings['twitter_consumer_secret']
            );

            if (!empty($oldSettings['twitter_user_prefix'])) {
                $newSettings['user_prefix'] = $oldSettings['twitter_user_prefix'];
            }

            $newSettings['legacy'] = 0;
            $this->settings->update($newSettings);

            delete_option('nextend_twitter_connect');
        }

        return true;
    }

    public function adminDisplaySubView($subview) {
        if ($subview == 'import' && $this->settings->get('legacy') == 1) {
            $this->admin->render('import', false);

            return true;
        }

        return parent::adminDisplaySubView($subview);
    }

    public function deleteLoginPersistentData() {
        parent::deleteLoginPersistentData();

        if ($this->client !== null) {
            $this->client->deleteLoginPersistentData();
        }
    }

    public function getAvatar($user_id) {

        if (!$this->isUserConnected($user_id)) {
            return false;
        }

        $picture = $this->getUserData($user_id, 'profile_picture');
        if (!$picture || $picture == '') {
            return false;
        }

        return $picture;
    }
}

NextendSocialLogin::addProvider(new NextendSocialProviderTwitter);