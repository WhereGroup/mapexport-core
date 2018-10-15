<?php

namespace Wheregroup\MapExport\CoreBundle\Component;


use Wheregroup\MapExport\CoreBundle\Entity\MapCanvas;

class RasterRenderer
{
    const MAX_REQUEST_SIZE = 262144; //512x512 Pixel

    protected $httpClient;

    public function __construct($httpClient)
    {
        $this->httpClient = $httpClient;

    }

    /**
     * Draws the returned image of a wms request on a MapCanvas
     *
     * @param MapCanvas $canvas
     * @param $layer
     * @return MapCanvas
     */

    public function drawLayer(MapCanvas $canvas, $layer)
    {
        //Test if request is too large
        if ($this->getWidthFromURL($layer['url']) * $this->getHeightFromURL($layer['url']) > self::MAX_REQUEST_SIZE) {
            //If yes, split layer
            $this->drawTiledLayer($canvas, $layer, $this->getBBFromURL($layer['url']));
        } else {
            //If not, draw layer
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
    }

    /**
     * Splits WMS Request into 2x2 requests
     *
     * @param MapCanvas $canvas
     * @param $layer
     * @param $BB
     */
    public function drawTiledLayer(MapCanvas $canvas, $layer, $BB)
    {
        $tiles = $this->splitWMS($layer, $this->getWidthFromURL($layer['url']), $this->getHeightFromURL($layer['url']),
            $BB);

        foreach ($tiles as $tile) {
            //build new layer with changed url
            $layerCache = $layer;
            $layerCache['url'] = $tile;

            $this->drawLayer($canvas, $layerCache);

        }

    }

    /**
     * Draws all returned images of wms requests on a MapCanvas
     *
     * @param MapCanvas $canvas
     * @param $data
     * @return MapCanvas
     */
    public function drawAllLayers(MapCanvas $canvas, $data)
    {
        foreach ($data['requests'] as $layer) {
            $layer['url'] = $this->getWMS($layer['url'], $data['width'], $data['height'], $this->getBB($data));
            $canvas = $this->drawLayer($canvas, $layer);
        }

        return $canvas;
    }

    /**
     * Builds a new wms request url that contains the desired bounding box, width and height
     *
     * @param $url
     * @param $width
     * @param $height
     * @param $BB
     * @return string
     */
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

    /**
     * Takes a WMS URL and splits it into 2x2 smaller requests
     *
     * @param $url
     * @param $width
     * @param $height
     * @param $BB
     * @return array
     */
    protected function splitWMS($url, $width, $height, $BB)
    {
        $BBArray = $this->splitBB($BB);

        return array(
            $this->getWMS($url, $width / 2, $height / 2, $BBArray[0]),
            $this->getWMS($url, $width / 2, $height / 2, $BBArray[1]),
            $this->getWMS($url, $width / 2, $height / 2, $BBArray[2]),
            $this->getWMS($url, $width / 2, $height / 2, $BBArray[3])
        );
    }

    protected function getWidthFromURL($url)
    {
        $urlarray = parse_url($url);

        parse_str($urlarray['query'], $query);

        return $query['WIDTH'];
    }

    protected function getHeightFromURL($url)
    {
        $urlarray = parse_url($url);

        parse_str($urlarray['query'], $query);

        return $query['HEIGHT'];
    }

    /**
     * Splits bounding box into array of 2x2 bounding boxes
     *
     * @param $BB
     * @return array
     */
    protected function splitBB($BB)
    {
        //Get center
        $centerX = $BB[2] - $BB[0];
        $centerY = $BB[3] - $BB[1];

        //Split bounding box
        //Top left
        $BB1 = $BB;
        $BB1[1] = $centerY;
        $BB1[2] = $centerX;
        //Top right
        $BB2 = $BB;
        $BB2[0] = $centerX;
        $BB2[1] = $centerY;
        //Bottom left
        $BB3 = $BB;
        $BB3[2] = $centerX;
        $BB3[3] = $centerY;
        //Bottom right
        $BB4 = $BB;
        $BB4[0] = $centerX;
        $BB4[3] = $centerY;

        return array($BB1, $BB2, $BB3, $BB4);
    }

    protected function getBBFromURL($url)
    {
        $urlarray = parse_url($url);

        parse_str($urlarray['query'], $query);

        return explode(',', $query['BBOX']);
    }

    /**
     * Returns the bounding box that is defined by the center coordinates and extent values in $data
     *
     * @param $data
     * @return array
     */
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
