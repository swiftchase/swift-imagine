<?php

namespace SwiftImagine\Metadata;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Exception\NotSupportedException;
use Imagine\Image\Metadata\AbstractMetadataReader;

/**
 * GPS Metadata from EXIF information
 */
class GpsMetadataReader extends AbstractMetadataReader
{
    public function __construct()
    {
        if (!function_exists('exif_read_data')) {
            throw new NotSupportedException('PHP exif extension is required to use the GpsMetadataReader');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function extractFromFile($file)
    {
        if (false === $data = @file_get_contents($file)) {
            throw new InvalidArgumentException(sprintf('File %s is not readable.', $file));
        }

        return $this->doReadData($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractFromData($data)
    {
        return $this->doReadData($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractFromStream($resource)
    {
        return $this->doReadData(stream_get_contents($resource));
    }

    /**
     * Extracts metadata from raw data, merges with existing metadata
     *
     * @param string $data
     *
     * @return array
     */
    private function doReadData($data)
    {
        if (substr($data, 0, 2) === 'II') {
            $mime = 'image/tiff';
        } else {
            $mime = 'image/jpeg';
        }

        return $this->extract('data://' . $mime . ';base64,' . base64_encode($data));
    }

    /**
     * Performs the exif data extraction given a path or data-URI representation.
     *
     * @see http://en.wikipedia.org/wiki/Geotagging#JPEG_photos
     *
     * @param string $path The path to the file or the data-URI representation.
     *
     * @return array
     */
    private function extract($path)
    {
        if (false === $exifData = @exif_read_data($path, null, true, false)) {
            return [];
        }

        $metadata = [];

        if (!isset ($exifData['GPS'])) {
            return [];
        }

        $gpsData = $exifData['GPS'];

        if (isset ($gpsData['GPSLatitude'], $gpsData['GPSLongitude'])) {
            $metadata['gps.latitude'] = $this->exifCoordToDecimal($gpsData['GPSLatitude'], $gpsData['GPSLatitudeRef']);
            $metadata['gps.longitude'] = $this->exifCoordToDecimal($gpsData['GPSLongitude'], $gpsData['GPSLongitudeRef']);
        }

        if (isset ($gpsData['GPSAltitude'])) {
            $metadata['gps.altitude'] = $this->fractionToDecimal($gpsData['GPSAltitude']);
        }

        return $metadata;
    }

    private function exifCoordToDecimal($exifCoord, $ref)
    {
        $dec = 0.0;

        while ($fraction = array_pop($exifCoord)) {
            $dec = $this->fractionToDecimal($fraction) + ($dec / 60);
        }

        if (in_array($ref, ['W','S'], 1)) {
            $dec *= -1.0;
        }

        return $dec;
    }

    /**
     * @param array $degrees Take the "600/100" format and return the decimal number
     *
     * @return float
     */
    private function fractionToDecimal($degrees)
    {
        list ($numerator, $denominator) = explode('/', $degrees);
        return (float)($numerator / $denominator);
    }

}
