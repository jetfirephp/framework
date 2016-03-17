<?php

use JetFire\Framework\App;

//----------------------------------------------------------------

if (!function_exists('app')) {

    /**
     * @param null $key
     * @return mixed
     */
    function app($key = null)
    {
        return (!is_null($key))
            ? App::getInstance()->get($key)
            : App::getInstance();
    }
}

//----------------------------------------------------------------

if (!function_exists('create')) {

    /**
     * @param null $key
     * @param array $params
     * @return mixed
     */
    function create($key,$params = [])
    {
        return app()->get($key,$params);
    }
}

//----------------------------------------------------------------

if (!function_exists('logger')) {


    function logger($name = null)
    {
        return is_null($name)
            ? app()->get('logger')->getLogger('main')
            : app()->get('logger')->getLogger($name);
    }
}

//----------------------------------------------------------------

if (!function_exists('view')) {

    /**
     * @param $path
     * @param array $data
     * @return mixed
     */
    function view($path = null, $data = [])
    {
        $app = App::getInstance();
        $view = $app->get('response')->getView();
        if(is_null($path) && empty($data)) return $view;
        $flash = $app->get('session')->getSession()->allFlash();
        foreach ($flash as $type => $messages) {
            $data[$type] = $messages;
        }
        return $view->render($path, $data);
    }

}

//----------------------------------------------------------------

if (!function_exists('session')) {


    function session($key = null)
    {
        $app = App::getInstance();
        if(is_null($key))
            return $app->get('session')->getSession();
        return $app->get('session')->getSession()->get($key);
    }

}

//----------------------------------------------------------------

if (!function_exists('redirect')) {

    /**
     * @param null $to
     * @return mixed
     */
    function redirect($to = null)
    {
        if (is_null($to))
            return App::getInstance()->get('response')->getRedirect();
        return App::getInstance()->get('response')->getRedirect()->to($to);
    }
}

//----------------------------------------------------------------

if (!function_exists('abort')) {


    function abort($code)
    {
        $routing = App::getInstance()->get('routing');
        $routing->getResponse()->setStatusCode($code);
        $routing->getRouter()->callResponse();
    }
}

//----------------------------------------------------------------

if (!function_exists('unauthorized')) {

    function unauthorized()
    {
        abort(403);
    }

}

//----------------------------------------------------------------

if (!function_exists('notfound')) {

    /**
     * @return mixed
     */
    function notfound()
    {
        abort(404);
    }

}

//----------------------------------------------------------------

if (!function_exists('head')) {
    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param  array $array
     * @return mixed
     */
    function head($array)
    {
        return reset($array);
    }
}
if (!function_exists('last')) {
    /**
     * Get the last element from an array.
     *
     * @param  array $array
     * @return mixed
     */
    function last($array)
    {
        return end($array);
    }
}

if (!function_exists('object_get')) {
    /**
     * Get an item from an object using "dot" notation.
     *
     * @param  object $object
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function object_get($object, $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') {
            return $object;
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_object($object) || !isset($object->{$segment})) {
                return value($default);
            }
            $object = $object->{$segment};
        }
        return $object;
    }
}
if (!function_exists('with')) {
    /**
     * Return the given object. Useful for chaining.
     *
     * @param  mixed $object
     * @return mixed
     */
    function with($object)
    {
        return $object;
    }
}
if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
if (!function_exists('preg_replace_sub')) {
    /**
     * Replace a given pattern with each value in the array in sequentially.
     *
     * @param  string $pattern
     * @param  array $replacements
     * @param  string $subject
     * @return string
     */
    function preg_replace_sub($pattern, &$replacements, $subject)
    {
        return preg_replace_callback($pattern, function ($match) use (&$replacements) {
            foreach ($replacements as $key => $value) {
                return array_shift($replacements);
            }
        }, $subject);
    }
}

if (!function_exists('slugify')) {
    /**
     * @param $text
     * @return mixed|string
     */
    function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text))
            return 'n-a';
        return $text;
    }
}

if (!function_exists('escape')) {
    /**
     * @param $value
     * @return string
     */
    function escape($value)
    {
        return (!is_array($value))
            ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false)
            : $value;
    }
}

if (!function_exists('get_dir_files')) {

    /**
     * @param $dir
     * @return array
     */
    function get_dir_files($dir)
    {
        $files = [];
        $dir_handle = opendir($dir);
        while ($entry = readdir($dir_handle))
            if (is_file($dir . '/' . $entry)) {
                array_push($files, $entry);
            }
        closedir($dir_handle);
        return $files;
    }
}

if (!function_exists('password_hash')) {

    /**
     * Hash the password using the specified algorithm
     *
     * @param string $password The password to hash
     * @param int $algo The algorithm to use (Defined by PASSWORD_* constants)
     * @param array $options The options for the algorithm to use
     *
     * @return string|false The hashed password, or false on error.
     */
    function password_hash($password, $algo, array $options = array())
    {
        if (!function_exists('crypt')) {
            trigger_error("Crypt must be loaded for password_hash to function", E_USER_WARNING);
            return null;
        }
        if (is_null($password) || is_int($password)) {
            $password = (string)$password;
        }
        if (!is_string($password)) {
            trigger_error("password_hash(): Password must be a string", E_USER_WARNING);
            return null;
        }
        if (!is_int($algo)) {
            trigger_error("password_hash() expects parameter 2 to be long, " . gettype($algo) . " given", E_USER_WARNING);
            return null;
        }
        $resultLength = 0;
        switch ($algo) {
            case 1:
                $cost = 10;
                if (isset($options['cost'])) {
                    $cost = $options['cost'];
                    if ($cost < 4 || $cost > 31) {
                        trigger_error(sprintf("password_hash(): Invalid bcrypt cost parameter specified: %d", $cost), E_USER_WARNING);
                        return null;
                    }
                }
                // The length of salt to generate
                $raw_salt_len = 16;
                // The length required in the final serialization
                $required_salt_len = 22;
                $hash_format = sprintf("$2y$%02d$", $cost);
                // The expected length of the final crypt() output
                $resultLength = 60;
                break;
            default:
                trigger_error(sprintf("password_hash(): Unknown password hashing algorithm: %s", $algo), E_USER_WARNING);
                return null;
        }
        $salt_requires_encoding = false;
        if (isset($options['salt'])) {
            switch (gettype($options['salt'])) {
                case 'NULL':
                case 'boolean':
                case 'integer':
                case 'double':
                case 'string':
                    $salt = (string)$options['salt'];
                    break;
                case 'object':
                    if (method_exists($options['salt'], '__tostring')) {
                        $salt = (string)$options['salt'];
                        break;
                    }
                case 'array':
                case 'resource':
                default:
                    trigger_error('password_hash(): Non-string salt parameter supplied', E_USER_WARNING);
                    return null;
            }
            if (_strlen($salt) < $required_salt_len) {
                trigger_error(sprintf("password_hash(): Provided salt is too short: %d expecting %d", _strlen($salt), $required_salt_len), E_USER_WARNING);
                return null;
            } elseif (0 == preg_match('#^[a-zA-Z0-9./]+$#D', $salt)) {
                $salt_requires_encoding = true;
            }
        } else {
            $buffer = '';
            $buffer_valid = false;
            if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
                $buffer = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }
            if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
                $buffer = openssl_random_pseudo_bytes($raw_salt_len);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }
            if (!$buffer_valid && @is_readable('/dev/urandom')) {
                $f = fopen('/dev/urandom', 'r');
                $read = _strlen($buffer);
                while ($read < $raw_salt_len) {
                    $buffer .= fread($f, $raw_salt_len - $read);
                    $read = _strlen($buffer);
                }
                fclose($f);
                if ($read >= $raw_salt_len) {
                    $buffer_valid = true;
                }
            }
            if (!$buffer_valid || _strlen($buffer) < $raw_salt_len) {
                $bl = _strlen($buffer);
                for ($i = 0; $i < $raw_salt_len; $i++) {
                    if ($i < $bl) {
                        $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                    } else {
                        $buffer .= chr(mt_rand(0, 255));
                    }
                }
            }
            $salt = $buffer;
            $salt_requires_encoding = true;
        }
        if ($salt_requires_encoding) {
            // encode string with the Base64 variant used by crypt
            $base64_digits =
                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
            $bcrypt64_digits =
                './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

            $base64_string = base64_encode($salt);
            $salt = strtr(rtrim($base64_string, '='), $base64_digits, $bcrypt64_digits);
        }
        $salt = _substr($salt, 0, $required_salt_len);

        $hash = $hash_format . $salt;

        $ret = crypt($password, $hash);

        if (!is_string($ret) || _strlen($ret) != $resultLength) {
            return false;
        }

        return $ret;
    }

    /**
     * Get information about the password hash. Returns an array of the information
     * that was used to generate the password hash.
     *
     * array(
     *    'algo' => 1,
     *    'algoName' => 'bcrypt',
     *    'options' => array(
     *        'cost' => PASSWORD_BCRYPT_DEFAULT_COST,
     *    ),
     * )
     *
     * @param string $hash The password hash to extract info from
     *
     * @return array The array of information about the hash.
     */
    function password_get_info($hash)
    {
        $return = array(
            'algo'     => 0,
            'algoName' => 'unknown',
            'options'  => array(),
        );
        if (_substr($hash, 0, 4) == '$2y$' && _strlen($hash) == 60) {
            $return['algo'] = 1;
            $return['algoName'] = 'bcrypt';
            list($cost) = sscanf($hash, "$2y$%d$");
            $return['options']['cost'] = $cost;
        }
        return $return;
    }

    /**
     * Determine if the password hash needs to be rehashed according to the options provided
     *
     * If the answer is true, after validating the password using password_verify, rehash it.
     *
     * @param string $hash The hash to test
     * @param int $algo The algorithm used for new password hashes
     * @param array $options The options array passed to password_hash
     *
     * @return boolean True if the password needs to be rehashed.
     */
    function password_needs_rehash($hash, $algo, array $options = array())
    {
        $info = password_get_info($hash);
        if ($info['algo'] != $algo) {
            return true;
        }
        switch ($algo) {
            case 1:
                $cost = isset($options['cost']) ? $options['cost'] : 10;
                if ($cost != $info['options']['cost']) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * Verify a password against a hash using a timing attack resistant approach
     *
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     *
     * @return boolean If the password matches the hash
     */
    function password_verify($password, $hash)
    {
        if (!function_exists('crypt')) {
            trigger_error("Crypt must be loaded for password_verify to function", E_USER_WARNING);
            return false;
        }
        $ret = crypt($password, $hash);
        if (!is_string($ret) || _strlen($ret) != _strlen($hash) || _strlen($ret) <= 13) {
            return false;
        }

        $status = 0;
        for ($i = 0; $i < _strlen($ret); $i++) {
            $status |= (ord($ret[$i]) ^ ord($hash[$i]));
        }

        return $status === 0;
    }
}


if (!function_exists('generate_password')) {

    /**
     * @param int $length
     * @return string
     */
    function generate_password($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

}

if (!function_exists('_strlen')) {

    /**
     * Count the number of bytes in a string
     *
     * We cannot simply use strlen() for this, because it might be overwritten by the mbstring extension.
     * In this case, strlen() will count the number of *characters* based on the internal encoding. A
     * sequence of bytes might be regarded as a single multibyte character.
     *
     * @param string $binary_string The input string
     *
     * @internal
     * @return int The number of bytes
     */
    function _strlen($binary_string)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($binary_string, '8bit');
        }
        return strlen($binary_string);
    }

    /**
     * Get a substring based on byte limits
     *
     * @see _strlen()
     *
     * @param string $binary_string The input string
     * @param int $start
     * @param int $length
     *
     * @internal
     * @return string The substring
     */
    function _substr($binary_string, $start, $length)
    {
        if (function_exists('mb_substr')) {
            return mb_substr($binary_string, $start, $length, '8bit');
        }
        return substr($binary_string, $start, $length);
    }

    /**
     * Check if current PHP version is compatible with the library
     *
     * @return boolean the check result
     */
    function check()
    {
        static $pass = NULL;

        if (is_null($pass)) {
            if (function_exists('crypt')) {
                $hash = '$2y$04$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
                $test = crypt("password", $hash);
                $pass = $test == $hash;
            } else {
                $pass = false;
            }
        }
        return $pass;
    }
}

if (!function_exists('generate_token')) {

    function generate_token($name = '')
    {
        $token = md5(uniqid(rand(), true));
        $session = App::getInstance()->get('session')->getSession();
        if (is_null($session->getFlash($name . '_token'))) {
            $session->flash($name . '_token', $token);
            $session->flash($name . '_token_time', time());
        }
        return $session->getFlash($name . '_token');
    }
}

if (!function_exists('is_token')) {

    function is_token($time, $name = '', $referer = null)
    {
        $session = App::getInstance()->get('session')->getSession();
        $request = App::getInstance()->get('request');
        if (!is_null($session->getFlash($name . '_token')) && !is_null($session->getFlash($name . '_token_time')) && $request->getPost()->get($name . '_token')) {
            if ($session->getFlash($name . '_token') == $request->getPost()->get($name . '_token')) {
                if ($session->getFlash($name . '_token_time') >= (time() - $time)) {
                    if (is_null($referer)) return true;
                    else if (!is_null($referer) && $request->referer() == ROOT . $referer) return true;
                }
            }
        }
        $session->flash('response', ['status'=>'error','message'=>'token invalid !']);
        return false;
    }

}