<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Distill_model extends CI_Model{
   //提货记录表
   private   $rr_distill = 'rr_distill';
   //订单表
   private   $rr_order  = 'rr_order_nonstandard';
   //管理员表
   private   $rr_manage = 'rr_manage';
    
   function __construct(){
       parent::__construct();
       $this->load->database();
   }
    /**
     * 提货记录列表
     */
    function selectList(){
        $where ='';
        if($this->name!=''){
            $where =' where b.manage_name like "%'.$this->name.'%" or 
                c. manage_name like "%'.$this->name.'%"';
        }
        //分页数据
        $count_sql='select * from '.$this->rr_distill.' a ' .
            'left join '.$this->rr_manage.' b on a.distill_req=b.manage_id ' .
            'left join '.$this->rr_manage.' c on a.distill_resp=c.manage_id'.$where;
        $count_query=$this->db->query($count_sql);
        $data['CountPage']=$count_query->num_rows();//总条数
        $data['Page']=ceil($count_query->num_rows()/10);//总页数
        if(empty($this->limit)){
            $where = $where . ' limit 0,10';
        }else{
            $start=($this->limit-1)*10;
            $end=10;
            $where = $where . ' limit '.$start.','.$end;
        }
        $sql='select a.distill_id as id,a.distill_conten as conten,
            b.manage_name as shopName,c.manage_name as distName,
            a.distill_deed as deed from '.$this->rr_distill.' a ' .
            'left join '.$this->rr_manage.' b on a.distill_req=b.manage_id ' .
            'left join '.$this->rr_manage.' c on a.distill_resp=c.manage_id '.$where;
        $query=$this->db->query($sql);
        if($query == false){
            return false;
        }
        $data['result']=$query->result_array();
         return $data;
    }
    /**
     * 查询单个提货订单记录 通过提货id
     */
    function getOneDistill(){
        $sql='select * from '.$this->rr_distill.' where distill_id='.$this->id;
        $query=$this->db->query($sql);
        if($query == false){
            return false;
        }
        $data['result']=$query->row_array();
        return $data;
    }
    /**
     * 更改订单状态为已入库状态
     */
    function updateOrderSta(){
        //修改提货记录状态
        $sql='update '.$this->rr_distill.' set distill_deed=5 where distill_id='.$this->id;
        //修改该批次订单的状态
        $order_sql='update '.$this->rr_order.'
            set order_orderstatus=13 where order_id in ('.$this->orderid.')';
        
        $this->db->trans_begin();
        $query=$this->db->query($sql);
        $rows = $this->db->affected_rows();
        
        $order_query=$this->db->query($order_sql);
        $order_rows = $this->db->affected_rows();
        
        if($this->db->trans_status() === FALSE ||$rows!=1 || $order_rows!=1){
            $this->db->trans_rollback();
            return false;
        }else{
            $this->db->trans_commit();
            return true;
        }
    }
}