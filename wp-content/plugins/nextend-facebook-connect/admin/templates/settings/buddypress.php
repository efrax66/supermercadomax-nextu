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
        <th scope="row"><?php _e('BuddyPress register form', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label><input type="radio" name="buddypress_register_button"
                              value="" <?php if ($settings->get('buddypress_register_button') == '') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('No Connect button', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="buddypress_register_button"
                              value="bp_before_register_page" <?php if ($settings->get('buddypress_register_button') == 'bp_before_register_page') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Connect button before register', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        bp_before_register_page</code></label><br>
                <label><input type="radio" name="buddypress_register_button"
                              value="bp_before_account_details_fields" <?php if ($settings->get('buddypress_register_button') == 'bp_before_account_details_fields') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Connect button before account details', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        bp_before_account_details_fields</code></label><br>
                <label><input type="radio" name="buddypress_register_button"
                              value="bp_after_register_page" <?php if ($settings->get('buddypress_register_button') == 'bp_after_register_page') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Connect button after register', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        bp_after_register_page</code></label><br>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('BuddyPress register button style', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label>
                    <input type="radio" name="buddypress_register_button_style"
                           value="default" <?php if ($settings->get('buddypress_register_button_style') == 'default') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Default', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/buttons/default.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="buddypress_register_button_style"
                           value="icon" <?php if ($settings->get('buddypress_register_button_style') == 'icon') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
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
