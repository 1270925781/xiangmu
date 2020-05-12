<?php
namespace app\store\controller;

use library\Controller;
use think\Db;

/**
 * 展厅审核管理
 * Class ShopAudit
 * @package app\store\controller
 */
class ShopAudit extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreShopAudit';

    /**
     * 展厅审核管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '展厅审核管理';
        $fields = 's.*,m.username as user';
        $this->_query($this->table)->alias('s')->field($fields)->leftJoin('store_member m','s.uid=m.id')->like('s.title#title')->equal('s.status#status')->order('s.id desc')->page();
    }

    /**
     * 审核通过
     */
    public function pass()
    {
        if($this->request->post('id')){
            $id = $this->request->post('id');
            $auditInfo = Db::name('StoreShopAudit')->where(['id'=>$id])->find();
             if(Db::name('StoreShop')->where(['title'=>$auditInfo['name'],'is_delete'=>2])->count()>0){
                $this->error('此展厅已存在。');
            }
            $memberInfo = Db::name('StoreMember')->where(['id'=>$auditInfo['uid']])->find();
            if(Db::name('SystemUser')->where(['username'=>$memberInfo['phone']])->count()>0){
            	$this->error('此账号已存在。');
            }
            $shopId = Db('StoreShop')->insertGetId([
                'title'=>$auditInfo['name'],
                'address'=>$auditInfo['address'],
                'contacts'=>$auditInfo['username'],
                'phone'=>$auditInfo['phone'],
                'is_freeze'=>1
            ]);
            if($shopId){
               
                 Db::name('SystemUser')->insertGetId([
                    'username'=>$memberInfo['phone'],
                    'password'=>$memberInfo['password'],
                    'phone'=>$memberInfo['phone'],
                    'nickname'=>$memberInfo['phone'],
                    'real_name'=>$memberInfo['phone'],
                    'authorize'=>2,
                    'shop_id'=>$shopId
                ]);
                Db::name('StoreMember')->where(['id'=>$auditInfo['uid']])->update(['shop_id'=>$shopId]);
                $code=qrcodepng($url=config('h5_url').'/pages/zhanting_zhuye/zhanting_zhuye?id='.$shopId,$filename="shop".$shopId.".png");
                Db::name('StoreShop')->where(['id'=>$shopId])->update([
                    'qr_code'=>$code['url']
                ]);
            }
            $TemplateParam = json_encode(array("username"=>$memberInfo['phone'],"password"=>$memberInfo['phone']));
            alisendsms($memberInfo['phone'], "SMS_189017985",$TemplateParam);
        }
        Db('StoreShopAudit')->where(['id'=>$id])->update(['status'=>2]);
        $this->success('展厅审核操作成功！');

    }

    /**
     * 审核拒绝
     */
    public function refuse()
    {
        if($this->request->post('id')){
            Db('StoreShopAudit')->where(['id'=>$this->request->post('id')])->update(['status'=>3]);
            $this->success('展厅审核操作成功！');
        }

    }


}