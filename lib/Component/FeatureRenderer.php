<?php

namespace Wheregroup\MapExport\CoreBundle\Component;

use Wheregroup\MapExport\CoreBundle\Entity\MapCanvas;

class FeatureRenderer
{
    public function drawAllFeatures($canvas, $features)
    {
        foreach ($features as $feature) {

            $canvas = $this->drawFeature($canvas, $feature);
        }

        return $canvas;

    }

    /**
     * Determines the right draw method for a feature depending on its type
     *
     * @param MapCanvas $canvas
     * @param $feature
     * @return mixed
     */
    public function drawFeature($canvas, $feature)
    {


        $geometry = $this->getGeometry($feature);
        $style = $this->getStyle($feature);

        switch ($this->getGeometryType($feature)) {
            case 'Polygon':
                $pointArrays = array();
                foreach ($geometry['coordinates'] as $pointArray) {
                    array_push($pointArrays, $canvas->transformCoords($pointArray));
                }
                $canvas = $this->drawPolygon($canvas, $pointArrays, $style);
                break;

            case 'Point':
                $points = $canvas->transformCoords(array($geometry['coordinates']));

                if (array_key_exists('label', $style)) {
                    $xPos = $points[0][0];
                    $yPos = $points[0][1];

                    if (array_key_exists('labelXOffset', $style)) {
                        $xPos += $style['labelXOffset'];
                    }
                    if (array_key_exists('labelYOffset', $style)) {
                        $yPos += $style['labelYOffset'];
                    }

                    $canvas = $this->drawLabel($canvas, array($xPos, $yPos), $style);
                }
                $canvas = $this->drawPoint($canvas, array($points[0][0], $points[0][1]), $style);
                break;

            case 'LineString':
                $points = $canvas->transformCoords($geometry['coordinates']);
                $canvas = $this->drawLineString($canvas, $points, $style);
                break;

            case 'MultiPolygon':
                $polygonArrays = array();
                foreach ($geometry['coordinates'] as $polygonArray) {
                    $pointArrays = array();
                    foreach ($polygonArray as $pointArray) {
                        array_push($pointArrays, $canvas->transformCoords($pointArray));
                    }
                    array_push($polygonArrays, $canvas->transformCoords($pointArrays));
                }
                $canvas = $this->drawMultiPolygon($canvas, $polygonArrays, $style);
                break;

            case 'MultiLineString':
                $pointArrays = array();
                foreach ($geometry['coordinates'] as $pointArray) {
                    array_push($pointArrays, $canvas->transformCoords($pointArray));

                }
                $canvas = $this->drawMultiLineString($canvas, $pointArrays, $style);
                break;

            case 'MultiPoint':
                $pointArrays = array();
                foreach ($geometry['coordinates'] as $pointArray) {
                    array_push($pointArrays, $canvas->transformCoords($pointArray));
                }
                $canvas = $this->drawMultiPoint($canvas, $pointArrays, $style);
                break;
            case 'Overlay':
                $points = $canvas->transformCoords(array($geometry['coordinates']));

                if (array_key_exists('label', $style)) {
                    $xPos = $points[0][0];
                    $yPos = $points[0][1];

                    if (array_key_exists('labelXOffset', $style)) {
                        $xPos += $style['labelXOffset'];
                    }
                    if (array_key_exists('labelYOffset', $style)) {
                        $yPos += $style['labelYOffset'];
                    }
                    $style['labelAlign'] = 'lt';
                    $canvas = $this->drawLabel($canvas, array($xPos, $yPos), $style);
                }
                break;
        }

        return $canvas;
    }

    /*
     * Functions that draw geometries onto a MapCanvas
     */

    public function drawPoint(MapCanvas $canvas, $coordinates, $style)
    {
        $img = $canvas->getImage();
        $rgbColor = $this->getColor($style['strokeColor']);

        $rgbStrokeColor = $this->getColor($style['strokeColor']);
        $strokeColor = imagecolorallocatealpha($img, $rgbStrokeColor[0], $rgbStrokeColor[1], $rgbStrokeColor[2],
            (1 - $style['strokeOpacity']) * 127);

        $transp_color = imagecolorallocatealpha($img, $rgbColor[0], $rgbColor[1], $rgbColor[2],
            (1 - $style['fillOpacity']) * 127);

        imagefilledellipse($img, $coordinates[0], $coordinates[1], $style['pointRadius'] * 2,
            $style['pointRadius'] * 2, $transp_color);
        imageellipse($img, $coordinates[0], $coordinates[1], $style['pointRadius'] * 2,
            $style['pointRadius'] * 2, $strokeColor);

        $canvas->setImage($img);

        return $canvas;
    }

    public function drawPolygon(MapCanvas $canvas, $coordinates, $style)
    {
        //ignore polygons with less than three points
        foreach ($coordinates as $polyCoords) {
            if (count($polyCoords) < 3) {
                return $canvas;
            }
        }

        $img = $canvas->getImage();

        //Set opacity if values are missing
        if (!isset($style['strokeOpacity'])) {
            $style['strokeOpacity'] = 1;
        }
        if (!isset($style['fillOpacity'])) {
            $style['fillOpacity'] = 1;
        }

        $rgbStrokeColor = $this->getColor($style['strokeColor']);
        if (isset($rgbStrokeColor[3])) {
            $style['fillOpacity'] = $rgbStrokeColor['3'];
        }
        $strokeColor = imagecolorallocatealpha($img, $rgbStrokeColor[0], $rgbStrokeColor[1], $rgbStrokeColor[2],
            (1 - $style['strokeOpacity']) * 127);

        $rgbFillColor = $this->getColor($style['fillColor']);
        if (isset($rgbFillColor[3])) {
            $style['fillOpacity'] = $rgbFillColor['3'];
        }
        $fillColor = imagecolorallocatealpha($img, $rgbFillColor[0], $rgbFillColor[1], $rgbFillColor[2],
            (1 - $style['fillOpacity']) * 127);

        $imgCache = imagecreatetruecolor(imagesx($img), imagesy($img));

        imagealphablending($imgCache, false);
        imagesavealpha($imgCache, true);
        imagesetthickness($imgCache, $style['strokeWidth']);

        $transparency = imagecolorallocatealpha($imgCache, 0, 0, 0, 127);
        imagefill($imgCache, 0, 0, $transparency);

        foreach ($coordinates as $index => $pointArray) {
            $num_points = sizeof($pointArray);

            $points = array();
            foreach ($pointArray as $point) {
                array_push($points, $point[0]);
                array_push($points, $point[1]);
            }

            if ($index == 0) {
                imagefilledpolygon($imgCache, $points, $num_points, $fillColor);
            } else {
                imagefilledpolygon($imgCache, $points, $num_points, $transparency);
            }

            imagepolygon($imgCache, $points, $num_points, $strokeColor);
        }

        imagecopy($img, $imgCache, 0, 0, 0, 0, imagesx($img), imagesy($img));

        $canvas->setImage($img);

        return $canvas;
    }

    public function drawLineString(MapCanvas $canvas, $coordinates, $style)
    {
        $strokeOpacity = array_key_exists('strokeOpacity', $style)?$style['strokeOpacity']:1;

        $img = $canvas->getImage();

        $rgbStrokeColor = $this->getColor($style['strokeColor']);
        $color = imagecolorallocatealpha($img, $rgbStrokeColor[0], $rgbStrokeColor[1], $rgbStrokeColor[2],
            (1 - $strokeOpacity) * 127);

        imagesetthickness($img, $style['strokeWidth']);

        //craw lines for each pair of coordinates
        for ($i = 0; $i < sizeof($coordinates) - 1; $i++) {
            imageline($img, $coordinates[$i][0], $coordinates[$i][1], $coordinates[$i + 1][0],
                $coordinates[$i + 1][1], $color);
        }

        $canvas->setImage($img);

        return $canvas;
    }

    public function drawMultiPoint(MapCanvas $canvas, $coordinates, $style)
    {
        $img = $canvas->getImage();

        foreach ($coordinates as $pointCoordinates) {
            $img = $this->drawPoint($img, $pointCoordinates, $style);
        }

        $canvas->setImage($img);

        return $canvas;
    }

    public function drawMultiPolygon(MapCanvas $canvas, $coordinates, $style)
    {
        $img = $canvas->getImage();

        foreach ($coordinates as $polygonCoordinates) {
            $img = $this->drawPolygon($img, $polygonCoordinates, $style);
        }

        $canvas->setImage($img);

        return $canvas;
    }

    public function drawMultiLineString(MapCanvas $canvas, $coordinates, $style)
    {
        $img = $canvas->getImage();

        foreach ($coordinates as $multiLineStringCoordinates) {
            $img = $this->drawLineString($img, $multiLineStringCoordinates, $style);
        }

        $canvas->setImage($img);

        return $canvas;
    }

    public function drawLabel(MapCanvas $canvas, $coordinates, $style)
    {
        $img = $canvas->getImage();

        if(array_key_exists('strokeColor', $style)) {
            $rgbStrokeColor = $this->getColor($style['strokeColor']);
        } else {
            $rgbStrokeColor = array(255,204,51);
        }

        if(isset($style['strokeOpacity'])) {
            $style['strokeOpacity'] = 0;
        }

        $textColor = imagecolorallocatealpha($img, $rgbStrokeColor[0], $rgbStrokeColor[1], $rgbStrokeColor[2],
            $style['strokeOpacity']);

        //TODO: Diesen Kram woanders festlegen
        //Textsize and font are never defined so they are hardcoded here
        $textsize = 10;
        $font = './components/open-sans/fonts/Bold/OpenSans-Bold.ttf';

        $textBBarray = imageftbbox($textsize, 0, $font, $style['label']);

        //default position
        if(!array_key_exists('labelAlign', $style)){
            $style['labelAlign'] = 'cb';
        }

        switch ($style['labelAlign']) {
            case 'lt':
                $coordinates[1] -= $textBBarray[7];
                break;
            case 'ct':
                $coordinates[0] -= ($textBBarray[2] - $textBBarray[0]) / 2;
                $coordinates[1] -= $textBBarray[7];
                break;
            case 'rt':
                $coordinates[0] -= $textBBarray[2];
                $coordinates[1] -= $textBBarray[7];
                break;
            case 'lm':
                $coordinates[1] -= ($textBBarray[7] - $textBBarray[1]) / 2;
                break;
            case 'cm':
                $coordinates[0] -= ($textBBarray[2] - $textBBarray[0]) / 2;
                $coordinates[1] -= ($textBBarray[7] - $textBBarray[1]) / 2;
                break;
            case 'rm':
                $coordinates[0] -= $textBBarray[2];
                $coordinates[1] -= ($textBBarray[7] - $textBBarray[1]) / 2;
                break;
            case 'lb':
                break;
            case 'cb':
                $coordinates[0] -= ($textBBarray[2] - $textBBarray[0]) / 2;
                break;
            case 'rb':
                $coordinates[0] -= $textBBarray[2];
                break;
        }

        //draw text outline
        $borderSize = 1;
        $backgroundColor = imagecolorallocatealpha($img, 0, 0, 0, 0);

        for($i = $coordinates[0]-$borderSize; $i <= $coordinates[0]+$borderSize; $i++){
            for($j = $coordinates[1]-$borderSize; $j <= $coordinates[1]+$borderSize; $j++){
                imagettftext($img, $textsize, 0, $i, $j, $backgroundColor, $font, $style['label']);
            }
        }

        //draw text
        imagettftext($img, $textsize, 0, $coordinates[0], $coordinates[1], $textColor, $font, $style['label']);

        $canvas->setImage($img);

        return $canvas;
    }

    /*
     * Support functions for conversions
     */

    protected function getStyle($feature)
    {
        return $feature['properties'];
    }

    protected function getGeometryType($feature)
    {
        return $feature['geometry']['type'];
    }

    protected function getGeometry($feature)
    {
        return $feature['geometry'];
    }

    /**
     * Converts hex colors and some color strings into arrays of r, g and b values
     *
     * @param $colorstring
     * @return array
     */
    protected function getColor($colorstring)
    {
        if (substr($colorstring, 0, 4) == 'rgba') {
            $color = substr($colorstring, 5, -1);
            return explode(',', $color);
        } else {
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

    }

    /**
     * Returns an array filled with the r, g and b value of a hex color
     *
     * @param $hex
     * @return array
     */
    protected function hexToRGB($hex)
    {
        $rgb = array();
        $rgb[0] = hexdec(substr($hex, 1, 2));
        $rgb[1] = hexdec(substr($hex, 3, 2));
        $rgb[2] = hexdec(substr($hex, 5, 2));

        return $rgb;
    }

}