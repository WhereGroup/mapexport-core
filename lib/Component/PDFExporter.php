<?php
/**
 * User: sbrieden
 * Date: 22.10.18
 * Time: 12:27
 */

namespace Wheregroup\MapExport\CoreBundle\Component;

use FPDI;
use Wheregroup\MapExport\CoreBundle\Entity\PDFPage;

class PDFExporter
{
    public function createPDFFromTemplate($data)
    {
        $odgParser = new OdgParser();

        //TODO: Find a better solution for fixed template location
        $templatePath = './MapbenderPrintBundle/templates/' . $data['template'];

        $conf = $odgParser->getConf($templatePath . '.odg');
        //$pdf = new FPDI($conf['orientation'], 'cm', 'A4');
        $pdf = new FPDI();

        $pdfPage = new PDFPage($pdf, $data, $conf);

        $odgParser->getElements($pdfPage, $templatePath . '.odg');



        return $pdfPage->getPDFPage();
    }
}