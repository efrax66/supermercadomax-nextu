<?php
if (!defined('NEW_GOOGLE_LOGIN')) {
    return;
}

require_once dirname(__FILE__) . '/apiClient.php';
require_once dirname(__FILE__) . '/contrib/apiOauth2Service.php';

$settings = maybe_unserialize(get_option('nextend_google_connect'));

$client = new apiClient();
$client->setClientId($settings['google_client_id']);
$client->setClientSecret($settings['google_client_secret']);
$client->setDeveloperKey($settings['google_api_key']);
$client->setRedirectUri(new_google_login_url());
$client->setApprovalPrompt('auto');

$oauth2 = new apiOauth2Service($client);
