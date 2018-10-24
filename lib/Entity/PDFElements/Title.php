<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Title extends PDFElement
{
    protected $title;

    protected function init()
    {
        //$this->getStyle($this->data['template']);
        $this->title = $this->data['extra']['title'];

    }

    protected function getStyle()
    {
        //TODO get stile with new ODG parser
    }

    public function draw()
    {
        $this->pdf->SetFont('Arial');
        $this->pdf->Text($this->x, $this->y, $this->title);

    }
}