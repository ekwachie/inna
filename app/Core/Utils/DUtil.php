<?php

/**
 * @author      Payperlez Team <inna@payperlez.org>
 * @copyright   Copyright (C), 2019. Payperlez Inc.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 * @todo PDO exception and error handling
 * @category    Database
 * @example
 * $this->query('INSERT INTO tb (col1, col2, col3) VALUES(?,?,?)', $var1, $var2, $var3);
 *
 *
 */

namespace app\Core\Utils;

class DUtil
{

    /**
     * @param $input
     * @param $length
     * @param bool|true $ellipses
     * @param bool|true $strip_html
     * @return string
     */
    public static function trim_text($input, $length, $ellipses = true, $strip_html = true)
    {
        if ($strip_html === true) {
            $input = strip_tags($input);
        }

        if (strlen($input) <= $length) {
            return $input;
        }

        $last_space = strrpos(substr($input, 0, $length), ' ');
        $trimmed_text = substr($input, 0, $last_space);

        if ($ellipses === true) {
            $trimmed_text .= '...';
        }

        return $trimmed_text;
    }

    /**
     * @param $arr
     * @return bool
     */
    public static function is_multiArray($arr)
    {
        $rv = array_filter($arr, 'is_array');
        return (count($rv) > 0) ? true : false;
    }

    /**
     * @return mixed
     */
    public static function get_ip()
    {
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            $headers = $_SERVER;
        }

        if (
            array_key_exists('X-Forwarded-For', $headers) &&
            filter_var($headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
        ) {
            $the_ip = $headers['X-Forwarded-For'];
        } elseif (
            array_key_exists('HTTP_X_FORWARDED_FOR', $headers) &&
            filter_var($headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
        ) {
            $the_ip = $headers['HTTP_X_FORWARDED_FOR'];
        } else {
            $the_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }

        return $the_ip;
    }

    /**
     * @param $data
     * param null $filename
     */
    public static function createCSV($data, $filename = null)
    {
        if (!isset($filename)) {
            $filename = "replies";
        }

        //Clear output buffer
        ob_clean();

        //Set the Content-Type and Content-Disposition headers.
        header("Content-type: text/x-csv");
        header("Content-Transfer-Encoding: binary");
        header("Content-Disposition: attachment; filename={$filename}-" . date('YmdHis', strtotime('now')) . ".csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        //Open up a PHP output stream using the function fopen.
        $fp = fopen('php://output', 'w');

        //Loop through the array containing our CSV data.
        foreach ($data as $row) {
            //fputcsv formats the array into a CSV format.
            //It then writes the result to our output stream.
            fputcsv($fp, $row);
        }

        //Close the file handle.
        fclose($fp);
    }

    /**
     * @param $algo - The hashing algorithm eg(md5, sha256 etc)
     * @param $data - The data that is going to be encoded
     * @param $salt - The key used as salt
     * @return string - The hashed/salted data
     */
    public static function hash_value($algo, $data, $salt)
    {
        $context = hash_init($algo, HASH_HMAC, $salt);
        hash_update($context, $data);
        return hash_final($context);
    }

    /**
     * @param array $array
     * @param array $keys
     * @return bool
     */
    public static function array_keys_exists($array, $keys)
    {
        if (count(array_intersect_key(array_flip($keys), $array)) === count($keys)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * hash_cost - Calculate the cost the server can take when using password_hash function
     *
     * @return int
     */
    public static function hash_cost()
    {
        $timeTarget = 0.05;
        $cost = 8;
        do {
            $cost++;
            $start = microtime(true);
            password_hash("innaframeworktest", PASSWORD_BCRYPT, ["cost" => $cost]);
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);

        return $cost;
    }

    /**
     * read_stdin - Read data from the command line
     *
     * @return string
     */
    public static function read_stdin()
    {
        $fr = fopen("php://stdin", "r"); // open our file pointer to read from stdin
        $input = fgets($fr, 255); // read a maximum of 255 characters
        $input = rtrim($input); // trim any trailing spaces.
        fclose($fr); // close the file handle
        return $input; // return the text entered
    }

    /**
     * debug - print array elements nicely in the browser;
     *
     * @param array $data
     *
     */
    public static function debug($data = array())
    {
        echo "<pre style='background:#222;color:green;padding:16px;'>";
        print_r($data);
        echo "</pre>";
        die();
    }

    /**
     * startsWith - check that a string starts with some character/string
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return boolean
     *
     */
    public static function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * endsWith - check that a string ends with some character/string
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return boolean
     *
     */
    public static function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    /**
     * isXmlHttpRequest - check the existence of an ajax object
     *
     * @return boolean
     *
     */
    public static function isXmlHttpRequest()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
    }

    /**
     * Cast an array or an stdClass to another class
     *
     * param array|stdClass; $instance
     * @param string $className
     * @return /new $className()
     */
    public static function castToObject($instance, $className)
    {
        if (is_array($instance)) {
            return unserialize(
                sprintf(
                    'O:%d:"%s"%s',
                    strlen($className),
                    $className,
                    strstr(serialize($instance), ':')
                )
            );
        } else if (is_object($instance)) {
            return unserialize(
                sprintf(
                    'O:%d:"%s"%s',
                    strlen($className),
                    $className,
                    strstr(strstr(serialize($instance), '"'), ':')
                )
            );
        }
    }

    /**
     * crypt AES 256
     *
     * @param /data $data
     * @param string $passphrase
     * @return /base64 encrypted data
     */
    public static function encrypt($data, $passphrase)
    {
        // Set a random salt
        $salt = openssl_random_pseudo_bytes(16);

        $salted = '';
        $dx = '';
        // Salt the key(32) and iv(16) = 48
        while (strlen($salted) < 48) {
            $dx = hash('sha256', $dx . $passphrase . $salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv = substr($salted, 32, 16);

        $encrypted_data = openssl_encrypt((string) $data, 'AES-256-CBC', $key, true, $iv);
        return base64_encode($salt . $encrypted_data);
    }

    /**
     * decrypt AES 256
     *
     * @param /data $edata
     * @param string $password
     * @return /decrypted data
     */
    public static function decrypt($edata, $passphrase)
    {
        $data = base64_decode((string) $edata);
        $salt = substr($data, 0, 16);
        $ct = substr($data, 16);

        $rounds = 3; // depends on key length
        $data00 = $passphrase . $salt;
        $hash = array();
        $hash[0] = hash('sha256', $data00, true);
        $result = $hash[0];
        for ($i = 1; $i < $rounds; $i++) {
            $hash[$i] = hash('sha256', $hash[$i - 1] . $data00, true);
            $result .= $hash[$i];
        }
        $key = substr($result, 0, 32);
        $iv = substr($result, 32, 16);

        return openssl_decrypt($ct, 'AES-256-CBC', $key, true, $iv);
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email address
     *
     * @param string $email The email address
     * @param integer $size Size of image in pixels. Desfaults to 80 [1 - 2048]
     * @param string $imageset Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
     * @param string $rating Maximum rating (inclusive) [ g | pg | r | x ]
     * @param boolean $tag True to return a complete IMG tag False for just the URL
     * @param array $attr Optional, additional key/value attributes to include in the IMG tag
     * return String containing either just a URL or a complete image tag
     */
    public static function gravatar($email, $size = 80, $imageset = 'mp', $rating = 'g', $tag = false, $attr = array())
    {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$size&d=$imageset&r=$rating";

        if ($tag) {
            $url = '<img src="' . $url . '"';
            foreach ($attr as $key => $val)
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }

        return $url;
    }

    //for generating qr codes
    public static function qr_code($data)
    {
        return 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=' . $data;
    }

    /** for hashing password
     *
     * In this case, we want to increase the default cost for BCRYPT to 12.
     * Note that we also switched to BCRYPT, which will always be 60 characters.
     */
    public static function passHash($password)
    {
        $options = [
            'cost' => 12,
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public static function passVerify($password, $hash)
    {
        return password_verify($password, $hash) ? true : false;
    }

    public static function cleanMonth($string)
    {
        // return $string = str_replace(' " ', '', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9-,".\-]/', '', $string); // Removes special chars.
    }

    /**
     * generate a GUID
     */
    public static function GUID()
    {
        // Generate 16 bytes (128 bits) of random data
        $data = random_bytes(16);

        // Set version to 4 (random) and adjust variant (RFC 4122 compliant)
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Version 4 (random)
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant is RFC 4122

        // Convert binary data into a readable UUID format
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    // logging  user activity in a log file
    public static function logActivity()
    {
        //Write action to txt log
        $log  = date("F j, Y. h:i:s a").' - '.self::get_ip() . ' - '. $_SERVER['HTTP_USER_AGENT'].' - '. $_SERVER['REQUEST_URI'].' - '. $_SERVER['HTTP_REFERER'] . PHP_EOL;
        self::isDir("access");
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log/access/log_' . date("j.n.Y") . '.log', $log, FILE_APPEND);
    }

    // create log directory
    public static function isDir($dir_name)
    {
        if (is_dir("./log/$dir_name")) {
            return true;
        } else {
            mkdir("./log/$dir_name",0777, true);
            return true;
        }
    }
    /**
     * Generate a CSRF token and store it in the session with an expiration time
     */
    public static function csrf_token()
    {
        // Set token expiration time to 15 minutes
        $token_expiry_time = time() + (15 * 60);

        // Generate a new random token
        $csrf_token = bin2hex(random_bytes(32));

        // Store the token and its expiry time in the session
        $_SESSION['csrf_token'] = $csrf_token;
        $_SESSION['csrf_token_expiry'] = $token_expiry_time;

        return htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8');
    }

    public static function csrf_verifier($token)
    {
        // Check if the token is set and if it has expired
        if (isset($_SESSION['csrf_token'], $_SESSION['csrf_token_expiry']) && $_SESSION['csrf_token_expiry'] > time()) {
            // Check if the token provided by the user matches the one in the session
            return hash_equals($_SESSION['csrf_token'], $token);
        }
        return false;
    }

    /**
     * Log the details of failed CSRF attempts for monitoring and debugging
     */
    public static function logCsrfFailure()
    {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $time = date('Y-m-d H:i:s');
        $log_message = "[$time] CSRF validation failed from IP: $ip_address" . PHP_EOL;

        // Log this attempt to a file (adjust the path to your needs)
        file_put_contents('./log/csrf_failures_' . date("j.n.Y") . '.log', $log_message, FILE_APPEND);
    }

    /**
     * Track failed CSRF attempts to apply rate-limiting
     */
    public static function trackFailedCsrfAttempts()
    {
        if (!isset($_SESSION['csrf_failures'])) {
            $_SESSION['csrf_failures'] = 0;
        }
        $_SESSION['csrf_failures']++;

        // Block further attempts if too many failures occur
        if ($_SESSION['csrf_failures'] > 5) {
            die("Too many failed attempts. Please try again later.");
        }
    }
}
