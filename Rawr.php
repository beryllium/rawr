<?php

namespace Beryllium\Rawr;

class Rawr
{
    protected $sandbox;
    protected $exiv;
    protected $exiftool;

    const EXIF_RAW        = 'raw';
    const EXIF_TRANSLATED = 'translated';

    public function __construct($sandbox, $exiv, $exiftool = null)
    {
        $this->sandbox  = rtrim($sandbox, '/');
        $this->exiv     = $exiv;
        $this->exiftool = $exiftool;
    }

    public function isReady()
    {
        return is_dir($this->sandbox)
            && is_writable($this->sandbox)
            && file_exists($this->exiv)
            && is_executable($this->exiv)
            && $this->exiftool ? file_exists($this->exiftool) && is_executable($this->exiftool) : true;
    }

    public function extractPreview($cr2, $index = 3)
    {
        if (!$this->isReady()) {
            throw new \RuntimeException('Not ready to extract previews');
        }
        if (!file_exists($cr2)) {
            throw new InvalidArgumentException('File does not exist: ' . $cr2);
        }
        $outputFile = $this->sandbox . '/' . basename($cr2);
        $outputFile = str_ireplace('.cr2', '-preview3.jpg', $outputFile);
        // exiv2 doesn't seem to have a quick fail, only a "force" option
        // we don't want to overwrite files by mistake, so we exit early
        if (file_exists($outputFile)) {
            return $outputFile;
        }
        $cmd = escapeshellarg($this->exiv)
            . ' -ep'
            . escapeshellarg($index)
            . ' -l '
            . escapeshellarg($this->sandbox)
            . ' ex '
            . escapeshellarg($cr2)
            . ' 2>&1 > /dev/null';
        exec($cmd);
        if (!file_exists($outputFile)) {
            throw new RuntimeException('Extraction failed!');
        }

        return $outputFile;
    }

    public function listPreviews($cr2)
    {
        if (!$this->isReady()) {
            throw new \RuntimeException('Not ready to list previews');
        }
        if (!file_exists($cr2)) {
            throw new InvalidArgumentException('File does not exist: ' . $cr2);
        }

        $output = null;
        $cmd    = escapeshellarg($this->exiv)
            . ' -pp'
            . ' pr '
            . escapeshellarg($cr2);
        exec($cmd, $output);

        return $this->normalizePreviews($output);
    }

    protected function normalizePreviews($previews)
    {
        $rawPreviews = array_map(function ($preview) {
            $regex   = '/Preview (?P<index>[0-9]+): (?P<type>image\/[a-z]+), (?P<width>[0-9]+)x(?P<height>[0-9]+) pixels, (?P<size>[0-9]+) bytes/';
            $matches = array();
            $result  = preg_match($regex, $preview, $matches);
            return $matches;
        }, $previews);

        $previews = array();
        foreach ($rawPreviews as $preview) {
            $preview = array(
                'index'  => (int)$preview['index'],
                'type'   => $preview['type'],
                'height' => (int)$preview['height'],
                'width'  => (int)$preview['width'],
                'size'   => (int)$preview['size'],
            );
            $previews[] = $preview;
        }

        return $previews;
    }

    public function listExifData($cr2, $type = 'raw')
    {
        if (!$this->isReady()) {
            throw new \RuntimeException('Not ready to list previews');
        }
        if (!file_exists($cr2)) {
            throw new InvalidArgumentException('File does not exist: ' . $cr2);
        }

        $output = null;
        $cmd    = escapeshellarg($this->exiv)
            . ' -Pk'
            . ($type === static::EXIF_RAW ? 'v' : 't')
            . ' pr '
            . escapeshellarg($cr2);
        exec($cmd, $output);

        return $this->normalizeExifData($output);
    }

    protected function normalizeExifData($data)
    {
        return array_reduce(
            array_map(
                function ($datum) {
                    $output = explode(' ', $datum, 2);

                    return array($output[0] => isset($output[1]) ? trim($output[1]) : null);
                },
                $data
            ),
            function ($carry, $item) {
                $carry += $item;

                return $carry;
            },
            array()
        );
    }

    public function transferExifData($source, $destination)
    {
        if (!$this->exiftool || !file_exists($this->exiftool) || !is_executable($this->exiftool)) {
            return;
        }
        if (!$this->isReady()) {
            throw new \RuntimeException('Not ready to list previews');
        }
        if (!file_exists($source)) {
            throw new InvalidArgumentException('Source File does not exist: ' . $source);
        }
        if (!file_exists($destination)) {
            throw new InvalidArgumentException('Destination File does not exist: ' . $destination);
        }

        $output = null;
        $cmd    = escapeshellarg($this->exiftool)
            . ' -overwrite_original '
            . ' -tagsFromFile '
            . escapeshellarg($source)
            . ' '
            . escapeshellarg($destination);
        exec($cmd, $output);
    }
}
