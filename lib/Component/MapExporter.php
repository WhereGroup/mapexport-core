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

    public function buildMap($data, $width = null, $height = null)
    {
        if ($width == null && isset($data['width'])) {
            $width = $data['width'];
        }

        if ($height == null && isset($data['height'])) {
            $height = $data['height'];
        }

        if (isset($data['rotation'])) {
            $angle = $data['rotation'];
        } else {
            $angle = 0;
        }

        if (isset($data['extentheight'])) {
            $extentheight = $data['extentheight'];
        } else {
            $extentheight = $data['extent']['height'];
        }

        //If aspect ratio of bounding box and image don't match, make bounding box wider
        $extentwidth = $extentheight * ($width / $height);

        /*if (isset($data['extentwidth'])) {
            $extentwidth = $data['extentwidth'];
        } else {
            $extentwidth = $data['extent']['width'];
        }*/

        if (isset($data['centerx'])) {
            $centerx = $data['centerx'];
        } else {
            $centerx = $data['center']['x'];
        }

        if (isset($data['centery'])) {
            $centery = $data['centery'];
        } else {
            $centery = $data['center']['y'];
        }

        $location = array(
            'width' => $width,
            'height' => $height,
            'extentwidth' => $extentwidth,
            'extentheight' => $extentheight,
            'centerx' => $centerx,
            'centery' => $centery
        );

        //Initialize MapCanvas
        $canvas = new MapCanvas($width, $height, $extentwidth, $extentheight, $centerx, $centery);

        if (isset($data['requests']) || array_key_exists('requests', $data)) {
            $requests = $data['requests'];
        } elseif (isset($data['layers']) || array_key_exists('layers', $data)) {
            //Separate wms requests from features
            $requests = array();
            foreach ($data['layers'] as $layer) {
                if ($layer['type'] == 'wms') {
                    array_push($requests, $layer);
                }
            }
        } else {
            $requests = null;
        }

        //Draw wms layers
        $canvas = $this->RasterRenderer->drawAllLayers($canvas, $requests, $location, $width, $height);

        //Get features from $data
        if (isset($data['vectorLayers']) || array_key_exists('vectorLayers', $data)) {
            $features = $data['vectorLayers'];
        } elseif (isset($data['layers']) || array_key_exists('layers', $data)) {
            //Separate features from wms requests
            $features = array();
            foreach ($data['layers'] as $layer) {
                if ($layer['type'] == 'GeoJSON+Style') {
                    array_push($features, $layer);
                }
            }

            //$features = $data['layers'];
        }

        //Draw features
        if (isset($features)) {
            $canvas = $this->FeatureRenderer->drawAllFeatures($canvas, $features);
        }

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