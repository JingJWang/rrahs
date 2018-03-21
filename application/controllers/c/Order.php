<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class  order extends  RR_Controller{
    /**
     * @abstract 效验登录后台的session
     */
    function checkLoginSess(){
        $newSess=array(
            'name'=>'',
            'role'=>''
        );
        if(is_array($_SESSION)){
            if(isset($_SESSION['manage']['manage_name']) && $_SESSION['manage']['manage_name']!=''){
                $newSess['name']=$_SESSION['manage']['manage_name'];
            }
            if(isset($_SESSION['manage']['manage_role']) && $_SESSION['manage']['manage_role']!=''){
                $newSess['role']=$_SESSION['manage']['manage_role'];
            }
        }
        return $newSess;
    }
    /**
     * @abstract 通过用户角色值来获取用户的角色名字
     */
    function getRoleName(){
        $this->load->model('c/Static_model');
        $this->Static_model->roleid=$this->role;
        $res=$this->Static_model->getRoleName();
        return $res;
    }
    /**
     * @abstract 获取后台管理系统-顶部top公共样式内容
     */
    function gonggong(){
        //效验登录后台的session 并获取相应的数据
        $newSess=$this->checkLoginSess();
        //获取角色名字
        $this->role=$newSess['role'];
        $roleName=$this->getRoleName();
        if($roleName){
            $rolename=$roleName['rolename'];
            $roleid=$roleName['roleid'];
        }else{
            $role='';
        }
        $this->assign('roleid',$roleid);
        $this->assign('rolename',$rolename);
        $this->assign('name',$newSess['name']);
    }
    /**
     * @abstract 获取门店地址 及该订单处理人的mingzi
     */
    function getManageMess(){
        $magMess=array();
        //获取该订单处理人的名字
        $this->Manage_model->number=$this->cnumber;
        $userName=$this->Manage_model->getManagerOne();
        $magMess['cnumber']=$userName['name'];
        //获取该订单处理人所在店铺的地址
        $this->load->model('c/Address_model');
        $this->Address_model->addId=$userName['addId'];
        $add=$this->Address_model->getAddressOne();
        $magMess['addid']=$add['addName'];
        return $magMess;
    }
    /**
     * @abstract 效验参数
     */
     function checkOrderData(){
        $data=$this->input->post();
        $newData=array(
            'option'=>'',
            'status'=>'',
            'ystar'=>'',
            'yend'=>'',
            'wstar'=>'',
            'wend'=>'',
            'mobile'=>'',
            'limit'=>''
        );
        if(isset($data['first']) && !empty($data['first'])){
            if(!in_array($data['first'],array('day','week','month'))){
                often_helper::output('41011','','','非法参数');
            }
        }
        $newData['option']=$data['first'];
        if(isset($data['status']) && empty($data['status']) && !is_numeric($data['status']) && $data['status']<0){
                often_helper::output('41011','','','非法参数');
        }
        $newData['status']=$data['status'];
        if(isset($data['ystart']) && !is_string($data['ystart'])){
            often_helper::output('41011','','','非法参数');
        }
        $newData['ystar']=$data['ystart'];
       
        if(isset($data['yend']) && !is_string($data['yend'])){
            often_helper::output('41011','','','非法参数');
        }
        $newData['yend']=$data['yend'];
        if(isset($data['wstart']) && !is_string($data['wstart'])){
            often_helper::output('41011','','','非法参数');
        }
        $newData['wstar']=$data['wstart'];
         
        if(isset($data['wend']) && !is_string($data['wend'])){
            often_helper::output('41011','','','非法参数');
        }
        $newData['wend']=$data['wend'];
        if(isset($data['mobile']) && !is_string($data['mobile'])){
            often_helper::output('41011','','','非法参数');
        }
        $newData['mobile']=$data['mobile'];
        if(isset($data['limit']) && !is_string($data['limit'])){
            often_helper::output('41011','','','非法参数');
        }
        $newData['limit']=$data['limit'];
        return $newData;
    }
    /**
     * @abstract 后台订单管理-订单列表
     */
    function getOrderList(){
        //获取参数 并效验
        $resData=$this->checkOrderData();
        $this->load->model('c/Order_model');
        $this->Order_model->option=$resData['option'];
        $this->Order_model->status=$resData['status'];
        $this->Order_model->ystar=$resData['ystar'];
        $this->Order_model->yend=$resData['yend'];
        $this->Order_model->wstar=$resData['wstar'];
        $this->Order_model->wend=$resData['wend'];
        $this->Order_model->mobile=$resData['mobile'];
        $this->Order_model->limit=$resData['limit'];
        $res=$this->Order_model->getOrderList();
        if($res['result']){
            foreach($res['result'] as $key=>$val){
                //获取该订单是谁接收的编号 
                $this->load->model('c/Manage_model');
                $this->cnumber=$res['result'][$key]['cnumber'];
                //获取其名字和门店id
                $maggMess=$this->getManageMess();
                $res['result'][$key]['cnumber']=$maggMess['cnumber'];
                $res['result'][$key]['addid']=$maggMess['addid'];
            }            
        }
        often_helper::output('41010',$res);
    }
    /**
     * @abstract 效验订单打款参数
     */
    function checkVoucher(){
        $data=$this->input->post();
        $res=array(
            'dkyh'=>'',
            'dklsh'=>'',
            'orderId'=>''
        );
        //校验银行卡开户行
        if(empty($data['dkyh']) || !isset($data['dkyh'])){
           often_helper::output('41011','','','开户行为空或填写格式不正确!');
        }
        $res['dkyh']=$data['dkyh'];
        //校验流水账号
        if(empty($data['dklsh']) || !isset($data['dklsh'])){
           often_helper::output('41011','','','流水账号为空或填写格式不正确!');
        }
         $res['dklsh']=$data['dklsh'];
        //校验报价价格
        if(empty($data['orderId']) || !is_numeric($data['orderId']) || $data['orderId'] < 0){
            often_helper::output('41011','','','订单编号异常!');
        }
        $res['orderId']=$data['orderId'];
        return  $res;
    }
    /**
     * @abstract
     */
    function voucher(){
        //效验订单参数
        $res=$this->checkVoucher();
        $this->load->model('c/Order_model');
        $this->Order_model->orderid=$res['orderId'];
        $thisOrder=$this->Order_model->getStatusOrder();
        if($thisOrder){
            $this->Order_model->orderid=$thisOrder['order_id'];
            unset($res['orderId']);
            $this->Order_model->voucher=json_encode($res);
            $res=$this->Order_model->updateOrderMess();
            if($res){
                often_helper::output('41010',$res);
            }else{
                often_helper::output('41011');
            }
        }else{
            often_helper::output('41011');
        }
       
    }
    /**
     * @abstract 后台订单管理-订单列表
     */
    function user(){
        $this->gonggong();
        $this->display('c/order_one.html');
    }
}