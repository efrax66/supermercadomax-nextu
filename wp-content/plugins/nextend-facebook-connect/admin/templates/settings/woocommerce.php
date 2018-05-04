<?php
defined('ABSPATH') || die();

$isPRO = NextendSocialLoginAdmin::isPro();

$attr = '';
if (!$isPRO) {
    $attr = ' disabled ';
}

$settings = NextendSocialLogin::$settings;

NextendSocialLoginAdmin::showProBox();
?>
<table class="form-table">
    <tbody>
    <tr>
        <th scope="row"><?php _e('WooCommerce login form', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label><input type="radio" name="woocommerce_login"
                              value="" <?php if ($settings->get('woocommerce_login') == '') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('No Connect button in login form', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="woocommerce_login"
                              value="before" <?php if ($settings->get('woocommerce_login') == 'before') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Connect button before login form', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        woocommerce_login_form_start</code></label><br>
                <label><input type="radio" name="woocommerce_login"
                              value="after" <?php if ($settings->get('woocommerce_login') == 'after') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Connect button after login form', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        woocommerce_login_form_end</code></label><br>
            </fieldset>
        </td>
    </tr>

    <tr>
        <th scope="row"><?php _e('WooCommerce register form', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label><input type="radio" name="woocommerce_register"
                              value="" <?php if ($settings->get('woocommerce_register') == '') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('No Connect button in register form', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="woocommerce_register"
                              value="before" <?php if ($settings->get('woocommerce_register') == 'before') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Connect button before register form', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        woocommerce_register_form_start</code></label><br>
                <label><input type="radio" name="woocommerce_register"
                              value="after" <?php if ($settings->get('woocommerce_register') == 'after') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Connect button after register form', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        woocommerce_register_form_end</code></label><br>
            </fieldset>
        </td>
    </tr>

    <tr>
        <th scope="row"><?php _e('WooCommerce billing form', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <legend class="screen-reader-text">
                    <span><?php _e('WooCommerce billing form', 'nextend-facebook-connect'); ?></span></legend>
                <label><input type="radio" name="woocommerce_billing"
                              value="" <?php if ($settings->get('woocommerce_billing') == '') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('No Connect button in billing form', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="woocommerce_billing"
                              value="before" <?php if ($settings->get('woocommerce_billing') == 'before') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Connect button before billing form', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        woocommerce_before_checkout_billing_form</code></label><br>
                <label><input type="radio" name="woocommerce_billing"
                              value="after" <?php if ($settings->get('woocommerce_billing') == 'after') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Connect button after billing form', 'nextend-facebook-connect'); ?></span></label>
                <code><?php _e('Action:'); ?>
                    woocommerce_after_checkout_billing_form</code><br>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('WooCommerce account details', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <legend class="screen-reader-text">
                    <span><?php _e('WooCommerce account details', 'nextend-facebook-connect'); ?></span></legend>
                <label><input type="radio" name="woocommerce_account_details"
                              value="before" <?php if ($settings->get('woocommerce_account_details') == 'before') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Link buttons before account details', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        woocommerce_edit_account_form_start</code></label><br>
                <label><input type="radio" name="woocommerce_account_details"
                              value="after" <?php if ($settings->get('woocommerce_account_details') == 'after') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Link buttons after account details', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        woocommerce_edit_account_form_end</code></label><br>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('WooCommerce button style', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <legend class="screen-reader-text">
                    <span><?php _e('WooCommerce button style', 'nextend-facebook-connect'); ?></span></legend>
                <label>
                    <input type="radio" name="woocoommerce_form_button_style"
                           value="default" <?php if ($settings->get('woocoommerce_form_button_style') == 'default') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Default', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/buttons/default.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="woocoommerce_form_button_style"
                           value="icon" <?php if ($settings->get('woocoommerce_form_button_style') == 'icon') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Icon', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/buttons/icon.png', NSL_ADMIN_PATH) ?>"/>
                </label><br>
            </fieldset>
        </td>
    </tr>
    </tbody>
</table>
<?php if ($isPRO): ?>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                             value="<?php _e('Save Changes'); ?>"></p>
<?php endif; ?>
