<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Title extends PDFElement
{
    protected $title;

    protected function init()
    {
        $this->title = $this->data['extra']['title'];
    }

    public function draw()
    {
        $this->pdf->SetFont($this->font, $this->fontStyle);
        $this->pdf->SetTextColor($this->textColor['r'], $this->textColor['g'], $this->textColor['b']);
        $this->pdf->SetFontSize($this->fontSize);

        $this->pdf->SetXY($this->x-1, $this->y);
        $this->pdf->Cell($this->width, $this->height, $this->title);
    }
}