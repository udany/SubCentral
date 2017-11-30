<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Daniel
 * Date: 11/02/15
 * Time: 00:35
 * To change this template use File | Settings | File Templates.
 */

class HttpAuth {
    public static function RequireAuth($valid, $realm='', $message='', $invalidMessage=''){
        if (!self::CheckAuth($valid, $realm, $message)){
            self::SendAuthRequest($realm, $invalidMessage);
        }
    }
    public static function CheckAuth($valid, $realm='', $message=''){
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $_SERVER['PHP_AUTH_USER'];
            $pw = $_SERVER['PHP_AUTH_PW'];

            if (is_callable($valid)){
                return $valid($user, $pw);
            }else if (is_array($valid)){
                return isset($valid[$user]) && $valid[$user] == $pw;
            }else{
                return false;
            }
        }

        self::SendAuthRequest($realm, $message);
    }

    public static function SendAuthRequest($realm='', $message=''){
        global $project;
        if (!$realm) $realm = $project;

        @ob_clean();

        header('WWW-Authenticate: Basic realm="'.$realm.'"');
        header('HTTP/1.0 401 Unauthorized');

        echo $message;
        exit;
    }
}