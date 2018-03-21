<?php
use phpDocumentor\Reflection\Types\This;
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 
 * @author mxt
 * @abstract  订单模块
 */
class Order_model extends  CI_Model{

    function __construct(){
        parent::__construct();
        $this->load->database();
    }
    /**
     * @abstract  根据用户的ID读取用户订单列表
     * @param  int  $uid  用户ID
     */
    function getList(){
        $sql='select 
              a.order_id as gid,
              a.order_name as gname,
              a.order_bid_price as price,
              a.ordre_dealtime as dealtime,
              a.order_confirm as constu,
              a.order_orderstatus as orderstu,
              a.order_jointime as jointime,
              a.order_uptime as uptime,
              a.order_status as stuts 
              from rr_order_nonstandard as a where
              a.wx_id='.$this->uid.' and order_status = 1';
        $query=$this->db->query($sql);
        $row=$query->num_rows();
        if($query === false || $row < 1){
            return false;
        }
        $data=$query->result_array();
        return $data;
    }
    /**
     * @abstract  读取店员的订单列表
     * @param  int     status  状态
     * @param  string  number  店员编号
     * @return  array 成功读取返回结果集 否则返回bool  false
     */
    function selectList(){
      switch ($this->role) {
          case $this->config->item('role_Clerk') :
              $where='  b.manage_id='.$this->user_id;
              break;
          case $this->config->item('role_Shopowner') :
              $where='  b.manage_fid='.$this->user_id;
              break;
          default:
              return  false;
              break;
      }
      $sql='select 
              a.order_id as gid,
              a.order_name as orderName,
              a.order_bid_price as orderPric,
              a.order_orderstatus as dealStatus, 
              a.order_jointime as joinTime,
              month(FROM_UNIXTIME(a.order_jointime)) as timd,
              a.order_orderstatus as orderstatus,
              a.ordre_dealtime as dealtime, 
              b.manage_name as manage_name
            from 
              `rr_order_nonstandard` a 
            left join rr_manage b on b.manage_number=a.cooperator_number
            where '.$where.'
            and a.order_orderstatus in ('.$this->status.') 
            and order_status=1  order by joinTime desc';
        $query=$this->db->query($sql);
        $row=$query->num_rows();
        if($query == false){
            return  false;  
        }
        if($row < 1){
            $result=array();
            return $result;
        }
        $data=$query->result_array();
        if($this->status == '10,11'){
            foreach ($data as $key=>$val){
                $result[$val['timd']][]=$data[$key];
            }
            $data=$result;
        }
        return $data;
    }    
    /**
     * @abstract  订单详情 根据订单查询订单详情
     * @param  $id  int  订单id
     * @getOrderDetailparam  array
     */
    function getOrderDetail(){
      switch ($this->user_role){
          case $this->config->item('role_customer'):
                $where = ' and a.wx_id = "'.$this->user_id.'"';
                break;
          case $this->config->item('role_Clerk'):
                $where = ' and a.cooperator_number = "'.$this->user_number.'"';
                break;
          case $this->config->item('role_Shopowner'):
                $where = ' and c.manage_fid = "'.$this->user_id.'"';
                break;
      }
      $sql='select
         a.wx_id AS wid,
         a.order_id AS oid,
         a.types_id AS gid,
         a.order_name AS gname,
         a.order_number as number,     
         a.order_bid_price AS price,
         a.order_jointime AS jtime,
         a.order_confirm AS confirm,
         a.cooperator_number AS numaddr,
         b.content_text AS text,
         b.content_tid AS cid,
         b.content_account AS account,
         a.order_orderstatus AS orderstatus
         from
           rr_order_nonstandard a
         LEFT JOIN rr_order_content b ON a.order_id = b.order_id
         LEFT JOIN rr_manage c ON a.cooperator_number = c.manage_number
         where
            a.order_status = 1
         and a.order_id = '.$this->order_id.$where;
        $query=$this->db->query($sql);
        $result=$query->row_array();
        if($result){
            return $result;
        }else{
            return false;
        }
    }
    /**
     * @abstract 根据用户ID查询所管辖的其他员工ID
     * @param  $id  int  员工Id
     * @return  array |  bool
     */
    function  getUserIdList(){
        
    }
    /**
     * @abstract  提货员根据店铺信息 查询订单数量
     */
    function getDistill(){
       $sql='SELECT
                count(order_id) as total,
                a.address_name,
        	    a.address_id,
                a.address_address
             FROM
        	 rr_shop_address  as  a
             right join rr_manage as b on a.address_id=b.address_id
             RIGHT join rr_order_nonstandard as c on b.manage_number=c.cooperator_number
             where 
	         a.manage_id='.$this->manage_id.'  
             and c.order_orderstatus in(10,11)  
             group by  a.address_id  ';
        $query=$this->db->query($sql);
        $result=$query->result_array();
        if($result === false){
            return false;
        }
        if($query->num_rows() < 1 ){
            return 0;
        }
        return $result;
    }   
    /**
     * 商户修改订单后保存修改的信息
     */
    function saveNextAgain(){
        //订单表信息
        $set=array(
                'order_bid_price'=>$this->pri*100,
                'order_confirm'=>'-1',
                'order_orderstatus'=>2,
                'order_uptime'=>time()
        );
        //订单故障表信息
        $setcon=array(
                'content_tid'=>$this->vid,
                'content_text'=>$this->vdata,
                'content_uptime'=>time()
        );
        $this->db->trans_begin();
        $this->db->update('rr_order_nonstandard',$set,array('order_id'=>$this->oid));
        $con=$this->db->affected_rows();
        $this->db->update('rr_order_content',$setcon,array('order_id'=>$this->oid));
        $setcon=$this->db->affected_rows();
        if ($this->db->trans_status() === FALSE || $con !=1 || $setcon != 1){
            $this->db->trans_rollback();
            return  false;
        }else{
            $this->db->trans_commit();
            $this->id=$this->oid;
            $data=$this->getOrderDetail();
            return  $data;
        }
    }
    
    /**
     * 读取订单详细信息
     */
    function getOrderInfo(){
        switch ($this->user_role){
            case $this->config->item('role_customer'):
                $where = ' and wx_id = "'.$this->user_id.'"';
                break;
            case $this->config->item('role_Clerk'):
                $where = ' and cooperator_number = "'.$this->cooperator_number.'"';
                break;
        }
        $sql=' select
         wx_id AS wid,
         order_id AS oid,
         types_id AS gid,
         order_name AS name,
         order_number AS number,
         order_bid_price AS price,
         order_jointime AS jtime,
         order_confirm AS confirm,
         order_orderstatus as orderstatus,
         cooperator_number AS numadd 
         from 
         rr_order_nonstandard 
         where 
         order_id='.$this->order_id.$where .' and order_status =1';
        $query=$this->db->query($sql);
        $result=$query->row_array();
        if($result){
            return $result;
        }else{
            return false;
        }
    }
    /**
     *@abstract  确认订单
     *@param   $user_id  int  当前用户ID
     *@param   $order_id  int  订单id
     *@return  bool
     */
    function confirmOrder(){
        //订单表信息
        $set=array(
                'order_confirm'=>$this->order_confirm,
                'order_orderstatus'=>10,
                'ordre_dealtime'=>time()
        );
        $query=$this->db->update('rr_order_nonstandard',$set,
             array('order_id'=>$this->order_id,
                   'cooperator_number'=>$this->cooperator_number));
        $row=$this->db->affected_rows();
        if($query === false  || $row !=1 ){
            return  false;
        }else{
            return true;
        }
    }
    /**
     * @abstract 订单确认  用户确认二次报价
     */
    function hShadow(){
        //订单表信息
        $set=array(
                'order_confirm'=>1,
                'order_orderstatus'=>10,
                'ordre_dealtime'=>time()
        );
        $query=$this->db->update('rr_order_nonstandard',$set,array('order_id'=>$this->order_id,'wx_id'=>$this->user_id));
        $row=$this->db->affected_rows();
        if($query === false  || $row !=1 ){
            return  false;
        }else{
            return true;
        }
    }
    /**
     * @abstract 订单列表   用户在订单列表页面点击取消订单
     */
    function cancleOrder(){
        //订单表信息
        $set=array(
                'order_orderstatus'=>'-1',
                'order_status'=>'-1',
                'ordre_uptime'=>time()
        );
        $this->db->update('rr_order_nonstandard',$set,array('order_id'=>$this->order_id,'wx_id'=>$this->user_id));
        $con=$this->db->affected_rows();
        if ( $con !=1 ){
            return  false;
        }else{
            return true;
        }
    }
    /**
     * @abstract  员工业绩结算
     */
    function userSettlement(){
        switch ($this->user_role) {
            case $this->config->item('role_Clerk') :
                $where='  and b.manage_id='.$this->user_id;
                break;
            case $this->config->item('role_Shopowner') :
                $where='  and b.manage_fid='.$this->user_id;
                break;
            default:
                return  false;
                break;
        }
        $sql='select 
              a.order_name,
              a.order_bid_price,
              a.ordre_dealtime,
              a.order_number,
              b.manage_name
              from  rr_order_nonstandard as a left join rr_manage as b
              on a.cooperator_number=b.manage_number
              where a.order_orderstatus in(10,11,12) '.$where;
        $query=$this->db->query($sql);
        $result=$query->result_array();
        if($result == false){
            return false;
        }
        $query->num_rows() < 1 ? $result=array() :'';
        if(empty($result)){
            return $resp=array('total'=>0,'list'=>0);;
        }
        $pri=0;
        $list=array();
        foreach ($result as $key=>$val){
            $k=date('Y-m-d',$val['ordre_dealtime']);
            $result[$key]['order_number']=substr($val['order_number'], 14);
            $result[$key]['order_name']=substr($val['order_name'], 0,18);
            $result[$key]['order_bid_price']=$val['order_bid_price']/100;
            $list[$k][]=$result[$key];
            $pri = $pri + $val['order_bid_price'];
        }
        ksort($list);
        $resp=array('total'=>$pri/100,'list'=>$list);
        return $resp;
    }
    /**
     * @abstract  提货  根据店铺Id 查看店铺内存货列表
     * @param  $manage_id  int  提货员Id
     * @param  $address_id  int  店铺Id
     * @return array | bool
     */
    function  shopOrderList(){
        $sql='select address_id,address_address from rr_shop_address where address_id='.
                $this->address_id.' and manage_id='.$this->manage_id;
        $query=$this->db->query($sql);
        $result=$query->result_array();
        if($result == false){
            return false;
        }
        return $result;
    }
    /**
     * @abstract  提货  根据店铺Id 查看 提货列表
     */
    function  extractList(){
       $sql='SELECT
	        b.manage_name,
            c.order_id,
            c.order_name,
            c.ordre_dealtime,
            c.order_orderstatus
            FROM
            	rr_shop_address AS a
            right join rr_manage as b on a.address_id=b.address_id
            right join  rr_order_nonstandard as c on b.manage_number=c.cooperator_number
            WHERE
            	a.address_id='.$this->address_id.' 
            and a.manage_id = '.$this->manage_id.' 
            and c.order_orderstatus in (10,11) ';
       $query=$this->db->query($sql);
       $result=$query->result_array();
       if($result == false){
           return false;
       }
       if($query->num_rows() < 1){
           return 0;
       }
       //订单状态
       $state=$this->config->item('order_status_manage');
       foreach ($result as $key=>$val){
           $result[$key]['order_name']=mb_substr($val['order_name'], 0,12);
           $result[$key]['ordre_dealtime']=date('y/m/d',$val['ordre_dealtime']);
           $result[$key]['order_orderstatus']=$state[$val['order_orderstatus']];
       }
       return $result;
    }
    
    /**
     *@abstract 提货  提货发起提货请求
     */
    function insDistill(){
        $data=array(
                'address_id'=>$this->address_id,
                'distill_req'=>$this->manage_id,
                'distill_conten'=>$this->distill_conten,
                'distill_jointime'=>time(),
                'distill_deed'=>1
        );
        $sql='update rr_order_nonstandard SET order_orderstatus = 11,
              order_uptime='.time().' where order_id in('.$this->distill_conten.')';
        $this->db->trans_begin();
        //添加提货记录
        $result = $this->db->insert('rr_distill',$data);
        $addrow = $this->db->affected_rows();
        //修改订单状态
        $query=$this->db->query($sql);
        $uprows = $this->db->affected_rows();
        if ($this->db->trans_status() === FALSE || $uprows != $this->nums || $addrow != 1){
            $this->db->trans_rollback();
            return false;
        }else{
            $this->db->trans_commit();
            return true;
        }
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
                and c.order_orderstatus = 10 
                and c.order_id in('.$this->distill_conten.') GROUP BY b.address_id';  
       
       $query=$this->db->query($sql);
       if($query === false || $query->num_rows() != 1){
           return false;
       }
       $result=$query->row_array();
       return $result['address_id'];
    }
    /**
     * @abstract 提货  查询店铺下的提货请求
     */
    function shopExtractInfo(){
       $sql='SELECT
            	a.distill_id,
            	a.distill_conten,
            	a.distill_deed,
                a.distill_req
              FROM
            	rr_distill AS a
              LEFT JOIN rr_manage AS b ON a.address_id = b.address_id
              WHERE
            	b.manage_id='.$this->manage_id;
        $query=$this->db->query($sql);
        if($query === false || $query->num_rows() != 1){
            return false;
        }
        $result=$query->row_array();
        $userinfo='SELECT manage_name  FROM  rr_manage  where manage_id='.$result['distill_req'];
        $user_query=$this->db->query($userinfo);
        if($user_query === false || $user_query->num_rows() != 1){
            return false;
        }
        $user=$user_query->row_array();
        $result['manage_name']=$user['manage_name'];
        return $result;
    }
    /**
     * @abstract 提货  根据提货内容 查询 订单列表
     */
    function  extractOrderList(){
        $sql='SELECT
                a.order_id,
            	a.order_name,
            	a.ordre_dealtime,
            	a.order_number,
            	b.manage_name
              FROM
            	rr_order_nonstandard AS a
              LEFT JOIN rr_manage AS b ON a.cooperator_number = b.manage_number
              WHERE
            	order_id IN('.$this->distill_conten.') and a.order_orderstatus in(11,12)';
        $query=$this->db->query($sql);
        if($query === false || $query->num_rows() < 1){
            return false;
        }
        $result=$query->result_array();
        $list=array();
        foreach ($result  as $key=>$val ){
            $result[$key]['order_name']=mb_substr($val['order_name'], 0,12);
            $result[$key]['ordre_dealtime']=date('Y/m/d', $val['ordre_dealtime']);
            if(!array_key_exists($val['order_id'], $list)){
                $list[$val['order_id']]=$result[$key];
            }
        }
        return $list;
    }
    /**
     * @abstract 提货 店长或店员确认提货请求
     */
    function updateDistill(){
        //确认当前订单属于店员或店长的 店铺
        $sql='SELECT
            	a.address_id,
            	b.distill_conten
              FROM
            	rr_manage AS a
              LEFT JOIN rr_distill AS b ON a.address_id = b.address_id
              WHERE
            	manage_id = '.$this->manage_id;
        $query=$this->db->query($sql);
        if($query === false || $query->num_rows() < 1){
            return false;
        }
        $result=$query->row_array();
        $arrid=explode(',', $result['distill_conten']);
        $nums=sizeof($arrid);
        $distill='update 
              rr_distill 
                  set 
                  distill_deed=2,
                  distill_sign=1,
                  distill_resp='.$this->manage_id.',
                  distill_uptime='.time().'
              where 
                  distill_id='.$this->distill_id.' and address_id='.$result['address_id'];
        
        $order='update rr_order_nonstandard SET order_orderstatus = 12,
              order_uptime='.time().' where order_id in('.$result['distill_conten'].')';
        $this->db->trans_begin();
        $query_distill=$this->db->query($distill);
        $rows_distill = $this->db->affected_rows();
        $query_order=$this->db->query($order);
        $rows_order = $this->db->affected_rows();
        if ($this->db->trans_status() === FALSE || $rows_order !=  $nums || $rows_distill != 1){
            $this->db->trans_rollback();
            return false;
        }else{
            $this->db->trans_commit();
            return true;
        }
    }
    /**
     * @abstract 提货日志查询
     */
    function  extractLogList(){
        $sql='SELECT
            	a.distill_id,
            	a.distill_deed,
                a.distill_req,
                a.distill_conten,
                a.distill_jointime,
                b.address_name,
                b.address_id
              FROM
            	rr_distill as a left join rr_shop_address as b  
                on a.address_id=b.address_id  
              where  
              manage_id='.$this->manage_id;
        $query=$this->db->query($sql);
        if($query === false){
            return false;
        }
        if( $query->num_rows() <  1){
            return 0;
        }
        $distill_state=$this->config->item('distill_state');
        $result=$query->result_array();
        foreach ($result  as $key=>$val ){
            $result[$key]['distill_deed']=$distill_state[$val['distill_deed']];
            $result[$key]['distill_jointime']=date('y/m/d',$val['distill_jointime']);
            $result[$key]['name']=$this->selManageName($val['address_id']);
        }        
        return $result;
    }
    /**
     * 根据Id 查询管理员名称
     */
    function selManageName($id){
        $userinfo='SELECT manage_name,manage_id  FROM  rr_manage  where address_id='.$id.
            ' and manage_role='.$this->config->item('role_Shopowner');
        $user_query=$this->db->query($userinfo);
        if($user_query === false || $user_query->num_rows() < 1){
            return false;
        }
        $user=$user_query->row_array();
        return $user['manage_name'];
    }
}