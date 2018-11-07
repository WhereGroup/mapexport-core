<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Extent extends PDFElement
{
    protected $scale;
    protected $name;

    public function __construct($pdf, $x, $y, $width, $height, $data, $name, $style = null)
    {
        $this->name = $name;

        parent::__construct($pdf, $x, $y, $width, $height, $data, $style);
    }

    protected function init()
    {
        //$this->getStyle($this->data['template']);
        $this->scale = $this->data['scale_select'];

    }

    private function addCoordinates()
    {
        $pdf = $this->pdf;

        $corrFactor = 2;
        $precision = 2;
        // correction factor and round precision if WGS84
        if($this->data['extent']['width'] < 1){
            $corrFactor = 3;
            $precision = 6;
        }

        switch ($this->name) {
            case ('extent_ur_y'):
                // upper right Y
                $pdf->Text($this->x + $corrFactor, $this->y + 3,
                    round($this->data['extent_feature'][2]['y'], $precision));
                break;
            case ('extent_ur_x'):
                // upper right X
                $pdf->TextWithDirection3($this->x + 1, $this->y,
                    round($this->data['extent_feature'][2]['x'], $precision),'D');
                break;
            case ('extent_ll_y'):
                // lower left Y
                $pdf->Text($this->x, $this->y + 3,
                    round($this->data['extent_feature'][0]['y'], $precision));
                break;
            case ('extent_ll_x'):
                // lower left X
                $pdf->TextWithDirection3($this->x + 3, $this->y + 30,
                    round($this->data['extent_feature'][0]['x'], $precision),'U');
                break;
        }
    }

    public function draw()
    {
        $this->pdf->SetFont($this->font, $this->fontStyle);
        $this->pdf->SetTextColor($this->textColor['r'], $this->textColor['g'], $this->textColor['b']);
        $this->pdf->SetFontSize($this->fontSize);

        $this->addCoordinates();
        //$this->pdf->SetXY($this->x-1, $this->y);
        //$this->pdf->Cell($this->width, $this->height, 'Extent');

    }
}