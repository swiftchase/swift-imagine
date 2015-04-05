<?php

namespace SwiftImagine\Service;

use Imagine;
use Imagine\Image\AbstractImagine;
use Imagine\Image\Metadata\ExifMetadataReader;
use SwiftImagine\Metadata\ChainedMetadataReader;
use SwiftImagine\Metadata\GpsMetadataReader;
use SwiftImagine\Metadata\IptcMetadataReader;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImagineFactory implements FactoryInterface
{
    /**
     * Create Service Factory
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @throws \DomainException
     * @return AbstractImagine
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (class_exists('Gmagick')) {
            $service = new Imagine\Gmagick\Imagine();
        } elseif (class_exists('Imagick')) {
            $service = new Imagine\Imagick\Imagine();
        } elseif (function_exists('gd_info')) {
            $service = new Imagine\Gd\Imagine();
        } else {
            throw new \DomainException('No image library available for Imagine. Gmagick, Imagick, or GD required');
        }

        /*
         * Grab as much info as possible
         */
        $service->setMetadataReader(
            new ChainedMetadataReader([
                new ExifMetadataReader(),
                new GpsMetadataReader(),
                new IptcMetadataReader()
            ])
        );

        return $service;
    }

}