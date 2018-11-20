<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


class Overview extends Map
{
    protected function init()
    {
        $this->createOverview();

        parent::init();
    }

    protected function createOverview()
    {

        //Build new $data array
        $data = array();
        //Imagesize
        $data['width'] = $this->element->width;
        $data['height'] = $this->element->height;

        $data['format'] = 'png';
        $data['quality'] = $this->element->data['quality'];

        $data['centerx'] = $this->element->data['center']['x'];
        $data['centery'] = $this->element->data['center']['y'];

        //Get extent of overview bounding box
        $data['extentwidth'] = $this->element->width * $this->element->data['overview'][0]['scale'] / 1000;
        $data['extentheight'] = $this->element->height * $this->element->data['overview'][0]['scale'] / 1000;


        //WMS service gets overlay
        $data['requests'] = $this->element->data['overview'];

        //Map Outline
        $data['vectorLayers'][0] = array(
            'type' => 'GeoJSON+Style',
            'opacity' => 1,
            'geometries' => array(
                array(
                    'type' => 'Polygon',
                    'coordinates' => array(
                        array(
                            array_values($this->element->data['extent_feature'][0]),
                            array_values($this->element->data['extent_feature'][1]),
                            array_values($this->element->data['extent_feature'][2]),
                            array_values($this->element->data['extent_feature'][3])
                        )
                    ),
                    'style' => array(
                        "fillColor" => "#ffffff",
                        "fillOpacity" => 0,
                        "strokeColor" => "#ff0000",
                        "strokeOpacity" => 1,
                        "strokeWidth" => 1
                    )
                )
            )
        );

        $this->data = $data;
    }
}