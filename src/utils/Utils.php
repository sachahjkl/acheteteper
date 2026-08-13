<?php

namespace utils;

use Acheteteper\Session;

class Utils
{
    public static function debugFromSession(): ?bool
    {
        if (session_status() === PHP_SESSION_NONE && !isset($_COOKIE[session_name()])) {
            return null;
        }
        $debug = Session::get('DEBUG', "none");
        if ($debug !== "none") {
            return $debug === "true" ? true : false;
        }
        return null;
    }
}
