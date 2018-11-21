<?php

namespace Wheregroup\MapExport\CoreBundle\Component;


class MapData
{
    //These values only exist in PrintRequest
    protected $template;

    protected $scale;
    protected $rotation;
    protected $quality;
    protected $extent_feature;

    protected $printLegend = false;

    protected $overview;

    protected $legends;


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

    protected $extra = array();

    public function __construct()
    {

    }

    public function fillFromGeoJSONbkk($data)
    {
        //Print request and image export request are not the same. If there is a template, decode json as print request
        if (isset($data['template'])) {
            //$this->template = $data['template'];

            //$this->title = $data['extra']['title'];
            //$this->scale = $data['scale_select'];

            //$this->quality = $data['quality'];

            //$this->rotation = $data['rotation'];

            //$this->extent_feature = $data['extent_feature'];

            //TODO: Vielleicht overview in eigener MapData?
            $this->overview = $data['overview'];

            /*if (array_key_exists('printLegend', $data)) {
                $this->printLegend = $data['printLegend'];
            }*/

            //Values that exist in PrintRequest and in ImageExportRequest
            //$this->centerX = $data['center']['x'];
            //$this->centerY = $data['center']['y'];

            //$this->extentWidth = $data['extent']['width'];
            //$this->extentHeight = $data['extent']['height'];

            /*foreach ($data['layers'] as $layer) {
                if ($layer['type'] == 'wms') {
                    array_push($this->layers, $layer);
                }
                if ($layer['type'] == 'GeoJSON+Style') {
                    $this->features += $this->arrangeFeatures($layer);
                }
            }*/

        } else {
            //$this->centerX = $data['centerx'];
            //$this->centerY = $data['centery'];

            //$this->extentWidth = $data['extentwidth'];
            //$this->extentHeight = $data['extentheight'];

            //Values that only exist in ImageExportRequest
            //$this->width = $data['width'];
            //$this->height = $data['height'];

            //$this->layers = $data['requests'];

            /*foreach ($data['vectorLayers'] as $layer) {
                $this->features += $this->arrangeFeatures($layer);
            }*/

            //$this->format = $data['format'];
        }
    }

    public function fillFromGeoJSON($data)
    {
        foreach ($data as $key => $mapElement) {
            switch ($key) {
                case('template'):
                    $this->template = $mapElement;
                    break;
                case('scale'):
                case('scale_select'):
                    $this->scale = $mapElement;
                    break;
                case('quality'):
                    $this->quality = $mapElement;
                    break;
                case('centerx'):
                    $this->centerX = $mapElement;
                    break;
                case('centery'):
                    $this->centerY = $mapElement;
                    break;
                case('center'):
                    $this->centerX = $mapElement['x'];
                    $this->centerY = $mapElement['y'];
                    break;
                case('width'):
                    $this->width = $mapElement;
                    break;
                case('height'):
                    $this->height = $mapElement;
                    break;
                case('extent'):
                    $this->extentWidth = $mapElement['width'];
                    $this->extentHeight = $mapElement['height'];
                    break;
                case('extentwidth'):
                    $this->extentWidth = $mapElement;
                    break;
                case('extentheight'):
                    $this->extentHeight = $mapElement;
                    break;
                case('rotation'):
                    $this->rotation = $mapElement;
                    break;
                case('extent_feature'):
                    $this->extent_feature = $mapElement;
                    break;
                case('layers'):
                    foreach ($mapElement as $layer) {
                        if ($layer['type'] == 'wms') {
                            array_push($this->layers, $layer);
                        }
                        if ($layer['type'] == 'GeoJSON+Style') {
                            $this->features += $this->arrangeFeatures($layer);
                        }
                    }
                    break;
                case('requests'):
                    $this->layers = $mapElement;
                    break;
                case('vectorLayers'):
                    foreach ($mapElement as $layer) {
                        $this->features += $this->arrangeFeatures($layer);
                    }
                    break;
                case('printLegend'):
                    $this->printLegend = $mapElement;
                    break;
                case('format'):
                    $this->format = $mapElement;
                    break;
                case('extra'):
                    foreach ($mapElement as $extraKey => $element) {
                        $this->extra[$extraKey] = $element;
                    }
                    break;
                case('legends'):
                    $this->legends = $mapElement;
                    break;
                case('overview'):
                    $this->overview = $mapElement;
                    break;
                default:
                    //everything else is filled in "extra" array
                    $this->extra[$key] = $mapElement;
                    break;
            }
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

    public function getPrintLegend()
    {
        return $this->printLegend;
    }

    public function getFromExtra($key)
    {
        if (array_key_exists($key, $this->extra)) {
            return $this->extra[$key];
        }
        return null;
    }

    public function getLegends()
    {
        return $this->legends;
    }

    //Setters
    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function setFormat($fileType)
    {
        $this->format = $fileType;
    }

    public function setQuality($quality)
    {
        $this->quality = $quality;
    }

    public function setCenterX($centerX)
    {
        $this->centerX = $centerX;
    }

    public function setCenterY($centerY)
    {
        $this->centerY = $centerY;
    }

    public function setExtentWidth($extentWidth)
    {
        $this->extentWidth = $extentWidth;
    }

    public function setExtentHeight($extentHeight)
    {
        $this->extentHeight = $extentHeight;
    }

    public function setLayers($layers)
    {
        $this->layers = $layers;
    }

    public function setFeatures($features)
    {
        foreach ($features as $layer) {
            $this->features += $this->arrangeFeatures($layer);
        }
    }

    public function addToExtra($key, $element)
    {
        $this->extra[$key] = $element;
    }


}