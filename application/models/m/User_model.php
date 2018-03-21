<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class User_model extends CI_Model {
    
    function __construct(){
        parent::__construct();
        $this->load->database();
    }
    //根据openid查询用户
    function  selUser(){
        $sql='select * from  rr_user where user_status=1 and user_openid="'.$this->openid.'"';
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($query === false || $row < 1){
            return false;
        }
        $data=$query->row_array();
        return  $data;
    }
    //更新用户最好登录时间
    function upUserLastTime(){
        $this->db->where('user_id', $this->userid);
        $this->db->update('rr_user',array('user_lasttime'=>time()));
        $row=$this->db->affected_rows();
        if($row == 1){
            return true;
        }
        return  false;
    }
    //添加用户
    function  addUser(){
        $this->db->insert('rr_user',array(
                'user_openid'=>$this->openid,
                'user_role'=>1,
                'user_jointime'=>time(),
                'user_status'=>1
        ));
        $row=$this->db->affected_rows();
        if($row == 1){
            return true;
        }else {
            return false;
        }
    }
    //根据网点编号查询用户信息
    function  selAgent(){
        $sql='select * from  rr_user where user_agent="'.$this->agent.'" and user_role=9';
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($query === false || $row < 1){
            return false;
        }
        $data=$query->row_array();
        return  $data;
    }
    //查询未更新资料的用户
    function userInfo(){
        $sql='select  user_wxname,user_openid,user_subscribe  from  rr_user where user_wxname="" and user_subscribe = 1';
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($query === false || $row < 1){
            return false;
        }
        $data=$query->row_array();
        return  $data;
    }
    //更新用户微信资料 昵称 关注状态
    function upWxInfo(){
        $this->db->where('user_openid', $this->openid);
        $this->db->update('rr_user',array('user_wxname'=>$this->nickname,'user_subscribe'=>$this->subscribe));
        $row=$this->db->affected_rows();
        if($row == 1){
            return true;
        }
        return  false;
    }
    //获取商户昵称及编号
    function getShopMess(){
        $sql='select * from  rr_user where user_id="'.$this->userid.'" and user_role=9 and user_status=1';
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($query === false || $row < 1){
            return false;
        }
        $data=$query->row_array();
        return  $data;
    }
    //更改用户的手机号码
    function upWxMobile(){
        $this->db->where('user_openid', $this->openid);
        $this->db->update('rr_user',array('user_mobile'=>$this->mobile,'user_subscribe'=>1));
        $row=$this->db->affected_rows();
        if($row == 1){
            return true;
        }
        return  false;
    }
    //根据当前的openid 获取工作人员的信息
    function selectManageRole(){
        $sql='select * from rr_manage a where manage_openid="'.$this->openid.'" and manage_status = 1';
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($query === false || $row < 1){
            return false;
        }
        $data=$query->row_array();
        return  $data;
    }
    //根据id 获取后台工作人员的信息
    function selectManageMsg(){
    	$sql='select * from rr_manage a where manage_id="'.$this->id.'"';
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($query === false || $row < 1){
            return false;
        }
        $data=$query->row_array();
        return  $data;
    }
    /**
     * 根据ID查询当前用户管辖的其他的用户ID
     */
    function getSourceUser(){
        $sql='select GROUP_CONCAT(manage_id) from rr_manage a where manage_id="'.$this->id.'"';
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($query === false || $row < 1){
            return false;
        }
        $data=$query->row_array();
        return  $data;
    }
}