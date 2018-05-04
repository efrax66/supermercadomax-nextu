<?php

class NextendSocialLoginPersistentAnonymous {

    private static $verifiedSession = false;

    private static function getSessionID($mustCreate = false) {
        if (self::$verifiedSession !== false) {
            return self::$verifiedSession;
        }
        if (isset($_COOKIE['nsl_session'])) {
            if (get_site_transient('n_' . $_COOKIE['nsl_session']) !== false) {
                self::$verifiedSession = $_COOKIE['nsl_session'];

                return self::$verifiedSession;
            }
        }
        if ($mustCreate) {
            self::$verifiedSession = uniqid('nsl', true);

            self::setcookie('nsl_session', self::$verifiedSession, time() + DAY_IN_SECONDS, apply_filters('nsl_session_use_secure_cookie', false));
            set_site_transient('n_' . self::$verifiedSession, 1, 3600);

            return self::$verifiedSession;
        }

        return false;
    }

    public static function set($key, $value, $expiration = 3600) {

        set_site_transient(self::getSessionID(true) . $key, (string)$value, $expiration);
    }

    public static function get($key) {

        $session = self::getSessionID();
        if ($session) {
            return get_site_transient($session . $key);
        }

        return false;
    }

    public static function delete($key) {

        $session = self::getSessionID();
        if ($session) {
            delete_site_transient(self::getSessionID() . $key);
        }
    }

    public static function destroy() {
        $sessionID = self::getSessionID();
        if ($sessionID) {
            self::setcookie('nsl_session', $sessionID, time() - YEAR_IN_SECONDS, apply_filters('nsl_session_use_secure_cookie', false));

            add_action('shutdown', 'NextendSocialLoginPersistentAnonymous::destroy_site_transient');
        }
    }

    public static function destroy_site_transient() {
        $sessionID = self::getSessionID();
        if ($sessionID) {
            delete_site_transient('n_' . $sessionID);
        }
    }

    private static function setcookie($name, $value, $expire, $secure = false) {

        setcookie($name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure);
    }

}

class NextendSocialLoginPersistentUser {

    private static function getSessionID() {
        return get_current_user_id();
    }

    public static function set($key, $value, $expiration = 3600) {

        set_site_transient(self::getSessionID() . $key, (string)$value, $expiration);
    }

    public static function get($key) {

        return get_site_transient(self::getSessionID() . $key);
    }

    public static function delete($key) {

        delete_site_transient(self::getSessionID() . $key);
    }

}