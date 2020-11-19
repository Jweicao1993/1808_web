<?php

namespace app\adminapi\controller;

use Encore\Admin\Widgets\Collapse;
use think\Controller;
use think\Image;
use think\Request;

class Category extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接收参数 pid  type
       $params = input();
       $where =[];
       if (isset($params['pid'])){
           $where['pid'] = $params['pid'];
       }
        //查询数据
        $list = \app\common\model\Category::where($where)->select();
       //转化为标准 二维数组结构
        $list = (new Collapse($list))->toArray();
        if (!isset($params['type']) && $params['type'] != 'list'){
            //转化为无限及分类列表
            $list = get_cate_list($list);
        }
        //返回数据
        $this->ok($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //接收参数
        $params =input();
        //参数验证
        $validate = $this->validate($params, [
            'cate_name' => 'require|length:2,20',
            'pid' => 'require|integer|egt:0',
            'is_show' => 'require|in:0,1',
            'is_hot' => 'require|in:0,1',
            'sort' => 'require|between:0,9999',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        //添加数据 (处理 pid_path pid_path_name level)
        if ($params['pid'] == 0){
            //顶级分类
            $params['pid_path'] = 0;
            $params['pid_path_name'] = '';
            $params['level'] = 0;
        }else{
            //不是顶级分类 查询其上级分类
            $p_info = \app\common\model\Category::where('id',$params['pid'])->find();
            if (empty($p_info)){
                //没查到 父级
                $this->fail('数据异常 , 请稍后再试');
            }
            $params['pid_path'] = $p_info['pid_path'].'_'.$p_info['id'];
            $params['pid_path_name'] = $p_info['pid_path_name'].'_'.$p_info['cate_name'];
            $params['level'] = $p_info['level'] + 1;
        }
        //logo 图片处理
        $params['image_url'] = isset($params['logo']) ? $params['logo'] : "";
        //生成缩略图 /uploads/category/20190716/1232.jpg
        if (isset($params['image_url']) && !empty($params['image_url']) && is_file($params['image_url'])){
            Image::open('.'.$params['image_url'])->thumb(200,100)->save('.'.$params['image_url']);
        }
        $cate = \app\common\model\Category::create($params,true);
        //查询 添加的数据
        $info = \app\common\model\Category::find($cate['id']);
        //返回数据
        $this->ok($info);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
        if (!is_numeric($id)){
            $this->fail('参数不合法');
        }
        $info = \app\common\model\Category::find($id);
        //返回数据
        $this->ok($info);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
            'cate_name' => 'require|length:2,20',
            'pid' => 'require|integer|egt:0',
            'is_show' => 'require|in:0,1',
            'is_hot' => 'require|in:0,1',
            'sort' => 'require|between:0,9999',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }

        //修改数据（处理pid_path pid_path_name level  image_url）
        if($params['pid'] == 0){
            //顶级分类
            $params['pid_path'] = 0;
            $params['pid_path_name'] = '';
            $params['level'] = 0;
        }else{
            //不是顶级分类，查询其上级分类
            $p_info = \app\common\model\Category::where('id', $params['pid'])->find();
            if(empty($p_info)){
                //没查到父级
                $this->fail('数据异常,请稍后再试');
            }
            $params['pid_path'] = $p_info['pid_path'] . '_' . $p_info['id'];
            $params['pid_path_name'] = $p_info['pid_path_name'] . '_' . $p_info['cate_name'];
            $params['level'] = $p_info['level'] + 1;
        }
        if(isset($params['logo']) && !empty($params['logo'])){
            $params['image_url'] = $params['logo'];
        }
        \app\common\model\Category::update($params, ['id' => $id], true);
        //返回数据
        $info = \app\common\model\Category::find($id);
        $this->ok($info);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!is_numeric($id)){
            $this->fail('参数不合法');
        }
        //判断分类下 是否 与子类存在
        $total = \app\common\model\Category::find($id);
        if ($total > 0){
            $this->fail('分类下有子分类 . 无法删除');
        }
        \app\common\model\Category::destroy($id);
        //返回数据
        $this->ok();
    }
}
