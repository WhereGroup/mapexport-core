<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Component\FeatureRenderer;
use Wheregroup\MapExport\CoreBundle\Component\HTTPClient;
use Wheregroup\MapExport\CoreBundle\Component\MapExporter;
use Wheregroup\MapExport\CoreBundle\Component\PDFExtensions;
use Wheregroup\MapExport\CoreBundle\Component\RasterRenderer;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Map
{
    /**
     * @var MapExporter
     */
    protected $mapExporter;

    protected $img;
    protected $element;
    protected $pdf;

    public function __construct(PDFExtensions &$pdf, PDFElement $element)
    {
        $this->pdf = $pdf;
        $this->element = $element;

        $this->init();
    }

    public function draw()
    {

        $width = round($this->element->width / 25.4 * $this->element->data['quality']);
        $height = round($this->element->height / 25.4 * $this->element->data['quality']);

        $map = $this->mapExporter->buildMap($this->element->data, $width, $height);
        $this->img = $map->getImage();

        //$this->img = $this->mapExporter->buildMap($this->data, $this->width, $this->height)->getImage();

        //Save image to put it on pdf
        $temp = tempnam('', 'img');
        imagepng($this->img, $temp);
        $this->pdf->Image($temp, $this->element->x, $this->element->y, $this->element->width, $this->element->height, 'png');

        //Remove the saved image
        unlink($temp);

        //Draw frame
        $this->pdf->Rect($this->element->x, $this->element->y, $this->element->width, $this->element->height);

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