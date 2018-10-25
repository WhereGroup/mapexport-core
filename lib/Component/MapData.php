<?php

namespace Wheregroup\MapExport\CoreBundle\Component;


class MapData
{
    //These values only exist in PrintRequest
    protected $template;

    protected $title;
    protected $scale;

    protected $rotation;
    protected $scale_select;

    protected $quality;

    protected $extent_feature;

    protected $overview;


    //These values exist in similar form in PrintRequest and ImageExportRequest
    protected $centerX;

    protected $centerY;
    protected $extentWidth;

    protected $extentHeight;
    protected $layers = array();

    protected $features = array();

    //Values that only exist in ImageExportRequest
    protected $width;
    protected $height;

    protected $format;

    public function __construct()
    {

    }

    public function fillFromGeoJSON($data)
    {
        //Print request and image export request are not the same. If there is a template, decode json as print request
        if (isset($data['template'])) {
            $this->template = $data['template'];

            $this->title = $data['extra']['title'];
            $this->scale = $data['scale_select'];

            $this->quality = $data['quality'];

            $this->rotation = $data['rotation'];

            $this->extent_feature = $data['extent_feature'];

            //TODO: Vielleicht overview in eigener MapData?
            $this->overview = $data['overview'];

            //Values that exist in PrintRequest and in ImageExportRequest
            $this->centerX = $data['center']['x'];
            $this->centerY = $data['center']['y'];

            $this->extentWidth = $data['extent']['width'];
            $this->extentHeight = $data['extent']['height'];

            foreach ($data['layers'] as $layer) {
                if ($layer['type'] == 'wms') {
                    array_push($this->layers, $layer);
                }
                if ($layer['type'] == 'GeoJSON+Style') {
                    $this->features += $this->arrangeFeatures($layer);
                }
            }

        } else {
            $this->centerX = $data['centerx'];
            $this->centerY = $data['centery'];

            $this->extentWidth = $data['extentwidth'];
            $this->extentHeight = $data['extentheight'];

            //Values that only exist in ImageExportRequest
            $this->width = $data['width'];
            $this->height = $data['height'];

            $this->layers = $data['requests'];

            foreach ($data['vectorLayers'] as $layer) {
                $this->features += $this->arrangeFeatures($layer);
            }

            $this->format = $data['format'];
        }
    }

    protected function arrangeFeatures($layer)
    {
        if (!is_array($layer)) {
            $layer = json_decode($layer, true);
        }

        $vectorLayer = array();

        foreach ($layer['geometries'] as $geometry) {
            array_push($vectorLayer, array(
                'type' => $layer["type"],
                'geometry' => array('type' => $geometry['type'], 'coordinates' => $geometry['coordinates']),
                'properties' => $geometry['style']
            ));
        }

        return $vectorLayer;
    }

    /*
     * Getters
     */

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * @return mixed
     */
    public function getRotation()
    {
        return $this->rotation;
    }

    /**
     * @return mixed
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @return mixed
     */
    public function getExtentFeature()
    {
        return $this->extent_feature;
    }

    /**
     * @return mixed
     */
    public function getOverview()
    {
        return $this->overview;
    }

    /**
     * @return mixed
     */
    public function getCenterX()
    {
        return $this->centerX;
    }

    /**
     * @return mixed
     */
    public function getCenterY()
    {
        return $this->centerY;
    }

    /**
     * @return mixed
     */
    public function getExtentWidth()
    {
        return $this->extentWidth;
    }

    /**
     * @return mixed
     */
    public function getExtentHeight()
    {
        return $this->extentHeight;
    }

    /**
     * @return mixed
     */
    public function getLayers()
    {
        return $this->layers;
    }

    /**
     * @return mixed
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }


}