<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;

use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Scalebar extends PDFElement
{
    protected $scale;

    public function draw()
    {
        $this->pdf->SetLineWidth(0.1);
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->SetFillColor(0, 0, 0);
        $this->pdf->SetFont('arial', '', 10);

        $length = 0.01 * $this->scale * 5;
        $suffix = 'm';

        $sbHeight = 2;
        $sbWidth = 50;

        $this->pdf->Text($this->x - 1, $this->y - 1, '0');
        $this->pdf->Text($this->x + $sbWidth - 4, $this->y - 1, $length . '' . $suffix);

        //Draw black part of scalebar
        $this->pdf->SetFillColor(0, 0, 0);
        $this->pdf->Rect($this->x, $this->y, $sbWidth, $sbHeight, 'FD');

        //Draw white parts of scalebar
        $partWidth = round($sbWidth / 5);
        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->Rect($this->x + $partWidth, $this->y, $partWidth, $sbHeight, 'FD');
        $this->pdf->Rect($this->x + $partWidth * 3, $this->y, $partWidth, $sbHeight, 'FD');

    }

    protected function init()
    {
        $this->scale = $this->data['scale_select'];
    }

}