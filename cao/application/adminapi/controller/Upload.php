<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Upload extends BaseApi
{
    /**
     * 单图片上传
     *
     * @return \think\Response
     */
    public function logo()
    {
        //接收参数  type
        $type = input('type');
        if ($type) {
            $this->fail('缺少参数');
        }

        // 获取表单上传文件 例如上传了001.jpg
        $file = \request()->file('image');
        if (empty($file)) {
            $this->fail('文件必须上传');
        }
        //图片移动 /public/uploads/goods/  /public/uploads/category/  /public/uploads/brand/
        $info = $file->validate(['size' => 10 * 1024 * 1024, 'ext' => 'jpg,jpeg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . $type);
        if ($info) {
            //返回图片路径  /uploads/category/20190715/dsfdsfas.jpg
            $logo = DS . 'uploads' . DS . $type . DS . $info->getSaveName();
            $this->ok($logo);
        } else {
            //返回报错
            $msg = $file->getError();
            $this->fail($msg);
            //$this->fail('上传失败');
        }
    }

    /**
     * 多图片上传
     *
     * @return \think\Response
     */
    public function images()
    {
        //接收参数 type
        $type = input('type', 'goods');
        if (empty($type)) {
            $this->fail('缺少参数');
        }
        // 获取表单上传文件 例如上传了001.jpg
        $files = \request()->file('images');
        //遍历数组逐个上传文件
        $data = ['success' => [], 'error' => []];
        foreach ($files as $file) {
            //移动文件到指定目录下 /public/uploads/goods/目录下
            $dir = ROOT_PATH . 'public' . DS . 'uploads' . DS . $type;
            if(!is_dir($dir)){
                mkdir($dir);
            }
            $info = $file->validate(['size' => 10*1024*1024, 'ext'=>'jpg,jpeg,png,gif'])->move($dir);
            if ($info){
                //成功拼接图片路径
                $path =DS.'uploads'.DS.$type.$info->getSaveName();
                $data['success']=$path;
            }else{
                //失败获取错误信息  getInfo()获取文件原始信息   getError()获取错误信息
                $data['error'][] = [
                    'name' => $file->getInfo('name'),
                    'msg' => $file->getError()
                ];
            }
        }
        $this->ok($data);
    }

}
