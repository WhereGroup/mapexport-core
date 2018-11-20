<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;

use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Scale
{
    protected $scale;
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
        //$this->getStyle($this->data['template']);
        $this->scale = $this->element->data['scale_select'];

    }

    public function draw()
    {
        $this->pdf->SetFont($this->element->font, $this->element->fontStyle);
        $this->pdf->SetTextColor($this->element->textColor['r'], $this->element->textColor['g'], $this->element->textColor['b']);
        $this->pdf->SetFontSize($this->element->fontSize);

        $this->pdf->SetXY($this->element->x-1, $this->element->y);
        $this->pdf->Cell($this->element->width, $this->element->height, '1 : '.$this->scale);

    }
}