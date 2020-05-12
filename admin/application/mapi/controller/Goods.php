<?php
namespace app\mapi\controller;
use think\Db;

class Goods extends Base
{
    /**
     * 获取列表数据
     */
    public function getList(){
        $data = input();
        $page = $data['page'];
        $pageLimit = 5;
        $shopId = $this->shopId;
        $where = "is_delete=2 and shop_id=$shopId";
        $where1 = "is_delete=2 and is_show_second=1 and shop_id=$shopId";
        $where2 = "is_delete=2 and is_show_second=2 and shop_id=$shopId";
        if($data['goods_name']!=''){
            $goods_name = $data['goods_name'];
            $where .= " and title like '%$goods_name%'" ;
            $where1 .=" and title like '%$goods_name%'" ;
            $where2 .=" and title like '%$goods_name%'" ;
        }
        if($data['is_show'] == 1){
            $where .= ' and is_show_second=2';
        }else{
            $where .= ' and is_show_second=1';
        }
        $list['show_count']  = Db::name('StoreGoods')->where($where1)->count();
        $list['sold_out_count']  = Db::name('StoreGoods')->where($where2)->count();
        $list['goods']  = Db::name('StoreGoods')->field('id,cover_image,title,is_recommend,is_show_second,is_delete,is_show_main')->where($where)->order('sort desc')->page($page,$pageLimit)->select();

        foreach($list['goods'] as $key=>$value){
            if($value['is_recommend']==1){
                $list['goods'][$key]['recommend'] = '是';
            }else{
                $list['goods'][$key]['recommend'] = '否';
            }
        }
        $list['page_limit'] = $pageLimit;
        return $this->showMsg(1,'',$list);
    }

    /**
     * 更新产品推荐状态
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function recommend(){
        $data = input();
        $id = $data['id'];
        $type = $data['type'];
        if($type == 1){     //推荐
            $save['is_recommend'] = 1;
        }else{              //取消推荐
            $save['is_recommend'] = 2;
        }
        Db::name('StoreGoods')->where(['id'=>$id])->update($save);

        if($type == 1){
            return $this->showMsg(1,'推荐成功');
        }else{
            return $this->showMsg(1,'取消成功');
        }
    }

    /**
     * 更新产品上/下架状态
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function putaway(){
        $data = input();
        $id = $data['id'];
        $type = $data['type'];
        if($type == 1){     //上架
            $save['is_show_second'] = 1;
        }else{              //下架
            $save['is_show_second'] = 2;
        }
        Db::name('StoreGoods')->where(['id'=>$id])->update($save);

        if($type == 1){
            return $this->showMsg(1,'上架成功');
        }else{
            return $this->showMsg(1,'下架成功');
        }
    }

    /**
     * 删除产品
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete(){
        $data = input();
        $id = $data['id'];
        $save['is_delete'] = 1;
        $goodInfo = Db::name('StoreGoods')->where(['id'=>$id])->find();
        Db::name('StoreGoods')->where(['id'=>$id])->update($save);

        $this->UserOperation($this->uid,'删除ID：'.$id.'，产品名称：【'.$goodInfo['title'].'】');
        return $this->showMsg(1,'删除成功');
    }
    /**
     * 获取产品品牌列表
     */
    public function getGoodsBrand(){
        $where = 'is_freeze=2 and is_delete=2';
        $list = Db::name('StoreGoodsBrand')->field('id,title')->where($where)->order('sort desc,id desc')->select();
        foreach ($list as $key=>$value){
            $list[$key]['value'] = $value['id'];
            $list[$key]['label'] = $value['title'];
        }
        return $this->showMsg(1,'',$list);
    }

    /**
     * 获取产品一级分类列表
     */
    public function getGoodsCate(){
        $where = 'status=1 and is_delete=2 and pid=0';
        $list = Db::name('StoreGoodsCate')->field('id,title')->where($where)->order('sort desc,id desc')->select();
        foreach ($list as $key=>$value){
            $list[$key]['value'] = $value['id'];
            $list[$key]['label'] = $value['title'];
        }
        return $this->showMsg(1,'',$list);
    }

    /**
     * 获取产品一级分类下的二级分类列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsSubCate(){
        $data = input();
        $where = 'status=1 and is_delete=2 and pid='.$data['id'];
        $list = Db::name('StoreGoodsCate')->field('id,title')->where($where)->order('sort desc,id desc')->select();
        foreach ($list as $key=>$value){
            $list[$key]['value'] = $value['id'];
            $list[$key]['label'] = $value['title'];
        }
        return $this->showMsg(1,'',$list);
    }

    /**
     * 添加产品
     */
    public function addGoods()
    {
        $data = input('info');
        // if(Db::name('StoreGoods')->where("is_delete = 2 and title = '".$data['title']."'")->count() > 0){
        //     return $this->showMsg(-1,'产品名称已存在');
        // }
        $data['shop_id'] = $this->shopId;
        $id = Db::name('StoreGoods')->insertGetId($data);
		$code=qrcodepng($url=\Config::get('h5_url').'/pages/zhanting/zhanting',$filename="goods".$id.".png");
        Db::name('StoreGoods')->where(['id'=>$id])->update([
            'qr_code'=>$code['url']
        ]);
        $this->UserOperation($this->uid,'添加ID为'.$id.'的产品【'.$data['title'].'】');
        return $this->showMsg(1,'添加成功');
    }

    /**
     * 获取产品信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsInfo(){
        $data = input();
        $id = $data['id'];
        $goods['info'] = Db::name('StoreGoods')->field('id,brand_id,cate_id,subcate_id,title,cover_image,image,description,content_img,video_url,is_recommend,recommend_sort,content')->where("id=$id")->find();
        if($goods['info']['image']){
            $goods['image'] = explode('|',$goods['info']['image']);
        }else{
            $goods['image'] = [];
        }
        if($goods['info']['content_img']){
            $goods['content_img'] = explode('|',$goods['info']['content_img']);
        }else{
            $goods['content_img'] = [];
        }
        if($goods['info']['cate_id']){
            $goods['brand_name'] = Db::name('StoreGoodsBrand')->where("id=".$goods['info']['brand_id'])->field('title')->find()['title'];
        }else{
            $goods['brand_name'] = '请选择';
        }
        
        $selectListBrand = Db::name('StoreGoodsBrand')->field('id,title')->where("is_freeze=2 and is_delete=2")->order('sort desc,id desc')->select();
        foreach ($selectListBrand as $key=>$value){
            $goods['selectListBrand'][$key]['value'] = $value['id'];
            $goods['selectListBrand'][$key]['label'] = $value['title'];
        }
        if($goods['info']['cate_id']){
            $goods['cate_name'] = Db::name('StoreGoodsCate')->where("id=".$goods['info']['cate_id'])->field('title')->find()['title'];
            $where_scate = 'status=1 and is_delete=2 and pid='.$goods['info']['cate_id'];
            $selectListSub = Db::name('StoreGoodsCate')->field('id,title')->where($where_scate)->order('sort desc,id desc')->select();
            if($selectListSub){
            	foreach ($selectListSub as $key=>$value){
		            $goods['selectListSub'][$key]['value'] = $value['id'];
		            $goods['selectListSub'][$key]['label'] = $value['title'];
        		}
            }
            
        }else{
            $goods['cate_name'] = '请选择';
        }
        if($goods['info']['subcate_id']){
            $goods['subcate_name'] = Db::name('StoreGoodsCate')->where("id=".$goods['info']['subcate_id'])->field('title')->find()['title'];
        }else{
            $goods['subcate_name'] = '请选择';
        }

        $where_cate = 'status=1 and is_delete=2 and pid=0';
        $selectList = Db::name('StoreGoodsCate')->field('id,title')->where($where_cate)->order('sort desc, id desc')->select();
        foreach ($selectList as $key=>$value){
            $goods['selectList'][$key]['value'] = $value['id'];
            $goods['selectList'][$key]['label'] = $value['title'];
        }
        if(empty($goods['info']['recommend_sort'])){
            $goods['info']['recommend_sort'] = '';
        }
        if(empty($goods['info']['description'])){
            $goods['info']['description'] = '';
        }
        if(empty($goods['info']['content'])){
            $goods['info']['content'] = '';
        }
        
        return $this->showMsg(1,'',$goods);
    }


    /**
     * 编辑产品
     */
    public function saveGoods()
    {
        $data = input('info');
        // if(Db::name('StoreGoods')->where("is_delete = 2 and id <> ".$data['id']." and title = '".$data['title']."'")->count() > 0){
        //     return $this->showMsg(-1,'产品名称已存在');
        // }
        Db::name('StoreGoods')->where("id=".$data['id'])->update($data);

        $this->UserOperation($this->uid,'编辑ID：'.$data['id'].'，产品名称：【'.$data['title'].'】');
        return $this->showMsg(1,'编辑成功');
    }

}