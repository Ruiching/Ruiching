<?php

namespace app\service;

use think\File;
use think\Image;
use \think\facade\Filesystem;

class UploadService
{
    public function uploadOne($fileName = 'file', $config = [])
    {
        $result = [];
        try {
            $file = request()->file($fileName);
            if ($file) {
                $savePath = '';
                if (isset($config['savePath'])) {
                    $savePath = $config['savePath'];
                }
                // 获取文件信息
                $ext = $file->getOriginalExtension();
                $originalFile = $file->getOriginalName();
                $fileInfo = pathinfo($file);
                $originalDirname = $fileInfo['dirname'];
                $originalBasename = $fileInfo['basename'];
                $originalPath = "{$originalDirname}/{$originalBasename}";
                $originalSize = $file->getSize();

                // 文件地址转文件类
                $fileUp = new File($originalPath);
                $saveName = md5($fileUp->md5()."_".time()) . '.' . $ext;

                //不同文件，储存不同的文件夹
                $folder = public_path() . '/uploads/' . $savePath; //存文件目录
                if(!file_exists($folder))mkdir($folder, 0700,TRUE);
                $fileUp->move($folder, $folder.'/'.$saveName);

                if ($saveName) {
                    $result = [
                        'error_code' => 0,
                        'url' => '/uploads/' . $savePath . "/" . $saveName,
                        'file_path' => $folder. "/" . $saveName,
                        'file_title' => $saveName,
                        'file_original' => $originalFile,
                        'file_type' => '.' . $ext,
                        'file_size' => $originalSize,
                    ];
                } else {
                    $result = [
                        'error_code' => 2,
                        'msg' => "上传错误"
                    ];
                }
            }
        } catch (\Exception $e) {
            $result = [
                'error_code' => 2,
                'msg' => $e->getMessage()
            ];
        }
        return $result;
    }

    public function base64Upload($base64, $saveDir = '', $fileName = '')
    {
        $saveDir = empty($saveDir) ? 'qrcode' : $saveDir;
        $rootPath = public_path() . '/uploads/';
        $savePath = $saveDir . '/' . date('Ymd', time()) . '/';
        if (!file_exists($rootPath . $savePath)) {
            mkdir($rootPath . $savePath, 0777, true);
        }
        if (empty($fileName)) {
            $fileName = md5(str_random(8) . '_' . rand(1, 999) . '_' . date('YmdHis', time()));
        }

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
            $type = $result[2];

            if (!in_array($type, array('pjpeg', 'jpeg', 'jpg', 'gif', 'bmp', 'png'))) {
                return [
                    'error_code' => 2,
                    'msg' => '图片上传类型错误'
                ];
            }

            $fileUrl = $rootPath . $savePath . $fileName . '.' . $type;
            file_put_contents($fileUrl, base64_decode(str_replace($result[1], '', $base64)));

            return [
                'error_code' => 0,
                'name' => $fileName . '.' . $type,
                'url' => '/uploads/' . $savePath . $fileName . '.' . $type,
                'path' => $rootPath . $savePath,
                'file_path' => $fileUrl
            ];
        } else {
            return [
                'error_code' => 2,
                'msg' => '图片上传失败'
            ];
        }
    }
}