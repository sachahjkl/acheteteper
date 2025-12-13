<?php

namespace utils;

use Acheteteper\Session;

class Utils
{
    public static function debugFromSession(): ?bool
    {
        $debug = Session::get('DEBUG', "none");
        if ($debug !== "none") {
            return $debug === "true" ? true : false;
        }
        return null;
    }
}
