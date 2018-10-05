<?php

namespace Wheregroup\MapExport\CoreBundle\Entity;


class MapCanvas
{
    protected $image;

    protected $mapWidth;
    protected $mapHeight;

    protected $mapCenterX;
    protected $mapCenterY;

    public function __construct($width, $height, $mapWidth, $mapHeight, $mapCenterX, $mapCenterY)
    {
        $this->initImage($width, $height);
        $this->mapWidth = $mapWidth;
        $this->mapHeight = $mapHeight;
        $this->mapCenterX = $mapCenterX;
        $this->mapCenterY = $mapCenterY;

    }

    protected function initImage($width, $height)
    {
        /*if ($angle != 0) {
            $newBB = $this->getBBOfRotatedImg($width, $height, $angle);
            $width = $newBB[0];
            $height = $newBB[1];
        }*/

        $this->image = @imagecreatetruecolor($width, $height)
        or die("Can not create GD-Image");

        $white = imagecolorallocate($this->image, 255, 255, 255);
        imagefill($this->image, 0, 0, $white);

    }

    public function transformCoords($points)
    {

        $worldX = $this->mapCenterX - ($this->mapWidth / 2);
        $worldY = $this->mapCenterY - ($this->mapHeight / 2);


        $pixelPoints = array();

        foreach ($points as $point) {
            $point[0] = round((($point[0] - $worldX) / $this->mapWidth) * $this->getWidth());
            $point[1] = $this->getHeight() - round((($point[1] - $worldY) / $this->mapHeight) * $this->getHeight());
            array_push($pixelPoints, $point);
        }
        return $pixelPoints;
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
}