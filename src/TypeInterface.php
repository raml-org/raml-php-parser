<?php

namespace Raml;

/**
 * Interface for RAML types
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
interface TypeInterface extends ArrayInstantiationInterface
{
    /**
     * Returns the name of the Type.
     **/
    public function getName();

    /**
     * Returns a multidimensional array of the Type's content.
     */
    public function toArray();

    /**
     * Returns boolean true when the given $value is valid against the type, false otherwise.
     *
     * @param mixed   $value    Value to validate.
     *
     * @return bool             Returns true when valid, false otherwise.
     */
    public function validate($value);
}
