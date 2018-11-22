<?php

namespace Wheregroup\MapExport\CoreBundle\Entity;


class MapCanvas
{
    protected $image;

    protected $mapWidth;
    protected $mapHeight;

    protected $mapCenterX;
    protected $mapCenterY;

    protected $width;
    protected $height;

    protected $bb;

    public function __construct($width, $height, $mapWidth, $mapHeight, $mapCenterX, $mapCenterY)
    {
        $this->initImage($width, $height);
        $this->mapWidth = $mapWidth;
        $this->mapHeight = $mapHeight;
        $this->mapCenterX = $mapCenterX;
        $this->mapCenterY = $mapCenterY;
        $this->width = $width;
        $this->height = $height;
        $this->initBB();

    }

    protected function initImage($width, $height)
    {
        $this->image = @imagecreatetruecolor($width, $height)
        or die("Can not create GD-Image");

        $white = imagecolorallocate($this->image, 255, 255, 255);
        imagefill($this->image, 0, 0, $white);

    }

    protected function initBB()
    {
        $extentwidth = $this->mapWidth;
        $extentheight = $this->mapHeight;
        $centerx = $this->mapCenterX;
        $centery = $this->mapCenterY;

        $this->bb = array();
        array_push($this->bb, ($centerx - $extentwidth / 2));
        array_push($this->bb, ($centery - $extentheight / 2));
        array_push($this->bb, ($centerx + $extentwidth / 2));
        array_push($this->bb, ($centery + $extentheight / 2));

    }

    /**
     * Transforms real world coordinates into pixel coordinates
     *
     * @param $points array()   An array of points were each points x and y coordinate are stored in an own array
     * @return array    pixel coordinates in same format as input
     */

    public function transformCoords($points)
    {

        $worldX = $this->mapCenterX - ($this->mapWidth / 2);
        $worldY = $this->mapCenterY - ($this->mapHeight / 2);


        $pixelPoints = array();

        foreach ($points as $point) {
            $point[0] = round((($point[0] - $worldX) / $this->mapWidth) * $this->width);
            $point[1] = $this->getHeight() - round((($point[1] - $worldY) / $this->mapHeight) * $this->height);
            array_push($pixelPoints, $point);
        }
        return $pixelPoints;
    }

    public function addLayer($img)
    {
        imagecopy($this->image, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
    }

    public function addTile($img, $xPos, $yPos)
    {
        $width = imagesx($img);
        $height = imagesy($img);
        imagecopy($this->image, $img, $width * $xPos, $height * $yPos, 0, 0, $width, $height);
    }

    public function transformToGeoPosition($coordinates)
    {
        $scaleFactorX = $this->mapWidth / $this->width;
        $scaleFactorY = $this->mapHeight / $this->height;

        $coordinates['x'] *= $scaleFactorX;
        $coordinates['y'] *= $scaleFactorY;
    }

    public function getPixelToMap($pixelValue)
    {
        return $pixelValue * $this->mapWidth / $this->width;
    }

    /*
     * Getters and setters
     */

    public function getBB()
    {
        return $this->bb;
    }

    public function getBBofTile($width, $height, $xPos, $yPos)
    {
        $bb = $this->bb;

        $newBB = $this->getBB();
        $newBB[0] = $bb[0] + $width * $xPos;
        $newBB[1] = $bb[1] + $height * $yPos;
        $newBB[2] = $newBB[0] + $width;
        $newBB[3] = $newBB[1] + $height;

        return $newBB;
    }

    public function getWidth()
    {
        return imagesx($this->image);
    }

    public function getHeight()
    {
        return imagesy($this->image);
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getCenterX()
    {
        return $this->mapCenterX;
    }

    public function getCenterY()
    {
        return $this->mapCenterY;
    }

    public function getExtentWidth()
    {
        return $this->mapWidth;
    }

    public function getExtentHeight()
    {
        return $this->mapHeight;
    }

}