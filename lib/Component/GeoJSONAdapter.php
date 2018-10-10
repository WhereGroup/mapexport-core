<?php

namespace Wheregroup\MapExport\CoreBundle\Component;

class GeoJSONAdapter
{

    protected $style;
    protected $geometry;

    public function translateToGeoJSON($data)
    {
        $vectorLayers = array();

        $data = json_decode($data, true);


        foreach ($data['geometries'] as $geometry) {
            array_push($vectorLayers, array(
                'type' => $data["type"],
                'geometry' => array('type' => $geometry['type'], 'coordinates' => $geometry['coordinates']),
                'properties' => $geometry['style']
            ));
        }

        return $vectorLayers;
    }

}