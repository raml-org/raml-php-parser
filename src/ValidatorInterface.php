<?php
declare(strict_types=1);

namespace Raml;

interface ValidatorInterface
{
    /**
     * Validates a string against the schema
     *
     * @param $string
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function validate($string);
}
