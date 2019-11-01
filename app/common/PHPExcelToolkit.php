<?php

namespace app\common;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PHPExcelToolkit
{

    public static function export($data, $info)
    {
        // Create new PHPExcel object
        $spreadsheet = new Spreadsheet();
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator($info['creator'])
            ->setLastModifiedBy($info['creator'])
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Export file");

        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle($info['sheetName']);    

        $index = 0;
        foreach ($info['title'] as $key => $value) {
            $char = chr(65 + $index);
            ++$index;
            $activeSheet->setCellValue("{$char}1", $value);
            $activeSheet->getColumnDimension($char)->setAutoSize(true);
        }

        $activeSheet->getRowDimension('1')->setRowHeight(18);
        if (!empty($data)) {
            $index = 2;
            foreach ($data as $one) {
                $i = 0;
                foreach ($info['title'] as $key => $value) {
                    $cellValue = $one[$key];
                    if ($key == 'created_time') {
                        $cellValue = date('Y-m-d', $cellValue);
                    }
                    $char = chr(65 + $i);
                    ++$i;
                    $activeSheet->setCellValue("{$char}{$index}", $cellValue);
                }
                $activeSheet->getRowDimension($index)->setRowHeight(18);
                ++$index;
            }
        }

        $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return $objWriter;
    }
}
