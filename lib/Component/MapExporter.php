<?php

namespace Wheregroup\MapExport\CoreBundle\Component;

use Wheregroup\MapExport\CoreBundle\Entity\MapCanvas;

class MapExporter
{
    /**
     * @var RasterRenderer
     */
    protected $RasterRenderer;

    /**
     * @var FeatureRenderer
     */
    protected $FeatureRenderer;

    protected static $instance = null;

    public static function getInstance($RasterRenderer, $FeatureRenderer)
    {
        if (null === self::$instance) {
            self::$instance = new self($RasterRenderer, $FeatureRenderer);
        }
        return self::$instance;
    }

    public function __construct($RasterRenderer, $FeatureRenderer)
    {
        $this->RasterRenderer = $RasterRenderer;
        $this->FeatureRenderer = $FeatureRenderer;
    }

    //Prevents copies of instance
    protected function __clone()
    {
    }

    public function buildMap($data, $angle)
    {
        $width = $data['width'];
        $height = $data['height'];

        //Initialize MapCanvas
        $canvas = new MapCanvas($width, $height, $data['extentwidth'], $data['extentheight'], $data['centerx'],
            $data['centery']);

        //Draw wms layers
        $canvas = $this->RasterRenderer->drawAllLayers($canvas, $data);

        //Draw features
        $canvas = $this->FeatureRenderer->drawAllFeatures($canvas, $data['vectorLayers']);

        //Rotate image back and crop
        if ($angle != 0) {
            $img = $canvas->getImage();
            $this->finishMap($img, $width, $height, $angle);
            $canvas->setImage($img);
        }

        return $canvas;
    }

    /**
     * Calculate minimal bounding box of rotated image
     *
     * @param $width
     * @param $height
     * @param $angle
     * @return array
     */
    private function getBBOfRotatedImg($width, $height, $angle)
    {
        $newWidth = round(abs(sin(deg2rad($angle)) * $height + cos(deg2rad($angle)) * $width));
        $newHeight = round(abs(cos(deg2rad($angle)) * $height + sin(deg2rad($angle)) * $width));
        return array($newWidth, $newHeight);
    }

    /**
     * Crop and rotate the image
     *
     * @param $img
     * @param $width
     * @param $height
     * @param $angle
     * @return resource
     */
    private function finishMap($img, $width, $height, $angle)
    {
        $img = imagerotate($img, $angle, 0);

        //$img->crop($width, $height);

        return $img;
    }


}
