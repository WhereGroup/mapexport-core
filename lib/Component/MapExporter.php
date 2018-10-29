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

    public function __construct($RasterRenderer, $FeatureRenderer)
    {
        $this->RasterRenderer = $RasterRenderer;
        $this->FeatureRenderer = $FeatureRenderer;
    }

    public function buildMap($data, $width = null, $height = null)
    {
        $mapData = new MapData();
        $mapData->fillFromGeoJSON($data);

        if ($width == null) {
            $width = $mapData->getWidth();
        }
        if ($height == null) {
            $height = $mapData->getHeight();
        }

        if ($mapData->getRotation() != null) {
            $angle = $mapData->getRotation();
        } else {
            $angle = 0;
        }

        $extentheight = $mapData->getExtentHeight();

        //If aspect ratio of bounding box and image don't match, make bounding box wider
        $extentwidth = $extentheight * ($width / $height);

        $centerx = $mapData->getCenterX();
        $centery = $mapData->getCenterY();

        //Set scale for resizing rotated image
        $widthScale = 1;
        $heightScale = 1;
        if($angle != 0){
            $scaleArray = $this->getBBOfRotatedImg($extentwidth, $extentheight, $angle);
            $widthScale = $scaleArray[0];
            $heightScale = $scaleArray[1];
        }

        $location = array(
            'extentwidth' => $extentwidth*$widthScale,
            'extentheight' => $extentheight*$heightScale,
            'centerx' => $centerx,
            'centery' => $centery
        );

        //Initialize MapCanvas
        $canvas = new MapCanvas(round($width*$widthScale), round($height*$heightScale), $extentwidth*$widthScale, $extentheight*$heightScale, $centerx, $centery);

        //Draw wms layers
        $requests = $mapData->getLayers();
        $canvas = $this->RasterRenderer->drawAllLayers($canvas, $requests, $location, round($width*$widthScale), round($height*$heightScale));

        //Draw features
        $features = $mapData->getFeatures();
        if (isset($features)) {
            $canvas = $this->FeatureRenderer->drawAllFeatures($canvas, $features);
        }

        //Rotate image back and crop
        if ($angle != 0) {
            $img = $canvas->getImage();
            $img = $this->rotateMap($img, $width, $height, $angle);
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
        $newWidth = round(abs(sin(deg2rad($angle)) * $height) + abs(cos(deg2rad($angle)) * $width));
        $newHeight = round(abs(cos(deg2rad($angle)) * $height) + abs(sin(deg2rad($angle)) * $width));
        return array($newWidth/$width, $newHeight/$height);
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
    private function rotateMap($img, $width, $height, $angle)
    {
        //rotate image
        $img = imagerotate($img, $angle, 0);

        //crop image
        $img = imagecrop($img, array(
            'x' => (imagesx($img) - $width) / 2,
            'y' => (imagesy($img) - $height) / 2,
            'width' => $width,
            'height' => $height
        ));
        return $img;
    }


}
