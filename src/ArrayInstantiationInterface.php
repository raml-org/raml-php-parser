<?php

namespace Raml;

interface ArrayInstantiationInterface
{
    /**
     * Create a new object from an array of data
     *
     * @param string    $key
     *
     * @return ArrayInstantiationInterface
     */
    public static function createFromArray($key, array $data = []);
}
