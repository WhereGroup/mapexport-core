<?php

namespace Wheregroup\MapExport\CoreBundle\Entity;


use Wheregroup\MapExport\CoreBundle\Component\PDFExtensions;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\TextBox;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Date;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Extent;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Legend;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\LegendImage;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Map;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Northarrow;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Overview;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Scale;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Scalebar;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Title;

class PDFPage
{
    protected $pdf;
    protected $page;
    protected $data;
    protected $conf;
    protected $templatePath;
    protected $elements = array();
    protected $containsLegend = false;
    protected $legendOverflow = null;

    public function __construct(PDFExtensions &$pdf, $data, $conf, $page = null, $templatePath = null)
    {
        $this->pdf = &$pdf;
        $this->data = $data;

        $this->templatePath = $templatePath;
        $this->page = $page;
        $this->conf = $conf;

        $this->init();
    }

    protected function init()
    {
        $this->pdf->AddPage($this->conf['orientation'],
            array($this->conf['pageSize']['width'] * 10, $this->conf['pageSize']['height'] * 10));
        if ($this->templatePath != null) {
            $this->pdf->setSourceFile($this->templatePath . '.pdf');
            $page = $this->pdf->importPage($this->page);
            $this->pdf->useTemplate($page);
        }
        $this->pdf->SetAutoPageBreak(false);
    }

    public function addElement(\DOMElement $xml, $style = null)
    {
        $name = $xml->getAttribute('draw:name');
        $x = substr($xml->getAttribute('svg:x'), 0, -3);
        $y = substr($xml->getAttribute('svg:y'), 0, -3);
        $width = substr($xml->getAttribute('svg:width'), 0, -3);
        $height = substr($xml->getAttribute('svg:height'), 0, -3);

        //Find out if there is a fitting class and add element to list
        switch ($name) {
            case 'northarrow':
                array_push($this->elements, new Northarrow($this->pdf, $x, $y, $width, $height, $this->data));
                break;
            case 'map':
                array_push($this->elements, new Map($this->pdf, $x, $y, $width, $height, $this->data));
                break;
            case 'overview':
                array_push($this->elements, new Overview($this->pdf, $x, $y, $width, $height, $this->data));
                break;
            case 'scalebar':
                array_push($this->elements, new Scalebar($this->pdf, $x, $y, $width, $height, $this->data));
                break;
            case 'date':
                array_push($this->elements, new Date($this->pdf, $x, $y, $width, $height, $this->data, $style));
                break;
            case 'scale':
                array_push($this->elements, new Scale($this->pdf, $x, $y, $width, $height, $this->data, $style));
                break;
            case 'legendpage_image':
                array_push($this->elements, new LegendImage($this->pdf, $x, $y, $width, $height, $this->data));
                break;
            case 'legend':
                //Test if client asks for legend
                if ($data['printLegend'] = 1) {
                    $this->containsLegend = true;

                    $legend = new Legend($this->pdf, $x, $y, $width, $height, $this->data, $style);

                    //print the remaining legend images on separate page
                    $this->legendOverflow = $legend->getRemainingImages();

                    array_push($this->elements, $legend);

                }
                break;
            case 'extent_ll_x':
            case 'extent_ll_y':
            case 'extent_ur_x':
            case 'extent_ur_y':
                array_push($this->elements,
                    new Extent($this->pdf, $x, $y, $width, $height, $this->data, $name, $style));
                break;
            default:
                array_push($this->elements,
                    new TextBox($this->pdf, $x, $y, $width, $height, $this->data, $name, $style));
                break;

            //var_dump($name);
        }


    }

    public function forceLegend($legend = null)
    {
        if ($legend == null) {
            $legend = new Legend($this->pdf, 0.5, 1, $this->pdf->getWidth() / 10, $this->pdf->getHeight() / 10,
                $this->data);
        } else {
            $legend->setPosition(0.5, 1, $this->pdf->getWidth() / 10, $this->pdf->getHeight() / 10);
            $legend->setAllPositions();
        }
        $legend->setStyle('Arial', 11, array('r' => 0, 'g' => 0, 'b' => 0), 'B');

        $this->containsLegend = true;
        $this->legendOverflow = $legend->getRemainingImages();

        array_push($this->elements, $legend);

        //legendPageImage
        $legendImageHeight = 1.5;
        array_push($this->elements,
            new LegendImage($this->pdf, $this->pdf->getWidth() / 10 - $legendImageHeight * 2.5,
                0 + $legendImageHeight * 0.5,
                0, $legendImageHeight, $this->data));

    }

    public function containsLegend()
    {
        return $this->containsLegend;
    }

    public function getLegendOverflow()
    {
        return $this->legendOverflow;
    }

    public function getPDFPage()
    {
        foreach ($this->elements as $element) {
            $element->draw();
        }

        return $this->pdf->Output();
    }

    public function makePDFPage()
    {
        foreach ($this->elements as $element) {
            $element->draw();
        }
    }

    public function getPDF()
    {
        return $this->pdf;
    }
}