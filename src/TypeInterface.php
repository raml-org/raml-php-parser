<?php

namespace Raml;

/**
 * Interface for RAML types
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
interface TypeInterface extends ValidatorInterface
{
    /**
     * Returns the name of the Type.
     **/
    public function getName();

    /**
     * Returns true if type discriminator matched discriminatorValue for class
     */
    public function discriminate($value);

    /**
     * Returns boolean true when the given $value is valid against the type, false otherwise.
     *
     * @param mixed   $value    Value to validate.
     *
     * @return bool             Returns true when valid, false otherwise.
     */
    public function validate($value);
}
