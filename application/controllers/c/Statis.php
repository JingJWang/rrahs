<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class  statis extends  RR_Controller{
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
     * @abstract 后台用户管理-用户管理-普通用户
     */
    function show(){
        $this->gonggong();
        $this->display('c/show.html');
    }
    /**
     * @abstract 后台用户管理-用户管理-系统用户
     */
    function user(){
        $this->gonggong();
        $this->display('c/righta.html');
    }
    /**
     * @abstract 后台用户管理-用户管理-系统用户 页面显示需要查询的条件验证
     */
    function checkUserData(){
        $data=$this->input->post();
        $newData=array(
            'option'=>'',
            'starttime'=>'',
            'endtime'=>'',
            'mobile'=>'',
            'limit'=>''
        );
        if(isset($data['option']) && !empty($data['option'])){
            if(!in_array($data['option'],array('day','week','month'))){
                often_helper::output('41011','','','非法参数');
            }
        }
        $newData['option']=$data['option'];
        if(isset($data['starttime']) && !is_string($data['starttime'])){
            often_helper::output('41011','','','非法参数');
        }
        $newData['starttime']=$data['starttime'];
       
        if(isset($data['endtime']) && !is_string($data['endtime'])){
            often_helper::output('41011','','','非法参数');
        }
        $newData['endtime']=$data['endtime'];
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
     * @abstract 后台用户管理-用户管理-系统用户 页面显示的数据
     */
    function getAllUser(){
        //获取参数 并效验
        $resData=$this->checkUserData();
        $this->load->model('c/Static_model');
        $this->Static_model->option=$resData['option'];
        $this->Static_model->starttime=$resData['starttime'];
        $this->Static_model->endtime=$resData['endtime'];
        $this->Static_model->mobile=$resData['mobile'];
        $this->Static_model->limit=$resData['limit'];
        $res=$this->Static_model->getManagerList();
        //获取店长列表
        $keeper=$this->Static_model->getKeeper();
		 //获取权限类别
        $quanxian=$this->Static_model->getRoleList();
        //获取门店地址详情
        $this->load->model('c/Address_model');
        $add=$this->Address_model->getAddList();
        $data=array(
            'res'=>$res,
            'qx'=>$quanxian,
            'adre'=>$add,
            'kep'=>$keeper
        );
        often_helper::output('41010',$data);
	}
    /**
     * 效验权限
     */
    function checkUserEdit(){
        $data=$this->input->post();
        $newDa=array(
            'id'=>'',
            'addrid'=>'',
            'roleid'=>'',
            'manid'=>0
        );
        if(!isset($data['userid']) || empty($data['userid']) || !is_numeric($data['userid']) || $data['userid']<0){
            often_helper::output('41011','','','非法参数!');
        }
        $newDa['id']=$data['userid'];
        if(!isset($data['addrid']) || empty($data['addrid']) || !is_numeric($data['addrid']) || $data['addrid']<0){
            often_helper::output('41011','','','非法参数!');
        }
        $newDa['addrid']=$data['addrid'];
        if(!isset($data['roleid']) || empty($data['roleid']) || !is_numeric($data['roleid']) || $data['roleid']<0){
            often_helper::output('41011','','','非法参数!');
        }
        $newDa['roleid']=$data['roleid'];
        if(!isset($data['manid'])){
            $newDa['manid']=0;
        }
        $newDa['manid']=$data['manid'];
        return $newDa;
    }
    /**
     * @abstract 后台用户管理-用户管理-系统用户- 审核
     */
    function editUser(){
        //效验参数数据
        $newDa=$this->checkUserEdit();
        //通过角色id 获取角色值
        $this->load->model('c/Static_model');
        $this->Static_model->roleid=$newDa['roleid'];
        $roleid=$this->Static_model->getRoleName();
        //通过用户选择的门店地址 以及用户自己的id 合组成 用户的账户编号
        //先通过门店地址id 找到门店的编号
        $this->load->model('c/Address_model');
        $this->Address_model->addId=$newDa['addrid'];
        $mdbh=$this->Address_model->getAddressOne();
         //获取门店编号 把店铺编号跟用户id 组成用户的账户编号
        $number=$mdbh['number'].'-'.$newDa['id'];
        //通过店长的id 查看该店长的角色是否是店长一角
        if($newDa['manid']>0){
            $this->Static_model->id=$newDa['manid'];
            $resu=$this->Static_model->getUserManageOne();
            //验证 给用户的角色是店员 并且选择的店长列表 角色是店长一角
            if($resu['roleid']==$this->config->item('role_Shopowner') && $roleid['roleid']==$this->config->item('role_Clerk')){
                $this->Static_model->manid=$newDa['manid'];
            }else{
                $this->Static_model->manid=0;
            }
        }else{
            $this->Static_model->manid=$newDa['manid'];
        }
        //赋值给参数
        $this->Static_model->id=$newDa['id'];
        $this->Static_model->number=$number;
        $this->Static_model->roleid=$roleid['roleid'];
        $this->Static_model->addrid=$newDa['addrid'];
        $res=$this->Static_model->editManagerRole();
        if(!$res){
            often_helper::output('41011');
        }
        often_helper::output('41010',$res);
    }
    /**
     * @abstract 后台用户管理-用户管理-系统用户- 解封/禁用/功能
     */
    function UpdateUserStatus(){
        $id=$this->input->post('id',true);
        if(!isset($id) || empty($id) || !is_numeric($id)){
            often_helper::output('41011','','','非法参数!');
        }
        $status=$this->input->post('status',true);
        if(!isset($status) || !is_string($status) || empty($status)){
            often_helper::output('41011','','','非法参数!');
        }
        $this->load->model('c/Static_model');
        $this->Static_model->id=$id;
        $this->Static_model->status=$status;
        $res=$this->Static_model->getManagerList();
        if(!$res){
            often_helper::output('41011');
        }
        often_helper::output('41010',$res);
    }
    /**
    * @abstract 后台用户管理-用户管理-普通用户 获取列表
    */
    function getUserKind(){
        $mobile=$this->input->post('mobile',true);
        //获取参数 并效验
        if(isset($mobile) && !is_string($mobile)){
            often_helper::output('41011','','','非法参数');
        }
        $limit=$this->input->post('limit',true);
        //获取参数 并效验
        if(isset($limit) && (!is_string($limit) || $limit<0)){
            often_helper::output('41011','','','非法参数');
        }
        $this->load->model('c/User_model');
        $this->User_model->mobile=$mobile;
        $this->User_model->limit=$limit;
        $res=$this->User_model->getUserList();
        often_helper::output('41010',$res);
    }
    /**
     * @abstract 后台用户管理系统-用户管理-普通用户-禁用用户功能
     */
    function updateUserKind(){
        $id=$this->input->post('id',true);
        if(!isset($id) || empty($id) || !is_numeric($id)){
            often_helper::output('41011','','','非法参数!');
        }
        $status=$this->input->post('status',true);
        if(!isset($status) || !is_string($status) || empty($status)){
            often_helper::output('41011','','','非法参数!');
        }
        $this->load->model('c/User_model');
        $this->User_model->id=$id;
        $this->User_model->status=$status;
        $res=$this->User_model->updateUserKind();
        if(!$res){
            often_helper::output('41011');
        }
        often_helper::output('41010',$res);
    }
    /**
     * @abstract 后台用户管理系统-系统用户-用户列表-修改用户-读取该用户的信息
     */
    function getUserManageOne(){
        $id=$this->input->post('id',true);
        if(!isset($id) || empty($id) || !is_numeric($id)){
            often_helper::output('41011','','','非法参数!');
        }
        $this->load->model('c/Static_model');
        $this->Static_model->id=$id;
        $res=$this->Static_model->getUserManageOne();
        if(!$res){
            often_helper::output('41011');
        }
        //通过地址id 获取地址名字及详情地址
        $this->load->model('c/Address_model');
        $this->Address_model->addId=$res['addid'];
        $addres=$this->Address_model->getAddressOne();
        $res['addids']=$addres['addName'];
        //获取权限类别
        $quanxian=$this->Static_model->getRoleList();
        //获取门店地址详情
        $this->load->model('c/Address_model');
        $add=$this->Address_model->getAddList();
        $data=array(
            'res'=>$res,
            'qx'=>$quanxian,
            'adre'=>$add
        );
        often_helper::output('41010',$data);
    }
    /**
     * @abstract 后台用户管理系统-系统用户-用户列表-修改用户-效验数据
     */
    function checkEditUser(){
        $data=$this->input->post();
        $newDa=array(
            'id'=>'',
            'number'=>'',
            'mobile'=>'',
            'roleid'=>'',
            'addrid'=>''
        );
        if(!isset($data['id']) || empty($data['id']) || !is_numeric($data['id']) || $data['id']<0 ){
            often_helper::output('41011','','','用户编号不正确!');
        }
        $newDa['id']=$data['id'];
        if(!isset($data['number']) || empty($data['number'])){
            often_helper::output('41011','','','编号不正确!');
        }
        $newDa['number']=$data['number'];
        if(!preg_match("/^1[34578]{1}\\d{9}$/",$data['mobile'])){
         often_helper::output('41011','','','手机号码不正确!');
        }  
        $newDa['mobile']=$data['mobile'];
        if(!isset($data['juese']) || empty($data['juese']) || !is_numeric($data['juese']) || $data['juese']<0 ){
            often_helper::output('41011','','','选取角色不正确');
        }
        $newDa['roleid']=$data['juese'];
        if(!isset($data['addid']) || empty($data['addid']) || !is_numeric($data['addid']) || $data['addid']<0 ){
            often_helper::output('41011','','','选取地址不正确!');
        }
        $newDa['addrid']=$data['addid'];
        return $newDa;
    }
    /**
     * @abstract 后台用户管理系统-系统用户-用户列表-修改用户-效验数据并保存
     */
    function editUserOne(){
        //效验参数
        $new=$this->checkEditUser();
        $this->load->model('c/Static_model');
        $this->Static_model->new=$new;
        $res=$this->Static_model->editUserOne();
        if(!$res){
            often_helper::output('41011');
        }
        often_helper::output('41010',$res);
    }
    /**
     * @abstract 获取提货员的信息
     */
    function  getTihuo(){
        $this->load->model('c/Static_model');
        $res=$this->Static_model->getTiHuo();
        if(!$res){
            often_helper::output('41011');
        }
        often_helper::output('41010',$res);
    }
}