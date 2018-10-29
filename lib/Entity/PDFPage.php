<?php

namespace Wheregroup\MapExport\CoreBundle\Entity;


use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Comment;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Date;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Extent;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Map;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Northarrow;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Overview;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Scale;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Scalebar;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Title;

class PDFPage
{
    protected $pdf;
    protected $data;

    protected $elements = array();

    public function __construct(&$pdf, $data, $conf)
    {
        $templatePath = './MapbenderPrintBundle/templates/' . $data['template'];

        $pdf->AddPage($conf['orientation'], array($conf['pageSize']['width']*10, $conf['pageSize']['height']*10));
        $pdf->setSourceFile($templatePath . '.pdf');
        $page = $pdf->importPage(1);
        $pdf->useTemplate($page);

        $this->pdf = &$pdf;
        $this->data = $data;
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
            case 'extent_ll_x':
            case 'extent_ll_y':
            case 'extent_ur_x':
            case 'extent_ur_y':
                array_push($this->elements, new Extent($this->pdf, $x, $y, $width, $height, $this->data, $style));
                break;
            default:
                if (strpos( $name, 'comment')!==false){
                    array_push($this->elements, new Comment($this->pdf, $x, $y, $width, $height, $this->data, $name, $style));
                    break;
                }
            //var_dump($name);
        }



    }

    public function getPDFPage()
    {
        foreach ($this->elements as $element) {
            $element->draw();
        }

        return $this->pdf->Output();
    }
}