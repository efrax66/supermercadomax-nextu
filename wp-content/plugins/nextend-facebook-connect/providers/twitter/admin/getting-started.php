<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>

<div class="nsl-admin-sub-content">
    <h2 class="title"><?php _e('Getting Started', 'nextend-facebook-connect'); ?></h2>

    <p style="max-width:55em;"><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create a %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'nextend-facebook-connect'), "Twitter", "Consumer Key", "Consumer Secret"); ?></p>

    <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'nextend-facebook-connect'), 'Twitter App'); ?></h2>

    <ol>
        <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://apps.twitter.com/" target="_blank">https://apps.twitter.com/</a>'); ?></li>
        <li><?php printf(__('Log in with your %s credentials if you are not logged in', 'nextend-facebook-connect'), 'Twitter'); ?></li>
        <li><?php _e('Click on the "Create New App" button', 'nextend-facebook-connect'); ?></li>
        <li><?php printf(__('Fill the name and description fields. Then enter your site\'s URL to the Website field: <b>%s</b>', 'nextend-facebook-connect'), site_url()); ?></li>
        <li><?php printf(__('Add the following URL to the "Callback URL" field: <b>%s</b>', 'nextend-facebook-connect'), $provider->getLoginUrl()); ?></li>
        <li><?php _e('Accept the Twitter Developer Agreement', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('Create your application by clicking on the Create your Twitter application button', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('Go to the Keys and Access Tokens tab and find the Consumer Key and Secret', 'nextend-facebook-connect'); ?></li>
    </ol>

    <a href="<?php echo $this->getUrl('settings'); ?>"
       class="button button-primary"><?php printf(__('I am done setting up my %s', 'nextend-facebook-connect'), 'Twitter App'); ?></a>

    <br>
    <div class="nsl-admin-embed-youtube">
        <div></div>
        <iframe src="https://www.youtube.com/embed/5m4kD11Ai2w?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>
</div>