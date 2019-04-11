<?php

namespace Application\Traits;
use PHPExcel;
use PHPExcel_Writer_Excel5;


/**
 * Class Excel
 * @package Application\Traits
 * @author Xuman
 * @version $Id$
 */
trait Excel
{
    public function createExcel($objPHPExcel, $data)
    {
        if(!$objPHPExcel){
            $objPHPExcel = new PHPExcel();
        }
        function convertUTF8($str)
        {
            if (empty($str)) return '';
            return $str;
            //return iconv('utf-8', 'utf-8', $str);
        }
        $count = count($data);
        $isFirstLoop = true;
        for ($i = 2; $i <= $count + 1; $i++) {
            $colNumber = 1;
            foreach($data[$i - 2] as $name => $value)
            {
                $colName = $this->dec2az($colNumber);
                if($isFirstLoop) {
                    $objPHPExcel->getActiveSheet()->setCellValue($colName . 1, convertUTF8($name));
                }
                $objPHPExcel->getActiveSheet()->setCellValue($colName . $i, convertUTF8($value));
                $colNumber++;
            }
            $isFirstLoop = false;
        }
        return $objPHPExcel;
    }

    public function dec2az($cNum)
    {
        if($cNum <= 26)
        {
            return chr(64 + $cNum);
        } else {
            $t = [];
            while($cNum > 26)
            {
                $t[] = chr(64 + $cNum%26);
                $cNum = floor($cNum / 26);
            }
            if($cNum > 0) {
                $t[] = chr(64 + $cNum);
            }
            return implode('',array_reverse($t));
        }
    }
} 