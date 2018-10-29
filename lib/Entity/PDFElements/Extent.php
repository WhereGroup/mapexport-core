<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Extent extends PDFElement
{
    protected $scale;

    protected function init()
    {
        //$this->getStyle($this->data['template']);
        $this->scale = $this->data['scale_select'];

    }

    public function draw()
    {
        $this->pdf->SetFont($this->font);
        $this->pdf->SetTextColor($this->textColor['r'], $this->textColor['g'], $this->textColor['b']);
        $this->pdf->SetFontSize($this->fontSize);

        $this->pdf->SetXY($this->x-1, $this->y);
        //$this->pdf->Cell($this->width, $this->height, 'Extent');

    }
}