<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 
 * @author mxt
 * 会员模块
 */
class  user extends  RR_Controller{
    /**
     * @abstract 生成推广二维码
     * @param 
     */
    function  createQRcode(){
        //校验是否登录
        $this->isOnline();
        //校验是否有权限可以访问
        $this->checkRole($this->user_role, $this->config->item('role_Shopowner'));
        //生成二维码
        $this->load->model('m/Wx_model');
        $token=$this->Wx_model->getToken();
        $url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$token;
        //expire_seconds 二维码有效期 action_name 二维码类型  action_info 二维码详细信息  scene_id
        $content ='{"expire_seconds": 60, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$this->user_id.',"url":"http://www.rrahs.com/c/manage/register"}}}';
        $result = often_helper::remote($url, $content);
        $info=json_decode($result);
        $address='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($info->ticket);
        header('Location:'.$address);
    }
    /**
     * @abstract  绑定手机号码
     * @return  json 返回结果
     */
    function upUserMobile(){
        $userCon=$this->regVer();
        if($userCon === false){
            $resp=array('21011','','','信息注册失败!');
            $temp=$this->checkDataOut(1,$resp);
        }
        $this->load->model('m/User_model');
        $this->User_model->openid=$_SESSION['user']['user_openid'];
        $this->User_model->mobile=$userCon['inf_phonew'];
        $userInfo = $this->User_model->upWxMobile();
        if($userInfo){
            often_helper::output('21010','',0,0);
        }else{
            often_helper::output('21011','','','信息注册失败!');
        }
    }
    /**
     * @abstract  绑定手机号码  验证提交参数
     * @param  $inf_txyzm   string  随机验证码
     * @param  $inf_yzm     int  手机验证码
     * @param  $inf_phonew  int  手机号码
     * @return  json 返回结果
     */
    function regVer(){
        $data=$this->input->post();
        //校验随机验证码
        if($data['inf_txyzm']!=$_SESSION['hst_code']){
            often_helper::output('21011','0','0','随机验证码不正确!');
        }
        $order['inf_txyzm'] = $data['inf_txyzm'];
        //校验手机验证码
        if (empty($data['inf_yzm']) || !is_numeric($data['inf_yzm']) || $data['inf_yzm'] < 0){
            often_helper::output('21011','','','手机验证码不正确!');
        }
        $order['inf_yzm'] = $data['inf_yzm'];
        //校验手机号码
        if(!preg_match("/^1[34578]{1}\d{9}$/",$data['inf_phonew'])){
            often_helper::output('21011','','','手机号码不正确!');
        }
        $order['inf_phonew'] = $data['inf_phonew'];
        //校验手机验证码是或否正确
        $this->mobile=$data['inf_phonew'];
        $this->invalid=1;
        $this->code=$data['inf_yzm'];
        $verify=$this->checkVerifyCode();
        if(!$verify){
            often_helper::output('21011','','','手机验证码不正确!');
        }else{
            return $order;
        }
    }
    /**
     * @abstract 绑定手机号码  根据手机号码校验验证码是或否正确
     *@param  $mobile   string    手机号码
     *@param  $code     string    手机验证码
     *@return bool 成功 true | 失败 false
     */
    function checkVerifyCode(){
        //验证手机验证码是否正确
        $this->load->model('m/Digital_model');
        $this->Digital_model->time=5;
        $this->Digital_model->mobile=$this->mobile;
        $this->Digital_model->code=$this->code;
        $validate=$this->Digital_model->validateCode();
        if(!$validate){
            return false;
        }
        if(!empty($this->invalid) && $this->invalid == 1){
            $_SESSION['user']['user_mobile']=$this->mobile;
            $this->Digital_model->code_id;
            $resp=$this->Digital_model->upVerifyCode();
        }
        return true;
    }
}