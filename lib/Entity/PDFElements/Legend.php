<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Component\HTTPClient;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Legend
{
    protected $legendImages = array();
    protected $drawableImages = array();
    protected $remainingImages = array();
    protected $pdf;
    protected $element;

    public function __construct(&$pdf, $element, $legendImages = null)
    {
        $this->pdf = $pdf;
        $this->element = $element;

        if ($legendImages == null) {
            $this->init();
        } else {
            $this->legendImages = $legendImages;
            $this->setAllPositions();
        }
    }

    protected function init()
    {
        if (array_key_exists('legends', $this->element->data)) {
            $httpClient = new HTTPClient();

            //fill legendImages with all images, even if they might not fit
            $index = 0;
            foreach ($this->element->data['legends'] as $legend) {
                $result = $httpClient->open(current($legend));
                $this->legendImages[$index]['title'] = key($legend);
                $this->legendImages[$index]['img'] = imagecreatefromstring($result->getData());
                $index++;
            }

            //set positions of every image and remove those that don't fit
            $this->setAllPositions();
        }
    }

    public function setAllPositions()
    {
        $index = count($this->legendImages);

        //set positions of every image and remove those that don't fit
        for ($i = 0; $i < $index; $i++) {
            $position = $this->getPosition($i);
            if ($position) {
                $this->legendImages[$i] += $position;
            } else {
                //move all images that can not be drawn to remainingImages
                $this->remainingImages = array_slice($this->legendImages, $i);

                $this->drawableImages = array_slice($this->legendImages, 0, $i);
                break;
            }

            $this->drawableImages = $this->legendImages;

        }
    }

    protected function getPosition($index)
    {
        if ($index == 0) {
            $x = 0;
            $y = 0;
        } else {
            $x = $this->legendImages[$index - 1]['x'] - $this->element->x;
            $y = $this->legendImages[$index - 1]['y'] - $this->element->y;

            $imageheight = (imagesy($this->legendImages[$index]['img']) * 25.4 / 96) + 10;

            $y += round(imagesy($this->legendImages[$index - 1]['img']) * 25.4 / 96) + 10;

            //test if this legend image is to large to add it below the last image
            if ($y + $imageheight > $this->element->height) {
                //End of column. Start new one.
                $x += 105;
                $y = 0;
            }
            if (($x) + 20 > ($this->element->width)) {
                return false;
            }

        }

        return array('x' => $x + $this->element->x, 'y' => $y + $this->element->y);
    }

    public function draw()
    {
        foreach ($this->drawableImages as $legendImage) {

            $x = $legendImage['x'];
            $y = $legendImage['y'];

            $imageheight = imagesy($legendImage['img']);
            $imagewidth = imagesx($legendImage['img']);

            //setup for title
            $this->pdf->SetFont($this->element->font, $this->element->fontStyle);
            $this->pdf->SetTextColor($this->element->textColor['r'], $this->element->textColor['g'],
                $this->element->textColor['b']);
            $this->pdf->SetFontSize($this->element->fontSize);

            //write title
            $this->pdf->SetXY($x, $y);
            $this->pdf->Cell(0, 0, utf8_decode($legendImage['title']));
            $y += 5;

            $imagepath = 'tempLegend';
            imagepng($legendImage['img'], $imagepath);

            //Add image onto pdf page
            $this->pdf->Image($imagepath, $x, $y, $imagewidth * 25.4 / 96, $imageheight * 25.4 / 96, 'png');

            unlink('tempLegend');

        }
    }

    public function getRemainingImages()
    {
        return $this->remainingImages;
    }


}