<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @author mxt
 * @abstract  订单模块
 */
class Order extends  RR_Controller{
    /**
     * @abstract  订单查询  根据角色不同查询所属订单列表
     * @param int  $user_role  角色权限用户组
     */
    function orderSee(){
       
        //当是微信环境 默认登录
        $this->wxLogin();
        
        //校验当前的用户是否已经登录
        $this->isOnline();
        switch ($this->user_role){
            //普通用户
            case $this->config->item('role_customer'):
                $this->getList();
                break;
            //店员
            case $this->config->item('role_Clerk'):
                $this->selectList();
                break;
            //店长
            case $this->config->item('role_Shopowner'):
                $this->selectList();
                break;
            //提货员
           case $this->config->item('role_Distill'):
                $this->getDistList();
                break;
        }
    }
    /**
     * @abstract 订单列表  普通用户读取订单列表
     * @param userid  int  用户ID
     * @param role  int  用户权限组
     *
     */
    function getList(){
        //校验接口权限
        $this->checkRole($this->user_role, $this->config->item('role_customer'));
        //读取当前用户订单列表
        $this->load->model('m/Order_model');
        $this->Order_model->uid=$this->user_id;
        $result= $this->Order_model->getList();
        if($result){
            $list=$result;
        }else{
            $list=0;
        }
        $this->assign('status', $this->config->item('order_status_ordinary'));
        $this->assign('result',$list);
        $this->display('m/list.html');
    }
    /**
     * @abstract 根据店员的编号读取订单列表
     * @param   int     $number  店员编号
     * @param   string  $status  订单状态
     * @return  array   读取列表成功 返回集合 否则返回bool  false  
     */
    function selectList(){
        //校验接口权限
        if($this->user_role != $this->config->item('role_Clerk') 
                && $this->user_role != $this->config->item('role_Shopowner')){
            often_helper::output('21011','','','您并不能进行此操作!');
        }
        $state=$this->input->get('status',true);
        if(!in_array($state, array('deal','unfinished'))){
            $status='1,2';
            $state='unfinished';
        }else{
            $oprion=array('deal'=>10,'unfinished'=>'1,2');
            $status=$oprion[$state];
        }
        //获取店员下的所有订单列表
        $this->load->model('m/Order_model');
        $this->Order_model->number=$this->user_number;
        $this->Order_model->status=$status;
        $this->Order_model->role=$this->user_role;
        $this->Order_model->user_id=$this->user_id;
        $list=$this->Order_model->selectList();
        if($list == false){
            $result=array('deal'=>0,'unfinished'=>0);
        }
        if($status == 1){
            $result=array('deal'=>0,'unfinished'=>$list);
        }else{
            $result=array('deal'=>$list,'unfinished'=>0);
        }
        //二维码显示
        $this->user_role == $this->config->item('role_Shopowner') ? $QRcode =1 : $QRcode=0;
        //订单状态
        $this->assign('state', $this->config->item('order_status_ordinary'));
        //用户资料
        $user=array('name'=>$this->user_name,'number'=>$this->user_number);
        $this->assign('QRcode', $QRcode);
        $this->assign('user', $user);
        $this->assign('status',$state);
        $this->assign('deal',$result['deal']);
        $this->assign('unfinished',$result['unfinished']);
        $this->display('m/earnlist.html');
    }
    /**
     * @abstract  提货员查看  查看需要提货的订单
     */
    function getDistList(){
        //校验是否登录
       $this->isOnline();
       //校验接口权限
       $this->checkRole($this->user_role, $this->config->item('role_Distill'));
       $this->load->model('m/Order_model');
       $this->Order_model->manage_id=$this->user_id;
	   $this->Order_model->user_openid=$this->user_openid;
	   $res=$this->Order_model->getDistill();
	   if($res === false){
	       often_helper::output('21011','','','读取提货信息出现异常!');
	   }
	   //用户资料
	   $user=array('name'=>$this->user_name,'number'=>$this->user_number);
	   $this->assign('user', $user);
	   $this->assign('list', $res);
       $this->display('m/sshop.html');
    }
    /**
     * @abstract  提货员根据店铺 查看每家店铺的提货列表
     * @param  $id  int  店铺Id
     * @return 
     */
    function extractManage(){
        //查看是登录
        $this->isOnline();
       //校验接口权限
        $this->checkRole($this->user_role, $this->config->item('role_Distill'));
        $addres_id=$this->input->get('id',true);
        if(!is_numeric($addres_id) || $addres_id < 0 ){
            often_helper::output('21011','','','');
        }
        $this->load->model('m/Order_model');
        $this->Order_model->manage_id=$this->user_id;
        $res=$this->Order_model->shopOrderList();
        if($res == false){
            often_helper::output('21011','','','');
        }
        $user=array('name'=>$this->user_name,'number'=>$this->user_number);
        $this->assign('user', $user);
        $this->assign('list', $res);
        $this->display('m/sshop.html');
    }
    /**
     * @abstract  提货  显示店铺提货清单
     * @param  $id  int  店铺Id
     */
    function extractList(){
        //查看是登录
        $this->isOnline();
        //校验接口权限
        $this->checkRole($this->user_role, $this->config->item('role_Distill'));
        $addres_id=$this->input->get('id',true);
        if(!is_numeric($addres_id) || $addres_id < 0 ){
            often_helper::output('21011','','','');
        }
        $this->load->model('m/Order_model');
        $this->Order_model->manage_id=$this->user_id;
        $this->Order_model->address_id=$addres_id;
        $res=$this->Order_model->extractList();
        //用户ziliao
        $user=array('name'=>$this->user_name,'number'=>$this->user_number);
        $this->assign('user', $user);
        $this->assign('list', $res);
        $this->display('m/shopMess.html');
    }
    /**
     * @abstract 提货  提货员发起提货请求
     * @param  $id string  提货Id
     * @return  json
     */
    function initiateExtract(){
        //校验是否在线
        $this->isOnline();
        //校验接口权限
        $this->checkRole($this->user_role, $this->config->item('role_Distill'));
        $order_id=$this->input->post('id');
        $arr_nums=explode(',', trim($order_id,','));
        $nums=sizeof($arr_nums);//订单数量
        //校验订单ID 类型是否是数组 切不是0
        $temp_id=str_replace(',','', $order_id);
        if(!is_numeric($temp_id) || $temp_id < 0 ){
            often_helper::output('21011','','','提货清单出现异常!');
        }
        $this->load->model('m/Order_model');
        //验证订单所属的店铺是改提货员
        $this->Order_model->manage_id=$this->user_id;
        $this->Order_model->distill_conten=trim($order_id,',');
        $address_id=$this->Order_model->checkOrderID();
        if($address_id === false ){
            often_helper::output('21011','','','校验提货清单出现异常!');
        }
        //添加提货记录
        $this->Order_model->address_id=$address_id; 
        $this->Order_model->nums=$nums;
        $this->Order_model->distill_req=$this->user_id;
        $resp=$this->Order_model->insDistill();
        if($resp){
                often_helper::output('21010','','','提货请求已经发送到店铺!');
        }else{
                often_helper::output('21011','','','本次请求包含非法字符!');
        }
       
    }
    /**
     * @abstract  提货   提货员查看提货记录
     */
    function extractLogList(){
        //校验是否在线
        $this->isOnline();
        //校验接口权限
        $this->checkRole($this->user_role, $this->config->item('role_Distill'));
        //添加提货记录
        $this->load->model('m/Order_model');
        $this->Order_model->manage_id=$this->user_id;
        $resp=$this->Order_model->extractLogList();
        if($resp === false ){
            often_helper::output('21011','','','');
        }
        $this->assign('result', $resp);
        $this->display('m/distilllist.html');
    }
    /**
     *@abstract 订单列表   根据订单Id 读取订单详细内容
     *@param   $id       int      订单Id
     *@param   $user_id  int      用户ID
     *@param   $number   string   员工编号
     *@return json 
     */
    function getOrderDetail(){
        //校验是否登录
        $this->isOnline();
        //校验接口权限
        if($this->user_role != $this->config->item('role_customer') 
                && $this->user_role != $this->config->item('role_Clerk') 
                && $this->user_role != $this->config->item('role_Shopowner')){
            often_helper::output('21011','','','您并不能进行此操作!');
        }
        $id=$this->input->get('id','true');
        if( !isset($id)|| !is_numeric($id)  || $id< 0 ){
            often_helper::output('21011','','','本次请求包含非法字符!');
        }
        $this->load->model('m/Order_model');
        $this->Order_model->user_number=$this->user_number;
        $this->Order_model->user_role=$this->user_role;
        $this->Order_model->user_id=$this->user_id;
        $this->Order_model->order_id=$id;
        $result=$this->Order_model->getOrderDetail();
        if($result === false){
            often_helper::output('21011','','','查询结果失败!');
        }
        $account=explode(',', ($result['account']));
        $vdata=explode(',',$result['text']);
        $this->assign('status', $this->config->item('order_status_ordinary'));
        $this->assign('account', $account);
        $this->assign('vdata', $vdata);
        $this->assign('oid', $id);
        $this->assign('result', $result);
        $this->assign('role', $this->user_role);
        $this->assign('role_customer', $this->config->item('role_customer'));
        $this->assign('role_Clerk', $this->config->item('role_Clerk'));
        $this->display('m/order.html');
    }
    /**
     * @abstract  提货  店长  店员 确认 提货员的提货请求
     */
    function extractInfoList(){
        //校验是否登录
        $this->isOnline();
        //校验接口权限
        if($this->user_role != $this->config->item('role_Shopowner')
                && $this->user_role != $this->config->item('role_Clerk')){
            often_helper::output('21011','','','您并不能进行此操作!');
        }
        //查看当前店铺的提货请求
        $this->load->model('m/Order_model');
        $this->Order_model->manage_id=$this->user_id;
        $resp=$this->Order_model->shopExtractInfo();
        if($resp !== false){
            $this->Order_model->distill_conten=$resp['distill_conten'];
            $list=$this->Order_model->extractOrderList();
            $resp['num']=sizeof($list);
        }else{
            $list=0;
        }
        if($resp === false ){
            $resp  = 0;
            $primit= 0;
            
        }else{
            $primit = $resp['distill_deed']== 1 ?  1: 0;
            $distill_state=$this->config->item('distill_state');
            $resp['distill_deed']=$distill_state[$resp['distill_deed']];
        }
        $this->assign('primit', $primit);
        $this->assign('list', $resp);
        $this->assign('order', $list);
        $this->display('m/pickgood.html');
    }
    /**
     * @abstract 提货   店员或店长  确认提货员发起的提货请求
     * @return  json
     */
    function updateDistill(){
        //校验是否登录
        $this->isOnline();
        //校验接口权限
        if($this->user_role != $this->config->item('role_Shopowner')
                && $this->user_role != $this->config->item('role_Clerk')){
            often_helper::output('21011','','','您并不能进行此操作!');
        }
        $id=$this->input->post('id',true);
        if(empty($id) || !is_numeric($id) || $id<0){
            often_helper::output('21011','','','本次请求包含非法字符!');
        }
        $this->load->model('m/Order_model');
        $this->Order_model->manage_id=$this->user_id;
        $this->Order_model->distill_id=$id;
        $res=$this->Order_model->updateDistill();
        if($res){
            often_helper::output('21010',$res,'','');
        }else{
            often_helper::output('21011','','','没有修改记录!');
        }
    }
    /**
     *@abstract 9在订单详情页面取消订单
     */
    function cancles(){
        $data=$this->input->post();
        $oid=$data['id'];
        if(!is_numeric($oid) || !isset($oid)){
            often_helper::output('21011','','','本次请求包含非法字符!');
        }
        $this->load->model('m/Digital_model');
        $this->Digital_model->oid=$oid;
        $result=$this->Digital_model->cancles();
        if($result){
            often_helper::output('21010','','','');
        }else{
            often_helper::output('21011','','','操作失败!');
        }
    }
    
    /**
     * @abstract  订单列表   用户在订单列表页面点击取消订单
     * @param $id int  订单id
     * @return  json
     */
    function cancleOrder(){
        //校验是否登录
        $this->isOnline();
        $oid=$this->input->post('id');//订单编号
        if(empty($oid) || !is_numeric($oid) || !isset($oid)){
            often_helper::output('21011','','','本次请求包含非法字符!');
        }
        $this->load->model('m/Order_model');
        $this->Order_model->order_id=$oid;
        $this->Order_model->user_id=$this->user_id;
        $result=$this->Order_model->cancleOrder();
        if($result){
            often_helper::output('21010','','','');
        }else{
            often_helper::output('21011','','','订单取消失败');
        }
    }
    /**
     * @abstract 确认订单   店员确认订单
     */
    function shopOrder(){
        //校验是否登录
        $this->isOnline();
        //校验接口权限
        if($this->user_role != $this->config->item('role_customer') 
                && $this->user_role != $this->config->item('role_Clerk')){
            often_helper::output('21011','','','您并不能进行此操作!');
        }
        $id=$this->input->post('id',true);
        if(!is_numeric($id) || $id < 0){
            often_helper::output('21011','','','获取订单编号出现异常!');
        }
        
        $this->load->model('m/Order_model');
        $this->load->model('m/Order_model');
        $this->Order_model->cooperator_number=$this->user_number;
        $this->Order_model->user_role=$this->user_role;
        $this->Order_model->user_id=$this->user_id;
        $this->Order_model->order_id=$id;
       $confirm=$this->Order_model->getOrderInfo();
       if($confirm['confirm']== -1){
            often_helper::output('21011','','','请等待用户确认最新的报价!');
       }
       if($confirm['orderstatus'] != 1 &&  $confirm['orderstatus'] != 2){
            often_helper::output('21011','','','当前订单状态不允许此项操作!');
       }
       if($confirm['confirm']== 0){
            $this->Order_model->order_confirm=0;
       }else{
           $this->Order_model->order_confirm=1;
       }
       $this->Order_model->oid=$confirm['oid'];//订单编号
       $this->Order_model->cooperator_number=$this->user_number;
       $this->Order_model->order_id=$id;
       $result=$this->Order_model->confirmOrder();
       if($result){
          often_helper::output('21010','','','操作成功');
       }else{
          often_helper::output('21011','','','操作失败');
       }
    }
    
    /**
     * @abstract 提交订单  客户同意报价
     */
    function hShadow(){
        //校验登录
        $this->isOnline();
        //校验接口全权限
        $this->checkRole($this->user_role, $this->config->item('role_customer'));
        $id=$this->input->post('id');
        if(!is_numeric($id) || !isset($id) || $id < 0){
            often_helper::output('21011','','','本次请求包含非法字符!');
        }
        $this->load->model('m/Order_model');
        $this->Order_model->user_id=$this->user_id;
        $this->Order_model->order_id=$id;//订单编号
        $result=$this->Order_model->hShadow();
        if($result){
            often_helper::output('21010','','','');
        }else{
            often_helper::output('21011','','','确认订单出现异常!');
        }
    }
    /**
     * @abstract 订单列表  修改订单参数列表
     */
    function getOrderOne(){
        //校验是否已经登录
        $this->isOnline();
        //校验传递的参数
        $oid=$this->input->get('oid');
        if(empty($oid) || !is_numeric($oid) || isset($oid{10})){
            often_helper::output('21011','','','本次请求包含非法字符!');
        }
        //获取保存的参数信息
        $this->load->model('m/Order_model');
        $this->Order_model->user_role=$this->user_role;
        $this->Order_model->order_id=$oid;
        $this->Order_model->user_id=$this->user_id;
        $this->Order_model->user_number=$this->user_number;
        $results=$this->Order_model->getOrderDetail();
        //查询型号参数配置
        $this->load->model('m/Digital_model');
        $this->typeid=$results['gid'];
        $this->Digital_model->typeid=$this->typeid;
        $result=$this->Digital_model->getParameter();
        if(empty($result['types_attr'])){
            often_helper::alert('本次请求包含非法字符!','quote',0);
        }
        $attr=json_decode($result['types_attr'],true);
        $option=$this->Digital_model->getTypeCon();
        foreach ($attr as $k=>$v){
            foreach ($v as $i=>$n){
                $attr[$k][$i]=str_replace(array('[',']'),array('',''),$n);
            }
        }
        $model=array();
        foreach ($option['model'] as $key=>$val){
            $model[$val['alias']]=array('model'=>$val['model'],'type'=>$val['type'],
                    'name'=>$val['name'],'logic'=>$val['logic']);
        }
        $info=array();
        foreach ($option['info'] as $key=>$val){
            $info[$val['id']]=$val['info'];
        }
        $role=$_SESSION['user']['user_role'];
        $resAttr=array();
        $resAttr=explode(',',$results['cid']);
        $resCon=array();
        $resCon=explode(',',$results['text']);
        $this->assign('resCon', $resCon);//已选中参数值
        $this->assign('resAttr', $resAttr);//已选中参数id
        $this->assign('attr', $attr);
        $this->assign('model', $model);
        $this->assign('info', $info);
        $this->assign('cdata', $results['text']);
        $this->assign('cid', $results['cid']);
        $this->assign('name',$results['gname']);
        $this->assign('oid', $oid);
        $this->assign('role', $role);
        $this->assign('role_customer',$this->config->item('role_customer'));
        $this->assign('role_Clerk',$this->config->item('role_Clerk'));
        $this->display('m/classify.html');
    }
    /**
     * 商户修改订单后保存修改的信息
     */
    function saveNextAgain(){
        $this->isOnline();
        //验证提交数据
        $this->againS=1;
        $res=$this->checkNextData();
        if($res){
            $attrs=$res['attr'];
            $this->attr=$this->handleData($attrs);
            $this->load->model('m/Order_model');
            $this->Order_model->user_role=$this->user_role;
            $this->Order_model->order_id=$res['orders']['oid'];
            $this->Order_model->user_id=$this->user_id;
            $this->Order_model->user_number=$this->user_number;
            $results=$this->Order_model->getOrderDetail();
            //查看订单参数
            $this->load->model('m/Digital_model');
            $this->typeid=$results['gid'];
            $this->Digital_model->typeid=$this->typeid;
            $this->Digital_model->coop=1447299307;
            $plan=$this->Digital_model->GetPlan();
            foreach ($plan as $k=>$v){
                $this->quote=json_decode($v['plan_content'],true);
                // 获取该型号的基本市价
                $this->lock=$plan['0']['plan_base_price'];
                $pri=$this->reckonRules();
            }
            //商户修改故障报单信息后 需要修改的数据
            $this->Order_model->oid=$res['orders']['oid'];//订单编号
            $this->Order_model->pri=$pri;//价格
            $this->Order_model->vid=$res['orders']['cid'];//订单故障id
            $this->Order_model->vdata=$res['orders']['cdata'];//订单故障内容
            $result=$this->Order_model->saveNextAgain();
            if($result){
                $this->orderid=$res['orders']['oid'];
                $this->sendUpPic();
                often_helper::output('21010',$this->orderid,'','');
            }else{
                often_helper::output('21011','','','保存数据失败!');
            }
        }else{
            often_helper::output('21011','','','非法数据!');
        }
    }
    /**
     * 用户确认订单后 发送通知给代理商
     */
    function sendConfirm(){
        //根据订单编号查询用户
        $this->load->model('m/Digital_model');
        $this->Digital_model->orderid=$this->orderid;
        $this->Digital_model->type=9;
        $resp=$this->Digital_model->getOpneid();
        if($resp === false){
            often_helper::alert('未获取到订单的归属用户!','quote',0);
        }
        $url='http://m.rrahs.com/m/digital/getOrderDetail?id='.$this->orderid;
        $link='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.
                $this->config->item('appid').'&redirect_uri='.urlencode($url).
                '&response_type=code&scope=snsapi_base&state=#wechat_redirect';
        $content = sprintf($this->config->item('msg_confirm'),
                $resp['user_openid'],$link);
        $this->load->model('m/wxcode_model');
        $this->wxcode_model->sendmessage($content);
    }
    /**
     *商户修改价格后发送通知给用户
     */
    function sendUpPic(){
        //根据订单编号查询用户
        $this->load->model('m/Digital_model');
        $this->Digital_model->orderid=$this->orderid;
        $this->Digital_model->type=1;
        $resp=$this->Digital_model->getOpneid();
        if($resp === false){
            often_helper::alert('未获取到订单的归属用户!','quote',0);
        }
        $url='http://m.rrahs.com/m/digital/getOrderDetail?id='.$this->orderid;
        $link='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.
                $this->config->item('appid').'&redirect_uri='.urlencode($url).
                '&response_type=code&scope=snsapi_base&state=#wechat_redirect';
        $content = sprintf($this->config->item('msg_upprc'),$resp['user_openid'],$link);
        $this->load->model('m/wxcode_model');
        $this->wxcode_model->sendmessage($content);
    }
     
    /**
     * 检查下单页面储存的手机故障信息数据
     *
     */
    function checkNextData(){
        $data=$this->input->post();
        $resp=array();
        isset($this->againS) ? '': $this->againS = 0 ;
        if(isset($this->againS) && $this->againS == 1){
            $order=array();
            $this->attr=array();
            $newOrder=array();
            $res=array();
        }
        //校验订单编号
        if (!is_numeric($data['oid']) || $data['oid'] < 0){
            $resp=array('21011','','','非法订单编号!');
        }
        $this->againS == 1 ? $newOrder['oid'] = $order['oid'] = $data['oid'] : '' ;
        if (empty($data['hideInp'])){
            $resp=array('21011','','','非法请求参数!');
        }
        $this->againS == 1 ? $newOrder['hideInp'] = $data['hideInp'] : '' ;
        $this->againS == 1 ? $newOrder['hideInpVal'] = $data['hideInpVal'] : '' ;
        $this->againS == 1 ? $newOrder['manycho'] = $data['manycho'] : '' ;
        $this->againS == 1 ? $newOrder['manychoVal'] = $data['manychoVal'] : '' ;
        $faultName=array();
        $faultId=array();
        $resName='';
        $resId='';
        $newOrder['vdata']=$data['hideInpVal'].$data['manychoVal'];
        $cdata=substr($newOrder['vdata'],0,strlen($newOrder['vdata'])-1);
        $faultName = explode(',',$cdata);
        foreach($faultName as $k=>$v){
            if(empty($v)){
                $resp=array('21011','','','非法请求参数!');
            }
            $resName=$resName.$v.',';
        }
        $newOrder['vid']=$data['hideInp'].$data['manycho'];
        $cid=substr($newOrder['vid'],0,strlen($newOrder['vid'])-1);
        $faultId = explode(',',$cid);
        foreach($faultId as $k=>$v){
            if(empty($v) || !is_numeric($v) || $v<0){
                $resp=array('21011','','','非法请求参数!');
            }
            $resId=$resId.$v.',';
        }
        $order['cdata']=substr($resName,0,strlen($resName)-1);
        $order['cid']=substr($resId,0,strlen($resId)-1);
        if(!empty($resp)){
            $this->checkDataOut(1,$resp);
        }
        if(isset($this->againS) && $this->againS == 1){
            $this->order=$order;
            $this->attr=array_diff_key($data,$newOrder);
        }
        $res['orders']=$this->order;
        $res['attr']=$this->attr;
        return $res;
    } 
    /**
     * @abstract 订单列表   查看我的业绩
     */
    function userSettlement(){
        //校验登录
        $this->isOnline();
        //校验接口权限
        if(!in_array($this->user_role,array($this->config->item('role_Shopowner'),
                 $this->config->item('role_Clerk')))){
            often_helper::output('21011','','','您并不能进行此操作!');
        }
        $year=$this->input->get('month');
        empty($year) ? $year = date('Y') : '';
        if(!is_numeric($year) ||  !in_array($year, array('2017','2018'))){
             often_helper::output('21011','','','选择的年份不正确!');
        }
        $month=$this->input->get('manth');
        empty($month) ? $month = date('m') : '';
        if(!is_numeric($month) ||  $month > 12 || $month < 1){
             often_helper::output('21011');
        }
        $this->load->model('m/Order_model');
        $this->Order_model->date=$year.'-'.$month;
        $this->Order_model->user_role=$this->user_role;
        $this->Order_model->user_id=$this->user_id;
        $result=$this->Order_model->userSettlement();
        if($result == false){
            often_helper::output('21011');
        }
        //var_dump($result['list']);exit();
        $commission_ratio=$this->config->item('commission_ratio');
        $ratio=$commission_ratio[$this->user_role];
        $commission=$result['total']*$ratio;
        $commission=intval($commission);
        //floor()/ceil()； round()；
        $this->assign('total', $result['total']);
        $this->assign('commission', $commission);
        $this->assign('list', $result['list']);
        $this->assign('url', '?year='.$year.'&month='.$month);
    	$this->display('m/performance.html');
    }
    /**
     * @abstract 处理自动报价的数据
     * @param   array  data 报价数据
     * @return  成功返回array | 失败输出 json  失败原因
     */
    function handleData($data){
        if(empty($data)){
            often_helper::output('21010','','','本次选择的属性不符合规范!');
        }
        //去除数组中为空的选项
        array_filter($data);
        //校验时候存在多选参数 存在转换成数组
        if(array_key_exists('other',$data)){
            if(count($data['other'])>0){
                $otee=$data['other'];
                $other_temp=often_helper::safe_replace($otee);
            }
        }
        if(isset($other_temp) && empty($other_temp) === false){
            $sre_other=often_helper::safe_replace($other_temp);
            if(!is_numeric($other_temp)){
                often_helper::output('21010','','','本次选择的属性不符合规范!');
            }
            $other=str_replace(array(' '),array(''),$otee);
            $other=trim($other,',');
            $other=explode(',',$other);
        }
        if(isset($other)){
            $data['other']=$other;
        }
        return $data;
    }
    /**
     *@abstract 自动报价运算规则
     *
     */
    function reckonRules(){
        $plan=$this->quote;
        //获取自动报价参数 分类
        $this->load->model('m/Digital_model');
        $model=$this->Digital_model->optionModel();
        $attr=$this->attr;
        $quote=array();
        $basics=array();
        $unive=array();
        $special=array();
        foreach ($model as $key=>$val){
            switch ($val['type']){
                case '0':
                    array_key_exists($val['alias'],$attr) == true ? $basics[$key]=$attr[$val['alias']] : '';
                    break;
                case '1':
                    array_key_exists($val['alias'],$attr) == true ? $unive[$key]=$attr[$val['alias']] : '';
                    break;
                case '2':
                    array_key_exists($val['alias'],$attr) == true ? $special=$attr[$val['alias']] : '';
                    break;
            }
        }
        $basics=$this->position($basics,$plan,1);
        $unive=$this->position($unive,$plan,2);
        $special=$this->position($special,$plan,2);
        $pri=$this->reckon($this->lock, $basics, $unive, $special);
        return $pri;
    }
    /**
     *@abstract 得到扣款中同类型百分比最多的一项
     *@param array  $option  订单参数
     *@param array  $plan    报价参数
     *@param int    $type    类型   值为1 不返回最大值 2返回最大值
     */
    function position($option,$plan,$type){
        $ratio=array();
        if(!is_array($option)){
            return 0;
        }else{
            foreach ($option as $key=>$val){
                $ratio[]=str_replace(array('-','+','%'),array('','',''),$plan[$val]);
            }
            switch($type){
                case 1:
                    $result=$ratio;
                    break;
                case 2:
                    $result=empty($ratio) ? '0' : max($ratio);
                    break;
            }
            return  $result;
        }
        exit;
    }
    /**
     *@abstract 计算价格
     */
    function reckon($pri,$basics,$unive,$special){
        if(!is_array($basics)){
            $pri=$pri-$basics;
        }else{
            foreach ($basics as $key=>$val){
                $pri=$pri-$val;
            }
        }
        $pri=$pri-$pri*$unive/100;
        if(!empty($special)){
            $pri=$pri-$pri*$special/100;
        }
        $pri=round($pri);
        return $pri;
    }
}