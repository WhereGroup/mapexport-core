<?php

namespace Wheregroup\MapExport\CoreBundle\Entity;

use Wheregroup\MapExport\CoreBundle\Component\PDFExtensions;
use Wheregroup\MapExport\CoreBundle\Component\MapData;

class PDFElement
{
    const scale = 10;

    public $name;

    public $x;
    public $y;

    public $width;
    public $height;

    public $data;

    public $font = 'Arial';
    public $fontSize = '14pt';
    public $textColor = array('r' => 0, 'g' => 0, 'b' => 0);
    public $fontStyle = '';

    public function __construct($name, $x, $y, $width, $height, MapData $data, $style = null)
    {
        $this->name = $name;

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

    protected function adaptValues(PDFElement $element)
    {
        $this->x = $element->getX();
        $this->y = $element->getY();
        $this->width = $element->getWidth();
        $this->height = $element->getHeight();
        $this->data = $element->getData();
        $this->font = $element->getFont();
        $this->fontSize = $element->getFontSize();
        $this->textColor = $element->getTextColor();
        $this->fontStyle = $element->getFontStyle();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getX()
    {
        return $this->x;
    }

    public function getY()
    {
        return $this->y;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getFont()
    {
        return $this->font;
    }

    public function getFontSize()
    {
        return $this->fontSize;
    }

    public function getFontStyle()
    {
        return $this->fontStyle;
    }

    public function getTextColor()
    {
        return $this->textColor;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

}