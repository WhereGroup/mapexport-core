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
        //TODO: Find a better solution for fixed template location
        $templatePath = './MapbenderPrintBundle/templates/' . $data['template'];

        $pdf = new FPDI();

        $pdfPage = new PDFPage($pdf, $data);

        $odgParser = new OdgParser();
        $odgParser->getElements($pdfPage, $templatePath . '.odg');



        return $pdfPage->getPDFPage();
    }
}