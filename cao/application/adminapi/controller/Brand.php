<?php

namespace app\adminapi\controller;

use Faker\Provider\Image;
use think\Controller;
use think\Request;

class Brand extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {

        //接收参数  cate_id keyword page
        $params = input();
        //print_r($params);exit();
        $where = [];
        if (isset($params['cate_id']) && !empty($params['cate_id'])){
            //分类下的品牌列表
            $where['cate_id'] = $params['cate_id'];
            //查询数据
            $list = \app\common\model\Brand::where($where)->field('id,name')->select();

        }else{
            //分页+搜索列表
            if (isset($params['keyword']) && !empty($params['keyword'])){
                $keyword = $params['keyword'];
                $where['t1.name'] = ['like',"%$keyword%"];
                //分页查询数据
                $list = \app\common\model\Brand::alias('t1')
                    ->join('pyg_category t2', 't1.cate_id=t2.id', 'left')
                    ->field('t1.*, t2.cate_name')
                    ->where($where)
                    ->paginate(10);
            }
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
        //接受参数
        $params = input();
        $validate = $this->validate($params,[
            'name' => 'require',
            'cate_id' => 'require|integer|gt:0',
            'is_hot' => 'require|in:0,1',
            'sort' => 'require|between:0,9999'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        //生成缩略图
        if (isset($params['logo']) && !empty($params['logo']) && is_file('.' . $params['logo'])){
            \think\Image::open('.'.$params['logo'])->thumb(200,100)->save('.'.$params['logo']);
        }
        //添加数据
        $data =  \app\common\model\Brand::create($params,true);
        $info = \app\common\model\Brand::find($data['id']);
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
        //查询一条数据
        if ( !is_numeric($id)){
            $this->fail('参数不合法');
        }
        $info = \app\common\model\Brand::find($id);
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
        //接收数据
        $params = input();
        //数据验证
        $validate = $this->validate($params,[
            'name' => 'require',
            'cate_id' => 'require|integer|gt:0',
            'is_hot' => 'require|in:0,1',
            'sort' => 'require|between:0,9999'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        if (isset($params['logo']) && !empty($params['logo']) && is_file('.'.$params['logo'])){
            \think\Image::open('.'.$params['logo'])->thumb(200,100)->save('.'.$params['logo']);
        }

        //修改数据
        \app\common\model\Brand::update($params,['id'=>$id],true);
        $info = \app\common\model\Brand::find($id);
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
        //判断 品牌下是否有商品
        $total = \app\common\model\Goods::where('brand_id', $id)->count();
        if($total > 0){
            $this->fail('品牌下有商品，不能删除');
        }
        //删除品牌
        \app\common\model\Brand::destroy($id);
        //返回结果
        $this->ok();
    }
}
