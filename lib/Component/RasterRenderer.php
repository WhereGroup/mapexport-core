<?php

namespace Wheregroup\MapExport\CoreBundle\Component;


use Wheregroup\MapExport\CoreBundle\Entity\MapCanvas;

class RasterRenderer
{
    //const MAX_REQUEST_SIZE = 262144; //512x512 Pixel
    const MAX_REQUEST_SIZE = null; //max request size switched off

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
     * @return mixed
     */

    public function drawLayer(MapCanvas $canvas, $layer)
    {
        $img = $canvas->getImage();

        $imageWidth = $this->getWidthFromURL($layer['url']);
        $imageHeight = $this->getHeightFromURL($layer['url']);

        //test if image is too large and should be tiled
        if ($imageWidth * $imageHeight > self::MAX_REQUEST_SIZE && self::MAX_REQUEST_SIZE != null) {
            //If image is too large, split it
            $canvas = $this->drawTiledLayer($canvas, $layer);
        } else {
            //If image is not too large, request it from WMS and draw
            $layerImage = $this->getImageFromLayer($layer, $imageWidth, $imageHeight);

            //Compare bounding boxes of full canvas and image part to get pixel coordinates
            $BB = $this->getBBFromURL($layer['url']);
            $canvasBB = $canvas->getBB();

            $x = round($imageWidth * (($BB[0] - $canvasBB[0]) / ($BB[2] - $BB[0])));
            $y = $canvas->getHeight() - round($imageHeight * (($BB[1] - $canvasBB[1]) / ($BB[3] - $BB[1]))) - $this->getHeightFromURL($layer['url']);

            //draw layer on canvas
            imagecopy($img, $layerImage, $x, $y, 0, 0, $imageWidth, $imageHeight);

            $canvas->setImage($img);
        }
        return $canvas;
    }

    /**
     * Splits WMS Request into 2x2 requests
     *
     * @param MapCanvas $canvas
     * @param $layer
     * @return mixed
     */
    public function drawTiledLayer(MapCanvas $canvas, $layer)
    {
        //Create new image as big as layer
        $width = $this->getWidthFromURL($layer['url']);
        $height = $this->getHeightFromURL($layer['url']);
        $image = @imagecreatetruecolor($width, $height)
        or die("Can not create GD-Image");

        //Set image background color to white
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        //Get WMS Request URLs for four parts of the original layer
        $tiles = $this->splitWMS($layer['url']);

        $tileCols = count($tiles);
        $tileRows = count($tiles[0]);

        //Draw Map on new image at the right coordinates
        for ($i = 0; $i < $tileCols; $i++) {
            for ($j = 0; $j < $tileRows; $j++) {
                $layerCache = $layer;
                $layerCache['url'] = $tiles[$i][$j];

                $canvas = $this->drawLayer($canvas, $layerCache);
            }
        }

        return $canvas;
    }

    /**
     * Draws all returned images of wms requests on a MapCanvas
     *
     * @param MapCanvas $canvas
     * @param $requests
     * @param $location
     * @param $width
     * @param $height
     * @return mixed|MapCanvas
     */
    public function drawAllLayers(MapCanvas $canvas, $requests, $location, $width, $height)
    {

        foreach ($requests as $layer) {
            //Filter for wms requests only
            if (isset($layer['url'])) {
                $layer['url'] = $this->getWMS($layer['url'], $width, $height, $this->getBB($location));
                $canvas = ($this->drawLayer($canvas, $layer));
            }
        }

        return $canvas;
    }

    protected function getImageFromLayer($layer, $width = null, $height = null)
    {
        $result = $this->httpClient->open($layer['url']);
        $layerImage = imagecreatefromstring($result->getData());

        if ($width != null && $height != null) {
            $layerImage = imagescale($layerImage, $width, $height);
        }

        imagealphablending($layerImage, false);
        imagesavealpha($layerImage, true);

        return $layerImage;
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
     * Takes a WMS URL and splits it into $cols x $rows of smaller requests (default: 2 x 2)
     *
     * @param $url
     * @param int $cols
     * @param int $rows
     * @return array
     */
    protected function splitWMS($url, $cols = 2, $rows = 2)
    {
        $width = round($this->getWidthFromURL($url) / $cols);
        $height = round($this->getHeightFromURL($url) / $rows);
        $BB = $this->getBBFromURL($url);
        $BBArray = $this->splitBB($BB, $cols, $rows);

        $urlArray = array(array());

        //Get a WMS request URL for every bounding box
        for ($i = 0; $i < $cols; $i++) {
            for ($j = 0; $j < $rows; $j++) {
                $urlArray[$i][$j] = $this->getWMS($url, $width, $height, $BBArray[$i][$j]);
            }
        }

        return $urlArray;
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
     * Splits bounding box into array of $cols x $rows of bounding boxes (default: 2 x 2)
     *
     * @param $BB
     * @param int $cols
     * @param int $rows
     * @return array
     */
    protected function splitBB($BB, $cols = 2, $rows = 2)
    {
        //Get size
        $width = ($BB[2] - $BB[0]) / $cols;
        $height = ($BB[3] - $BB[1]) / $rows;

        $newBB = array(array());

        for ($i = 0; $i < $cols; $i++) {
            for ($j = 0; $j < $rows; $j++) {
                $newBB[$i][$j][0] = ($i * $width) + $BB[0];
                $newBB[$i][$j][1] = ($j * $height) + $BB[1];
                $newBB[$i][$j][2] = (($i + 1) * $width) + $BB[0];
                $newBB[$i][$j][3] = (($j + 1) * $height) + $BB[1];
            }
        }

        return $newBB;

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
     * @param $location
     * @return array
     */
    public function getBB($location)
    {
        if (isset($location['extentwidth'])) {
            $extentwidth = $location['extentwidth'];
        } else {
            $extentwidth = $location['extent']['width'];
        }

        if (isset($location['extentheight'])) {
            $extentheight = $location['extentheight'];
        } else {
            $extentheight = $location['extent']['height'];
        }

        if (isset($location['centerx'])) {
            $centerx = $location['centerx'];
        } else {
            $centerx = $location['center']['x'];
        }

        if (isset($location['centery'])) {
            $centery = $location['centery'];
        } else {
            $centery = $location['center']['y'];
        }

        $BB = array();
        array_push($BB, $centerx - $extentwidth / 2);
        array_push($BB, $centery - $extentheight / 2);
        array_push($BB, $centerx + $extentwidth / 2);
        array_push($BB, $centery + $extentheight / 2);

        return $BB;
    }


}