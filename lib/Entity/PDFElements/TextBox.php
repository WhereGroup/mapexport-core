<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;

use Wheregroup\MapExport\CoreBundle\Component\PDFExtensions;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class TextBox
{
    protected $content;
    protected $pdf;
    protected $element;

    public function __construct(PDFExtensions &$pdf, PDFElement $element)
    {
        $this->pdf = $pdf;
        $this->element = $element;

        $this->init();
    }

    protected function init()
    {
        $this->content = $this->element->data->getFromExtra($this->element->name);
    }

    public function draw()
    {
        $this->pdf->SetFont($this->element->font, $this->element->fontStyle);
        $this->pdf->SetTextColor($this->element->textColor['r'], $this->element->textColor['g'],
            $this->element->textColor['b']);
        $this->pdf->SetFontSize($this->element->fontSize);

        $this->pdf->SetXY($this->element->x - 1, $this->element->y);
        $this->pdf->Cell($this->element->width, $this->element->height, utf8_decode($this->content));
    }
}