<?php

use think\migration\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $filePath = root_path() . 'view/10-07.xlsx';
        var_dump($filePath);
        $fileConfigs = [
            [
                'sheet' => 'event',
                'table' => 'event',
                'field' => [
                    'event_id' => ['Event Id', 'string'],
                    'name' => ['Name', 'string'],
                    'action' => ['Action', 'string'],
                    'object_attributive' => ['object_attributive', 'string'],
                    'object' => ['Object', 'string'],
                    'time' => ['time', 'string'],
                    'formated_time' => ['formated_time', 'string'],
                    'time_granularity' => ['time_granularity', 'int'],
                    'timestamp' => ['timestamp', 'int'],
                    'location' => ['Location', 'string'],
                    'formated_location_id' => ['Formated Location Id', 'int'],
                    'introduction' => ['Introduction', 'string'],
                    'signifiance' => ['Signifiance', 'string'],
                    'importance' => ['Importance', 'int'],
                    'quality_score' => ['quality_score', 'int'],
                ]
            ]
        ];


        $server = new \app\service\ExcelService();

        //导入文件
        $eventIds = [];
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
                    if ($fileConfig['sheet'] == 'event') {
                        $eventIds[] = $insertItem['event_id'];
                    }
                    if ($fileConfig['sheet'] == 'Event__Field' && isset($eventIds[$k])) {
                        $insertItem['event_id'] = $eventIds[$k];
                    }
                    $insertData[] = $insertItem;
                }
                $this->table($fileConfig['table'])->insert($insertData)->saveData();
            }
        }
    }
}