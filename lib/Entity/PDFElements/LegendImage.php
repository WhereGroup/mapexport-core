<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;


class LegendImage extends PDFElement
{
    //TODO: Path to Image
    const legendPageImage = 'MapbenderPrintBundle/images/legendpage_image.png';

    protected function init()
    {

    }

    public function draw()
    {
        $this->pdf->Image(self::legendPageImage, $this->x, $this->y, 0,  $this->height, 'png');
    }
}
