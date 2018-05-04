<?php

class NextendSocialProviderGoogle extends NextendSocialProvider {

    /** @var NextendSocialProviderGoogleClient */
    protected $client;

    protected $color = '#dc4e41';

    protected $svg = '<svg xmlns="http://www.w3.org/2000/svg"><path fill="#fff" d="M7.636 11.545v2.619h4.331c-.174 1.123-1.309 3.294-4.33 3.294-2.608 0-4.735-2.16-4.735-4.822 0-2.661 2.127-4.821 4.734-4.821 1.484 0 2.477.632 3.044 1.178l2.073-1.997C11.422 5.753 9.698 5 7.636 5A7.63 7.63 0 0 0 0 12.636a7.63 7.63 0 0 0 7.636 7.637c4.408 0 7.331-3.098 7.331-7.462 0-.502-.054-.884-.12-1.266h-7.21zm16.364 0h-2.182V9.364h-2.182v2.181h-2.181v2.182h2.181v2.182h2.182v-2.182H24"/></svg>';

    public function __construct() {
        $this->id    = 'google';
        $this->label = 'Google';

        $this->path = dirname(__FILE__);

        $this->requiredFields = array(
            'client_id'     => 'Client ID',
            'client_secret' => 'Client Secret'
        );

        parent::__construct(array(
            'client_id'     => '',
            'client_secret' => '',
            'login_label'   => 'Continue with <b>Google</b>',
            'link_label'    => 'Link account with <b>Google</b>',
            'unlink_label'  => 'Unlink account from <b>Google</b>',
            'legacy'        => 0
        ));

        if ($this->settings->get('legacy') == 1) {
            $this->loadCompat();
        }
    }

    protected function forTranslation() {
        __('Continue with <b>Google</b>', 'nextend-facebook-connect');
        __('Link account with <b>Google</b>', 'nextend-facebook-connect');
        __('Unlink account from <b>Google</b>', 'nextend-facebook-connect');
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
                case 'client_id':
                case 'client_secret':
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

    public function getClient() {
        if ($this->client === null) {

            require_once dirname(__FILE__) . '/google-client.php';

            $this->client = new NextendSocialProviderGoogleClient($this->id);

            $this->client->setClientId($this->settings->get('client_id'));
            $this->client->setClientSecret($this->settings->get('client_secret'));
            $this->client->setRedirectUri($this->getLoginUrl());
            $this->client->setApprovalPrompt('auto');
        }

        return $this->client;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getCurrentUserInfo() {
        return $this->getClient()
                    ->get('userinfo');
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
                return $this->authUserData['email'];
            case 'name':
                return $this->authUserData['name'];
            case 'first_name':
                return $this->authUserData['given_name'];
            case 'last_name':
                return $this->authUserData['family_name'];
            case 'picture':
                return $this->authUserData['picture'];
        }

        return parent::getAuthUserData($key);
    }

    public function syncProfile($user_id, $provider, $access_token) {
        if ($this->needUpdateAvatar($user_id)) {
            $this->updateAvatar($user_id, $this->getAuthUserData('picture'));
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
            require_once(dirname(__FILE__) . '/compat/nextend-google-connect.php');
        } else {
            if (basename($_SERVER['PHP_SELF']) !== 'plugins.php') {
                require_once(dirname(__FILE__) . '/compat/nextend-google-connect.php');
            } else {

                add_action('admin_menu', array(
                    $this,
                    'loadCompatMenu'
                ), 1);
            }
        }
    }

    public function loadCompatMenu() {
        add_options_page('Nextend Google Connect', 'Nextend Google Connect', 'manage_options', 'nextend-google-connect', array(
            'NextendGoogleSettings',
            'NextendGoogle_Options_Page'
        ));
    }

    public function import() {
        $oldSettings = maybe_unserialize(get_option('nextend_google_connect'));
        if ($oldSettings === false) {
            $newSettings['legacy'] = 0;
            $this->settings->update($newSettings);
        } else if (!empty($oldSettings['google_client_id']) && !empty($oldSettings['google_client_secret'])) {
            $newSettings = array(
                'client_id'     => $oldSettings['google_client_id'],
                'client_secret' => $oldSettings['google_client_secret']
            );

            if (!empty($oldSettings['google_user_prefix'])) {
                $newSettings['user_prefix'] = $oldSettings['google_user_prefix'];
            }

            $newSettings['legacy'] = 0;
            $this->settings->update($newSettings);

            delete_option('nextend_google_connect');
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

NextendSocialLogin::addProvider(new NextendSocialProviderGoogle);