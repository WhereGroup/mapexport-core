<?php

namespace Wheregroup\MapExport\CoreBundle\Entity;


use Wheregroup\MapExport\CoreBundle\Component\PDF_Extensions;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Comment;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Date;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Extent;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Legend;
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

    public function __construct(PDF_Extensions &$pdf, $data, $conf, $page = null, $templatePath = null)
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
        $x = $xml->getAttribute('svg:x');
        $y = $xml->getAttribute('svg:y');
        $width = $xml->getAttribute('svg:width');
        $height = $xml->getAttribute('svg:height');

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
            case 'title':
                array_push($this->elements, new Title($this->pdf, $x, $y, $width, $height, $this->data, $style));
                break;
            case 'scale':
                array_push($this->elements, new Scale($this->pdf, $x, $y, $width, $height, $this->data, $style));
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
                if (strpos($name, 'comment') === 0) {
                    array_push($this->elements,
                        new Comment($this->pdf, $x, $y, $width, $height, $this->data, $name, $style));
                    break;
                }
                //TODO Überprüfen ob das funktioniert
                if (strpos($name, 'dynamic_image') === 0) {
                    array_push($this->elements,
                        new DynamicText($this->pdf, $x, $y, $width, $height, $this->data, $name, $style));
                    break;
                }
                if (strpos($name, 'dynamic_text') === 0) {
                    array_push($this->elements,
                        new DynamicImage($this->pdf, $x, $y, $width, $height, $this->data, $name, $style));
                    break;
                }
            //var_dump($name);
        }


    }

    public function forceLegend($legend = null)
    {
        if ($legend == null) {
            $legend = new Legend($this->pdf, 0.5, 1, $this->pdf->getWidth() / 10, $this->pdf->getHeight() / 10,
                $this->data);
            $legend->setStyle('Arial', 11, null, 'B');
        } else {
            $legend->setPosition(0.5,1, $this->pdf->getWidth() / 10, $this->pdf->getHeight() / 10);
            $legend->setAllPositions();
        }

        $this->containsLegend = true;
        $this->legendOverflow = $legend->getRemainingImages();

        array_push($this->elements, $legend);

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