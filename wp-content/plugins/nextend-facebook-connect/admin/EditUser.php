<?php
/** @var $user WP_User */
?>

<?php foreach (NextendSocialLogin::$enabledProviders AS $provider): ?>
    <?php
    if (!$provider->isUserConnected($user->ID)) continue;
    $hasData = false;
    ob_start();
    ?>

    <h2><?php echo $provider->getLabel(); ?></h2>

    <table class="form-table">
        <tbody>
            <?php foreach ($provider->getSyncFields() AS $fieldName => $fieldData): ?>
                <tr>
                    <th><label><?php echo $fieldData['label'] ?></label></th>
                    <td>
                        <?php
                        $value = get_user_meta($user->ID, $fieldName, true);
                        if (!empty($value)) {
                            echo esc_html($value);
                            $hasData = true;
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
    <?php
    if ($hasData) {
        echo ob_get_clean();
    } else {
        ob_end_clean();
    }
    ?>
<?php endforeach; ?>