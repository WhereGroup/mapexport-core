<?php

namespace Wheregroup\PrintBundle\Component;


use Wheregroup\PrintBundle\Entity\Image;

class FeatureRenderer
{
    protected static $instance = null;

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    //externe Instanzierung verbieten
    public function __construct()
    {
    }

    //Kopieren von Instanzen verbieten
    protected function __clone()
    {
    }

    public function getWorldFormat($centerx, $centery, $extentwidth, $extentheight, $width, $height)
    {
        return array(
            'worldX' => $centerx - ($extentwidth / 2),
            'worldY' => $centery - ($extentheight / 2),
            'worldWidth' => $extentwidth,
            'worldHeight' => $extentheight,
            'pixelWidth' => $width,
            'pixelHeight' => $height
        );
    }

    /**
     * @param Image $img
     * @param $features
     * @param $worldFormat
     * @return mixed
     */
    public function renderAll($img, $features, $worldFormat)
    {
        foreach ($features as $vectorLayer) {
            foreach ($vectorLayer['geometries'] as $figure) {
                $color = $this->getColor($figure['style']['strokeColor']);

                switch ($figure['type']) {
                    case 'Polygon':
                        $pointarrays = array();
                        foreach ($figure['coordinates'] as $pointarray) {
                            array_push($pointarrays, $this->transformPoints($pointarray,
                                array($worldFormat['worldX'], $worldFormat['worldY']),
                                array($worldFormat['worldWidth'], $worldFormat['worldHeight']),
                                array($worldFormat['pixelWidth'], $worldFormat['pixelHeight'])));
                        }
                        $img->drawPolygon($pointarrays, $color, $figure['style']['strokeWidth'],
                            $figure['style']['fillOpacity']);
                        break;
                    case 'Point':
                        $points = $this->transformPoints(array($figure['coordinates']),
                            array($worldFormat['worldX'], $worldFormat['worldY']),
                            array($worldFormat['worldWidth'], $worldFormat['worldHeight']),
                            array($worldFormat['pixelWidth'], $worldFormat['pixelHeight']));

                        if (array_key_exists('label', $figure['style'])) {
                            $xPos = $points[0][0];
                            $yPos = $points[0][1];

                            if (array_key_exists('labelXOffset', $figure['style'])) {
                                $xPos += $figure['style']['labelXOffset'];
                            }
                            if (array_key_exists('labelYOffset', $figure['style'])) {
                                $yPos += $figure['style']['labelYOffset'];
                            }

                            $img->drawLabel($xPos, $yPos, $color, $figure['style']['label'], 16,
                                $figure['style']['labelAlign']);
                        }
                        $img->drawPoint($points[0][0], $points[0][1], $color, $figure['style']['pointRadius'],
                            $figure['style']['fillOpacity']);

                        break;
                    case 'LineString':
                        $points = $this->transformPoints($figure['coordinates'],
                            array($worldFormat['worldX'], $worldFormat['worldY']),
                            array($worldFormat['worldWidth'], $worldFormat['worldHeight']),
                            array($worldFormat['pixelWidth'], $worldFormat['pixelHeight']));
                        $img->drawOpenPolygon($points, $color, $figure['style']['strokeWidth']);
                        break;
                    //TODO MultiPoint, MultiLineString, MultiPolygon
                    case 'MultiPolygon':
                        break;
                    case 'MultiLineString':
                        break;
                    case 'MultiPoint':
                        break;
                }
            }
        }
        imagepng($img->getImage(), "test.png");
        return $img;
    }

    private function transformPoints($points, $src_root, $src_size, $dest_size)
    {
        $pixelPoints = array();

        foreach ($points as $point) {
            $point[0] = round((($point[0] - $src_root[0]) / $src_size[0]) * $dest_size[0]);
            $point[1] = $dest_size[1] - round((($point[1] - $src_root[1]) / $src_size[1]) * $dest_size[1]);
            array_push($pixelPoints, $point);
        }
        return $pixelPoints;
    }

    private function getColor($colorstring)
    {
        if (substr($colorstring, 0, 1) == '#') {
            return $this->hexToRGB($colorstring);
        } else {
            switch ($colorstring) {
                case 'red':
                    return array(255, 0, 0);
                    break;
                case 'green':
                    return array(0, 255, 0);
                    break;
                case 'blue':
                    return array(0, 0, 255);
                    break;
                case 'white':
                    return array(255, 255, 255);
                    break;
                default:
                    return array(0, 0, 0);
                    break;
            }
        }

    }

    private function hexToRGB($hex)
    {
        $rgb = array();
        $rgb[0] = hexdec(substr($hex, 1, 2));
        $rgb[1] = hexdec(substr($hex, 3, 2));
        $rgb[2] = hexdec(substr($hex, 5, 2));

        return $rgb;
    }

    public function rotate($center, $points)
    {
        $rotatedPoints = array();

        return $rotatedPoints;
    }

}