<?php
require_once NSL_PATH . '/includes/oauth2.php';

class NextendSocialProviderGoogleClient extends NextendSocialOauth2 {

    protected $access_token_data = array(
        'access_token' => '',
        'expires_in'   => -1,
        'created'      => -1
    );

    private $accessType = 'offline';
    private $approvalPrompt = 'force';

    protected $scopes = array(
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email'
    );

    protected $endpointAuthorization = 'https://accounts.google.com/o/oauth2/auth';

    protected $endpointAccessToken = 'https://accounts.google.com/o/oauth2/token';

    protected $endpointRestAPI = 'https://www.googleapis.com/oauth2/v1/';

    protected $defaultRestParams = array(
        'alt' => 'json'
    );

    /**
     * @param string $access_token_data
     */
    public function setAccessTokenData($access_token_data) {
        $this->access_token_data = json_decode($access_token_data, true);
    }


    public function createAuthUrl() {
        return add_query_arg(array(
            'access_type'     => urlencode($this->accessType),
            'approval_prompt' => urlencode($this->approvalPrompt)
        ), parent::createAuthUrl());
    }

    /**
     * @param string $approvalPrompt
     */
    public function setApprovalPrompt($approvalPrompt) {
        $this->approvalPrompt = $approvalPrompt;
    }

    /**
     * @param $response
     *
     * @throws Exception
     */
    protected function errorFromResponse($response) {
        if (isset($response['error'])) {
            throw new Exception($response['error']);
        }
    }

}