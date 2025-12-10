<?php

namespace Httpeur;

class DebugUtils
{

    /**
     * Dump variables and display them in a pretty format with a default title.
     * 
     * @param mixed ...$variables The variables to dump.
     * @return void
     */
    public static function dump(...$variables,)
    {
        self::titledDump("Debug", ...$variables);
    }

    /**
     * Dump variables and display them in a pretty format with a title.
     * 
     * @param string $title The title of the debug section.
     * @param mixed ...$variables The variables to dump.
     * @return void
     */
    public static function titledDump(string $title, ...$variables,)
    {
        echo "<div style='margin-bottom: 10px; background-color: #f4d200; padding: 10px;'>";
        echo "<details open>";
        echo "<summary style='font-weight: bold; font-size: 1.2em; background-color: white; padding: 5px; margin-bottom: 5px; border: 1px solid #ccc;'>";

        if ($title == "Debug") {
            echo "Debug";
        } else {
            echo "Debug: " . $title;
        }
        echo "</summary>";
        foreach ($variables as $variable) {
            echo "<details open style='border: 1px solid #ccc; padding: 10px; background-color: #f0f0f0; margin-bottom: 10px;'>";
            echo "<summary>" . gettype($variable) . "</summary>";
            echo "<pre style='padding: 10px; background-color: #fff; border: 1px solid #ccc;'>";
            var_dump($variable);
            echo "</pre>";
            echo "</details>";
        }
        echo "</details>";
        echo "</div>";
    }
}
