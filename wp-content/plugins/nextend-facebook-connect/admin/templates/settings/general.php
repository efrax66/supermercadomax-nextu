<script type="text/javascript">
    (function ($) {
        $(document).ready(function () {
            $('#custom_redirect_enabled').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#redirect').css('display', '');
                }
                else {
                    $('#redirect').css('display', 'none');
                }
            });

            $('#custom_redirect_reg_enabled').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#redirect_reg').css('display', '');
                }
                else {
                    $('#redirect_reg').css('display', 'none');
                }
            });
        });
    })(jQuery);
</script>



<table class="form-table">
    <tbody>
    <tr>
        <th scope="row"><?php _e('Debug mode', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <legend class="screen-reader-text">
                    <span><?php _e('Debug mode', 'nextend-facebook-connect'); ?></span></legend>
                <label><input type="radio" name="debug"
                              value="0" <?php if ($settings->get('debug') == '0') : ?> checked="checked" <?php endif; ?>>
                    <span><?php _e('Disabled', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="debug"
                              value="1" <?php if ($settings->get('debug') == '1') : ?> checked="checked" <?php endif; ?>>
                    <span><?php _e('Enabled', 'nextend-facebook-connect'); ?></span></label><br>
            </fieldset>
        </td>
    </tr>

    <tr>
        <th scope="row"><?php _e('Store Avatar', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label><input type="radio" name="avatar_store"
                              value="0" <?php if ($settings->get('avatar_store') == '0') : ?> checked="checked" <?php endif; ?>>
                    <span><?php _e('Disabled', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="avatar_store"
                              value="1" <?php if ($settings->get('avatar_store') == '1') : ?> checked="checked" <?php endif; ?>>
                    <span><?php _e('Enabled', 'nextend-facebook-connect'); ?></span></label><br>
            </fieldset>
        </td>
    </tr>

    <tr>
        <th scope="row"><label
                    for="redirect"><?php _e('Fixed redirect url for login', 'nextend-facebook-connect'); ?></label>
        </th>
        <td>
            <?php
            $useCustom = false;
            $redirect  = $settings->get('redirect');
            if (!empty($redirect)) {
                $useCustom = true;
            }
            ?>
            <fieldset><label for="custom_redirect_enabled">
                    <input name="custom_redirect_enabled" type="checkbox" id="custom_redirect_enabled"
                           value="1" <?php if ($useCustom): ?> checked<?php endif; ?>>
                    <?php _e('Use custom', 'nextend-facebook-connect'); ?></label>
            </fieldset>
            <input name="redirect" type="text" id="redirect" value="<?php echo esc_attr($redirect); ?>"
                   class="regular-text"<?php if (!$useCustom): ?> style="display:none;"<?php endif; ?>>
        </td>
    </tr>
    <tr>
        <th scope="row"><label
                    for="redirect_reg"><?php _e('Fixed redirect url for register', 'nextend-facebook-connect'); ?></label>
        </th>
        <td>
            <?php
            $useCustom   = false;
            $redirectReg = $settings->get('redirect_reg');
            if (!empty($redirectReg)) {
                $useCustom = true;
            }
            ?>
            <fieldset><label for="custom_redirect_reg_enabled">
                    <input name="custom_redirect_reg_enabled" type="checkbox" id="custom_redirect_reg_enabled"
                           value="1" <?php if ($useCustom): ?> checked<?php endif; ?>>
                    <?php _e('Use custom', 'nextend-facebook-connect'); ?></label>
            </fieldset>
            <input name="redirect_reg" type="text" id="redirect_reg"
                   value="<?php echo esc_attr($redirectReg); ?>"
                   class="regular-text"<?php if (!$useCustom): ?> style="display:none;"<?php endif; ?>>
        </td>
    </tr>
    </tbody>
</table>

<?php
include dirname(__FILE__) . '/general-pro.php';
?>