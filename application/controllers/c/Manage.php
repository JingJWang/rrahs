<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class manage extends RR_Controller{
    /**
     * 系统管理登录入口
     */
    function login(){
        $this->display('c/login.html');
    }
    /**
     * @abstract 校验登录提交的信息格式
     */
    function loginData(){
        $check=$this->input->post('check');
        if(empty($check) || $check != $_SESSION['hst_code']){
            often_helper::output('41011','','','随机验证码输入错误!');
        }
        $user=$this->input->post('user');
        if(empty($user)){
            often_helper::output('41011','','','登录账号输入错误!');
        }
        $this->user=$user;
        $pwd=$this->input->post('pwd');
        if(empty($pwd)){
            often_helper::output('41011','','','登录账号输入错误!');
        }
        $this->pwd=$pwd;
        
    }
    /**
     * @abstract 校验登录
     */
    function checkLogin(){
        //校验数据
        $this->loginData();  
        //校验账号,密码     
        $this->load->model('c/Manage_model');
        $this->Manage_model->user=$this->user;
        $this->Manage_model->pwd=hash('sha256',$this->pwd.'sdd2017');
        $resp=$this->Manage_model->checkUser();
        if(!$resp){
            often_helper::output('41011','','','账号或密码输入错误!');
        }
        //session保存当前登录的用户信息
        $_SESSION['manage']=array(
               'manage_id'=>$resp['manage_id'],
               'manage_name'=>$resp['manage_name'],
               'manage_openid'=>$resp['manage_openid'],
               'manage_mobile'=>$resp['manage_mobile'],
               'manage_number'=>$resp['manage_number'],
               'manage_email'=>$resp['manage_email'],
               'manage_role'=>$resp['manage_role']
        );
        $url='http://'.$_SERVER['HTTP_HOST'].'/c/statis/show';
        often_helper::output('41010','',$url,'');
    }
    function loginM(){
    	$this->display('c/login_m.html');
    }
    /**
     * 系统用户注册
     */
    function  register(){
        $code=$this->input->get('code');
        $this->assign('wxcode', $code);
        $this->display('c/register.html');
    }
    /**
     *@abstract  添加用户
     *@param  $name   string  姓名
     *@param  $phone  int     手机号码
     *@param  $code   string  图形验证码
     *@return  返回结果提示  json 
     */
    function  registerUser(){
        $name=$this->input->post('name',true);
        if(empty($name)){
            often_helper::output('41011','','','姓名为空或者格式不正确!');
        }
        $phone=$this->input->post('phone',true);
        if(empty($phone) || !is_numeric($phone)){
            often_helper::output('41011','','','电话为空或者格式不正确!');
        }
        $code=$this->input->post('code',true);
        if(empty($code) || $code != $_SESSION['hst_code']){
            often_helper::output('41011','','','验证码为空或者格式不正确!');
        }
        $wxcode=$this->input->post('wxcode',true);
        if(empty($wxcode) || $wxcode == 'null'){
            often_helper::output('10011','','','获取微信附加信息出现异常!');
        }
        $this->load->model('m/wxcode_model');
        $openid=$this->wxcode_model->getOpenid($wxcode);
        //校验当前手机号码是否已经注册过
        $this->registerCheckMobile($phone);
        //保存当前注册用户
        $this->load->model('c/Manage_model');
        $this->Manage_model->manage_name=$name;
        $this->Manage_model->manage_openid=$openid;
        $this->Manage_model->manage_mobile=$phone;
        $resulr=$this->Manage_model->registerUser();
        if($resulr){
            $_SESSION['hst_code']='';
            often_helper::output('41010','','','账号已经注册,请等待审核!');
        }else{
            $_SESSION['hst_code']='';
            often_helper::output('41011','','','账号注册出现异常!');
        }
    }
    /**
     * @abstract 根据手机号码校验当前用户是否已经注册
     * @param  $phone  int  手机号码
     * @return bool 不存在  true | 存在 false 
     */
    function registerCheckMobile($phone){
        $this->load->model('c/Manage_model');
        $this->Manage_model->manage_mobile=$phone;
        $resulr=$this->Manage_model->registerCheckMobile();
        if(!$resulr){
            often_helper::output('41011','','','当前手机号码已经注册!');
        }
    }
}