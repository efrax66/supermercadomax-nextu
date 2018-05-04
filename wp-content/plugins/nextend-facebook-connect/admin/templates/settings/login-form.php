<?php
defined('ABSPATH') || die();

$settings = NextendSocialLogin::$settings;
?>
    <table class="form-table">
    <tbody>
    <tr>
        <th scope="row"><?php _e('Login Form', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label><input type="radio" name="show_login_form"
                              value="show" <?php if ($settings->get('show_login_form') == 'show') : ?> checked="checked" <?php endif; ?>>
                    <span><?php _e('Show login buttons', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="show_login_form"
                              value="hide" <?php if ($settings->get('show_login_form') == 'hide') : ?> checked="checked" <?php endif; ?>>
                    <span><?php _e('Hide login buttons', 'nextend-facebook-connect'); ?></span></label><br>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Registration Form', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label><input type="radio" name="show_registration_form"
                              value="show" <?php if ($settings->get('show_registration_form') == 'show') : ?> checked="checked" <?php endif; ?>>
                    <span><?php _e('Show login buttons', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="show_registration_form"
                              value="hide" <?php if ($settings->get('show_registration_form') == 'hide') : ?> checked="checked" <?php endif; ?>>
                    <span><?php _e('Hide login buttons', 'nextend-facebook-connect'); ?></span></label><br>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php _e('Embedded login form', 'nextend-facebook-connect'); ?>
            <br>
            <code>wp_login_form</code>
        </th>
        <td>
            <fieldset>
                <label><input type="radio" name="show_embedded_login_form"
                              value="show" <?php if ($settings->get('show_embedded_login_form') == 'show') : ?> checked="checked" <?php endif; ?>>
                    <span><?php _e('Show login buttons', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="show_embedded_login_form"
                              value="hide" <?php if ($settings->get('show_embedded_login_form') == 'hide') : ?> checked="checked" <?php endif; ?>>
                    <span><?php _e('Hide login buttons', 'nextend-facebook-connect'); ?></span></label><br>
            </fieldset>
        </td>
    </tr>
    </tbody>
</table>

<?php
include dirname(__FILE__) . '/login-form-pro.php';
?>