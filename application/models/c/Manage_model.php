<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_model extends CI_Model{
   //管理员表
   private   $rr_manage = 'rr_manage';
    
   function __construct(){
       parent::__construct();
       $this->load->database();
   }
    /**
     * @abstract  校验当前登录用户是否存可以登录
     */
    function checkUser(){
        $sql='select  manage_id,manage_name,manage_openid ,manage_mobile,
              manage_password,manage_number,manage_email,manage_role from  
              '.$this->rr_manage.' where  (manage_number="'.$this->user.'" or manage_mobile="'.
              $this->user.'" or  manage_email ="'.$this->user.'") and  
              manage_status = 1 ';
        $query=$this->db->query($sql);
        //验证登录用户是否存在 并且账号是否异常
        if($query->num_rows() != 1){
            return false;
        }
        //校验密码是否正确
        $data=$query->row_array();
        if($data['manage_password'] != $this->pwd){
            return false;
        }
        //判断当前登录用户用户组是否可以登录系统
        if($data['manage_role'] < $this->config->item('role_Manage')){
            return false;
        }
        return $data;
    }
    /**
     * @abstract 用户注册
     */
    function registerUser(){
        $data=array(
                'manage_name'=>$this->manage_name,
                'manage_openid'=>$this->manage_openid,
                'manage_mobile'=>$this->manage_mobile,
                'manage_jointime'=>time(),
                'manage_status'=>0
        );
        $query=$this->db->insert($this->rr_manage,$data);
        $row=$this->db->affected_rows();
        if($query && $row == 1){
            return  true;
        }else{
            return  false;
        }
    }
    /**
     * @abstract 根据手机号码  校验当前用户是否已经注册
     */
    function  registerCheckMobile(){
        $sql='select manage_id from '.$this->rr_manage.' 
              where manage_mobile='.$this->manage_mobile;
        $query=$this->db->query($sql);
        if($query->num_rows() < 1){
            return true;
        }else{
            return false;
        }
    }
    /**
     * @abstract  扫码绑定微信账号 并且审核
     * @param $manage_openid  string  微信openid
     * @param $manage_role    int     用户组
     * @param $manage_fid     int     所属店员
     * @return  bool 结果成功 true 失败 false
     */
    function reviewedUser(){       
        //校验是否存在当前用户
        $result=$this->selOpenidUser();
        if($result === false){
            return  false;
        }
        $option=array(
                'manage_id'=>$result['manage_id'],
                'manage_openid'=>$this->manage_openid,
        );
        $data=array(
                'manage_fid'=>$this->manage_fid,
                'manage_role'=>$this->manage_role,
                'manage_number'=>'SDD'.substr(1000000+$result['manage_id'],1,6),
                'manage_uptime'=>time(),
                'manage_status'=>1
        );
        $query=$this->db->update($this->rr_manage,$data,$option,$option);
        $row=$this->db->affected_rows();
        if($query && $row == 1){
            return  true;
        }else{
            return  false;
        }
    }
   /**
    * @abstract 根据openid 读取用户
    */
    function selOpenidUser(){
        $sql='select manage_id,manage_openid from '.$this->rr_manage.'
              where manage_openid="'.$this->manage_openid.'"';
        $query=$this->db->query($sql);
        if($query->num_rows() > 0){
            $result=$query->row_array();
            return $result;
        }else{
            return false;
        }
    }
    /**
     * @abstract 通过回收商账户编号 获取相应工作人员的信息
     */
    function getManagerOne(){
        $sql='select manage_id as mid,manage_name as name,address_id as addId
             from '.$this->rr_manage.' where manage_number="'.$this->number.'"';
        $query=$this->db->query($sql);
        if($query->num_rows() > 0){
            $result=$query->row_array();
            return $result;
        }else{
            return false;
        }
    }
}