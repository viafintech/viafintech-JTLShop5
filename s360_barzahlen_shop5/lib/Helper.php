<?php declare(strict_types = 1);

namespace Plugin\s360_barzahlen_shop5\lib;


class Helper {
    
    public static function isset_noempty($var) {
        return (isset($var) && !empty($var));
    }
    
    public static function now() {
        return date('Y-m-d H:i:s', time());
    }
    
    public static function contains_string($string, $needle) {
        return strpos($string, $needle);
    }
    
    public static function date_null($date):bool {
        if ($date==="0000-00-00 00:00:00") {
            return true;
        }
        return false;
    }
    
}