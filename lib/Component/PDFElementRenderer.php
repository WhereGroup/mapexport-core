<?php

namespace Wheregroup\MapExport\CoreBundle\Component;


use Wheregroup\MapExport\CoreBundle\Entity\PDFElement;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Northarrow;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Map;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Overview;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Scalebar;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Date;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Scale;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\LegendPageImage;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Legend;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Extent;
use Wheregroup\MapExport\CoreBundle\Entity\PDFElements\Comment;

class PDFElementRenderer
{
    protected $pdf;
    protected $legendOverflow;

    public function __construct(&$pdf)
    {
        $this->pdf = $pdf;
    }

    public function draw($elements, $legendImages = null)
    {
        foreach ($elements as $element) {
            //Find out if there is a fitting class and add element to list
            $name = $element->name;
            switch ($name) {
                case 'northarrow':
                    $object = new Northarrow($this->pdf, $element);
                    break;
                case 'map':
                    $object = new Map($this->pdf, $element);
                    break;
                case 'overview':
                    $object = new Overview($this->pdf, $element);
                    break;
                case 'scalebar':
                    $object = new Scalebar($this->pdf, $element);
                    break;
                case 'date':
                    $object = new Date($this->pdf, $element);
                    break;
                case 'scale':
                    $object = new Scale($this->pdf, $element);
                    break;
                case 'legendpage_image':
                    $object = new LegendPageImage($this->pdf, $element);
                    break;
                case 'legend':
                    //Test if client asks for legend
                    if ($data['printLegend'] = 1) {
                        if(isset($legendImages)) {
                            $object = new Legend($this->pdf, $element, $legendImages);
                        } else  {
                            $object = new Legend($this->pdf, $element);
                        }
                        //print the remaining legend images on separate page
                        $this->legendOverflow = $object->getRemainingImages();
                    }
                    break;
                case 'extent_ll_x':
                case 'extent_ll_y':
                case 'extent_ur_x':
                case 'extent_ur_y':
                    $object = new Extent($this->pdf, $element);
                    break;
                default:
                    if (strpos($name, 'comment') === 0) {
                        $object = new Comment($this->pdf, $element);
                        break;
                    }
            }
            if (isset($object)) {
                $object->draw();
            }
        }
    }

    public function getLegendOverflow()
    {
        $overflow = $this->legendOverflow;
        return $overflow;
    }

}

