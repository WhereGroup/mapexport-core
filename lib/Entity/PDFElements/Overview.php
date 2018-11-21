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
        //Build new MapData
        $data = new MapData();
        $data->setWidth($this->element->width);
        $data->setHeight($this->element->height);

        $data->setFormat('png');
        $data->setQuality($this->element->data->getQuality());

        $data->setCenterX($this->element->data->getCenterX());
        $data->setCenterY($this->element->data->getCenterY());

        $overview = $this->element->data->getOverview();
        $data->setExtentWidth($this->element->width * $overview[0]['scale'] / 1000);
        $data->setExtentHeight($this->element->height * $overview[0]['scale'] / 1000);

        $data->setLayers($overview);

        $extentFeature =  $this->element->data->getExtentFeature();
        $features[0] = array(
            'type' => 'GeoJSON+Style',
            'opacity' => 1,
            'geometries' => array(
                array(
                    'type' => 'Polygon',
                    'coordinates' => array(
                        array(
                            array_values($extentFeature[0]),
                            array_values($extentFeature[1]),
                            array_values($extentFeature[2]),
                            array_values($extentFeature[3])
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
        $data->setFeatures($features);

        $this->element->setData($data);

    }
}