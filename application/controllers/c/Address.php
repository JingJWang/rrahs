<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class address extends RR_Controller{
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
     * @abstract 后台订单管理-地址列表
     */
    function getAddressList(){
        $addCon=$this->input->post('address',true);
        //获取参数 并效验
        if(empty($addCon) && preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $addCon) < 0){
             often_helper::Output('41010','非法参数');
        }
        $limit=$this->input->post('limit',true);
        $this->load->model('c/Address_model');
        $this->Address_model->addCon=$addCon;
        $this->Address_model->limit=$limit;
        $res=$this->Address_model->selectList();
        often_helper::output('41010',$res);
    }
    /**
     * @abstract 后台订单管理-订单列表
     */
    function shop(){
        $this->gonggong();
        $this->display('c/order_add.html');
    }
    /**
     * @abstract 效验店铺地址的id
     */
    function  checkAddId(){
        $new=array('id'=>'');
        $data=$this->input->post();
        if(!isset($data['id']) || empty($data['id']) || $data['id']<0 || !is_numeric($data['id'])){
            often_helper::output('41011','','','非法参数');
        }
        $new['id']=$data['id'];
        return $new['id'];
    }
    
    /**
     * @abstract  效验店铺 地址和名字
     */
    function checkData(){
        $newData=array(
            'name'=>'',
            'address'=>'',
            'number'=>'',
            'manid'=>''
        );
        $data=$this->input->post();
        if(!isset($data['name']) || empty($data['name'])){
            often_helper::output('41011','','','非法参数4');
        }
        $newData['name']=$data['name'];
        
        if(!isset($data['address']) || empty($data['address'])){
            often_helper::output('41011','','','非法参数3');
        }
        $newData['address']=$data['address'];
        
        if(!isset($data['number']) || empty($data['number'])){
            often_helper::output('41011','','','非法参数2');
        }
        $newData['number']=$data['number'];
        
        if(!isset($data['manid']) || empty($data['manid'])){
            often_helper::output('41011','','','非法参数1');
        }
        $newData['manid']=$data['manid'];
        return $newData;
    }
    /**
     * @abstract 通过地址编号 获取地址内容信息
     */
    function getAddressOne(){
        $id=$this->checkAddId();
        $this->load->model('c/Address_model');
        $this->Address_model->addId=$id;
        $res=$this->Address_model->getAddressOne();
        $this->load->model('c/Static_model');
        $tihuo=$this->Static_model->getTiHuo();
        $res['tihuo']=$tihuo;
        if($res){
            often_helper::output('41010',$res);
        }else{
            often_helper::output('41011');
        }
    }
    /**
     * @abstract 修改门店地址
     */
    function updateAddress(){
        //效验地址id
        $id=$this->checkAddId();
        //效验地址的名字和名字
        $data=$this->checkData();
        $this->load->model('c/Address_model');
        $this->Address_model->addid=$id;
        $this->Address_model->name=$data['name'];
        $this->Address_model->manid=$data['manid'];
        $this->Address_model->number=$data['number'];
        $this->Address_model->address=$data['address'];
        $res=$this->Address_model->updateAddress();
        if($res){
            often_helper::output('41010',$res);
        }else{
            often_helper::output('41011');
        }
    }
    /**
     * @abstract 添加门店地址
     */
    function insertAddress(){
        //效验地址的名字和名字
        $data=$this->checkData();
        $this->load->model('c/Address_model');
        $this->Address_model->name=$data['name'];
        $this->Address_model->manid=$data['manid'];
        $this->Address_model->address=$data['address'];
        $this->Address_model->number=$data['number'];
        $res=$this->Address_model->insertAddress();
        if($res){
            often_helper::output('41010',$res);
        }else{
            often_helper::output('41011');
        }
    }
}