<?php
namespace SVG\Nodes\Structures;

use \RuntimeException;

class SVGFont extends SVGStyle
{
    private $name;

    private $path;

    public function __construct($name, $path, $embed = false, $mimeType = null)
    {
        parent::__construct(
            sprintf(
                "@font-face {font-family: %s; src:url('%s');}",
                $name,
                self::resolveFontUrl($path, $embed, $mimeType)
            )
        );

        $this->name = $name;
        $this->path = $path;
    }

    public function getFontPath()
    {
        return $this->path;
    }

    public function getFontName()
    {
        return $this->name;
    }

    private static function resolveFontUrl($path, $embed, $mimeType)
    {
        if (!$embed) {
            return $path;
        }

        $data = file_get_contents($path);
        if ($data === false) {
            throw new RuntimeException('Font file "' . $path . '" could not be read.');
        }

        return sprintf('data:%s;base64,%s', $mimeType, base64_encode($data));
    }
}
