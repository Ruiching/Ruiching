<?php

use think\migration\Seeder;

class BaseSeeder extends Seeder
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
            ],
            [
                'sheet' => 'Event__Subject',
                'table' => 'event_subject',
                'field' => [
                    'event_id' => ['Event_Id (Event__Subject)', 'string'],
                    'subject_id' => ['Subject Id', 'string'],
                ]
            ],
            [
                'sheet' => 'Subject_B',
                'table' => 'subject',
                'field' => [
                    'subject_id' => ['Subject Id (Subject)', 'string'],
                    'subject_type' => ['Subject Type', 'string'],
                    'name' => ['Name (Subject)', 'string'],
                    'gender' => ['Gender', 'string'],
                    'country' => ['Countryd', 'string'],
                    'birth_date' => ['Birth Date', 'string'],
                    'birth_date_timestamp' => ['Birth Date Timestamp', 'int'],
                    'death_date' => ['Death Date', 'string'],
                    'death_date_timestamp' => ['Death Date Timestamp', 'int'],
                    'career' => ['Career', 'string'],
                    'archivement' => ['Archivement', 'string'],
                ]
            ],
            [
                'sheet' => 'Event__Evolve_Theme',
                'table' => 'event_evolve_theme',
                'field' => [
                    'event_id' => ['Event_Id (Event__Evolve_Theme)', 'string'],
                    'theme' => ['Theme', 'string'],
                ]
            ],
            [
                'sheet' => 'event Relation',
                'table' => 'event_relation',
                'field' => [
                    'relation_id' => ['Relation Id', 'string'],
                    'relation_type' => ['Relation Type', 'string'],
                    'source_event_id' => ['Source Event Id', 'string'],
                    'target_event_id' => ['Target Event Id', 'string'],
                ]
            ],
            [
                'sheet' => 'Event__Field',
                'table' => 'event_field',
                'field' => [
                    'event_id' => ['Event_Id (Event__Field)', 'string'],
                    'full_name' => ['full_name (event__field)', 'string'],
                    'level_0_name' => ['level_0_name (event__field)', 'string'],
                    'level_1_name' => ['level_1_name (event__field)', 'string'],
                    'level_2_name' => ['level_2_name (event__field)', 'string'],
                ]
            ],
            [
                'sheet' => 'Field',
                'table' => 'field',
                'field' => [
                    'field_id' => ['Field Id', 'string'],
                    'level_0_name' => ['Level 0 Name', 'string'],
                    'level_1_name' => ['Level 1 Name', 'string'],
                    'level_2_name' => ['Level 2 Name', 'string'],
                ]
            ],
            [
                'sheet' => 'Event__Tag',
                'table' => 'event_tag',
                'field' => [
                    'tag_id' => ['Tag Id', 'string'],
                    'event_id' => ['Event_Id (Event__Tag)', 'string'],
                ]
            ],
            [
                'sheet' => 'Tag',
                'table' => 'tag',
                'field' => [
                    'tag_id' => ['Tag Id (Tag)', 'string'],
                    'name' => ['Name (Tag)', 'string'],
                    'type' => ['Type (Tag)', 'string'],
                ]
            ],
            [
                'sheet' => 'Event__Reference',
                'table' => 'event_reference',
                'field' => [
                    'event_id' => ['Event_Id (Event__Reference)', 'string'],
                    'reference_id' => ['reference_id', 'string'],
                    'related_props' => ['Related Props', 'string'],
                ]
            ],
            [
                'sheet' => 'Literature',
                'table' => 'literature',
                'field' => [
                    'literature_id' => ['Literature Id', 'string'],
                    'type' => ['Type', 'string'],
                    'title' => ['Title', 'string'],
                    'url' => ['Url', 'string'],
                ]
            ],
            [
                'sheet' => 'Literature Textual Content',
                'table' => 'literature_textual_content',
                'field' => [
                    'literature_id' => ['Literature Id (Literature Textual Content)', 'string'],
                    'page_num' => ['Page Num', 'int'],
                    'text' => ['Text (Literature Textual Content)', 'string'],
                ]
            ],
            [
                'sheet' => 'Reference',
                'table' => 'reference',
                'field' => [
                    'literature_id' => ['Literature Id (Reference)', 'string'],
                    'reference_id' => ['Reference Id (Reference)', 'string'],
                    'text' => ['Text', 'string'],
                    'page_no' => ['page_no', 'int'],
                    'paragraph_index' => ['paragraph_index', 'int'],
                    'ner_result' => ['ner_result', 'string'],
                ]
            ],
            [
                'sheet' => 'Event Quality',
                'table' => 'event_quality',
                'field' => [
                    'event_id' => ['Event Id (Event Quality)', 'string'],
                    'ao_status' => ['Ao Status', 'int'],
                    'actual_status' => ['Actual Status', 'int'],
                ]
            ],
        ];


        $server = new \app\service\ExcelService();

        //导入文件
        foreach ($fileConfigs as $fileConfig) {
            $this->query('TRUNCATE TABLE '. $fileConfig['table']);
            $excelResult = $server->import($filePath, $fileConfig['sheet']);
            if (!empty($excelResult)) {
                $insertData = [];
                foreach ($excelResult as $item) {
                    $insertItem = [];
                    foreach ($fileConfig['field'] as $field => $key) {
                        if (isset($item[$key[0]]) && !empty($item[$key[0]])) {
                            $insertItem[$field] = $item[$key[0]];
                            if ($key[1] == 'int') {
                                $insertItem[$field] = intval($item[$key[0]]);
                            }
                        }
                    }
                    $insertData[] = $insertItem;
                }
                $this->table($fileConfig['table'])->insert($insertData)->saveData();
            }
        }
    }
}