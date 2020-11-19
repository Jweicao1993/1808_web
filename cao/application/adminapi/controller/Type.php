<?php

namespace app\adminapi\controller;

use app\common\model\Attribute;
use app\common\model\Spec;
use app\common\model\SpecValue;
use think\Controller;
use think\Db;
use think\Exception;
use think\Model;
use think\Request;

class Type extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //查询数据类型
        $list = \app\common\model\Type::select();
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
        $params = [
            'type_name' => '米莱',
            'spec' => [
                ['name' => '颜色', 'sort' => 50, 'spec_value'=>['黑色', '白色', '金色']],
                //['name' => '颜色1', 'sort' => 50, 'value'=>['', '']],
                ['name' => '内存', 'sort' => 50, 'spec_value'=>['64G', '128G', '256G']],
            ],
            'attr' => [
                ['name' => '毛重', 'sort'=>50, 'attr_value' => []],
                ['name' => '产地', 'sort'=>50, 'attr_value' => ['进口', '国产','']],
            ]
        ];

        //接受参数
//        $params = input();
//        $validate = $this->validate($params,[
//            'type_name|模型名称' => 'require|max:20',
//            'spec|规格' => 'require|array',
//            'attr|属性' => 'require|array',
//        ]);
//        if ($validate !== true){
//            $this->fail($validate);
//        }

        //循环过滤规格 数组
        foreach( $params['spec'] as  $key => $value){
            //判断如果规格名 为空 删除整个规格数组
            if (empty(trim($value['name']))){
                unset($params['spec'][$key]);
                continue;
            }
            //内层循环 处理规格值
            foreach($value['spec_value'] as $k => $v){
                //规格值为空 删除规格 该 规格值
                if (empty(trim($v))){
                    unset($params['spec'][$key]['spec_value'][$k]);
                }
            }
            //过滤规格值 的整个数组 为空
            if (empty($params['spec'][$key]['spec_value'])){
                //删除 整个 规格 数组
                unset($params['spec'][$key]);
            }
        }

        //循环 过滤属性数组
        foreach($params['attr'] as $key => $value){
            if (empty(trim($value['name']))){
                unset($params['attr'][$key]);
                continue;
            }
            //内层循环 处理规格值
            foreach($value['attr_value'] as $k => $v){
                //规格值为空 删除规格 该 规格值
                if (empty(trim($v))){
                    unset($params['attr'][$key]['attr_value'][$k]);
                }
            }
        }
        //开启事务
        Db::startTrans();
        try {
            //将模型数据入库保存
            $type = \app\common\model\Type::create($params,true);
            //规格是多个，处理出规格的结果集（二维数组）
            $specs = [];
            foreach ($params['spec'] as $val){
                $row = [
                    'type_id' => $type->id,
                    'spec_name' => $val['name'],
                    'sort' => $val['sort'],
                ];
                $specs[] =$row;
            }
            //将规格数据入库保存
            $spec = model('Spec')->saveAll($specs,true);

            //规格值是多个，处理出规格的结果集（二维数组）
            $spec_values = [];
            foreach($params['spec'] as $key => $val) {
                foreach ($val['spec_value'] as $v) {
                    $data = [
                        'spec_id' => $spec[$key]['id'],
                        'type_id' => $type->id,
                        'spec_value' => $v
                    ];
                    $spec_values[] = $data;
                }
            }
            //将规格值数据入库保存
            $spec_value_model = new \app\common\model\SpecValue();
            $spec_value_model->saveAll($spec_values);
            //model('Specvalue')->saveAll($spec_values,true);
            //批量添加属性名称属性值
            $attrs = [];
            foreach($params['attr'] as $attr){
                $row = [
                    'attr_name' => $attr['name'],
                    'attr_values' => implode(',', $attr['attr_value']),
                    'sort' => $attr['sort'],
                    'type_id' => $type->id,
                ];
                $attrs[] = $row;
            }
            //批量添加
            $attr_model = new \app\common\model\Attribute();
            $attr_model->saveAll($attrs);
            //事务提交
            Db::commit();
            $type = \app\common\model\Type::find($type['id']);
            $this->ok($type);
        }catch(\Exception $e){
            //回滚事务
            \think\Db::rollback();
            $this->fail('添加失败');
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //查询一条数据（包含规格信息、规格值、属性信息）  注意：with方法多个关联，逗号后不要加空格
        $info = \app\common\model\Type::with('specs,specs.spec_values,attrs')-> find($id);
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
        $params = [
            'type_name' => '手机',
            'spec' => [
                ['name' => '颜色', 'sort' => 50, 'spec_value'=>['黑色', '白色', '金色']],
                //['name' => '颜色1', 'sort' => 50, 'value'=>['', '']],
                ['name' => '内存', 'sort' => 50, 'spec_value'=>['64G', '128G', '256G']],
            ],
            'attr' => [
                ['name' => '毛重', 'sort'=>50, 'attr_value' => []],
                ['name' => '产地', 'sort'=>50, 'attr_value' => ['进口', '国产','']],
            ]
        ];

        //接受参数
//        $params = input();
//        $validate = $this->validate($params,[
//            'type_name|模型名称' => 'require|max:20',
//            'spec|规格' => 'require|array',
//            'attr|属性' => 'require|array',
//        ]);
//        if ($validate !== true){
//            $this->fail($validate);
//        }
        //循环过滤规格 数组
        foreach($params['spec'] as $key => $val){
            //如果 规格名为空 删除规格数组
            if (empty(trim($val['name']))){
                unset($params['spec'][$key]);
                continue;
            }
            //内链 循环 如果 规格值为空 删除规格名
            foreach($val['spec_value']  as $k => $v){
                if(empty(trim($v))){
                    unset($params['spec'][$key]['spec_name'][$k]);
                }
            }

            //如果规格中的规格值数组都是空，则整个规格值的数组删掉
            if (empty($params['spec'][$key]['spec_value'])){
                unset($params['spec'][$key]);
            }
        }

        //循环过滤属性
        foreach($params['attr'] as $key => $val){
            if (empty(trim($val['name']))){
                unset($params['attr'][$key]);
            }
            foreach ($val['attr_value'] as $k => $v){
                if (empty($v)){
                    unset($params['attr'][$key]['attr_value'][$k]);
                }
            }
        }

        //开启事务
        Db::startTrans();
        try{
            //修改模型名称
            \app\common\model\Type::update(['type_name'=>$params['type_name']],['id'=>$id] ,true);
            //批量删除原来的规格名  删除条件 类型type_id
            \app\common\model\Spec::destroy(['id'=>$id]);
            //批量添加新的规格名
            $specs = [];
            foreach($params['spec'] as $key =>$val){
                $row = [
                    'type_id' =>$id,
                    'spec_name' =>$val['name'],
                    'sort' => $val['sort']
                ];
                $specs[] = $row;
            }
            $spec = new Spec();
            $spec = $spec->saveAll($specs);

            //规格值 循环 入库
            //删除原规格值
            SpecValue::destroy(['type_id'=>$id]);

            $spec_values = [];
            foreach ($params['spec']['spec_value'] as $key =>$val ){
                $row =[
                    'type_id' =>$id,
                    'spec_id' =>$spec['id'],
                    'spec_value' =>$v,

                ];
                $spec_values[] = $row;
            }
            $specvalue = new SpecValue();
            $specvalue->saveAll($spec_values);
            //属性 入库
            //删除原属性
            Attribute::destroy(['type_id'=>$id]);
            //循环
            //批量添加属性名称属性值
            $attrs = [];
            foreach($params['attr'] as $attr){
                $row = [
                    'attr_name' => $attr['name'],
                    'attr_values' => implode(',', $attr['attr_value']),
                    'sort' => $attr['sort'],
                    'type_id' => $id,
                ];
                $attrs[] = $row;
            }
            $attr = new Attribute();
            $attr->saveAll($attrs);
            //事务提交
            Db::commit();
            $this->ok($params);
        }catch(\Exception $e){
            //事务回滚
            Db::rollback();
            $this->fail('修改失败');
        }



    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //判断该模型下是否有商品
        if (is_numeric($id)){
            $info = \app\common\model\Goods::where('type_id',$id)->find();
            if ($info){
                $this->fail('该模型正在使用 不能删除');
            }
            //开启事务
            Db::startTrans();
            try {
                \app\common\model\Type::destroy($id);
//                \app\common\model\Spec::destroy(['type_id'=> $id]);
//                \app\common\model\SpecValue::destroy(['type_id'=> $id]);
//                \app\common\model\Attribute::destroy(['type_id'=> $id]);
                Spec::where('type_id',$id)->delete();
                SpecValue::where('type_id',$id)->delete();
                Attribute::where('type_id',$id)->delete();
                Db::commit();
                $this->ok();
            }catch(\Exception $e){
                Db::rollback();
                $this->fail('删除失败');
            }

        }else{
            $this->fail('参数id不合法');
        }

    }
}
