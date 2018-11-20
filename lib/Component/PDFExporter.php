<?php

namespace Wheregroup\MapExport\CoreBundle\Component;

use Wheregroup\MapExport\CoreBundle\Entity\PDFPage;

class PDFExporter
{
    protected $container;
    protected $user;
    protected $resourceDir;

    public function __construct($container)
    {
        $this->container = $container;

        // resource dir
        $this->resourceDir = $this->container->getParameter('kernel.root_dir') . '/Resources/MapbenderPrintBundle';

        $this->user = $this->getUser();
    }

    public function createPDFFromTemplate($data)
    {
        $templatePath = $this->resourceDir . '/templates/' . $data['template'];

        $odgParser = new OdgParser();

        //Get page configuration (Orientation, width, height)
        $conf = $odgParser->getConf($templatePath . '.odg');

        //Create PDF
        $pdf = new PDF_Extensions();
        $pdfPages = array();

        //Go through pages of template and fill pdf with objects defined there
        $legendExists = false;
        $legendOverflow = null;
        $templatePageNumber = $odgParser->getPageNumber($templatePath . '.odg');
        for ($i = 1; $i <= $templatePageNumber; $i++) {
            //Add new Page to PDF
            $pdfPages[$i] = new PDFPage($pdf, $data, $conf, $i, $templatePath);
            //Fill PDF Page
            $odgParser->getElements($pdfPages[$i], $templatePath . '.odg', $i);
            $pdfPages[$i]->makePDFPage();
            if ($legendExists == false && $pdfPages[$i]->containsLegend()) {
                $legendExists = true;
            }
            if ($pdfPages[$i]->getLegendOverflow() != null) {
                $legendOverflow = $pdfPages[$i]->getLegendOverflow();
            }
        }

        //Add new legend page if there was no legend
        //if client wants printed legend and there was no place for it OR if there is unhandled legend overflow
        if ((array_key_exists('printLegend', $data) && $data['printLegend'] == 1 && !$legendExists) || $legendOverflow != null) {
            //add legend pages as long as there is unhandled legend overflow
            do {
                $lpconf = array('orientation' => 'Portrait', 'pageSize' => array('height' => 29.7, 'width' => 21.0));
                $legendPage = new PDFPage($pdf, $data, $lpconf);

                $legendPage->forceLegend();

                $legendPage->setLegendOverflow($legendOverflow);
                $legendPage->makePDFPage();

                $legendOverflow = $legendPage->getLegendOverflow();
            } while ($legendOverflow != null);
        }

        //return $pdfPage->getPDFPage();
        return $pdf->Output();
    }

    protected function getUser()
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $token = $tokenStorage->getToken();
        if ($token) {
            return $token->getUser();
        } else {
            return null;
        }
    }
}