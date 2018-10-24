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

    public function __construct(&$pdf, $x, $y, $width, $height, $data)
    {
        $this->pdf = $pdf;

        $this->x = $x * self::scale;
        $this->y = $y * self::scale;

        $this->width = $width * self::scale;
        $this->height = $height * self::scale;

        $this->data = $data;

        $this->init();
    }

    public function draw()
    {

    }

    abstract protected function init();
}