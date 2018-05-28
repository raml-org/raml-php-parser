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
        'text/xml',
    ];

    public static function convertStringByContentType($string, $contentType)
    {
        $generalContentType = self::parseMediaRange($contentType);

        if (in_array($generalContentType, self::$jsonTypes, true)) {
            $value = json_decode($string, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidJsonException(json_last_error_msg());
            }
        } elseif (in_array($generalContentType, self::$xmlTypes, true)) {
            $value = new \DOMDocument();

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

    private static function parseMediaRange($mediaRange)
    {
        $parts = explode(';', $mediaRange);
        $params = [];
        foreach ($parts as $i => $param) {
            if (strpos($param, '=') !== false) {
                list($k, $v) = explode('=', trim($param));
                $params[$k] = $v;
            }
        }
        $fullType = trim($parts[0]);
        if ($fullType === '*') {
            return '*/*';
        }
        list($type, $subtype) = explode('/', $fullType);
        if (!$subtype) {
            throw new \UnexpectedValueException('Malformed media-range: ' . $mediaRange);
        }
        $plusPos = strpos($subtype, '+');
        if (false !== $plusPos) {
            $genericSubtype = substr($subtype, $plusPos + 1);
        } else {
            $genericSubtype = $subtype;
        }

        return sprintf('%s/%s', trim($type), trim($genericSubtype));
    }
}
