# SwiftImagine Zend Framework 2 Module

## Purpose

SwiftImagine makes the [Imagine](https://github.com/avalanche123/Imagine) library available as a
service within a ZF2 application, automatically selecting the "best possible" adapter
(selecting from [Gmagick](http://php.net/gmagick), [Imagick](http://php.net/imagick), or [GD](http://php.net/gd)).

## Installation

Add the library to composer:

    $ composer require "swiftchase/swift-imagine:*"
    
Then, add the `SwiftImagine` module in your `config/application.config.php`.
Afterwards, the `SwiftImagine\Service\Imagine` service will be available via the service manager.

## Additional Metadata readers

The `Imagine` library itself comes with an `ExifMetadataReader`. 

The `SwiftImagine` module adds additional readers:

  * `ChainedMetadataReader` - aggregates information from multiple metadata readers 
  * `GpsMetadataReader` - extracts GPS location and altitude from the EXIF data
  * `IptcMetadataReader` - extracts IPTC photo metadata.

## Limitations / To-dos

Currently there's no way to configure the preferred graphics library adapter, change which metadata readers are
enabled by default, or easily making multiple imagine services available each with different configurations.

There are also issues with the streams, haven't bothered to poke at it.
