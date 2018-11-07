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
        $conf = $odgParser->getConf($templatePath . '.odg');

        //Create PDF
        $pdf = new PDF_Extensions();

        //Add new Page to PDF
        $pdfPage = new PDFPage($pdf, $data, $conf, $templatePath);

        //Fill PDF Page
        $odgParser->getElements($pdfPage, $templatePath . '.odg');
        $pdfPage->makePDFPage();

        //Test if client wants legend to be printed and if there isn't already a field for it on first page
        if($data['printLegend'] = 1 && !$pdfPage->containsLegend()) {
            $legendPage = new LegendPage($pdf, $data, $conf, $templatePath);
            $legendPage->makePDFPage();
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