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

        //Adds the new element to list of all drawable elements on page
        array_push($this->elements, new PDFElement($name, $x, $y, $width, $height, $this->data, $style));

    }

    /* public function forceLegend($legendElement = null)
     {
         if ($legendElement == null) {
             $legendElement = new PDFElement('legend', 0.5, 1, $this->pdf->getWidth() / 10, $this->pdf->getHeight() / 10, $this->data);
         } else {
             $legendElement->setPosition(0.5, 1, $this->pdf->getWidth() / 10, $this->pdf->getHeight() / 10);
             //TODO Dies war nötig!
             //$legendElement->setAllPositions();
         }
         $legendElement->setStyle('Arial', 11, array('r' => 0, 'g' => 0, 'b' => 0), 'B');

         $this->legendOverflow = $legend->getRemainingImages();

         array_push($this->elements, $legend);

         //legendPageImage
         $legendImageHeight = 1.5;
         array_push($this->elements,
             new LegendPageImage($this->pdf, $this->pdf->getWidth()/10 - $legendImageHeight * 2.5, 0 + $legendImageHeight * 0.5,
                 0, $legendImageHeight, $this->data));

     }*/

    public function forceLegend()
    {
        $style = array(
            'fontSize' => 11,
            'textColor' => array('r' => 0, 'g' => 0, 'b' => 0),
            'bold' => true,
            'italic' => false,
            'underlined' => false
        );

        array_push($this->elements,
            new PDFElement('legend', 0.5, 1, $this->pdf->getWidth() / 10, $this->pdf->getHeight() / 10, $this->data,
                $style));

        //legendPageImage
        $legendImageHeight = 1.5;
        array_push($this->elements,
            new PDFElement('legendpage_image', $this->pdf->getWidth() / 10 - $legendImageHeight * 2.5,
                0 + $legendImageHeight * 0.5,
                0, $legendImageHeight, $this->data, $style));
    }

    public function containsLegend()
    {
        //search elements for legend
        foreach ($this->elements as $element) {
            if ($element->name == 'legend') {
                return true;
            }
        }
        //if there is no legend
        return false;
    }

    public function getLegendOverflow()
    {
        $overflow = $this->legendOverflow;
        return $overflow;
    }

    public function setLegendOverflow($overflow)
    {
        $this->legendOverflow = $overflow;
    }

    public function makePDFPage()
    {
        $pdfElementRenderer = new PDFElementRenderer($this->pdf);
        if (isset($this->legendOverflow)) {
            $pdfElementRenderer->draw($this->elements, $this->legendOverflow);
        } else {
            $pdfElementRenderer->draw($this->elements);
        }
        $overflow = $pdfElementRenderer->getLegendOverflow();
        $this->legendOverflow = $overflow;
    }

    public function getPDF()
    {
        return $this->pdf;
    }
}