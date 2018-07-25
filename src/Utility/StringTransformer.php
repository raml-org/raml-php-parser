<?php

namespace Raml\Utility;

class StringTransformer
{
    const LOWER_CAMEL_CASE = 1;
    const LOWER_HYPHEN_CASE = 2;
    const LOWER_UNDERSCORE_CASE = 4;
    const UPPER_CAMEL_CASE = 8;
    const UPPER_HYPHEN_CASE = 16;
    const UPPER_UNDERSCORE_CASE = 32;

    private static $possibleTransformations = [
        self::LOWER_CAMEL_CASE,
        self::LOWER_HYPHEN_CASE,
        self::LOWER_UNDERSCORE_CASE,
        self::UPPER_CAMEL_CASE,
        self::UPPER_HYPHEN_CASE,
        self::UPPER_UNDERSCORE_CASE,
    ];

    /**
     * Applies given function on string
     *
     * @param string $string Input string, accepts camelcase, pascalcase, snakecase and hyphencase.
     * @param int $convertTo Type of conversion to apply (use constants).
     *
     * @return string Returns the converted string
     */
    public static function convertString($string, $convertTo)
    {
        if (!in_array($convertTo, self::$possibleTransformations, true)) {
            throw new \Exception('Invalid parameter "' . $convertTo . '" given for ' . __CLASS__ . __METHOD__);
        }

        // make a best possible guess about input type and split string into parts
        preg_match_all('/((?:^|[A-Z])[A-Z]|[a-z]+|(?:_)[A-Z]|[a-z]+|(?:-)[A-Z]|[a-z]+)/', $string, $matches);
        $split = $matches[0];
        $newString = '';
        $delimiter = '';
        for ($i = 0, $size = count($split); $i < $size; $i++) {
            if ($i > 0) {
                if ($convertTo === self::LOWER_HYPHEN_CASE || $convertTo === self::UPPER_HYPHEN_CASE) {
                    $delimiter = '-';
                }
                if ($convertTo === self::LOWER_UNDERSCORE_CASE || $convertTo === self::UPPER_UNDERSCORE_CASE) {
                    $delimiter = '_';
                }
            }
            switch ($convertTo) {
                case self::LOWER_CAMEL_CASE:
                    if ($i === 0) {
                        $newString .= lcfirst($split[$i]);

                        break;
                    }
                    // LOWER_CAMEL_CASE only applies to first part, rest follows same logic as UPPER_CAMEL_CASE
                    // no break
                case self::UPPER_CAMEL_CASE:
                    $newString .= ucfirst($split[$i]);

                    break;
                // lowercase functions
                case self::LOWER_HYPHEN_CASE:
                case self::LOWER_UNDERSCORE_CASE:
                    $newString .= $delimiter . strtolower($split[$i]);

                    break;
                // uppercase functions
                case self::UPPER_UNDERSCORE_CASE:
                case self::UPPER_HYPHEN_CASE:
                    $newString .= $delimiter . strtoupper($split[$i]);

                    break;
            }
        }

        return $newString;
    }
}
