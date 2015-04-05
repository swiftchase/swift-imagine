<?php


namespace SwiftImagine\Metadata;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Exception\NotSupportedException;
use Imagine\Image\Metadata\AbstractMetadataReader;

/**
 * Metadata driven by IPTC information
 */
class IptcMetadataReader extends AbstractMetadataReader
{

    const OBJECT_NAME = '005';
    const EDIT_STATUS = '007';
    const PRIORITY = '010';
    const CATEGORY = '015';
    const SUPPLEMENTAL_CATEGORY = '020';
    const FIXTURE_IDENTIFIER = '022';
    const KEYWORDS = '025';
    const RELEASE_DATE = '030';
    const RELEASE_TIME = '035';
    const SPECIAL_INSTRUCTIONS = '040';
    const REFERENCE_SERVICE = '045';
    const REFERENCE_DATE = '047';
    const REFERENCE_NUMBER = '050';
    const CREATED_DATE = '055';
    const CREATED_TIME = '060';
    const ORIGINATING_PROGRAM = '065';
    const PROGRAM_VERSION = '070';
    const OBJECT_CYCLE = '075';
    const BYLINE = '080';
    const BYLINE_TITLE = '085';
    const CITY = '090';
    const PROVINCE_STATE = '095';
    const COUNTRY_CODE = '100';
    const COUNTRY = '101';
    const ORIGINAL_TRANSMISSION_REFERENCE = '103';
    const HEADLINE = '105';
    const CREDIT = '110';
    const SOURCE = '115';
    const COPYRIGHT_STRING = '116';
    const CAPTION = '120';
    const LOCAL_CAPTION = '121';
    const CAPTION_WRITER = '122';

    public function __construct()
    {
        if (!function_exists('exif_read_data')) {
            throw new NotSupportedException('PHP exif extension is required to use the ExifMetadataReader');
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
     * @param string $path The path to the file or the data-URI representation.
     *
     * @return array
     */
    private function extract($path)
    {
        getimagesize($path, $info);

        if (!isset ($info['APP13'])) {
            return [];
        }

        $reflect = new \ReflectionClass(get_class($this));
        $constants = array_flip($reflect->getConstants());
        $data = iptcparse($info['APP13']);

        $metadata = [];
        foreach ($data as $key => $value) {
            $property = substr($key, 2);
            if (array_key_exists($property, $constants)) {

                if (count($value) == 1) {
                    $value = $value[0];
                }

                $metadata['iptc.'. strtolower($constants[$property])] = $value;
            }
        }
        return $metadata;
    }
}
