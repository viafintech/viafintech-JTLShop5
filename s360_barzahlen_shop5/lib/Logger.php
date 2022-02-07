<?php declare(strict_types = 1);

namespace Plugin\s360_barzahlen_shop5\lib;

use JTL\Shop;


class Logger {

    const LOG_PREFIX = 'Barzahlen: ';

    public static function debug($message) {
        Shop::Container()->getLogService()->debug(self::LOG_PREFIX . $message);
    }

    public static function notice($message) {
        Shop::Container()->getLogService()->notice(self::LOG_PREFIX . $message);
    }

    public static function error($message) {
        Shop::Container()->getLogService()->error(self::LOG_PREFIX . $message);
    }
    
    public static function api_message($action="", $request="", $response="") {
            $msg = "Action:" .$action. " Request:" .$request. " Response:" .$response;
            self::debug($msg);
    }
    
}
