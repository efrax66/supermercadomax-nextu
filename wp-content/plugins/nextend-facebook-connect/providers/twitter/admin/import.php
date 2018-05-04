<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>

<div class="nsl-admin-sub-content">
    <h2 class="title"><?php _e('Import Twitter configuration', 'nextend-facebook-connect'); ?></h2>
    <p><?php _e('Be sure to read the following notices before you proceed.', 'nextend-facebook-connect'); ?></p>

    <h4><?php _e('Important steps before the import', 'nextend-facebook-connect'); ?></h4>
    <p><?php _e('Make sure that the redirect URI for your app is correct before proceeding.', 'nextend-facebook-connect'); ?></p>
    <ol>
        <li><?php printf(__('Visit %s.', 'nextend-facebook-connect'), '<a href="https://apps.twitter.com/" target="_blank">https://apps.twitter.com/</a>'); ?></li>
        <li><?php _e('Select your app.', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('Go to the Settings tab.', 'nextend-facebook-connect'); ?></li>
        <li><?php printf(__('Make sure that the "%1$s" field contains %2$s', 'nextend-facebook-connect'), 'Callback URL', $provider->getLoginUrl()); ?> </li>
        <li><?php _e('Save your changes.', 'nextend-facebook-connect'); ?></li>
    </ol>

    <h4><?php _e('The following settings will be imported:', 'nextend-facebook-connect'); ?></h4>
    <ol>
        <li><?php _e('Your old API configurations', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('The user prefix you set', 'nextend-facebook-connect'); ?></li>
    </ol>

    <h4><?php _e('Create a backup of the old settings', 'nextend-facebook-connect'); ?></h4>
    <textarea cols="160" rows="6" readonly title=""><?php echo esc_textarea(wp_json_encode(maybe_unserialize(get_option('nextend_twitter_connect')), defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0)); ?></textarea>

    <h4><?php _e('Other changes', 'nextend-facebook-connect'); ?></h4>
    <ol>
        <li><?php _e('The custom redirect URI is now handled globally for all providers, so it won\'t be imported from the previous version. Visit "Nextend Social Login > Global settings" to set the new redirect URIs.', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('The login button\'s layout will be changed to a new, more modern look. If you used any custom buttons that won\'t be imported.', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('The old version\'s PHP functions are not available anymore. This means if you used any custom codes where you used these old functions, you need to remove them.', 'nextend-facebook-connect'); ?></li>
    </ol>
    <p><?php _e('After the importing process finishes, you will need to <b>test</b> your app and <b>enable</b> the provider. You can do both in the next screen.', 'nextend-facebook-connect'); ?></p>
    <p>
    <p>
        <a href="<?php echo wp_nonce_url(add_query_arg(array(
            'action'   => 'nextend-social-login',
            'view'     => 'import',
            'provider' => $provider->getId()
        ), admin_url('admin-post.php')), 'nextend-social-login'); ?>" class="button button-primary">
			<?php _e('Import Configuration', 'nextend-facebook-connect'); ?>
        </a>
    </p>
</div>