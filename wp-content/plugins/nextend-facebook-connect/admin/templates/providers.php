<?php
wp_enqueue_script('jquery-ui-sortable');
?>
<div class="nsl-dashboard-providers-container">
    <div class="nsl-dashboard-providers">
        <?php
        if (count(NextendSocialLogin::$enabledProviders) > 0) {
            include_once(dirname(__FILE__) . '/review.php');
        }
        ?>

        <?php foreach (NextendSocialLogin::$providers AS $provider): ?>
            <?php
            $state         = $provider->getState();
            $providerAdmin = $provider->getAdmin();
            ?>

            <div class="nsl-dashboard-provider" data-provider="<?php echo $provider->getId(); ?>"
                 data-state="<?php echo $state; ?>">
                <div class="nsl-dashboard-provider-top" style="background-color: <?php echo $provider->getColor(); ?>;">
                    <img src="<?php echo $provider->getIcon(); ?>" width="60" height="60"
                         alt="<?php echo esc_attr($provider->getLabel()); ?>"/>
                    <h2><?php echo $provider->getLabel(); ?></h2>
                </div>
                <div class="nsl-dashboard-provider-bottom">
                    <div class="nsl-dashboard-provider-bottom-state">
						<?php
                        switch ($state) {
                            case 'pro-only':
                                _e('Not Available', 'nextend-facebook-connect');
                                break;
                            case 'not-configured':
                                _e('Not Configured', 'nextend-facebook-connect');
                                break;
                            case 'not-tested':
                                _e('Not Verified', 'nextend-facebook-connect');
                                break;
                            case 'disabled':
                                _e('Disabled', 'nextend-facebook-connect');
                                break;
                            case 'enabled':
                                _e('Enabled', 'nextend-facebook-connect');
                                break;
                            case 'legacy':
                                _e('Legacy', 'nextend-facebook-connect');
                                break;
                        }
                        ?>
                    </div>

                    <?php
                    switch ($state) {
                        case 'pro-only':
                            ?>
                            <a href="<?php echo NextendSocialLoginAdmin::trackUrl('https://nextendweb.com/social-login/', 'buy-pro-addon-button-' . $provider->getId()); ?>"
                               class="button button-secondary" target="_blank">
								<?php _e('Upgrade Now', 'nextend-facebook-connect'); ?>
                            </a>
                            <?php
                            break;
                        case 'not-configured':
                            ?>
                            <a href="<?php echo $providerAdmin->getUrl(); ?>" class="button button-secondary">
								<?php _e('Getting Started', 'nextend-facebook-connect'); ?>
                            </a>
                            <?php
                            break;
                        case 'not-tested':
                            ?>
                            <a href="<?php echo $providerAdmin->getUrl('settings'); ?>"
                               class="button button-secondary">
								<?php _e('Verify Settings', 'nextend-facebook-connect'); ?>
                            </a>
                            <?php
                            break;
                        case 'disabled':
                            ?>
                            <a href="<?php echo wp_nonce_url(add_query_arg('provider', $provider->getId(), NextendSocialLoginAdmin::getAdminUrl('enable')), 'nextend-social-login_enable_' . $provider->getId()); ?>"
                               class="button button-primary">
								<?php _e('Enable', 'nextend-facebook-connect'); ?>
                            </a>
                            <a href="<?php echo $providerAdmin->getUrl('settings'); ?>"
                               class="button button-secondary">
								<?php _e('Settings', 'nextend-facebook-connect'); ?>
                            </a>
                            <?php
                            break;
                        case 'enabled':
                            ?>
                            <a href="<?php echo wp_nonce_url(add_query_arg('provider', $provider->getId(), NextendSocialLoginAdmin::getAdminUrl('disable')), 'nextend-social-login_disable_' . $provider->getId()); ?>"
                               class="button button-secondary">
								<?php _e('Disable', 'nextend-facebook-connect'); ?>
                            </a>
                            <a href="<?php echo $providerAdmin->getUrl('settings'); ?>"
                               class="button button-secondary">
								<?php _e('Settings', 'nextend-facebook-connect'); ?>
                            </a>
                            <?php
                            break;
                        case 'legacy':
                            ?>
                            <a href="<?php echo $providerAdmin->getUrl('import'); ?>" class="button button-primary">
								<?php _e('Import', 'nextend-facebook-connect'); ?>
                            </a>
                            <?php
                            break;
                    }
                    ?>
                </div>

                <div class="nsl-dashboard-provider-sortable-handle"></div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="nsl-clear"></div>
</div>

<script>
	(function ($) {
        $(document).ready(function () {
            var _ajax_nonce = '<?php echo wp_create_nonce("nextend-social-login"); ?>',
                savingMessage = <?php echo wp_json_encode(__('Saving...', 'nextend-facebook-connect')); ?>,
                errorMessage = <?php echo wp_json_encode(__('Saving failed', 'nextend-facebook-connect')); ?>,
                successMessage = <?php echo wp_json_encode(__('Order Saved', 'nextend-facebook-connect')); ?>;
            $('.nsl-dashboard-providers').sortable({
                handle: '.nsl-dashboard-provider-sortable-handle',
                items: ' > .nsl-dashboard-provider',
                tolerance: 'pointer',
                stop: function (event, ui) {
                    var $providers = $('.nsl-dashboard-providers > .nsl-dashboard-provider'),
                        providerList = [];
                    for (var i = 0; i < $providers.length; i++) {
                        providerList.push($providers.eq(i).data('provider'));
                    }

                    ui.item.find('.nsl-provider-notice').remove();

                    var $notice = $('<div class="nsl-provider-notice">' + savingMessage + '</div>')
                        .appendTo(ui.item);

                    $.ajax({
                        type: 'post',
                        dataType: 'json',
                        url: ajaxurl,
                        data: {
                            '_ajax_nonce': _ajax_nonce,
                            'action': 'nextend-social-login',
                            'view': 'orderProviders',
                            'ordering': providerList
                        },
                        success: function () {
                            $notice.html(successMessage);
                            setTimeout(function () {
                                $notice.fadeOut(300, function () {
                                    $notice.remove();
                                });
                            }, 2000);
                        },
                        error: function () {
                            $notice.html(errorMessage);
                            setTimeout(function () {
                                $notice.fadeOut(300, function () {
                                    $notice.remove();
                                });
                            }, 3000);
                        }
                    });
                }
            });
        });
    })(jQuery);
</script>