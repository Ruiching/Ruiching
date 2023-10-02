<?php

namespace app\service;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelService
{

    //Excel导出
    public function export($fileName = '', $headArr = [], $data = [])
    {
        include_once 'xlsxwriter.class.php';
        set_time_limit(0);
        ini_set("memory_limit", "1024M");
        $fileName .= "-" . date("YmdHi", time()) . ".xls";
        /*$objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties();
        $key = ord("A"); // 设置表头
        foreach ($headArr as $v) {
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $key += 1;
        }
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($data as $key => $rows) { // 行写入
            $span = ord("A");
            foreach ($rows as $keyName => $value) { // 列写入
                $objActSheet->setCellValue(chr($span) . $column, removeEmoji($value));
                $span++;
            }
            $column++;
        }
        $fileName = iconv("utf-8", "utf-8", $fileName); // 重命名表
        $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表
        header('Content-type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$fileName");
        header('Cache-Control: max-age=0');
        header ('Pragma: public');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        $objWriter->save('php://output'); // 文件通过浏览器下载
        exit();*/

        $header = [];
        $writer = new \XLSXWriter();
        foreach ($headArr as $v) {
            $header[$v] = "string";
        }
        $writer->writeSheetHeader('Sheet1', $header );
        foreach($data as $row){
            $writer->writeSheetRow('Sheet1', $row );
        }
        //$writer->writeToFile($fileName);
        header('Content-type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$fileName");
        header('Cache-Control: max-age=0');
        header ('Pragma: public');
        $writer->writeToStdOut();
        exit(0);
    }

    //数据导入
    public function import($file, $sheetName = '')
    {
        // 有Xls和Xlsx格式两种
        $fileArr = explode('.', $file);
        $fileExt = $fileArr[count($fileArr) - 1];
        if ($fileExt == 'xls') {
            $reader = IOFactory::createReader('Xls'); //加载excel对象
        } else {
            $reader = IOFactory::createReader('Xlsx'); //加载excel对象
        }
        $PHPExcel = $reader->load($file);
        $sheet = $PHPExcel->getSheetByName($sheetName); //获取指定的sheet表
        $rows = $sheet->getHighestRow();//行数
        $cols = $sheet->getHighestColumn();//列数

        $data = [];
        $fieldName = [];
        //循环第一行，获取字段名
        for ($fj = 'A'; $fj <= $cols; $fj++) { //列数是以A列开始
            $value = $sheet->getCell($fj . '1')->getValue();
            if(empty($value)) {
                $value = null;
            }else{
                $value = (string)trim($sheet->getCell($fj . '1')->getValue());
            }
            $fieldName[$fj] = $value;
        }


        for ($i = 2; $i <= $rows; $i++){ //行数是以第1行开始
            $dataItem = [];
            for ($j = 'A'; $j <= $cols; $j++) { //列数是以A列开始
                $value = $sheet->getCell($j . $i)->getValue();
                if(empty($value)) {
                    $value = null;
                }else{
                    $value = (string)$sheet->getCell($j . $i)->getValue();
                }
                $dataItem[$fieldName[$j]]= $value;
            }
            $data[] = $dataItem;
        }
        return $data;
    }
}