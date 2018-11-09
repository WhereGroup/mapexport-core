<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Component\HTTPClient;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Legend extends PDFElement
{
    protected $legendImages = array();
    protected $drawableImages = array();
    protected $remainingImages = array();

    protected function init()
    {
        if (array_key_exists('legends', $this->data)) {
            $httpClient = new HTTPClient();

            //fill legendImages with all images, even if they might not fit
            $index = 0;
            foreach ($this->data['legends'] as $legend) {
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
            $x = $this->legendImages[$index - 1]['x'] - $this->x;
            $y = $this->legendImages[$index - 1]['y'] - $this->y;

            $imageheight = (imagesy($this->legendImages[$index]['img']) * 25.4 / 96) + 10;

            $y += round(imagesy($this->legendImages[$index - 1]['img']) * 25.4 / 96) + 10;

            //test if this legend image is to large to add it below the last image
            if ($y + $imageheight > $this->height) {
                //End of column. Start new one.
                $x += 105;
                $y = 0;
            }
            if (($x) + 20 > ($this->width)) {
                return false;
            }

        }

        return array('x' => $x + $this->x, 'y' => $y + $this->y);
    }

    public function draw()
    {
        foreach ($this->drawableImages as $legendImage) {

            $x = $legendImage['x'];
            $y = $legendImage['y'];

            $imageheight = imagesy($legendImage['img']);
            $imagewidth = imagesx($legendImage['img']);

            //setup for title
            $this->pdf->SetFont($this->font, $this->fontStyle);
            $this->pdf->SetTextColor($this->textColor['r'], $this->textColor['g'], $this->textColor['b']);
            $this->pdf->SetFontSize($this->fontSize);

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
        if ($this->remainingImages != null) {
            $newLegend = clone $this;

            $newLegend->legendImages = $newLegend->remainingImages;
            $newLegend->drawableImages = null;
            $newLegend->remainingImages = null;
            //$newLegend->setAllPositions();

            return $newLegend;
        } else {
            return null;
        }
    }


}