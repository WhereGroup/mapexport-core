<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Component\FeatureRenderer;
use Wheregroup\MapExport\CoreBundle\Component\GeoJSONAdapter;
use Wheregroup\MapExport\CoreBundle\Component\HTTPClient;
use Wheregroup\MapExport\CoreBundle\Component\MapExporter;
use Wheregroup\MapExport\CoreBundle\Component\RasterRenderer;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Map extends PDFElement
{
    /**
     * @var MapExporter
     */
    protected $mapExporter;

    protected $img;

    public function draw()
    {

        $width = round($this->width / 25.4 * $this->data['quality']);
        $height = round($this->height / 25.4 * $this->data['quality']);

        $map = $this->mapExporter->buildMap($this->data, $width, $height);
        $this->img = $map->getImage();

        //$this->img = $this->mapExporter->buildMap($this->data, $this->width, $this->height)->getImage();

        //Save image to put it on pdf
        //TODO Darf ich das?
        $temp = tempnam('', 'img');
        imagepng($this->img, $temp);
        $this->pdf->Image($temp, $this->x, $this->y, $this->width, $this->height, 'png');

        //Remove the saved image
        unlink($temp);

        //Draw frame
        $this->pdf->Rect($this->x, $this->y, $this->width, $this->height);

    }


    protected function init()
    {
        if ($this->mapExporter == null) {
            $httpClient = new HTTPClient();
            $rasterRenderer = new RasterRenderer($httpClient);
            $featureRenderer = new FeatureRenderer();
            $this->mapExporter = new MapExporter($rasterRenderer, $featureRenderer);
        }

    }
}