<?php

namespace Wheregroup\MapExport\CoreBundle\Component;


use Wheregroup\MapExport\CoreBundle\Entity\MapCanvas;

class RasterRenderer
{
    protected $httpClient;

    public function __construct($httpClient)
    {
        $this->httpClient = $httpClient;

    }

    public function drawLayer(MapCanvas $canvas, $layer)
    {
        $img = $canvas->getImage();

        $result = $this->httpClient->open($layer['url']);
        $layerImage = imagecreatefromstring($result->getData());

        $layerImage = imagescale($layerImage, $canvas->getWidth(), $canvas->getHeight());

        imagealphablending($layerImage, false);
        imagesavealpha($layerImage, true);

        imagecopy($img, $layerImage, 0, 0, 0, 0, $canvas->getWidth(), $canvas->getHeight());

        $canvas->setImage($img);

        return $canvas;
    }

    public function drawAllLayers(MapCanvas $canvas, $data)
    {
        foreach ($data['requests'] as $layer) {
            $layer['url'] = $this->getWMS($layer['url'], $data['width'], $data['height'], $this->getBB($data));
            $canvas = $this->drawLayer($canvas, $layer);
        }

        return $canvas;
    }

    public function getWMS($url, $width, $height, $BB)
    {
        $urlarray = parse_url($url);

        //create new query with updated values
        parse_str($urlarray['query'], $query);
        $BBString = implode(',', $BB);
        $query['BBOX'] = $BBString;
        $query['WIDTH'] = $width;
        $query['HEIGHT'] = $height;
        $query = http_build_query($query);

        //build new wms request
        if (array_key_exists('scheme', $urlarray)) {
            $url = $urlarray['scheme'] . '://';
        }
        if (array_key_exists('host', $urlarray)) {
            $url .= $urlarray['host'];
        }
        if (array_key_exists('port', $urlarray)) {
            $url .= $urlarray['port'];
        }
        if (array_key_exists('user', $urlarray)) {
            $url .= $urlarray['user'];
        }
        if (array_key_exists('pass', $urlarray)) {
            $url .= $urlarray['pass'];
        }
        if (array_key_exists('path', $urlarray)) {
            $url .= $urlarray['path'];
        }
        if (array_key_exists('query', $urlarray)) {
            $url .= '?' . $query;
        }
        if (array_key_exists('fragment', $urlarray)) {
            $url .= '#' . $urlarray['fragment'];
        }

        return $url;
    }

    //Returns the optimal bounding box
    public function getBB($data)
    {
        $BB = array();
        array_push($BB, $data['centerx'] - $data['extentwidth'] / 2);
        array_push($BB, $data['centery'] - $data['extentheight'] / 2);
        array_push($BB, $data['centerx'] + $data['extentwidth'] / 2);
        array_push($BB, $data['centery'] + $data['extentheight'] / 2);

        return $BB;
    }


}
