<?php

namespace Wheregroup\MapExport\CoreBundle\Component;


use Wheregroup\MapExport\CoreBundle\Entity\MapCanvas;

class RasterRenderer
{

    const MAX_REQUEST_WIDTH = 1024;
    const MAX_REQUEST_HEIGHT = 1024;

    const MARGIN = 32;

    protected $httpClient;

    public function __construct($httpClient)
    {
        $this->httpClient = $httpClient;

    }

    public function drawLayer(MapCanvas $canvas, $layer)
    {
        $width = $canvas->getWidth();
        $height = $canvas->getHeight();

        //draw layer in one piece
        $layer['url'] = $this->getWMS($layer['url'], $width, $height, $canvas->getBB());
        $image = $this->getImageFromLayer($layer);
        $canvas->addLayer($image);

        return $canvas;
    }

    /**
     * Cuts the layer into pieces and requests each piece separately.
     * Each request gets a tile that is slightly larger than it has to be, which then gets cropped.
     * This is necessary because a tile could contain information that has been cropped, but the adjacent tile might not contain the missing part.
     *
     * @param MapCanvas $canvas
     * @param $layer
     * @return MapCanvas
     */
    public function drawTiledLayer(MapCanvas $canvas, $layer)
    {
        //width of whole image
        $imageWidth = $canvas->getWidth();
        $imageHeight = $canvas->getHeight();

        //number of tiles in each row and column
        $horTiles = round($imageWidth / self::MAX_REQUEST_WIDTH);
        $verTiles = round($imageHeight / self::MAX_REQUEST_HEIGHT);

        //width of tiles on map
        $tileWidth = $canvas->getExtentWidth() / ($horTiles + 1);
        $tileHeight = $canvas->getExtentHeight() / ($verTiles + 1);

        //width of tiles in pixel (original and with added margin)
        $imageTileWidthO = round($imageWidth / ($horTiles + 1));
        $imageTileHeightO = round($imageHeight / ($verTiles + 1));
        $imageTileWidth = round($imageWidth / ($horTiles + 1)) + self::MARGIN * 2;
        $imageTileHeight = round($imageHeight / ($verTiles + 1)) + self::MARGIN * 2;

        $margin = $canvas->getPixelToMap(self::MARGIN);

        //create new image resource for tiles
        $croppedImage = @imagecreatetruecolor($imageTileWidthO, $imageTileHeightO)
        or die("Can not create GD-Image");
        imagealphablending($croppedImage, false);
        imagesavealpha($croppedImage, true);

        //loop that gets tiles and places them
        for ($i = 0; $i <= $verTiles; $i++) {
            for ($j = 0; $j <= $horTiles; $j++) {
                //create url with changed extent
                $bb = $canvas->getBBofTile($tileWidth, $tileHeight, $j, $i);

                //add margin
                $bb = $this->addBBMargin($bb, $margin);

                //get image
                $layer['url'] = $this->getWMS($layer['url'], $imageTileWidth, $imageTileHeight, $bb);
                $image = $this->getImageFromLayer($layer);

                //cut margin
                imagecopyresampled($croppedImage, $image, 0, 0, self::MARGIN, self::MARGIN, $imageTileWidthO,
                    $imageTileHeightO, $imageTileWidthO, $imageTileHeightO);

                //draw image on canvas
                $canvas->addTile($croppedImage, $j, $verTiles - $i);
            }
        }
        return $canvas;
    }

    /**
     * Draws all returned images of wms requests on a MapCanvas
     *
     * @param MapCanvas $canvas
     * @param $requests
     * @return mixed|MapCanvas
     */
    public function drawAllLayers(MapCanvas $canvas, $requests)
    {
        foreach ($requests as $layer) {
            //Filter for wms requests only
            if (isset($layer['url'])) {
                //$canvas = ($this->drawLayer($canvas, $layer));
                if ($canvas->getWidth() <= self::MAX_REQUEST_WIDTH && $canvas->getHeight() <= self::MAX_REQUEST_HEIGHT) {
                    $canvas = ($this->drawLayer($canvas, $layer));
                } else {
                    $canvas = ($this->drawTiledLayer($canvas, $layer));
                }
            }
        }
        return $canvas;
    }

    protected function getImageFromLayer($layer, $width = null, $height = null)
    {
        $result = $this->httpClient->open($layer['url']);
        $contenttype = $this->httpClient->headers['content_type'];

        if(strpos($contenttype, 'image') === 0 ) {
            $layerImage = imagecreatefromstring($result->getData());

            imagealphablending($layerImage, false);
            imagesavealpha($layerImage, true);

            //Set opacity
            if (array_key_exists('opacity', $layer)) {
                $transparency = 1 - $layer['opacity'];
                imagefilter($layerImage, IMG_FILTER_COLORIZE, 0, 0, 0, 127 * $transparency);
            }

            return $layerImage;
        }
        return null;
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
        $query = array_change_key_case($query, CASE_UPPER);
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
            $url .= ':' . $urlarray['port'];
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

    protected function getBBFromURL($url)
    {
        $urlarray = parse_url($url);

        parse_str($urlarray['query'], $query);

        return explode(',', $query['BBOX']);
    }

    protected function addBBMargin($bb, $margin)
    {
        $bb[0] -= $margin;
        $bb[1] -= $margin;
        $bb[2] += $margin;
        $bb[3] += $margin;

        return $bb;
    }

}