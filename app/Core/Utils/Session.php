<?php

namespace app\Core\Utils;

/**
 * @author      Obed Ademang <kizit2012@gmail.com>
 * @copyright   Copyright (C), 2015 Obed Ademang
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 *
 */
class Session {

    /**
     *
     * init - start a session
     *
     */
    public static function init() {
        // if(SESSION_STORE === 'redis') {
        //     $sessionHandler = new DRedisHandler(new PredisClient(REDIS_URI), SECRET_KEY);
        //     @session_set_save_handler($sessionHandler, true);
        // }

        // if(SESSION_STORE === 'files') {
        //     $sessionHandler = new DFileHandler(SECRET_KEY);
        //     ini_set('session.save_handler', 'files');
        // }

        @session_start();
    }

    // --------------------------------------------------------------------------

    /**
     *
     * set - Set a session value or variable
     * @param string $key The session variable to set
     * @param string $value The new value
     *
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    // ---------------------------------------------------------------------------

    /**
     *
     * get - Return value of session variable
     * @param string $key the session variable
     * @return mixed The variable's value
     *
     */
    public static function get($key) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
    }

    // -----------------------------------------------------------------------------

    public static function unsert($key){
        if(isset($_SESSION[$key])){
            unset($_SESSION[$key]);
        } else{
            return;
        }
    }

    //------------------------------------------------------------------------------

    public static function issert($key){
        return (isset($_SESSION[$key])) ? true : false;
    }

    /**
     *
     * destroy()
     * Completely destroy session data
     * - Unset $_SESSION by making it empty
     * - Use the default cookie behaviour to delete the data
     * - Call session_destroy
     */
    public static function destroy() {
        $_SESSION = array();
        if(ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), ' ', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }

}
