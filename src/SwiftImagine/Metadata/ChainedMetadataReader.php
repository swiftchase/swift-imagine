<?php

namespace SwiftImagine\Metadata;


use Imagine\Image\Metadata\AbstractMetadataReader;

/**
 * ChainedMetadataReader
 *
 * This reader simply chains all other metadata readers together in order to
 * extract the most amount of information from a file.
 *
 * @package SwiftImagine\Metadata
 */
class ChainedMetadataReader extends AbstractMetadataReader
{

    protected $readers = [];

    public function __construct(array $readers)
    {
        foreach ($readers as $reader) {
            $this->addReader($reader);
        }
    }

    public function addReader($reader)
    {
        if (is_string($reader)) {
            $reader = new $reader();
        }

        if (!$reader instanceof AbstractMetadataReader) {
            throw new \Exception('Bad Metadata Reader');
        }

        $this->readers[] =  $reader;
    }

    /**
     * Extracts metadata from a file
     *
     * @param $file
     *
     * @return array An associative array of metadata
     */
    protected function extractFromFile($file)
    {
        $data = [];
        foreach ($this->readers as $reader) { /* @var $reader AbstractMetadataReader */
            $data = array_merge($data, $reader->extractFromFile($file));
        }
        return $data;
    }

    /**
     * Extracts metadata from raw data
     *
     * @param $data
     *
     * @return array An associative array of metadata
     */
    protected function extractFromData($data)
    {
        $data = [];
        foreach ($this->readers as $reader) { /* @var $reader AbstractMetadataReader */
            $data = array_merge($data, $reader->extractFromData($data));
        }
        return $data;
    }

    /**
     * Extracts metadata from a stream
     *
     * @param $resource
     *
     * @return array An associative array of metadata
     */
    protected function extractFromStream($resource)
    {
        $data = [];
        foreach ($this->readers as $reader) { /* @var $reader AbstractMetadataReader */
            $data = array_merge($data, $reader->extractFromStream($resource));
        }
        return $data;
    }
}