<?php
defined('BASEPATH') OR exit('No direct script access allowed');
session_start();
class RR_Controller extends CI_Controller {
    
    protected   $user_id       =   '';//用户id
    
    protected   $user_role     =   '';//用户当前的权限组
    
    protected   $user_openid   =   '';//用户的openid
    
    protected   $user_number   =   '';//管理员编号
    
    protected   $user_name     =   '';//用户名称
    
    protected   $user_mobile   =   '';//手机号码
    
    public function __construct() {
        parent::__construct();
    }
    /**
     * 默认微信登录
     * @param  $code  string  获取微信用户资料需要的 校验码
     */    
    //微信默认授权获取code
    function wxLogin(){
        //当已经登录不需要重复登录
        if(isset($_SESSION['user']) && !empty($_SESSION['user'])){
            return true;
        }
        $pattern=$this->input->get('pattern',true);
        if(!empty($pattern) &&  $pattern='develop'){
            $openid=$this->input->get('openid');
        }else{
            $code=$this->input->get('code',true);
            if(empty($code) || $code == 'null'){
                often_helper::output('10011','','','获取微信code出现异常!');
            }
            $this->load->model('m/wxcode_model');
            $openid=$this->wxcode_model->getOpenid($code);
        }
        if($openid == ''){
            often_helper::output('10011','','','未获取到用户openid!');
        }
        $this->user_openid=$openid;
        //校验是否是管理用户
        $res=$this->checkMange();
        if($res==false){
            //校验是否是普通用户
            $result=$this->checkUser();
            if($result){
                return true;
            }
        }else{
            return true;
        }
    }
    /**
     * @abstract 效验管理员表中是否存在用户 不存在检查用户表
     */
    function checkMange(){
        $this->load->model('m/user_model');
        $this->user_model->openid=$this->user_openid;
        $resp=$this->user_model->selectManageRole();
        if($resp){
            $_SESSION['user']=array(
                    'user_id'     => $resp['manage_id'],
                    'user_openid' => $resp['manage_openid'],
                    'user_fid'    => $resp['manage_fid'],
                    'user_name'   => $resp['manage_name'],
                    'user_role'   => $resp['manage_role'],
                    'user_number' => $resp['manage_number'],
                    'user_identity'=>'manage',
            );
            return true;
        }else{
            return false;
        }
    }
    /**
     * @abstract 校验是否是会员用户 如果不是则添加该用户
     * @return boolean
     */
    function checkUser(){
        $this->load->model('m/user_model');
        $this->user_model->openid=$this->user_openid;
        $resp=$this->user_model->selUser();
        if($resp === false){
            $res=$this->user_model->addUser();
            $_SESSION['user']=array(
                    'user_id'=>$this->user_model->last_insert_id(),
                    'user_openid'=>$this->openid,
                    'user_role'=>$this->config->item('role_customer'),
                    'user_identity'=>'ordinary',
            );
                    
            return true;
        }else{
            $this->user_model->userid=$resp['user_id'];
            $res=$this->user_model->upUserLastTime();
            $_SESSION['user']=array(
                    'user_id'=>$resp['user_id'],
                    'user_openid'=>$resp['user_openid'],
                    'user_mobile'=>$resp['user_mobile'],
                    'user_role'=>$this->config->item('role_customer'),
                    'user_identity'=>'ordinary',
            );
            return true;
        }
    }
    /**
     * @abstract 用户端校验是否登录
     * 
     */
    function isOnline(){
        //判断session是否存在
        if(empty($_SESSION)){
            exit('您还没有登录!');
        }
        $user=$_SESSION;
        if(isset($user['user']['user_id'])){
            $this->user_id=$user['user']['user_id'];
        }else{
            return  false;
        }
        if(isset($user['user']['user_role'])){
            $this->user_role=$user['user']['user_role'];
        }else{
            return  false;
        }
        if(isset($user['user']['user_openid'])){
            $this->user_openid=$user['user']['user_openid'];
        }else{
            return  false;
        }
        if(isset($user['user']['user_number'])){
            $this->user_number=$user['user']['user_number'];
        }else{
            return  false;
        }
        if(isset($user['user']['user_name'])){
            $this->user_name=$user['user']['user_name'];
        }else{
            return  false;
        }
        if(isset($user['user']['user_mobile'])){
            $this->user_mobile=$user['user']['user_mobile'];
        }else{
            return  false;
        }
    }
    /**
     * @abstract 校验当前用户是否有权限
     * @param int  $role  当前用户的权限
     * @param int  $power 需要满足的权限
     */
    function checkRole($role,$power){
        if($role != $power){
            exit('权限不足!');
        }
    }
    /**
     * @abstract  smarty 模板传递
     * @param string  $key   
     * @param string  $val
     */
    public function assign($key,$val){
        $this->rr_smarty->assign($key,$val);
    }
    /**
     * @abstract smarty  调用模板
     * @param string $html
     */
    public function display($html){
        $this->rr_smarty->display($html);
    }
    
}