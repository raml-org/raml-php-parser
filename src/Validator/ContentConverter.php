<?php

namespace Raml\Validator;

use Raml\Exception\InvalidJsonException;
use Raml\Exception\InvalidXmlException;

class ContentConverter
{
    private static $jsonTypes = [
        'application/json',
    ];

    private static $xmlTypes = [
        'application/xml',
        'application/soap+xml',
        'text/xml',
    ];

    public static function convertStringByContentType($string, $contentType)
    {
        if (in_array($contentType, self::$jsonTypes, true)) {
            $value = json_decode($string);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidJsonException(json_last_error_msg());
            }
        } elseif (in_array($contentType, self::$xmlTypes, true)) {
            $value = new \DOMDocument;

            $originalErrorLevel = libxml_use_internal_errors(true);

            $value->loadXML($string);
            $errors = libxml_get_errors();
            libxml_clear_errors();
            if ($errors) {
                libxml_use_internal_errors($originalErrorLevel);

                throw new InvalidXmlException($errors);
            }
            libxml_use_internal_errors($originalErrorLevel);
        } else {
            $value = $string;
        }

        return $value;
    }
}
