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

    public function getConf($template)
    {

        $doc = $this->getXML($template, 'styles.xml');
        $xPath = new \DOMXPath($doc);
        $node = $xPath->query("//style:page-layout-properties");
        $pageGeometry = $node->item(0);
        $conf = array(
            'orientation' => ucwords($pageGeometry->getAttribute('style:print-orientation')),
            'pageSize' => array(
                'height' => substr($pageGeometry->getAttribute('fo:page-height'), 0, -2),
                'width' => substr($pageGeometry->getAttribute('fo:page-width'), 0, -2)
            )
        );

        return $conf;
    }

    public function getPageNumber($path)
    {
        $doc = $this->getXML($path, 'content.xml');
        $xpath = new \DOMXPath($doc);

        $pages =  $xpath->query('//office:drawing')->item(0)->childNodes;

        //count number of pages
        $counter = 0;
        foreach ($pages as $page){
            $counter++;
        }
        return $counter;
    }

    public function getElements(PDFPage &$pdfPage, $path, $page)
    {

        $doc = $this->getXML($path, 'content.xml');
        $xpath = new \DOMXPath($doc);

        $pages =  $xpath->query('//office:drawing');

        $elements = $pages->item(0)->firstChild;

        //Find the right page
        for ($i = 1; $i < $page; $i++){
            $elements = $elements->nextSibling;
        }

        $element = $elements->firstChild;
        while ($element != null) {

            if ($element != null && $element->hasAttribute('draw:name')) {
                //now check if there is a style
                $styleElement = $xpath->query('.//text:span', $element);
                if ($styleElement->item(0) != null) {
                    //If there are style attributes defined, add an element with style to the page
                    $styleCode = $styleElement->item(0)->getAttribute('text:style-name');

                    $styleNode = $xpath->query('//style:style[@style:name="' . $styleCode . '"]/style:text-properties')->item(0);
                    $fontSize = substr($styleNode->getAttribute('fo:font-size'), 0, -2);
                    $textColor = $styleNode->getAttribute('fo:color');
                    $bold = $styleNode->getAttribute('fo:font-weight') == 'bold' ? true : false;
                    $italic = $styleNode->getAttribute('fo:font-style') == 'italic' ? true : false;
                    $underlined = $styleNode->getAttribute('style:text-underline-style') == 'solid' ? true : false;
                    $rgb = array();
                    $rgb['r'] = hexdec(substr($textColor, 1, 2));
                    $rgb['g'] = hexdec(substr($textColor, 3, 2));
                    $rgb['b'] = hexdec(substr($textColor, 5, 2));

                    $fontStyle = array(
                        'fontSize' => $fontSize,
                        'textColor' => $rgb,
                        'bold' => $bold,
                        'italic' => $italic,
                        'underlined' => $underlined
                    );

                    $pdfPage->addElement($element, $fontStyle);
                } else {
                    //If there are no style attributes defined, add an element without style to the page
                    $pdfPage->addElement($element);
                }


            }
            $element = $element->nextSibling;
        }

    }

}