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
     * Returns the name of the Type
     **/
    public function getName();

    /**
     * Returns a multidimensional array of the Type's content
     */
    public function toArray();
}
