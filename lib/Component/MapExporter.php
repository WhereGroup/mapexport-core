<?php

namespace Wheregroup\PrintBundle\Component;

use Wheregroup\PrintBundle\Entity\Image;

class MapRenderer
{

    protected static $instance = null;

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __construct()
    {
    }

    //Prevents copies of instance
    protected function __clone()
    {
    }

    public function buildMap($data, $width, $height, $angle)
    {
        $httpClient = new HTTPClient();

        //Initialize image with possibly larger size to make up for space lost through rotation
        $img = $this->initMap($width, $height, $angle);


        $this->drawLayers($img, $data, $httpClient);

        $featureRenderer = FeatureRenderer::getInstance();
        $worldformat = $featureRenderer->getWorldFormat($data['centerx'], $data['centery'], $data['extentwidth'],
            $data['extentheight'], $data['width'], $data['height']);
        $img = $featureRenderer->renderAll($img, $data['vectorLayers'], $worldformat);

        //Rotate image back and crop
        if ($angle != 0) {
            $this->finishMap($img, $width, $height, $angle);
        }

        return $img->getImage();

    }

    /**
     * @param Image $img
     * @param $data
     * @param $httpClient
     */
    private function drawLayers($img, $data, $httpClient)
    {
        $layers = $data['requests'];

        foreach ($layers as $layer) {
            $result = $httpClient->open($layer['url']);
            $img->addLayer(imagecreatefromstring($result->getData()));
        }
    }

    private function getBBOfRotatedImg($width, $height, $angle)
    {
        $newWidth = round(abs(sin(deg2rad($angle)) * $height + cos(deg2rad($angle)) * $width));
        $newHeight = round(abs(cos(deg2rad($angle)) * $height + sin(deg2rad($angle)) * $width));
        return array($newWidth, $newHeight);
    }

    private function initMap($width, $height, $angle)
    {
        /*if ($angle != 0) {
            $newBB = $this->getBBOfRotatedImg($width, $height, $angle);
            $width = $newBB[0];
            $height = $newBB[1];
        }*/
        $img = new Image($width, $height);
        return $img;

    }

    /**
     * @param Image $img
     * @param $width
     * @param $height
     * @param $angle
     */
    private function finishMap($img, $width, $height, $angle)
    {
        $img->rotate(-1 * $angle);
        //$img->crop($width, $height);
    }


}
