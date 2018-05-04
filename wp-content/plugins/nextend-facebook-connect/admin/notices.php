<?php

class NextendSocialLoginAdminNotices {

    private static $notices;

    public static function init() {
        self::$notices = maybe_unserialize(NextendSocialLoginPersistentUser::get('_nsl_admin_notices'));
        if (!is_array(self::$notices)) {
            self::$notices = array();
        }
        if (basename($_SERVER['PHP_SELF']) !== 'options-general.php' || empty($_GET['page']) || $_GET['page'] !== 'nextend-social-login') {
            add_action('admin_notices', 'NextendSocialLoginAdminNotices::admin_notices');
        }

    }

    private static function add($type, $message) {
        if (!isset(self::$notices[$type])) {
            self::$notices[$type] = array();
        }

        if (!in_array($message, self::$notices[$type])) {
            self::$notices[$type][] = $message;
        }

        NextendSocialLoginPersistentUser::set('_nsl_admin_notices', maybe_serialize(self::$notices));
    }

    public static function addError($message) {
        self::add('error', $message);
    }

    public static function addSuccess($message) {
        self::add('success', $message);
    }

    public static function displayNotices() {

        $html = self::getHTML();

        if (!empty($html)) {
            echo '<div class="nsl-admin-notices">' . $html . '</div>';
        }
    }

    public static function admin_notices() {
        echo self::getHTML();
    }

    private static function getHTML() {
        $html = '';
        if (isset(self::$notices['success'])) {
            foreach (self::$notices['success'] AS $message) {
                $html .= '<div class="updated"><p>' . $message . '</p></div>';
            }
        }

        if (isset(self::$notices['error'])) {
            foreach (self::$notices['error'] AS $message) {
                $html .= '<div class="error"><p>' . $message . '</p></div>';
            }
        }

        NextendSocialLoginPersistentUser::delete('_nsl_admin_notices');
        self::$notices = array();

        return $html;
    }
}