<?php

namespace Maite;

class String {

    /** @var string */
    protected static $classInjection = 'Maite\Utils\Strings';

    /** @var string */
    protected $string = '';



    public function __construct($string = '') {
        $this->string = (string) $string;
    }



    public static function from($string) {
        return new String($string);
    }



    public function replace($search, $replace = null, $caseInsensitive = false) {
        if (is_array($search) and is_null($replace)) {
            $search = array_keys($search);
            $replace = array_values($search);
        }
        $fn = $caseInsensitive ? 'str_ireplace' : 'str_replace';

        return self::from($fn($search, $replace, $this->string));
    }



    public function strPos($needle) {
        return strpos($this->string, $needle);
    }



    public function subStr($start, $end = false) {
        $string = $end ? substr($this->string, $start, $end) : substr($this->string, $start);
        return self::from($string);
    }



    public function iconv($in_charset, $out_charset) {
        return self::from(iconv($in_charset, $out_charset, $this->string));
    }



    public function pregReplace($search, $replace) {
        return self::from(preg_replace($search, $replace, $this->string));
    }



    public function match($search) {
        return \Maite\Utils\Strings::match($search, $this->string);
    }




    public function __toString() {
        return $this->string;
    }



    /**
     * Magically calls methods of injected string class;
     * @param string
     * @param array
     */
    public function __call($name, $arguments) {
        array_unshift($arguments, $this->string);
        return self::from(call_user_func_array(self::$classInjection.'::'.$name, $arguments));
    }

}
