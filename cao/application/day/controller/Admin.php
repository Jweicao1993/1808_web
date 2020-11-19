<?php

namespace app\day\controller;

use app\adminapi\controller\BaseApi;
use think\Controller;

class Admin extends BaseApi
{
    /*
     * 管理员列表
     *
     */
    public function index()
    {
        //接收参数
        $params = input();
        $where = [];
        if (isset($params['keyword']) && !empty($params['keyword'])){
            $keyword = $params['keyword'];
            $where['nickname'] = ['like' ,"%$keyword%"];
        }
        $list = \app\day\model\Admin::alias('t1')
            ->join('pyg_role t2','t1.role_id=t2.id','left')
            ->field('t1.*,t2.role_name')
            ->where($where)
            ->paginate(10);
        $this->ok($list);
    }

    /*
     * 管理员添加
     */
    public function save()
    {
        //接收数据
        $params = input();
        //参数验证
        $validate = $this->validate($params,[
            'username|用户名' =>'require|unique:admin|max:20',
            'password|用户密码' => 'number|length:6,16',
            'email|邮箱'    => 'email',
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        //如果密码未设置 添加 默认密码
        if (empty($params['password'])){
            $params['password'] = 123456;
        }
        //密码加密处理
        $params['password'] = encrypt_password($params['password']);
        //昵称处理
        if (!isset($params['nickname']) && empty($params['nickname'])){
            $params['nickname'] =$params['username'];
        }
        //添加入库
        $admin = \app\day\model\Admin::create($params,true);
        $info = \app\day\model\Admin::find($admin['id']);
        $this->ok($info);
    }

    /*
     * 管理员详情
     */
    public function read($id)
    {
        if (!is_numeric($id)){
            $this->fail('参数id不合法');
        }
        $info = \app\day\model\Admin::find($id);
        $this->ok($info);
    }

    /*
     * 管理员修改
     */
    public function update($id)
    {
        if ($id == 1){
            $this->fail('超级管理员不能修改');
        }
        $params =input();
        if(!empty($params['type']) && $params['type'] == 'reset_pwd'){
            $password = encrypt_password('123456');
            \app\common\model\Admin::update(['password' => $password], ['id' => $id], true);
        }else{
            //参数检测
            $validate = $this->validate($params, [
                'email|邮箱' => 'email',
                'role_id|所属角色' => 'integer|gt:0',
                'nickname|昵称' => 'max:50',
            ]);
            if($validate !== true){
                $this->fail($validate);
            }
            //修改数据（用户名不让改）
            unset($params['username']);
            unset($params['password']);
            \app\common\model\Admin::update($params, ['id' => $id], true);
        }
        $info = \app\common\model\Admin::find($id);
        //返回数据
        $this->ok($info);



    }
    /*
     * 管理员删除
     * int $id
     */
    public function delete($id)
    {
        if (!is_numeric($id)){
            $this->fail('参数id 不合法');
        }
        if ($id == 1){
            $this->fail('超级管理员不能删除');
        }
        if ($id == input('user_id')){
            $this->fail('删除自己 ?? 你在开玩笑');
        }
        \app\day\model\Admin::destroy($id);
        $this->ok();
    }
}
