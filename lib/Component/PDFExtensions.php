<?php

namespace Wheregroup\MapExport\CoreBundle\Component;

use FPDI;

class PDFExtensions extends FPDI
{

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->h;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->w;
    }

    function TextWithDirection($x, $y, $txt, $direction = 'R')
    {
        if ($direction == 'R') {
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 1, 0, 0, 1, $x * $this->k,
                ($this->h - $y) * $this->k, $this->_escape($txt));
        } elseif ($direction == 'L') {
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', -1, 0, 0, -1, $x * $this->k,
                ($this->h - $y) * $this->k, $this->_escape($txt));
        } elseif ($direction == 'U') {
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 0, 1, -1, 0, $x * $this->k,
                ($this->h - $y) * $this->k, $this->_escape($txt));
        } elseif ($direction == 'D') {
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 0, -1, 1, 0, $x * $this->k,
                ($this->h - $y) * $this->k, $this->_escape($txt));
        } else {
            $s = sprintf('BT %.2F %.2F Td (%s) Tj ET', $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        }
        if ($this->ColorFlag) {
            $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        }
        $this->_out($s);
    }


    function TextWithDirection2($x, $y, $txt, $direction = 'R')
    {
        switch ($direction) {
            case 'R':
                $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 1, 0, 0, 1, $x * $this->k,
                    ($this->h - $y) * $this->k, $this->_escape($txt));
                break;
            case 'L':
                $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', -1, 0, 0, -1, $x * $this->k,
                    ($this->h - $y) * $this->k, $this->_escape($txt));
                break;
            case 'U':
                $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 0, 1, -1, 0, $x * $this->k,
                    ($this->h - $y) * $this->k, $this->_escape($txt));
                break;
            case 'D':
                $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 0, -1, 1, 0, $x * $this->k,
                    ($this->h - $y) * $this->k, $this->_escape($txt));
                break;
            default:
                $s = sprintf('BT %.2F %.2F Td (%s) Tj ET', $x * $this->k, ($this->h - $y) * $this->k,
                    $this->_escape($txt));
        }
        if ($this->ColorFlag) {
            $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        }
        $this->_out($s);
    }


    function TextWithDirection3($x, $y, $txt, $direction = 'R')
    {
        switch ($direction) {
            case 'R':
                $s = 'BT 1 0 0 1 ' . $x * $this->k . ' ' . ($this->h - $y) * $this->k . ' Tm (' . $this->_escape($txt) . ') Tj ET';
                break;
            case 'L':
                $s = 'BT -1 0 0 -1 ' . $x * $this->k . ' ' . ($this->h - $y) * $this->k . ' Tm (' . $this->_escape($txt) . ') Tj ET';
                break;
            case 'U':
                $s = 'BT 0 1 -1 0 ' . $x * $this->k . ' ' . ($this->h - $y) * $this->k . ' Tm (' . $this->_escape($txt) . ') Tj ET';
                break;
            case 'D':
                $s = 'BT 0 -1 1 0 ' . $x * $this->k . ' ' . ($this->h - $y) * $this->k . ' Tm (' . $this->_escape($txt) . ') Tj ET';
                break;
            default:
                $s = 'BT ' . $x * $this->k . ' ' . ($this->h - $y) * $this->k . ' Td (' . $this->_escape($txt) . ') Tj ET';
        }
        if ($this->ColorFlag) {
            $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        }
        $this->_out($s);
    }

}