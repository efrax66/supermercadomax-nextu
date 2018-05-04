<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();

$settings = $provider->settings;
?>

<hr/>
<h2><?php _e('Other settings', 'nextend-facebook-connect'); ?></h2>
<table class="form-table">
    <tbody>
    <tr>
        <th scope="row"><label
                    for="user_prefix"><?php _e('Username prefix on register', 'nextend-facebook-connect'); ?></label></th>
        <td><input name="user_prefix" type="text" id="user_prefix"
                   value="<?php echo esc_attr($settings->get('user_prefix')); ?>" class="regular-text"></td>
    </tr>
    <tr>
        <th scope="row"><label
                    for="user_fallback"><?php _e('Fallback username prefix on register', 'nextend-facebook-connect'); ?></label></th>
        <td><input name="user_fallback" type="text" id="user_fallback"
                   value="<?php echo esc_attr($settings->get('user_fallback')); ?>" class="regular-text">
            <p class="description" id="tagline-user_fallback"><?php _e('Used when username is invalid', 'nextend-facebook-connect'); ?></p></td>
    </tr>
    </tbody>
</table>