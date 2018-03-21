<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Order_model extends CI_Model {
    //订单表
    private $table_order='rr_order_nonstandard';
    
    
    function __construct(){
        parent::__construct();
        $this->load->database();
    }
    //根据相关搜索条件查找订单信息
    function getOrderList(){
        $where=' where order_status=1';
        //效验手机号
        if(!empty($this->mobile)){
            $where .='  and order_mobile like "%'.$this->mobile.'%"';
        }
        //效验未成交的起止日期
        if(!empty($this->wstar) && !empty($this->wend)){
            $this->wend=(int)$this->wend+86399;
            $where.=' and order_jointime > '.$this->wstar.' and order_jointime < '.$this->wend;
        }else{
            $where.='';
        }
        //效验已成交的起止日期
        if(!empty($this->ystar) && !empty($this->yend)){
            $this->yend=(int)$this->yend+86399;
            $where.=' and ordre_dealtime > '.$this->ystar.' and ordre_dealtime < '.$this->yend;
        }else{
            $where.='';
        }
        //效验 day week month
        $year = date("Y");
        $month = date("m");
        $day = date("d");
        $daystart = mktime(0,0,0,$month,$day,$year);//当天开始时间戳
        $dayend= mktime(23,59,59,$month,$day,$year);//当天结束时间戳
        $week=strtotime(date('Y-m-d',strtotime('-7 days')));//7天前的日期
        $month=strtotime(date("Y-m-d H:i:s", strtotime("-1 month")));//一个月之前的日期
        if(!empty($this->option)){
            switch($this->option){
                case 'day':
                    $where.=' and order_jointime > '.$daystart.' and order_jointime < '.$dayend;
               case 'week':
                        $where.=' and order_jointime > '.$week;
               case 'month':
                    $where.=' and order_jointime > '.$month;
            } 
        }
        //效验订单状态
        if(!empty($this->status)){
            $where.=' and order_orderstatus > '.$this->status;
        }
        
        $count_sql='select * from '.$this->table_order.$where;
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
        $sql='select order_id as oid,order_name as goodsname,order_number as number, 
            order_mobile as mobile,order_bid_price as price,order_orderstatus as orderstu,
            order_confirm as confirm,ordre_dealtime as dealtime,cooperator_number as cnumber,
            order_jointime as jointime,order_status as status,order_voucherstatus as voustatus
            from '.$this->table_order.$where;
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($query === false || $row < 1){
            return false;
        }
        $data['result']=$query->result_array();
        return  $data;
    }
    /**
     * @abstract 提货 验证提货订单是提货员管理的店铺
     */
    function checkOrderID(){
        $sql='select
                	a.address_id
                from
                	rr_shop_address AS a
                right join rr_manage AS b ON a.address_id = b.address_id
                right join rr_order_nonstandard AS c ON b.manage_number = c.cooperator_number
                where
                	a.manage_id = '.$this->manage_id.'
                and c.order_orderstatus = 12
                and c.order_id in('.$this->distill_conten.') GROUP BY b.address_id';
         
        $query=$this->db->query($sql);
        if($query === false || $query->num_rows() != 1){
            return false;
        }
        $result=$query->row_array();
        return $result['address_id'];
    }
    /**
     * 查看订单状态
     */
    function getStatusOrder(){
        $sql='select * from '.$this->table_order.' where order_id='.$this->orderid;
        $query=$this->db->query($sql);
        $result=$query->row_array();
        return  $result;
    }
    /**
     * 添加 订单打款 信息
     */
    function updateOrderMess(){
        $sql='update '.$this->table_order.' set order_voucherstatus=1, order_voucher=\''.$this->voucher.
            '\' where order_id='.$this->orderid.' and 
             order_orderstatus>=10 and order_voucherstatus!=1';
        $query=$this->db->query($sql);
        $rows = $this->db->affected_rows();
        if($rows==1){
            return true;
        }else{
            return false;
        }
    }
}