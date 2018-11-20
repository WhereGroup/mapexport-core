<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;


class LegendPageImage
{
    //TODO: Path to Image
    const legendPageImage = 'MapbenderPrintBundle/images/legendpage_image.png';

    protected $pdf;
    protected $element;

    public function __construct(&$pdf, $element)
    {
        $this->pdf = $pdf;
        $this->element = $element;

        $this->init();
    }

    protected function init()
    {

    }

    public function draw()
    {
        $this->pdf->Image(self::legendPageImage, $this->element->x, $this->element->y, 0,  $this->element->height, 'png');
    }
}
