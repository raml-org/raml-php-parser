<?php

namespace Raml\FileLoader;

/**
 * Interface for file loaders
 */
interface FileLoaderInterface
{
    /**
     * Load a file from a path and return a string
     *
     * @param string $filePath
     *
     * @return string
     */
    public function loadFile($filePath);

    /**
     * Get a list of valid file extensions
     *
     * @return string[]
     */
    public function getValidExtensions();
}
