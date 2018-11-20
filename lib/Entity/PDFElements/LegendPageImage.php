<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Component\PDFExtensions;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;


class LegendPageImage
{
    //TODO: Path to Image
    const legendPageImage = 'MapbenderPrintBundle/images/legendpage_image.png';

    protected $pdf;
    protected $element;

    public function __construct(PDFExtensions &$pdf, PDFElement $element)
    {
        $this->pdf = $pdf;
        $this->element = $element;
    }

    public function draw()
    {
        $this->pdf->Image(self::legendPageImage, $this->element->x, $this->element->y, 0,  $this->element->height, 'png');
    }
}
