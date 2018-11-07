<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;

use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Comment extends PDFElement
{
    protected $comment;
    protected $commentID;

    public function __construct($pdf, $x, $y, $width, $height, $data, $commentID, $style = null)
    {
        $this->commentID = $commentID;
        parent::__construct($pdf, $x, $y, $width, $height, $data, $style);
    }

    protected function init()
    {
        if (array_key_exists($this->commentID, $this->data['extra'])) {
            $this->comment = $this->data['extra'][$this->commentID];
        }

    }

    public function draw()
    {
        $this->pdf->SetFont($this->font, $this->fontStyle);
        $this->pdf->SetTextColor($this->textColor['r'], $this->textColor['g'], $this->textColor['b']);
        $this->pdf->SetFontSize($this->fontSize);

        $this->pdf->SetXY($this->x - 1, $this->y);
        $this->pdf->Cell($this->width, $this->height, $this->comment);
    }
}