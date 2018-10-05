<?php

namespace Wheregroup\MapExport\CoreBundle\Component;


use Wheregroup\MapExport\CoreBundle\Entity\MapCanvas;

class RasterRenderer
{
    protected $httpClient;

    public function __construct($httpClient)
    {
        $this->httpClient = $httpClient;

    }

    public function drawLayer(MapCanvas $canvas, $layer)
    {
        $img = $canvas->getImage();

        $result = $this->httpClient->open($layer['url']);
        $layerImage = imagecreatefromstring($result->getData());

        $layerImage = imagescale($layerImage, $canvas->getWidth(), $canvas->getHeight());

        imagealphablending($layerImage, false);
        imagesavealpha($layerImage, true);

        imagecopy($img, $layerImage, 0, 0, 0, 0, $canvas->getWidth(), $canvas->getHeight());

        $canvas->setImage($img);

        return $canvas;
    }

}
