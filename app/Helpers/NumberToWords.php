<?php

namespace App\Helpers;

use Numbers_Words;

class NumberToWords
{
    public static function convert($number)
    {
        // Check if the number is valid
        if ($number < 0) {
            return "Negative numbers are not supported";  // Optional: Handle negative numbers differently
        }

        // Create an instance of the Numbers_Words class
        $nw = new Numbers_Words();

        // Convert the number to words
        $words = $nw->toWords($number);

        // Capitalize the first letter and return the result
        return ucfirst($words);
    }
}
