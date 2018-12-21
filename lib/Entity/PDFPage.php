<?php

namespace Wheregroup\MapExport\CoreBundle\Entity;


use Exception;
use Wheregroup\MapExport\CoreBundle\Component\PDFExtensions;
use Wheregroup\MapExport\CoreBundle\Component\PDFElementRenderer;

class PDFPage
{
    protected $pdf;
    protected $page;
    protected $data;
    protected $conf;
    protected $templatePath;
    protected $elements = array();
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
            try {
                $this->pdf->setSourceFile($this->templatePath . '.pdf');
            } catch (Exception $e) {
                echo 'Could not open template';
            }
            $page = $this->pdf->importPage($this->page);
            $this->pdf->useTemplate($page);
        }
        $this->pdf->SetAutoPageBreak(false);
    }

    public function addElement(\DOMElement $xml, $style = null)
    {
        $name = $xml->getAttribute('draw:name');
        $x = (float)$xml->getAttribute('svg:x');
        $y = (float)$xml->getAttribute('svg:y');
        $width = (float)$xml->getAttribute('svg:width');
        $height = (float)$xml->getAttribute('svg:height');

        //Adds the new element to list of all drawable elements on page
        array_push($this->elements, new PDFElement($name, $x, $y, $width, $height, $this->data, $style));

    }

    public function forceLegend()
    {
        $style = array(
            'fontSize' => 11,
            'textColor' => array('r' => 0, 'g' => 0, 'b' => 0),
            'bold' => true,
            'italic' => false,
            'underlined' => false
        );

        $mLeft = 0.5;
        $mTop = 1;
        array_push($this->elements,
            new PDFElement('legend', $mLeft, $mTop, $this->pdf->getWidth() / 10 - $mLeft * 2,
                $this->pdf->getHeight() / 10 - $mTop * 2, $this->data, $style));

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