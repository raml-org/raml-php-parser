<?php

namespace Raml\FileLoader;

/**
 * Default file loader
 */
class DefaultFileLoader implements FileLoaderInterface
{
    /**
     * Load a file from a path and resolve references
     *
     * @param string $filePath
     *
     * @throws \Exception
     *
     * @return string
     */
    public function loadFile($filePath)
    {
        return file_get_contents($filePath);
    }

    /**
     * Get the list of supported extensions
     * Valid for any file type
     *
     * @return string[]
     */
    public function getValidExtensions()
    {
        return ['*'];
    }
}
