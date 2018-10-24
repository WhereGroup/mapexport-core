<?php

namespace Wheregroup\MapExport\CoreBundle\Component;

class GeoJSONAdapter
{
    //TODO: Bullshit. Weg mit Arrays, lieber eigene Objekte
    protected $style;
    protected $geometry;

    public function translateToGeoJSON($data)
    {
        $vectorLayers = array();
        //Todo alles Mist, alles neu machen
        if (!is_array($data)) {
            $data = json_decode($data, true);
        }
        //TODO Oh Gott, bloß fixen. Ich lass das jetzt nur zum testen drin
        /*if($data['type']=='GeoJSON+Style')
        {
            $data = $data['geometries'];
        }*/

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