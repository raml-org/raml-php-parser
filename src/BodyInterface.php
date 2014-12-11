<?php

namespace Raml;

interface BodyInterface
{
    /**
     * Get the media type of the body
     *
     * @return string
     */
    public function getMediaType();
}
