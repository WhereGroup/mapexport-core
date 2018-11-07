<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


use Wheregroup\MapExport\CoreBundle\Component\HTTPClient;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;

class Legend extends PDFElement
{
    protected $legendImages = array();

    protected function init()
    {
        $httpClient = new HTTPClient();

        //get image
        foreach ($this->data['legends'] as $legend) {
            $result = $httpClient->open(current($legend));
            $this->legendImages[key($legend)] = imagecreatefromstring($result->getData());
        }
    }

    public function draw()
    {
        $x = $this->x;
        $y = $this->y;
        $counter = 0;

        foreach ($this->legendImages as $title => $img) {

            $counter++;

            $imageheight = imagesy($img);
            $imagewidth = imagesx($img);

            //test if this legend image is to large to add it below the last image
            if ((($y-$this->y) + round($imageheight * 25.4 / 96) + 10) > ($this->height)) {
                //End of column. Start new one.
                $x += 105;
                $y = 10;
            }
            if (($x-$this->x) + 20 > ($this->width) && $counter < count($this->legendImages)) {
                //End of page. Start new one.
                $this->pdf->addPage('P');
                $x = 5;
                $y = 10;
                //TODO Legend Page Image
                /*if (!empty($this->conf['legendpage_image'])) {
                    $this->addLegendPageImage();
                }*/
            }


            //setup for title
            $this->pdf->SetFont($this->font, $this->fontStyle);
            $this->pdf->SetTextColor($this->textColor['r'], $this->textColor['g'], $this->textColor['b']);
            $this->pdf->SetFontSize($this->fontSize);

            //write title
            $this->pdf->SetXY($x, $y);
            $this->pdf->Cell(0, 0, utf8_decode($title));
            $y += 5;

            $imagepath = 'tempLegend';
            imagepng($img, $imagepath);

            //Add image onto pdf page
            $this->pdf->Image($imagepath, $x, $y, $imagewidth * 25.4 / 96, $imageheight * 25.4 / 96, 'png');

            unlink('tempLegend');

            //Set y to end of image
            $y += round($imageheight * 25.4 / 96) + 10;

        }
    }


}