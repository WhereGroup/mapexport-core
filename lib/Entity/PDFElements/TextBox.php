<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;

use Wheregroup\MapExport\CoreBundle\Component\PDFExtensions;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class TextBox
{
    protected $comment;
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
        if (array_key_exists($this->element->name, $this->element->data['extra'])) {
            $this->comment = $this->element->data['extra'][$this->element->name];
        } else {
            if (array_key_exists($this->element->name, $this->element->data)) {
                $this->comment = $this->element->data[$this->element->name];
            }
        }

    }

    public function draw()
    {
        $this->pdf->SetFont($this->element->font, $this->element->fontStyle);
        $this->pdf->SetTextColor($this->element->textColor['r'], $this->element->textColor['g'], $this->element->textColor['b']);
        $this->pdf->SetFontSize($this->element->fontSize);

        $this->pdf->SetXY($this->element->x - 1, $this->element->y);
        $this->pdf->Cell($this->element->width, $this->element->height, $this->comment);
    }
}