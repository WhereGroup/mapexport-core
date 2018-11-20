<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Extent
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
        $this->scale = $this->element->data['scale_select'];

    }

    private function addCoordinates()
    {
        $pdf = $this->pdf;

        $corrFactor = 2;
        $precision = 2;
        // correction factor and round precision if WGS84
        if($this->element->data['extent']['width'] < 1){
            $corrFactor = 3;
            $precision = 6;
        }

        switch ($this->element->name) {
            case ('extent_ur_y'):
                // upper right Y
                $pdf->Text($this->element->x + $corrFactor, $this->element->y + 3,
                    round($this->element->data['extent_feature'][2]['y'], $precision));
                break;
            case ('extent_ur_x'):
                // upper right X
                $pdf->TextWithDirection3($this->element->x + 1, $this->element->y,
                    round($this->element->data['extent_feature'][2]['x'], $precision),'D');
                break;
            case ('extent_ll_y'):
                // lower left Y
                $pdf->Text($this->element->x, $this->element->y + 3,
                    round($this->element->data['extent_feature'][0]['y'], $precision));
                break;
            case ('extent_ll_x'):
                // lower left X
                $pdf->TextWithDirection3($this->element->x + 3, $this->element->y + 30,
                    round($this->element->data['extent_feature'][0]['x'], $precision),'U');
                break;
        }
    }

    public function draw()
    {
        $this->pdf->SetFont($this->element->font, $this->element->fontStyle);
        $this->pdf->SetTextColor($this->element->textColor['r'], $this->element->textColor['g'], $this->element->textColor['b']);
        $this->pdf->SetFontSize($this->element->fontSize);

        $this->addCoordinates();

    }
}