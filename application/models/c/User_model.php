<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class User_model extends CI_Model {
    //用户表
    private $table_user='rr_user';
    
    function __construct(){
        parent::__construct();
        $this->load->database();
    }
    //根据手机号码查询用户
    function getUserList(){
        $where = '';
        $where .=' where user_subscribe=1 ';
        if(!empty($this->mobile)){
            $where .='  and user_mobile like "%'.$this->mobile.'%"';
        }
        $count_sql='select * from '.$this->table_user.$where;
        $count_query=$this->db->query($count_sql);
        $data['CountPage']=$count_query->num_rows();//总条数
        $data['Page']=ceil($count_query->num_rows()/10);//总页数
        //分页
        if(empty($this->limit)){
            $where = $where . ' limit 0,10';
        }else{
            $start=($this->limit-1)*10;
            $end=10;
            $where = $where . ' limit '.$start.','.$end;
        }
        $sql='select user_id as uid,user_wxname as wxname,user_mobile as mobile, 
            user_jointime as jointime,user_status as status from '.$this->table_user.$where;
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($query === false || $row < 1){
            return false;
        }
       $data['result']=$query->result_array();
        return  $data;
    }
    //禁用该用户登录系统
    function updateUserKind(){
        $yuanstatus='';//储存之前的数据状态
        switch ($this->status){
            case 'close'://禁用
                $yuanstatus=1;
                $this->status=-1;
                break;
            case 'open'://解封
                $yuanstatus=-1;
                $this->status=1;
                break;
            default:;break;
        }
        $sql='update '.$this->table_user.' set user_status='.$this->status.'
            where user_id='.$this->id.' and user_status='.$yuanstatus;
        $query=$this->db->query($sql);
        $rows = $this->db->affected_rows();
        if($rows==1){
            return true;
        }else{
            return false;
        }
    }
}