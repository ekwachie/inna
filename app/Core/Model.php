<?php
/**
 * @author      Evans Kwachie <evans.kwachie@ucc.edu.gh>
 * @copyright   Copyright (C), 2019 Evans Kwachie.
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
namespace app\Core;

/**
 * Validation
 *
 * Semplice classe PHP per la validazione.
 *
 * @author Davide Cesarano <davide.cesarano@unipegaso.it>
 * @copyright (c) 2016, Davide Cesarano
 * @license https://github.com/davidecesarano/Validation/blob/master/LICENSE MIT License
 * @link https://github.com/davidecesarano/Validation
 */

class Model
{
    /**
     * @var array $patterns
     */
    public $patterns = [
        'uri' => '[A-Za-z0-9-\/_?&=]+',
        'url' => '[A-Za-z0-9-:.\/_?&=#]+',
        'alpha' => '[\p{L}]+',
        'words' => '[\p{L}\s]+',
        'alphanum' => '[\p{L}0-9]+',
        'int' => '[0-9]+',
        'float' => '[0-9\.,]+',
        'tel' => '[0-9+\s()-]+',
        'text' => '[\p{L}0-9\s-.,;:!"%&()?+\'°#\/@]+',
        'file' => '[\p{L}\s0-9-_!%&()=\[\]#@,.;+]+\.[A-Za-z0-9]{2,4}',
        'folder' => '[\p{L}\s0-9-_!%&()=\[\]#@,.;+]+',
        'address' => '[\p{L}0-9\s.,()°-]+',
        'date_dmy' => '[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}',
        'date_ymd' => '[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}',
        'email' => '[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+[.]+[a-z-A-Z]',
    ];

    /**
     * @var array $errors
     */
    public $errors = [];

    /**
     * Return field name
     *
     * @param string $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Return value field
     *
     * @param mixed $value
     * @return  $this
     */
    public function value($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * File
     *
     * @param mixed $value
     * @return $this
     */
    public function file($value)
    {
        $this->file = $value;
        return $this;
    }

    /**
     * Return an error if the input has a different format than the pattern
     *
     *
     * @param string
     * @return  $this
     */
    public function pattern($name)
    {
        if ($name == 'array') {
            if (!is_array($this->value)) {
                $this->errors[] = 'Field format ' . $this->name . ' invalid.';
            }
        } else {
            $regex = '/^(' . $this->patterns[$name] . ')$/u';
            if ($this->value != '' && !preg_match($regex, $this->value)) {
                $this->errors[] = 'Field format ' . $this->name . ' invalid.';
            }
        }
        return $this;
    }

    /**
     * Return an error if the input has a different format than the custom pattern
     *
     * @param string $pattern
     * @return  $this
     */
    public function customPattern($pattern)
    {
        $regex = '/^(' . $pattern . ')$/u';
        if ($this->value != '' && !preg_match($regex, $this->value)) {
            $this->errors[] = 'Field format ' . $this->name . ' invalid.';
        }
        return $this;
    }

    /**
     * Returns an error if the input is empty
     *
     * @return  $this
     */
    public function required()
    {
        if (
            (isset($this->file) && $this->file['error'] == 4) ||
            ($this->value == '' || $this->value == null)
        ) {
            $this->errors[] = 'Field ' . $this->name . ' required.';
        }
        return $this;
    }

    /**
     * Return an error if the input is shorter than the parameter
     *
     *
     * @param int $min
     * @return  $this
     */
    public function min($length)
    {
        if (is_string($this->value)) {
            if (strlen($this->value) < $length) {
                $this->errors[] =
                    'Field value ' .
                    $this->name .
                    ' less than the minimum value';
            }
        } else {
            if ($this->value < $length) {
                $this->errors[] =
                    'Field value ' .
                    $this->name .
                    ' less than the minimum value';
            }
        }
        return $this;
    }

    /**
     * Return an error if the input is longer than the parameter
     *
     *
     * @param int $max
     * @return  $this
     */
    public function max($length)
    {
        if (is_string($this->value)) {
            if (strlen($this->value) > $length) {
                $this->errors[] =
                    'Field value' .
                    $this->name .
                    ' higher than the maximum value';
            }
        } else {
            if ($this->value > $length) {
                $this->errors[] =
                    'Field vlaue ' .
                    $this->name .
                    ' higher than the maximum value';
            }
        }
        return $this;
    }

    /**
     * Return an error if the input is not same as the parameter
     *
     *
     * @param mixed $value
     * @return  $this
     */
    public function equal($value)
    {
        if ($this->value != $value) {
            $this->errors[] = 'Field value ' . $this->name . ' does not match.';
        }
        return $this;
    }

    /**
     * Return an error if the file size exceeds the maximum allowable size
     *
     * @param int $size
     * @return  $this
     */
    public function maxSize($size)
    {
        if ($this->file['error'] != 4 && $this->file['size'] > $size) {
            $this->errors[] =
                'The file ' .
                $this->name .
                ' exceeds the maximum size of ' .
                number_format($size / 1048576, 2) .
                ' MB.';
        }
        return $this;
    }

    /**
     * Return an error if the file extension is not same the parameter
     *
     * @param string $extension
     * @return  $this
     */
    public function ext($extension)
    {
        if (
            $this->file['error'] != 4 &&
            pathinfo($this->file['name'], PATHINFO_EXTENSION) != $extension &&
            strtoupper(pathinfo($this->file['name'], PATHINFO_EXTENSION)) !=
            $extension
        ) {
            $this->errors[] =
                'The file ' . $this->name . ' is not a ' . $extension . '.';
        }
        return $this;
    }

    /**
     * Purify to prevent attacks XSS
     *
     * @param string $string
     * @return $string
     */
    public function purify($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Return true if there are no errors
     *
     * @return boolean
     */
    public function isSuccess()
    {
        if (empty($this->errors)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Validation errors
     *
     * @return array $this->errors
     */
    public function getErrors()
    {
        if (!$this->isSuccess()) {
            return $this->errors;
        } else {
            return [];
        }
    }

    /**
     * View errors in Html format
     *
     * @return string $html
     */
    public function displayErrors()
    {
        $html = '<pre>';
        foreach ($this->getErrors() as $error) {
            $html .= '<li>' . $error . '</li>';
        }
        $html .= '</pre>';

        return $html;
    }

    /**
     * View validation result
     *
     * @return / booelan|string
     */
    public function result()
    {
        if (!$this->isSuccess()) {
            foreach ($this->getErrors() as $error) {
                echo "$error\n";
            }
            exit();
        } else {
            return true;
        }
    }

    /**
     * Check if the value is * an integer
     *
     * @param mixed $value
     * @return boolean
     */
    public static function is_int($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if the value is * a float number
     *
     * @param mixed $value
     * @return boolean
     */
    public static function is_float($value)
    {
        if (filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if the value is
     *  a letter of the alphabet
     *
     * @param mixed $value
     * @return boolean
     */
    public static function is_alpha($value)
    {
        if (
            filter_var($value, FILTER_VALIDATE_REGEXP, [
                'options' => ['regexp' => '/^[a-zA-Z]+$/'],
            ])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if the value is
     * a letter or number
     *
     * @param mixed $value
     * @return boolean
     */
    public static function is_alphanum($value)
    {
        if (
            filter_var($value, FILTER_VALIDATE_REGEXP, [
                'options' => ['regexp' => '/^[a-zA-Z0-9]+$/'],
            ])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if the value is
     * a url // Return true if the value is an url (protocol is required)
     *
     * @param mixed $value
     * @return boolean
     */
    public static function is_url($value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if the value is  a uri
     * Return true if the value is an uri (protocol is not required)
     * @param mixed $value
     * @return boolean
     */
    public static function is_uri($value)
    {
        if (
            filter_var($value, FILTER_VALIDATE_REGEXP, [
                'options' => ['regexp' => '/^[A-Za-z0-9-\/_]+$/'],
            ])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *Check if the value is * true or false *
     * @param mixed $value
     * @return boolean
     */
    public static function is_bool($value)
    {
        if (
            is_bool(
                filter_var(
                    $value,
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                )
            )
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if the value is * an e-mail *
     * @param mixed $value
     * @return boolean
     */
    public static function is_email($value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }
}