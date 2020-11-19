<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Admin extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接受参数 keyword page
        $params = input();
        //搜索条件
        $where = [];
        if (! empty($params['keyword'])){
            $keyword = $params['keyword'];
            //模糊查询
            $where['t1.username'] = ['like' ,"%$keyword%"];
        }
        //分页查询
        $list = \app\common\model\Admin::where($where)->paginate();
        //SELECT t1.*, t2.role_name FROM pyg_admin t1 left join pyg_role t2 on t1.role_id = t2.id where username like '%a%' limit 0, 2;
        $list = \app\common\model\Admin::alias('t1')
            ->join('pyg_role t2','t1.role_id=t2.id')
            ->field('t1.*','t2.role_name')
            ->where($where)
            ->paginate(10);
        //返回数据
        $this->ok($list);

    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //接收数据
        $params = input();
        //参数验证
        $validate = $this->validate($params, [
            'username' =>'require|unique:admin|max:9',
            'password' =>'length:6,20',
            'role_id|所属角色' => 'require|integer|gt:0',
            'email' =>'require|email',
        ]);
        //如果验证失败 返回验证信息
        if ($validate !==true){
            $this->fail($validate);
        }
        //如果 密码未填写
        if (empty($params['password'])){
            $params['password'] = '123456';
        }
        $params['password'] = encrypt_password($params['password']);
        $params['nickname']=$params['username'];
        //添加数据入库
        $info = \app\common\model\Admin::create($params,true);

        $admin = \app\common\model\Admin::find($info['id']);

        $this->ok($admin);

    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //判断id合法
        if (is_numeric($id)){
            $info = \app\common\model\Admin::find($id);
            if ($info){
                $this->ok($info);
            }
        }

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
        //获取参数
        if ($id == 1){
            $this->fail('超级管理员不能修改');
        }

        $params = input();

        if (!empty($params['type']) && $params['type'] == 'reset_pwd' ){
            $password = encrypt_password('123456');
            \app\common\model\Admin::update(['password' => $password], ['id' => $id], true);
        }else{
            $validate = $this->validate($params, [
                'email|邮箱' => 'email',
                'role_id|所属角色' => 'integer|gt:0',
                'nickname|昵称' => 'max:50',
            ]);
            if($validate !== true){
                $this->fail($validate);
            }
            //修改数据
            unset($params['username']);
            unset($params['password']);
            \app\common\model\Admin:: update($params,['id'=>$id],true);
        }

        //查询返回修改后的数据
        $info = \app\common\model\Admin::find($id);
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
        //判断
        if (is_numeric($id)){
            //删除数据（不能删除超级管理员admin、不能删除自己）
            if($id == 1){
                $this->fail('不能删除超级管理员');
            }
            if($id == input('user_id')){
                $this->fail('删除自己? 你在开玩笑嘛');
            }
            \app\common\model\Admin::destroy($id);
            //返回数据
            $this->ok();
        }
    }
}
