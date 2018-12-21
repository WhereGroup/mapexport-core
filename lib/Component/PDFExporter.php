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

        $this->odgParser = new OdgParser();
    }

    public function createPDF(MapData $data)
    {
        $templatePath = $this->resourceDir . '/templates/' . $data->getTemplate();

        //Get page configuration (Orientation, width, height)
        $conf = $this->odgParser->getConf($templatePath . '.odg');

        //Create PDF
        $pdf = new PDFExtensions();

        //Create pages from template
        $legendOverflow = $this->createPDFFromTemplate($pdf, $data, $conf, $templatePath);

        //Add pages for every geometry
        if ($data->getFromExtra('snapshots')) {
            $this->createSnapshots($pdf, $data, $conf, $templatePath);
        }

        //Add new legend page if there was no legend
        //if client wants printed legend and there was no place for it OR if there is unhandled legend overflow
        if ($data->getLegends() != null) {
            if ($data->getPrintLegend() == 1 || $legendOverflow != null) {

                $this->createLegendPage($pdf, $data, $conf, $legendOverflow);

            }
        }
        //set page number
        //$pdf->AliasNbPages();

        //return $pdfPage->getPDFPage();
        return $pdf->Output();
    }

    protected function createPDFFromTemplate($pdf, MapData $data, $conf, $templatePath)
    {
        $pdfPages = array();

        //Go through pages of template and fill pdf with objects defined there
        $legends = $data->getLegends();
        $legendOverflow = null;
        $templatePageNumber = $this->odgParser->getPageNumber($templatePath . '.odg');
        for ($i = 1; $i <= $templatePageNumber; $i++) {
            //Add new Page to PDF
            $pdfPages[$i] = new PDFPage($pdf, $data, $conf, $i, $templatePath);
            //Fill PDF Page
            $this->odgParser->getElements($pdfPages[$i], $templatePath . '.odg', $i);
            $pdfPages[$i]->makePDFPage();

            //$this->preparePageNo($pdf);

            if ($pdfPages[$i]->containsLegend()) {
                //if the page has a place for the legend images, return the overflow
                $legendOverflow = $pdfPages[$i]->getLegendOverflow();

            }
        }
        return $legendOverflow;
    }

    protected function createSnapshots($pdf, MapData $data, $conf, $templatePath)
    {
        $features = $data->getFeatures();
        $snapshotList = array();
        foreach ($features as $feature) {
            //Create new MapData to create a new page with
            $snapshot = clone($data);
            $snapshot->setScale(null);

            $featureList = array();
            if (array_key_exists('MASSNAHMEVORGID', $feature)) {
                $id = $feature['MASSNAHMEVORGID'];
                $snapshot->addToExtra('description',
                    'Vorgang: ' . $data->getFromExtra('vorgangId') . " Maßnahme: " . $id);

                //bundle all polygons with the same ID
                foreach ($features as $key => $compFeature) {
                    if ($id == $compFeature['MASSNAHMEVORGID']) {
                        array_push($featureList, $compFeature);
                        //delete feature from list to avoid duplicates
                        unset($features[$key]);
                    }
                }

                //don't bundle polygons
                //array_push($featureList, $feature);

            }

            if (!empty($featureList)) {
                //set $feature as only feature
                $snapshot->setFeatures($featureList);

                array_push($snapshotList, $snapshot);
            }
        }

        foreach ($snapshotList as $snap) {

            //Set Extent and center
            $snap->fitExtentToFeatures();

            //add new page
            $pdfFeaturePage = new PDFPage($pdf, $snap, $conf, 1, $templatePath);

            //Fill PDF Page
            $this->odgParser->getElements($pdfFeaturePage, $templatePath . '.odg', 1);
            $pdfFeaturePage->makePDFPage();
            //$this->preparePageNo($pdf);
        }

    }

    protected function createLegendPage($pdf, MapData $data, $conf, $legendOverflow)
    {
        //add legend pages as long as there is unhandled legend overflow
        do {
            //$lpconf = array('orientation' => 'Portrait', 'pageSize' => array('height' => 29.7, 'width' => 21.0));
            $legendPage = new PDFPage($pdf, $data, $conf);

            $legendPage->forceLegend();

            $legendPage->setLegendOverflow($legendOverflow);
            $legendPage->makePDFPage();

            $legendOverflow = $legendPage->getLegendOverflow();
            //$this->preparePageNo($pdf);
        } while ($legendOverflow != null);

    }

    protected function preparePageNo(&$pdf)
    {
        // add pageNumber
        //Position 1,5 cm von unten
        $pdf->SetY(-7);
        //Arial kursiv 8
        $pdf->SetFont('Arial', 'I', intval(8));
        //Farbe
        $pdf->SetTextColor(0, 0, 0);
        //Seitenzahl
        $pdf->Cell(0, 10, 'Seite ' . $pdf->PageNo() . '/{nb}', 0, 0, 'C');
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