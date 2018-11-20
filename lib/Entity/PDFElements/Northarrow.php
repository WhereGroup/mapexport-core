<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Component\PDFExtensions;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Northarrow
{
    //TODO: Path to Image
    const northArrowImage = 'MapbenderPrintBundle/images/northarrow.png';

    protected $rotation;
    protected $pdf;
    protected $element;

    public function __construct(PDFExtensions &$pdf, PDFElement $element)
    {
        $this->pdf = $pdf;
        $this->element = $element;

        $this->init();
    }

    public function draw()
    {
        if ($this->rotation != 0) {
            //If there is a rotation, rotate template first
            $northArrowImage = $this->rotate();
            //Save image to put it on pdf
            imagepng($northArrowImage, 'newNorthArrow.png');
            //Draw image onto pdf
            $this->pdf->Image('newNorthArrow.png', $this->element->x, $this->element->y, $this->element->width, $this->element->height, 'png');
            //Removes the rotated compass rose image
            unlink('newNorthArrow.png');
        } else {
            //If there is no rotation, just draw the image
            $this->pdf->Image(self::northArrowImage, $this->element->x, $this->element->y, $this->element->width, $this->element->height, 'png');
        }

    }

    private function rotate()
    {
        $northArrowImage = imagecreatefrompng(self::northArrowImage);
        if (($this->rotation > 45 && $this->rotation < 135) || ($this->rotation > 225 && $this->rotation < 315)) {
            $height = imagesx($northArrowImage);
            $width = imagesy($northArrowImage);
        } else {
            $width = imagesx($northArrowImage);
            $height = imagesy($northArrowImage);
        }

        //Background of compass rose is NOT transparent. I think it should be. But since it isn't, the background color value of rotateimage has to be the same as the images background color or there would be ugly lines after rotation
        //$pngTransparency = imagecolorallocatealpha($northArrowImage , 0, 0, 0, 127);
        $pngTransparency = imagecolorallocate($northArrowImage, 255, 255, 255);

        $northArrowImage = imagerotate($northArrowImage, $this->rotation, $pngTransparency);
        //After rotation the image changes its size, so we have to crop it back
        $northArrowImage = imagecrop($northArrowImage, array(
            'x' => (imagesx($northArrowImage) - $width) / 2,
            'y' => (imagesy($northArrowImage) - $height) / 2,
            'width' => $width,
            'height' => $height
        ));

        return $northArrowImage;
    }

    protected function init()
    {
        $this->rotation = $this->element->data['rotation'];
    }
}