<?php
namespace app\store\controller;
use library\Controller;
use think\Db;

/**
 * 展厅管理
 * Class Shop
 * @package app\store\controller
 */
class Shop extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreShop';

    /**
     * 展厅管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '展厅管理';
        $user = session('user');
        $where = 's.is_delete=2';
        if($user['authorize'] == 2){
            $where .= " and s.id=".$user['shop_id'];
        }
        $fields = 's.*,c.title as cate_name';
        $this->_query($this->table)->alias('s')->field($fields)->leftJoin('store_shop_cate c','s.cate_id=c.id')->equal('s.is_freeze#is_freeze,s.cate_id#cate_id')->like('s.title#title')->where($where)->order('s.sort desc,s.id desc')->page();
    }

    /**
     * 数据列表处理
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data)
    {
        $this->clist = Db::name('StoreShopCate')->where(['is_delete' => '2', 'status' => '1'])->select();
        $this->user = session('user');
        foreach ($data as $k=>$v){
            $change = time() + 604800;
            if($v['home_end_time'] >= $change){
                $data[$k]['home_is_over'] = 0;
            }else{
                $data[$k]['home_is_over'] = 1;
            }

            if($v['minisns_end_time'] >= $change){
                $data[$k]['mini_is_over'] = 0;
            }else{
                $data[$k]['mini_is_over'] = 1;
            }
            
            if($v['use_end_time'] >= $change){
            	 $data[$k]['use_is_over'] = 0;
            }else{
            	 $data[$k]['use_is_over'] = 1;
            }
            
            
            if($v['home_start_time']){
                $data[$k]['home_start_time'] = date('Y-m-d H:i:s',$v['home_start_time']);
            }
            if($v['home_end_time']){
                $data[$k]['home_end_time'] = date('Y-m-d H:i:s',$v['home_end_time']);
            }
            if($v['minisns_start_time']){
                $data[$k]['minisns_start_time'] = date('Y-m-d H:i:s',$v['minisns_start_time']);
            }
            if($v['minisns_end_time']){
                $data[$k]['minisns_end_time'] = date('Y-m-d H:i:s',$v['minisns_end_time']);
            }
             if($v['use_start_time']){
                $data[$k]['use_start_time'] = date('Y-m-d H:i:s',$v['use_start_time']);
            }
            if($v['use_end_time']){
                $data[$k]['use_end_time'] = date('Y-m-d H:i:s',$v['use_end_time']);
            }
        	if($v['create_at']){
                $data[$k]['create_date'] = date('Y-m-d ',strtotime($v['create_at']));
                $data[$k]['create_time'] = date('H:i:s ',strtotime($v['create_at']));
            }
        }
    }

	public function phoneInfo(){
        $post = $this->request->post();
        $phone = $post['phone'];
        $info = Db::name('StoreMember')->where(['phone' => $post['phone']])->field('phone,username,realname,shop_id')->find();
        if($info){
            if($info['shop_id'] > 0){
                return array('code'=>-1,'msg'=>"该手机号已经开通店铺！");
            }else{
                return array('code'=>1,'data'=>$info);
            }
        }else{
             return array('code'=>2,'msg'=>"该手机号未在用户端注册！");
        }
    }
    
    public function user_edit(){
        $this->applyCsrfToken();
        if ($this->request->isGet()) {
            $this->verify = false;
            $this->_form($this->table, 'user_edit');
        } else {
            $post = $this->request->post();
            $info = Db::name('StoreMember')->where(['phone' => $post['phone']])->find();
            $shop_id = $post['id'];
            $phone = $post['phone'];
            if($info){
                if($info['shop_id'] > 0){
                    $this->error('该手机号已经开通过店铺！');
                }else{
                    //手机号已在用户端注册
                    Db::name('StoreMember')->where(['shop_id' => $shop_id])->update(['shop_id' => 0]);
                    Db::name('SystemUser')->where(['shop_id' => $shop_id])->delete();
                    
                    Db::name('StoreMember')->where(['phone' => $phone])->update(['shop_id' => $shop_id]);
                    //添加展厅端账号
                    Db::name('SystemUser')->insertGetId([
                        'username'=>$phone,
                        'password'=>md5($phone),
                        'phone'=>$phone,
                        'authorize'=>2,
                        'shop_id'=>$shop_id
                    ]);
                    $TemplateParam = json_encode(array("username"=>$phone,"password"=>$phone));
                    alisendsms($phone, "SMS_189017985",$TemplateParam);
                    $this->success('变更成功！', '');
                }
            }else{
                //手机号在用户端没有注册
                Db::name('StoreMember')->where(['shop_id' => $shop_id])->update(['shop_id' => 0]);
                Db::name('SystemUser')->where(['shop_id' => $shop_id])->delete();
                
                //添加用户端账号
                $inviter_code = $this->random();
                $info['shop_id'] = $shop_id;
                $info['username'] = $phone;
                $info['password'] = md5($phone);
                $info['phone'] = $phone;
                $info['nickname'] = $phone;
                $info['realname'] = $phone;
                $info['inviter_code'] = $inviter_code;
                $info['create_at'] = date('Y-m-d H:i:s',time());
                $uid = Db::name('StoreMember')->insertGetId($info);
                
                //添加展厅端账号
                Db::name('SystemUser')->insertGetId([
                    'username'=>$phone,
                    'password'=>$post['phone'],
                    'phone'=>$phone,
                    'authorize'=>2,
                    'shop_id'=>$shop_id
                ]);
                $TemplateParam = json_encode(array("username"=>$phone,"password"=>$phone));
            	alisendsms($phone, "SMS_189017985",$TemplateParam);
                $this->success('变更成功！', '');
            }
        }
    }
    
    //生成随机邀请码
    function random(){
        $pattern = '1234567890ABCDEFGHIJKLOMNOPQRSTUVWXYZ'; 
        $key = "";
        for($i=0;$i<6;$i++)  
        {  
         $key .= $pattern{mt_rand(0,35)}; 
        }
        $member = Db::name('StoreMember')->where(['inviter_code' => $key])->find();
        if($member){
            $this->random();
        }
        return $key;
    }

    /**
     * 添加展厅
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        return $this->_form($this->table, 'form_add');
    }

    /**
     * 编辑添加展厅
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        return $this->_form($this->table, 'form_edit');
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _form_filter(&$data)
    {
        if ($this->request->isPost()) {								//数据提交
            if(isset($data['username'])){
                if(Db::name('SystemUser')->where(['username' => $data['username']])->count() > 0){
                    $this->error('用户账号已经存在，请使用其它账号！');
                }
            }
            
            $labelArr = explode(',',$data['labels']);
            $data['labels'] = join(',',array_filter($labelArr));
            if(count($labelArr)>4){
                $this->error('标签最多只能添加4个');
            }
            foreach ($labelArr as $vo){
            	if(Db::name('StoreShopLabel')->where(['title'=>$vo,'is_freeze'=>1])->count()>0){
                    $this->error('标签（'.$vo.'）已被禁用，不能使用！');
                }
            	 if(strpos($vo,'，') !== false){
                    $this->error('标签中不能包含中文逗号');
                }
                if(mb_strlen($vo)>3){
                    $this->error('每个标签最多3个字');
                }
               
            }
            
           
            if(empty($data['cover_image'])){
                $this->error('请上传展厅封面图');
            }
            if(empty($data['slide_show'])){
                $this->error('请上传展厅轮播图');
            }
            if($data['is_home'] == 1){
                if($data['home_sort'] == ''){
                    $this->error('请输入首页排序');
                }
                if(empty($data['home_start_time'])){
                    $this->error('请选择首页开始时间');
                }
                if(empty($data['home_end_time'])){
                    $this->error('请选择首页结束时间');
                }
            }
            if($data['is_minisns'] == 1){
                if($data['minisns_sort'] == ''){
                    $this->error('请输入微社区排序');
                }
                if(empty($data['minisns_start_time'])){
                    $this->error('请选择微社区开始时间');
                }
                if(empty($data['minisns_end_time'])){
                    $this->error('请选择微社区结束时间');
                }
            }
            if(empty($data['use_start_time'])){
            	$this->error('请选择展厅使用开始时间');
            }
             if(empty($data['use_end_time'])){
            	$this->error('请选择展厅使用结束时间');
            }
            if(empty($data['longitude'])){
                $this->error('请获取坐标');
            }
            if(empty($data['address'])){
                $this->error('请输入展厅地址');
            }
            if(empty($data['intro'])){
                $this->error('请输入展厅简介');
            }
            if(empty($data['details'])){
                $this->error('请输入展厅详情');
            }

			if($this->request->post('id')){							//编辑数据
                $id = $this->request->post('id');
                if(isset($data['title'])){
                    if(Db::name($this->table)->where("is_delete=2 and id!=$id and title='".$data['title']."'")->count() > 0){
                        $this->error('展厅名称已经存在！');
                    }
                }
                $shopLabes = Db::name('StoreShop')->field('labels')->where(['id'=>$id])->find();
                $oldLabels = explode(',',$shopLabes['labels']);
                $diffArr1 = array_diff($oldLabels,$labelArr);
                $diffArr2 = array_diff($labelArr,$oldLabels);
                if($diffArr1) {
                    foreach ($diffArr1 as $vo) {
                        $nowCount = Db::name('StoreShopLabel')->field('nowcount')->where(['title'=>$vo])->find()['nowcount'];
                        if($nowCount==1){
                            Db::name('StoreShopLabel')->where(['title' => $vo])->delete();
                        }elseif($nowCount>1){
                            Db::name('StoreShopLabel')->where(['title' => $vo])->setDec('nowcount');
                        }
                    }
                }
                if($diffArr2){
                    foreach ($diffArr2 as $vo){
                        $word = Db::name('StoreShopLabel')->where(['title'=>$vo])->find();
                        if($word){
                            Db::name('StoreShopLabel')->where(['title'=>$vo])->setInc('hotcount');
                            Db::name('StoreShopLabel')->where(['title'=>$vo])->setInc('nowcount');
                        }
                    }
                }
            }else{													//添加数据
            	if(isset($data['title'])){
                    if(Db::name($this->table)->where("is_delete=2 and title='".$data['title']."'")->count() > 0){
                        $this->error('展厅名称已经存在！');
                    }
                }
            	$data['is_freeze'] = 1;
            }

            if($data['is_home'] == 1){
                $data['home_start_time'] = strtotime($data['home_start_time']);
                $data['home_end_time'] = strtotime($data['home_end_time']);
                if($data['home_start_time']>=$data['home_end_time']){
                    $this->error('推荐首页开始时间必须早于结束时间！');
                }
            }else{
                $data['home_sort'] = '';
                $data['home_start_time'] = '';
                $data['home_end_time'] = '';
            }
            if($data['is_minisns'] == 1){
                $data['minisns_start_time'] = strtotime($data['minisns_start_time']);
                $data['minisns_end_time'] = strtotime($data['minisns_end_time']);
                if($data['minisns_start_time']>=$data['minisns_end_time']){
                    $this->error('推荐微社区开始时间必须早于结束时间！');
                }
            }else{
                $data['minisns_sort'] = '';
                $data['minisns_start_time'] = '';
                $data['minisns_end_time'] = '';
            }
            $data['use_start_time'] = strtotime($data['use_start_time']);
            $data['use_end_time'] = strtotime($data['use_end_time']);
            if($data['use_start_time']>=$data['use_end_time']){
                $this->error('展厅使用开始时间必须早于结束时间！');
            }
            if($data['labels']){
                $data['labels_arr'] = explode(',',$data['labels']);
                foreach ($data['labels_arr'] as $vo) {
                    $word = Db::name('StoreShopLabel')->where(['title'=>$vo])->find();
                    if(empty($word)){
                        if(!empty($data['id'])){
                            Db::name('StoreShopLabel')->insert([
                                'title'=>$vo,
                                'shop_id'=>$data['id']
                            ]);
                        }
                    }
                }
            }
        }else{											//渲染页面
            $this->clist = Db::name('StoreShopCate')->where(['is_delete' => '2', 'status' => '1'])->select();
            if(empty($data['longitude'])){
            	$data['longitude'] = 0;
            }
        	$home_start_time = array_key_exists('home_start_time', $data) ? $data['home_start_time']:'';
        	if($home_start_time){
                $data['home_start_time'] = date('Y-m-d H:i:s',$data['home_start_time']);
            }else{
            	$data['home_start_time'] = "";
            }
            $home_end_time = array_key_exists('home_end_time', $data) ? $data['home_end_time']:'';
            if($home_end_time){
                $data['home_end_time'] = date('Y-m-d H:i:s',$data['home_end_time']);
            }else{
            	$data['home_end_time'] = "";
            }
            $minisns_start_time = array_key_exists('minisns_start_time', $data) ? $data['minisns_start_time']:'';
            if($minisns_start_time){
                $data['minisns_start_time'] = date('Y-m-d H:i:s',$data['minisns_start_time']);
            }else{
            	$data['minisns_start_time'] = "";
            }
            $minisns_end_time = array_key_exists('minisns_end_time', $data) ? $data['minisns_end_time']:'';
            if($minisns_end_time){
                $data['minisns_end_time'] = date('Y-m-d H:i:s',$data['minisns_end_time']);
            }else{
            	$data['minisns_end_time'] = "";
            }
            $use_start_time = array_key_exists('use_start_time', $data) ? $data['use_start_time']:'';
            if($use_start_time){
            	$data['use_start_time'] = date('Y-m-d H:i:s',$data['use_start_time']);
            }else{
            	$data['use_start_time'] = "";
            }
            $use_end_time = array_key_exists('use_end_time', $data) ? $data['use_end_time']:'';   
            if($use_end_time){
            	$data['use_end_time'] = date('Y-m-d H:i:s',$data['use_end_time']);
            }else{
            	$data['use_end_time'] = "";
            }
        	
        }
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
        if($this->request->post('labels')){
           $labels = $this->request->post('labels');
            $labels = explode(',',$labels);
            foreach ($labels as $vo) {
				$vo=trim($vo);
                $word = Db::name('StoreShopLabel')->where(['title'=>$vo])->find();
                if(empty($word)){
                    if($result && $this->request->isPost()){
                        $id = $result;
                    }elseif($this->request->post('id')){
                        $id = $this->request->post('id');
                    }
                    Db::name('StoreShopLabel')->insert([
                        'title'=>$vo,
                        'shop_id'=>$id
                    ]);
                }
            }
        }

        if($result && $this->request->isPost()){
            if($this->request->post('id')){
                $title = $this->getShopTitle($this->request->post('id'));
                _syslog('编辑展厅','编辑展厅ID:'.$this->request->post('id').',名称：'.$title);
            }else{
                $title = $this->getShopTitle($result);
                $code=qrcodepng($url=config('h5_url').'/pages/zhanting_zhuye/zhanting_zhuye?id='.$result,$filename="shop".$result.".png");
                Db::name('StoreShop')->where(['id'=>$result])->update([
                    'qr_code'=>$code['url']
                ]);
                _syslog('添加展厅','添加展厅ID:'.$this->request->post('id').',名称：'.$title);
            }
        }

        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $phone = $this->request->post('phone');
        if( $username && $password){
            $userId = Db::name('SystemUser')->insertGetId([
                'username'=>$username,
                'password'=>md5($password),
                'phone'=>$phone,
                'authorize'=>2
            ]);
            $user = session('user');
            if($user['authorize'] == '' || $user['authorize'] == 1){
                Db::name('SystemUser')->where(['id'=>$userId])->update([
                    'shop_id'=>$result
                ]);
            }
        }
          $this->success('操作成功！');
    }

    /**
     * 禁用展厅
     */
    public function forbid()
    {
        $id = input('id');
        $userId = Db::name('SystemUser')->where(['shop_id'=>$id])->find()['id'];
        Db::name('StoreGoods')->where(['shop_id'=>$id])->update(['is_show_main'=>2]);
        $idArry = Db::name('StoreGoods')->field('id')->where(['shop_id'=>$id])->select();
    	if($idArry){
            $id_str = '';
            foreach ($idArry as &$vo){
                $id_str .= $vo['id'].',';
            }
            $id_str = substr($id_str,0,strlen($id_str)-1);
            Db::name('StoreAds')->where("ad_type=4 and route_id in ($id_str)" )->update(['status'=>0]);
        }
        Db::name('SystemUser')->where(['shop_id'=>$id])->update(['status'=>0]);
        Db::name('StoreToken')->where(['user_type'=>1,'user_id'=>$userId])->update(['status'=>0]);
        Db::name('StoreAds')->where("ad_type=1 and route_id=$id" )->update(['status'=>0]);
        $this->_save($this->table, ['is_freeze' => '1']);
        $title = $this->getShopTitle($id);
        _syslog('禁用展厅','禁用展厅ID:'.$id.',名称：'.$title);
        $this->_save($this->table, ['is_freeze' => '1']);
    }

    /**
     * 启用展厅
     */
    public function resume()
    {
        $id = input('id');
    	$stime = Db::name($this->table)->where(['id'=>$id])->find()['use_start_time'];
    	if($stime==0){
    		$this->error('请编辑展厅使用时间后再启用');
    	}

        $userId = Db::name('SystemUser')->where(['shop_id'=>$id])->find()['id'];
        Db::name('StoreGoods')->where(['shop_id'=>$id])->update(['is_show_main'=>1]);
        $idArry = Db::name('StoreGoods')->field('id')->where(['shop_id'=>$id])->select();
        if($idArry){
            $id_str = '';
            foreach ($idArry as &$vo){
                $id_str .= $vo['id'].',';
            }
            $id_str = substr($id_str,0,strlen($id_str)-1);
            Db::name('StoreAds')->where("ad_type=4 and route_id in ($id_str)" )->update(['status'=>1]);
        }
        Db::name('SystemUser')->where(['shop_id'=>$id])->update(['status'=>1]);
        Db::name('StoreToken')->where(['user_type'=>1,'user_id'=>$userId])->update(['status'=>1]);
        Db::name('StoreAds')->where("ad_type=1 and route_id=$id" )->update(['status'=>1]);
        $title = $this->getShopTitle($id);
        _syslog('启用展厅','启用展厅ID:'.$id.',名称：'.$title);
        $this->_save($this->table, ['is_freeze' => '2']);
    }

    /**
     * 删除展厅
     */
    public function remove()
    {
        $id = input('id');
        $title = $this->getShopTitle($id);
        _syslog('删除展厅','删除展厅ID:'.$id.',名称：'.$title);
        $this->_save($this->table,['is_delete'=>1]);

    }

    /**
     * 获取展厅名称
     * @param $id ID
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopTitle($id){
        $info = Db::name('StoreShop')->field('title')->where(['id'=>$id])->find();
        return $info['title'];
    }

}