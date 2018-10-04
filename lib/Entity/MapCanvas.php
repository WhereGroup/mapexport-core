<?php

namespace Wheregroup\MapExport\CoreBundle\Entity;


class Canvas
{
    protected $image;

    protected $mapWidth;
    protected $mapHeight;

    protected $mapCenterX;
    protected $mapCenterY;

    public function __construct($image)
    {
        $this->image = $image;

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