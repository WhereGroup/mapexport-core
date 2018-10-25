<?php

namespace Wheregroup\MapExport\CoreBundle\Entity\PDFElements;


class Overview extends Map
{
    protected function init()
    {
        $this->createOverview();

        parent::init(); // TODO: Change the autogenerated stub
    }

    protected function createOverview()
    {

        //Build new $data array
        $data = array();
        //Imagesize
        //$data['rotation'] = $this->data['rotation'];
        $data['width'] = $this->width;
        $data['height'] = $this->height;

        $data['format'] = 'png';
        $data['quality'] = $this->data['quality'];

        $data['centerx'] = $this->data['center']['x'];
        $data['centery'] = $this->data['center']['y'];

        $data['extentwidth'] = $this->data['extent']['width'] * 2;
        $data['extentheight'] = $this->data['extent']['height'] * 2;


        //TODO Overview Ausschnitt bestimmen
        //$data['extentwidth'] = $this->width * $this->data['overview'][0]['scale'] / 1000;
        //$data['extentheight'] = $this->height * $this->data['overview'][0]['scale'] / 1000;


        //WMS service gets overlay
        $data['requests'] = $this->data['overview'];

        //Map Outline
        $data['vectorLayers'][0] = array(
            'type' => 'GeoJSON+Style',
            'opacity' => 1,
            'geometries' => array(
                array(
                    'type' => 'Polygon',
                    'coordinates' => array(
                        array(
                            array_values($this->data['extent_feature'][0]),
                            array_values($this->data['extent_feature'][1]),
                            array_values($this->data['extent_feature'][2]),
                            array_values($this->data['extent_feature'][3])
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