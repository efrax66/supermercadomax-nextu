<script type="text/javascript">
	(function ($) {
        $(document).ready(function () {
            var $form = $('#loginform,#registerform,#front-login-form,#setupform'),
                $main = $('#nsl-custom-login-form-main');

            if ($form.parent().hasClass('tml')) {
                $form = $form.parent();
            }

            $main.find('.nsl-container')
                .addClass('nsl-container-login-layout-below')
                .css('display', 'block');

            $main.appendTo($form)
        });
    }(jQuery));
</script>
<style type="text/css">
    #nsl-custom-login-form-main .nsl-container {
        display: none;
    }

    #nsl-custom-login-form-main .nsl-container-login-layout-below {
        clear: both;
        padding: 20px 0 0;
    }

    .login form {
        padding-bottom: 20px;
    }
</style>
<noscript>
    <style>
        #nsl-custom-login-form-main .nsl-container {
            display: block;
        }
    </style>
</noscript>