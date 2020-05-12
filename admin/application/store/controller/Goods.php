<?php
namespace app\store\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 产品信息管理
 * Class Goods
 * @package app\store\controller
 */
class Goods extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreGoods';

    /**
     * 产品信息管理
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '产品信息管理';
        $user = session('user');
        $this->authorize = $user['authorize'];
        $fields = 'g.*,s.title as shop_name';
        $where = 'g.is_delete=2';
         if($user['authorize'] == 2){
            $where .= ' and g.shop_id='.$user['shop_id'];
            $this->_query($this->table)->alias('g')->field($fields)->leftJoin('store_shop s','g.shop_id=s.id')->equal('g.is_show_main#make_status,g.is_show_second#sale_status,g.cate_id#cate_id,g.subcate_id#subcate_id')->like('g.title#title')->where($where)->order('g.sort asc,g.id desc')->page();
        }else{
            $this->_query($this->table)->alias('g')->field($fields)->leftJoin('store_shop s','g.shop_id=s.id')->equal('g.is_show_main#make_status,g.is_show_second#sale_status,g.cate_id#cate_id,g.subcate_id#subcate_id')->like('g.title#title')->where($where)->order('g.sort asc,g.id desc')->page();
        }

    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data)
    {
        $this->clist = Db::name('StoreGoodsCate')->where(['is_delete' => '2', 'status' => '1', 'pid' => '0'])->select();
        if($this->request->get('cate_id')){
            $cate_id = $this->request->get('cate_id');
            $this->sclist = Db::name('StoreGoodsCate')->where("is_delete=2 and status=1 and pid=$cate_id")->select();
        }else{
            $this->sclist = [];
        }
        $list = Db::name('StoreGoodsList')->where('status', '1')->whereIn('goods_id', array_unique(array_column($data, 'id')))->select();
        foreach ($data as &$vo) {
            list($vo['list'], $vo['cate']) = [[], []];
            foreach ($list as $goods) if ($goods['goods_id'] === $vo['id']) array_push($vo['list'], $goods);
            foreach ($this->clist as $cate) if ($cate['id'] === $vo['cate_id']) $vo['cate'] = $cate;
            $change = time() + 604800;

            if($vo['hot_end_time'] >= $change){
                $vo['hot_is_over'] = 0;
            }else{
                $vo['hot_is_over'] = 1;
            }

            if($vo['new_end_time'] >= $change){
                $vo['new_is_over'] = 0;
            }else{
                $vo['new_is_over'] = 1;
            }
            if($vo['hot_start_time']){
                $vo['hot_start_time'] = date('Y-m-d H:i:s',$vo['hot_start_time']);
            }
            if($vo['hot_end_time']){
                $vo['hot_end_time'] = date('Y-m-d H:i:s',$vo['hot_end_time']);
            }
            if($vo['new_start_time']){
                $vo['new_start_time'] = date('Y-m-d H:i:s',$vo['new_start_time']);
            }else{
            	$vo['new_start_time'] = '';
            }
            if($vo['new_end_time']){
                $vo['new_end_time'] = date('Y-m-d H:i:s',$vo['new_end_time']);
            }else{
            	$vo['new_end_time'] = '';
            }
        }
    }

    /**
     * 添加产品信息
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->isAddMode = '1';
        return $this->_form($this->table, 'form_add');
    }

    /**
     * 编辑产品信息
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->isAddMode = '0';
        return $this->_form($this->table, 'form_edit');
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    protected function _form_filter(&$data)
    {
        // 生成产品ID
//        if (empty($data['id'])) $data['id'] = Data::uniqidNumberCode(10);
        if ($this->request->isGet()) {
            if(empty($data)){
                $data['is_recommend'] = 2;
                $data['is_hot'] = 2;
                $data['is_new'] = 2;
            }else{
                if($data['hot_start_time']){
                    $data['hot_start_time'] = date('Y-m-d H:i:s',$data['hot_start_time']);
                }
                if($data['hot_end_time']){
                    $data['hot_end_time'] = date('Y-m-d H:i:s',$data['hot_end_time']);
                }
                if($data['new_start_time']){
                    $data['new_start_time'] = date('Y-m-d H:i:s',$data['new_start_time']);
                }
                if($data['new_end_time']){
                    $data['new_end_time'] = date('Y-m-d H:i:s',$data['new_end_time']);
                }
            }
            $fields = 'goods_spec,goods_id,status,price_market market,price_selling selling,number_virtual `virtual`,number_express express';
            if(isset($data['id'])){
                $defaultValues = Db::name('StoreGoodsList')->where(['goods_id' => $data['id']])->column($fields);
                $this->defaultValues = json_encode($defaultValues, JSON_UNESCAPED_UNICODE);
            }
            $this->cates = Db::name('StoreGoodsCate')->where(['is_delete' => '2', 'status' => '1', 'pid'=>0])->field('id,title')->order('sort asc,id desc')->select();
            $this->brands = Db::name('StoreGoodsBrand')->where([ 'is_freeze' => '2','is_delete'=>2])->field('id,title')->order('sort asc,id desc')->select();
            $this->shops = Db::name('StoreShop')->field('id,title')->order('sort asc,id desc')->select();
            if(isset($data['cate_id'])) $this->subCates = Db::name('StoreGoodsCate')->where(['is_delete' => '2', 'status' => '1', 'pid'=>$data['cate_id']])->field('id,title')->order('sort asc,id desc')->select();

        } elseif ($this->request->isPost()) {
            if($data['is_hot'] == 1){
                $data['hot_start_time'] = strtotime($data['hot_start_time']);
                $data['hot_end_time'] = strtotime($data['hot_end_time']);
            }else{
                $data['hot_start_time'] = '';
                $data['hot_end_time'] = '';
            }
            if($data['is_new'] == 1){
                $data['new_start_time'] = strtotime($data['new_start_time']);
                $data['new_end_time'] = strtotime($data['new_end_time']);
            }else{
                $data['new_start_time'] = '';
                $data['new_end_time'] = '';
            }
            if(empty($data['title'])){
                $this->error('请输入产品名称');
            }
            if(empty($data['cate_id'])){
                $this->error('请选择产品分类');
            }
            //  if(empty($data['subcate_id'])){
            //     $this->error('请选择产品二级分类');
            // }
            if(empty($data['shop_id'])){
                $this->error('请选择所属店铺');
            }
            if(empty($data['cover_image'])){
                $this->error('请上传产品封面图');
            }
            if(empty($data['image'])){
                $this->error('请上传产品轮播图');
            }
            // print_r($data);die;
            if($data['is_hot'] == 1){
            	if($data['hot_sort'] == ''){
            		$this->error('请输入爆款排序');
            	}
            	if(empty($data['hot_start_time'])){
            		 $this->error('请选择爆款开始时间');
            	}
            	if(empty($data['hot_end_time'])){
            		 $this->error('请选择爆款结束时间');
            	}
            }
            if($data['is_new'] == 1){
            	if($data['new_sort'] == ''){
            		$this->error('请输入新品排序');
            	}
            	if(empty($data['new_start_time'])){
            		 $this->error('请选择新品开始时间');
            	}
            	if(empty($data['new_end_time'])){
            		 $this->error('请选择新品结束时间');
            	}
            }
            if(empty($data['content'])){
                $this->error('请输入产品详细内容');
            }
        }
    }

    /**
     * 获取二级分类
     */
    public function getSubcate(){
        $id = $this->request->post('id');
        $cateList = Db::name('StoreGoodsCate')->where(['pid'=>$id,'is_delete'=>2,'status'=>1])->field('id,title')->order('sort desc,id asc')->select();
        return $cateList;
    }

    /**
     * 表单结果处理
     * @param $result
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    protected function _form_result($result)
    {
        if ($result && $this->request->isPost()) {
            if($result){
                Db::name('StoreGoodsList')->where(['goods_id' => $result])->update(['status' => '0']);
            }
            if($this->request->post('lists')){
                foreach (json_decode($this->request->post('lists'), true) as $vo) Data::save('StoreGoodsList', [
                    'goods_id'       => $result,
                    'goods_spec'     => $vo[0]['key'],
                    'price_market'   => $vo[0]['market'],
                    'price_selling'  => $vo[0]['selling'],
                    'number_virtual' => $vo[0]['virtual'],
                    'number_express' => $vo[0]['express'],
                    'status'         => $vo[0]['status'] ? 1 : 0,
                ], 'goods_spec', ['goods_id' => $result]);
            }
            if($this->request->post('id')){
                $title = $this->getGoodTitle($this->request->post('id'));
                _syslog('编辑产品','编辑产品ID:'.$this->request->post('id').',名称：'.$title);
            }else{
                $title = $this->getGoodTitle($result);
                $code=qrcodepng($url=\Config::get('h5_url').'/pages/zhanting/zhanting',$filename="goods".$result.".png");
                Db::name('StoreGoods')->where(['id'=>$result])->update([
                    'qr_code'=>$code['url']
                ]);
                _syslog('添加产品','添加产品ID:'.$this->request->post('id').',名称：'.$title);
            }
             $this->success('操作成功！');
        }
    }

    /**
     * 下架产品信息
     */
    public function forbid()
    {
        $id = input('id');
        Db::name('StoreAds')->where("ad_type=4 and route_id=$id" )->update(['status'=>0]);
        $title = $this->getGoodTitle($id);
        _syslog('下架产品','下架产品ID:'.$id.',名称：'.$title);
        $this->_save($this->table, ['is_show_main' => '2']);
    }

    /**
     * 上架产品信息
     */
    public function resume()
    {
        $id = input('id');
        Db::name('StoreAds')->where("ad_type=4 and route_id=$id" )->update(['status'=>1]);
        $title = $this->getGoodTitle($id);
        _syslog('上架产品','上架产品ID:'.$id.',名称：'.$title);
        $this->_save($this->table, ['is_show_main' => '1']);
    }

    /**
     * 删除产品信息
     */
    // public function remove()
    // {
    //     $id = input('id');
    //     Db::name('StoreAds')->where("ad_type=4 and route_id=$id" )->update(['status'=>0]);
    //     $title = $this->getGoodTitle($id);
    //     _syslog('删除产品','删除产品ID:'.$id.',名称：'.$title);
    //     $this->_save($this->table,['is_delete'=>1]);
    // }

    /**
     * 获取产品名称
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodTitle($id){
        $info = Db::name('StoreGoods')->field('title')->where(['id'=>$id])->find();
        return $info['title'];
    }
}