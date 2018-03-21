<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Address_model extends CI_Model{
   //管理员表
   private   $rr_manage = ' rr_manage';
   //店铺表
   private   $rr_address = 'rr_shop_address';
    
   function __construct(){
       parent::__construct();
       $this->load->database();
   }
    /**
     * @abstract 通过地址编号 获取相应地址信息
     */
    function getAddressOne(){
       $sql='select a.address_id as addid,a.address_name as addName,
            a.address_address as addloca,a.address_number as number,
            a.manage_id as manid,b.manage_name as name from '.
            $this->rr_address.' as a left join '.$this->rr_manage.
            ' as b on a.manage_id=b.manage_id where a.address_status=1
            and a.address_id="'.$this->addId.'"';
        $query=$this->db->query($sql);
        if($query->num_rows() > 0){
            $result=$query->row_array();
            return $result;
        }else{
            return false;
        }
    }
    
    /**
     * @abstract 添加店铺信息
     */
    function insertAddress(){
        $data=array(
            'address_name'=>$this->name,
            'address_address'=>$this->address,
            'manage_id'=>$this->manid,
            'address_number'=>$this->number,
            'address_status'=>1,
            'address_jointime'=>time()
        );
        $this->db->insert($this->rr_address,$data);
        $row=$this->db->affected_rows();
        if($row==1){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 修改地址
     */
    function updateAddress(){
        $data=array(
            'address_name'=>$this->name,
            'manage_id'=>$this->manid,
            'address_address'=>$this->address,
            'address_number'=>$this->number,
            'address_uptime'=>time()
        );
        $id=array('address_id'=>$this->addid);
        $this->db->update($this->rr_address,$data,$id);
        $row=$this->db->affected_rows();
        if($row==1){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 查看地址
     */
    function selectList(){
        $where ='';
        if($this->addCon!=''){
            $where =' and address_name like "%'.$this->addCon.'%" or 
                address_address like "%'.$this->addCon.'%"';
        }
        //分页数据
        $count_sql='select * from '.$this->rr_address.' as a 
            left join '.$this->rr_manage.' as b on 
             a.manage_id=b.manage_id where b.manage_role='
            .$this->config->item('role_Distill').$where;
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
        $sql='select *  from '.$this->rr_address.'  as a 
            left join '.$this->rr_manage.' as b on 
             a.manage_id=b.manage_id where b.manage_role='
            .$this->config->item('role_Distill').$where;
        $query=$this->db->query($sql);
        if($query == false){
            return false;
        }
        $data['result']=$query->result_array();
         return $data;
    }
    /**
     * 查看所有的地址
     */
    function getAddList(){
        $sql='select address_id as addid,address_name as addname,
            address_address as address,address_number as number  from '.$this->rr_address.
            ' where address_status=1';
        $query=$this->db->query($sql);
        if($query == false){
            return false;
        }
        $data['result']=$query->result_array();
        return $data;
    }
}