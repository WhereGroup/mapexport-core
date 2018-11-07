<?php

namespace Wheregroup\MapExport\CoreBundle\Entity;


use Wheregroup\MapExport\CoreBundle\Component\PDF_Extensions;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Legend;

class LegendPage extends PDFPage
{
    /**
     * @var Legend
     */
    protected $legend;

    protected function init()
    {
        $this->pdf->addPage();

        $this->pdf->SetAutoPageBreak(false);

        $legend = new Legend($this->pdf, 0.5, 1, $this->pdf->getPageWidth()/10, $this->pdf->getPageHeight()/10, $this->data);
        $legend->setStyle('Arial', 11, null, 'B');

        array_push($this->elements, $legend);
    }

    public function setLegend($legend){
        $this->legend = $legend;
    }
}