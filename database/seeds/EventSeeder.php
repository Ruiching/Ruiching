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
        $filePath = root_path() . 'view/09-26.xlsx';
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
                    //处理event中的年份
                    if ($fileConfig['sheet'] == 'event') {
                        $timeArr = explode('年', $insertItem['time']);
                        $insertItem['min_year'] = $timeArr[0];
                        $insertItem['max_year'] = $timeArr[0];

                        if (strpos($timeArr[0], '～') !== false) {
                            $timeArr = explode('～', $timeArr[0]);
                            $insertItem['min_year'] = $timeArr[0];
                            $insertItem['max_year'] = $timeArr[1];
                        }
                        if (strpos($timeArr[0], '-') !== false) {
                            $timeArr = explode('-', $timeArr[0]);
                            $insertItem['min_year'] = $timeArr[0];
                            $insertItem['max_year'] = $timeArr[1];
                        }
                        if (strpos($timeArr[0], '世纪') !== false) {
                            if (strpos($timeArr[0], '世纪中叶') !== false) {
                                $timeArr = explode('世纪', $timeArr[0]);
                                $minYear = ($timeArr[0] - 1) * 100 + 40;
                                $maxYear = ($timeArr[0] - 1) * 100 + 60;
                                $insertItem['min_year'] = $minYear;
                                $insertItem['max_year'] = $maxYear;
                            } else {
                                $timeArr = explode('世纪', $timeArr[0]);
                                $year = ($timeArr[0] - 1) * 100 + $timeArr[1];
                                $insertItem['min_year'] = $year;
                                $insertItem['max_year'] = $year;
                            }
                        }
                        if (strpos($timeArr[0], '公元') !== false && strpos($timeArr[0], '公元前') === false) {
                            $timeArr = explode('公元', $timeArr[0]);
                            $insertItem['min_year'] = $timeArr[1];
                            $insertItem['max_year'] = $timeArr[1];
                        }
                        if (strpos($timeArr[0], '公元前') !== false) {
                            $timeArr = explode('公元前', $timeArr[0]);
                            $insertItem['min_year'] = '-' . $timeArr[1];
                            $insertItem['max_year'] = '-' . $timeArr[1];
                        }
                    }

//                    if ($fileConfig['sheet'] == 'event') {
//                        $eventIds[] = $insertItem['event_id'];
//                    }
//                    if ($fileConfig['sheet'] == 'Event__Field' && isset($eventIds[$k])) {
//                        $insertItem['event_id'] = $eventIds[$k];
//                    }
                    $insertData[] = $insertItem;
                }
                $this->table($fileConfig['table'])->insert($insertData)->saveData();
            }
        }
    }
}