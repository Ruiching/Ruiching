<?php

use think\migration\Seeder;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        $filePath = root_path() . 'view/09-26.xlsx';
        var_dump($filePath);
        $fileConfigs = [
            [
                'sheet' => 'Event__Subject',
                'table' => 'event_subject',
                'field' => [
                    'event_id' => ['Event_Id (Event__Subject)', 'string'],
                    'subject_id' => ['Subject Id', 'string'],
                ]
            ],
        ];


        $server = new \app\service\ExcelService();

        //导入文件
        $eventModel = new \app\model\Event();
        $eventIds = $eventModel->column('event_id');

        foreach ($fileConfigs as $fileConfig) {
            $this->query('TRUNCATE TABLE '. $fileConfig['table']);
            $excelResult = $server->import($filePath, $fileConfig['sheet']);
            if (!empty($excelResult)) {
                $insertData = [];
                foreach ($excelResult as $k => $item) {
                    $insertItem = [];
                    foreach ($fileConfig['field'] as $field => $key) {
                        if (isset($item[$key[0]]) && !empty($item[$key[0]])) {
                            $insertItem[$field] = $item[$key[0]];
                            if ($key[1] == 'int') {
                                $insertItem[$field] = intval($item[$key[0]]);
                            }
                        }
                    }
                    if ($fileConfig['sheet'] == 'Event__Subject' && isset($eventIds[$k])) {
                        $insertItem['event_id'] = $eventIds[$k];
                    }
                    $insertData[] = $insertItem;
                }
                $this->table($fileConfig['table'])->insert($insertData)->saveData();
            }
        }
    }
}