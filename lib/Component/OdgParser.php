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

        if ($elementNEW->hasAttribute('draw:name')) {
            $pdfPage->addElement($elementNEW);
        }

        for ($i=0; $i<=$size; $i++){
            $elementOLD = $elementNEW;
            $elementNEW = $elementOLD->nextSibling;

            if ($elementNEW->hasAttribute('draw:name')) {
                $pdfPage->addElement($elementNEW);
            }
        }



        $elements = array();


        return $elements;
    }

}