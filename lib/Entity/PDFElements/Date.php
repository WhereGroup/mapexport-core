<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Date extends PDFElement
{
    protected function init()
    {
        //$this->getStyle($this->data['template']);

    }

    public function draw()
    {
        $this->pdf->SetFont($this->font);
        $this->pdf->SetTextColor($this->textColor['r'], $this->textColor['g'], $this->textColor['b']);
        $this->pdf->SetFontSize($this->fontSize);

        $this->pdf->SetXY($this->x, $this->y);
        $this->pdf->Cell($this->width, $this->height, date('d.m.Y'));

    }
}