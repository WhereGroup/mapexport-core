<?php

namespace Wheregroup\MapExport\CoreBundle\Entity;

use Wheregroup\MapExport\CoreBundle\Component\PDFExtensions;

abstract class PDFElement
{
    const scale = 10;

    /**
     * @var PDFExtensions
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
    protected $fontStyle = '';

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
        if ($style['bold']) {
            $this->fontStyle .= 'B';
        }
        if ($style['italic']) {
            $this->fontStyle .= 'I';
        }
        if ($style['underlined']) {
            $this->fontStyle .= 'U';
        }


        $this->init();
    }

    public function setStyle($font = null, $fontSize = null, $textColor = null, $fontStyle = null)
    {
        if ($font) {
            $this->font = $font;
        }
        if ($fontSize) {
            $this->fontSize = $fontSize;
        }
        if ($textColor) {
            $this->textColor = $textColor;
        }
        if ($fontStyle) {
            $this->fontStyle = $fontStyle;
        }

    }

    public function setPosition($x, $y, $width, $height)
    {
        $this->x = $x * self::scale;
        $this->y = $y * self::scale;
        $this->width = $width * self::scale;
        $this->height = $height * self::scale;
    }

    abstract public function draw();

    abstract protected function init();
}