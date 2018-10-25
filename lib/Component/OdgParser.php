<?php

namespace Wheregroup\MapExport\CoreBundle\Component;


use Wheregroup\MapExport\CoreBundle\Entity\PDFPage;

class OdgParser
{

    /**
     * @param $odgFile
     * @param $file
     * @return \DOMDocument
     */
    protected function getXML($odgFile, $file)
    {
        $opened = zip_open($odgFile);
        $xmlString = null;

        //Go through content of archive and return content of the requested xml file
        while ($zipEntry = zip_read($opened)) {
            if (zip_entry_name($zipEntry) == $file) {
                zip_entry_open($opened, $zipEntry);
                $xmlString = zip_entry_read($zipEntry, 51200);
                break;
            }
        }

        zip_close($opened);

        $doc = new \DOMDocument();
        $doc->loadXML($xmlString);


        return $doc;

    }

    public function getElements(PDFPage &$pdfPage, $path)
    {

        $doc = $this->getXML($path, 'content.xml');
        $xpath = new \DOMXPath($doc);

        $elements = $xpath->query('//draw:page');
        //$size = $elements->length;
        $size = 7;

        $elementNEW = $elements->item(0)->firstChild;

        //TODO aufräumen
        if ($elementNEW->hasAttribute('draw:name')) {
            $pdfPage->addElement($elementNEW);
        }

        for ($i = 0; $i <= $size; $i++) {
            $elementOLD = $elementNEW;
            $elementNEW = $elementOLD->nextSibling;

            if ($elementNEW->hasAttribute('draw:name')) {
                //now check if there is a style
                $styleElement = $xpath->query('.//text:span', $elementNEW);
                if ($styleElement->item(0) != null) {
                    $styleCode = $styleElement->item(0)->getAttribute('text:style-name');
                }

                if (isset($styleCode)) {
                    $styleNode = $xpath->query('//style:style[@style:name="' . $styleCode . '"]/style:text-properties')->item(0);
                    $fontSize = $styleNode->getAttribute('fo:font-size');
                    $textColor = $styleNode->getAttribute('fo:color');
                    $rgb = array();
                    $rgb['r'] = hexdec(substr($textColor, 1, 2));
                    $rgb['g'] = hexdec(substr($textColor, 3, 2));
                    $rgb['b'] = hexdec(substr($textColor, 5, 2));

                    $fontStyle = array('fontSize' => $fontSize, 'textColor' => $rgb);

                    $pdfPage->addElement($elementNEW, $fontStyle);
                } else {
                    $pdfPage->addElement($elementNEW);
                }


            }
        }

    }

}