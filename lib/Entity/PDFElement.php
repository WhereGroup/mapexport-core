<?php

namespace Wheregroup\MapExport\CoreBundle\Entity;

use FPDF;

abstract class PDFElement
{
    const scale = 10;

    /**
     * @var FPDF
     */
    protected $pdf;

    protected $x;
    protected $y;

    protected $width;
    protected $height;

    protected $data;

    protected $font = 'Arial';
    protected $fontSize = '14pt';
    protected $textColor = array('r' => 0, 'g' => 0, 'b' => 0);

    public function __construct(&$pdf, $x, $y, $width, $height, $data, $style = null)
    {
        $this->pdf = $pdf;

        $this->x = $x * self::scale;
        $this->y = $y * self::scale;

        $this->width = $width * self::scale;
        $this->height = $height * self::scale;

        $this->data = $data;

        //set Style
        if ($style['fontSize'] != null) {
            $this->fontSize = $style['fontSize'];
        }
        if ($style['textColor'] != null) {
            $this->textColor = $style['textColor'];
        }

        $this->init();
    }

    public function setStyle($font, $fontSize, $textColor)
    {
        $this->font = $font;
        $this->fontSize = $fontSize;
        $this->textColor = $textColor;

    }

    public function draw()
    {

    }

    abstract protected function init();
}