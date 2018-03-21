<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Digital_model extends CI_Model{
	
    function __construct(){
        parent::__construct();
        $this->load->database();
    }
    /**
     * @abstract  读取品牌列表
     * @param       int       type       物品类型
     * @param       int       star       开始的页数
     * @return      失败返回false|成功返回数组
     */
    function getBrand(){
        $sql = 'select brand_name as name,brand_id as id,brand_img as img from rr_brand 
                where brand_classification=5 and brand_status=1 and brand_id not in (14,15,22,28)';
        $result = $this->db->query($sql);
        $row=$this->db->affected_rows();
        if($row < 1){
            return false;
        }
        $data=$result->result_array();
        return $data;
    }
    /**
     * @abstract  读取型号列表
     * @param       int       bid       商品的id
     * @param       int       star      开始的页数
     * @param       int       num       要取的个数
     * @return      失败返回false|成功返回数组
     */
    function getShops($bid){
       $sql = 'select distinct a.types_id as id,a.types_name as name
                from rr_electronic_types a left join rr_quote_plan as b on a.types_id = b.types_id  
                where brand_id='.$bid.' and a.types_status=1 order by a.types_id desc';
        $result = $this->db->query($sql);
        $row=$this->db->affected_rows();
        if($row<1){
            return false;
        }
        return $result->result_array();
    }
    /**
     * 获取报价方案
     * @param   int   typeid   型号id
     * @return  成功的时候返回 array | 失败的时候返回 false
     */
    function GetPlan(){
        $sql='select coop_number,plan_base_price,plan_content,
              plan_garbage_price from rr_quote_plan where types_id='.
                  $this->typeid.' and plan_status=1';
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($query  === false && $row < 1){
            return false;
        }
        $response=$query->result_array();
        return $response;
    }
    /**
     * @abstract 品牌 型号 搜索   根据关键词搜索型号列表
     */
    function seachShop(){
      $sql = 'select  
                  types_id as id,
                  types_name as name,
                  types_img as img
              from rr_electronic_types
              where types_name like "%'.$this->seach.'%" 
              and types_status=1 order by types_id desc';
        $query = $this->db->query($sql);
        if($query === false){
            return false;
        }
        $row=$this->db->affected_rows();
        if($row < 1){
            return  0;
        }
        $result=$query->result_array();
        return $result;
    } 
    /**
     *@abstract 获取参数详细信息
     *@return  成功返回 array || 失败返回 false
     */
    function getTypeCon(){
        $model_sql='select model_model as model ,model_type as type,model_alias as alias,
                model_logic as logic,model_name as name from rr_option_model where model_status=1';
        $model_query=$this->db->query($model_sql);
        $row=$this->db->affected_rows();
        if($model_query === false || $row < 1){
            return false;
        }
        $model=$model_query->result_array();
        $info_sql='select info_id as id ,info_info as info from rr_option_info where info_status=1';
        $info_query=$this->db->query($info_sql);
        $rows=$this->db->affected_rows();
        if($info_query === false || $rows < 1){
            return false;
        }
        $info=$info_query->result_array();
        $response=array('model'=>$model,'info'=>$info);
        return $response;
    }
    /**
     * 获取配置的参数信息
     * @param  int   id  型号id
     * @return  成功返回 true |失败返回false
     */
    function getParameter(){
        $sql='select types_id,types_name,types_attr from rr_electronic_types where types_id='.$this->typeid;
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($query === false || $row < 1){
            return false;
        }
        $attr=$query->row_array();
        return $attr;
    }
    
    /**
     * 获取产品参数
     */
    function optionModel(){
        $model_sql='select model_type as type,model_alias as alias
                   from rr_option_model where model_status=1 and product_id=1';
        $model_query=$this->db->query($model_sql);
        $row=$this->db->affected_rows();
        if($model_query === false || $row < 1){
            return false;
        }
        $info=$model_query->result_array();
        return $info;
    }
    /**
     * 储存用户首次报单信息
     */
    function savefirOrder(){
            //储存订单信息
            $data_order=array(
                'wx_id'=>$this->userid,
                'cooperator_number'=>$this->inp,
                'types_id'=>$this->phoneid,
                'order_name'=>$this->phonename,
                'order_mobile'=>$this->mobile,
                'order_number'=>$this->number,
                'order_ctype'=>2,
                'order_ftype'=>1,
                'order_bid_price'=>$this->price*100,
                'order_orderstatus'=>1,
                'order_jointime'=>time(),
                'order_confirm'=>0//默认为0 用户点击确定提价 1 取消为-1
            );
            $this->db->trans_begin();
            $this->db->insert('rr_order_nonstandard',$data_order);
            $row=$this->db->affected_rows();
            $orderid=$this->db->insert_id();
            $data_con=array(
                       'order_id'=>$orderid,
                       'content_tid'=>$this->contentid,
                       'content_text'=>$this->content,
                       'content_account'=>$this->username.','.$this->bankname.','.$this->banknumber,
                       'content_jointime'=>time()
            );
            $this->db->insert('rr_order_content',$data_con);
            $row1=$this->db->affected_rows();
            /*$this->db->update('rr_user',
                    array('user_mobile'=>$this->mobile),
                    array('user_id'=>$this->userid));
            $usrow=$this->db->affected_rows();*/
            if ($this->db->trans_status() === FALSE || $row1 != 1 || $row != 1 ){
                 $this->db->trans_rollback();
                 return  false;
            }else{
                $this->db->trans_commit();
                 return $orderid ;
            }
     }
    
    /**
     * 取消订单  
     */
    function cancles(){
        //查询是否存在此订单
        $this->id=$this->oid;
        $oneOrder=$this->getOrderDetail();
        if(!$oneOrder){
           return false; 
        }
        /* //获取订单里面的回收商编号
        $this->number=$oneOrder['numaddr'];
        $isTrue=$this->isTrueOrder();
        //查看用户表里面该回收编号是否只属于一个商户
        if(!$isTrue){
            return false;
        } */
        $set=array(
            'order_orderstatus'=>'0',
            'order_uptime'=>time()
        );
        $this->db->update('rr_order_nonstandard',$set,array('order_id'=>$this->oid));
        $con=$this->db->affected_rows();
        if ($con !=1 ){
            return false;
        }else{
            return true;
        }
    }
    
   
    
    /*
     * 根据订单编号查询用户openid
     */
    function  getOpneid(){
        if($this->type == 9){
            $sql='select b.manage_id as user_id,b.manage_openid as user_openid,b.manage_fid as fid from rr_order_nonstandard
              as a left join rr_manage as b on  a.cooperator_number=b.manage_number where  order_id ='.$this->orderid;            
        }else{
            $sql='select b.user_id,b.user_openid from rr_order_nonstandard
              as a left join rr_user as b on  a.wx_id=b.user_id where  order_id ='.$this->orderid;            
        }
        $query=$this->db->query($sql);
        $row=$query->num_rows();
        if($query === false || $row < 1){
            return false;
        }
        $data=$query->row_array();
        return $data;
    }
    
    
    /**
     * 用户订单列表查看
     */
    function getOrderlist(){
        $sql='SELECT a.order_id as id,a.order_name as orderName,a.order_bid_price as orderPric, 
            a.order_orderstatus as dealStatus, a.order_jointime as joinTime,a.order_confirm as confirm 
            from `rr_order_nonstandard` a left join rr_user b on b.user_id=a.wx_id 
            where b.user_id='.$this->userid.' and order_status=1
            order by joinTime desc';
        $query=$this->db->query($sql);
        $row=$query->num_rows();
        if($row>0){
            $data=$query->result_array();
            return $data;
        }else{
            return false;
        }
    }
    /**
     * 储存商户报单信息
     */
    /* function saveMerch(){
        //储存订单信息
        $data_order=array(
            'wx_id'=>$this->userid,
            'types_id'=>$this->phoneid,
            'order_name'=>$this->phonename,
            'order_number'=>$this->number,
            'order_ctype'=>2,
            'order_ftype'=>1,
            'order_bid_price'=>$this->price*100,
            'order_orderstatus'=>1,//用户添加信息  为0 点击确认成交改为1
            'order_jointime'=>time(),
            'order_status'=>1,//商户点击 确认为1 取消为-1
            'order_confirm'=>1//默认为0 用户点击确定提价 1 取消为-1
        );
        $this->db->trans_begin();
        $this->db->insert('rr_order_nonstandard',$data_order);
        $row=$this->db->affected_rows();
        $orderid=$this->db->insert_id();
        $data_con=array(
            'order_id'=>$orderid,
            'user_mobile'=>$this->mobile,
            'content_tid'=>$this->contentid,
            'content_text'=>$this->content,
            'content_jointime'=>time()
        );
        $this->db->insert('rr_order_content',$data_con);
        $row1=$this->db->affected_rows();
        if ($this->db->trans_status() === FALSE || $row1 != 1 || $row != 1){
            $this->db->trans_rollback();
            return false;
        }else{
            $this->db->trans_commit();
            return $orderid ;
        }
    } */
    
    
    /**
     * 校验店长身份
     */
    function checkManager(){
        $sql='select * from rr_manage a left join rr_manage_role as b 
             on a.manage_role=b.role_role where a.manage_role='.$this->roleVal.' and a.manage_id='.$this->id;
        $query=$this->db->query($sql);
        $row=$query->num_rows();
        if($row>0){
            $data=$query->row_array();
            return $data;
        }else{
            return false;
        }
    }
    /**
     * 检验身份
     */
    function getDistillList(){
        $sql='select * from rr_manage a left join rr_manage_role as b
             on a.manage_role=b.role_role where a.manage_role='.$this->roleVal;
        $query=$this->db->query($sql);
        $row=$query->num_rows();
        if($row>0){
            $data=$query->result_array();
            return $data;
        }else{
            return false;
        }
    }
    
    
    /**
     *通过提货订单id查看该订单详情
     */
     function distillOneDetail(){
     	$sql='select a.distill_id as id,a.distill_conten as conten,b.manage_name as shopName,'.
     		' c.manage_name as distName,a.distill_deed as deed'.
     		' from rr_distill a' .
     		' left join rr_manage b on a.distill_req=b.manage_id ' .
     		' left join rr_manage c on a.distill_resp=c.manage_id ' .
     		' where a.distill_id='.$this->id;
     	$query=$this->db->query($sql);
        $row=$query->num_rows();
        if($row>0){
            $data=$query->row_array();
            return $data;
        }else{
            return false;
        }
     }
    
    /**
     * 查询用户短信最后一条
     */
    function getOneMess(){
        $sqls='select code_number as nums from rr_verify_code a where code_moblie='.$this->mobile.'
                and code_status =1 order by code_jointime desc limit 1';
        $querys=$this->db->query($sqls);
        $data=$querys->result_array();
        if($data){
            return $data;
        }else{
            return false;
        }
    }
    /**
     * 
     * 查询用户短信 在一个区间内是否超过规定数量
     */
    function checkVerifyCode(){
        $starttime=time()-$this->time*60;
        $endtime=time();
        $number=$this->num;
        $sql='select count(code_id) as count  from rr_verify_code a
          where code_moblie='.$this->mobile.' and  code_jointime <= '.$endtime.
              ' and code_jointime>='.$starttime;
        $query=$this->db->query($sql);
        $row=$query->num_rows();
        if($row > $number){
            return false;
        }else{
            return true;
        }
    }
    
    /**
     * 发送短信验证码结果存入数据库.
     */
    function saveVerifyCode(){
        //内容
        $data=array(
            'code_moblie'=>$this->mobile,
            'code_number'=>$this->code,
            'response_sid'=>$this->respid,
            'code_jointime'=>time(),
        );
        $result = $this->db->insert('rr_verify_code',$data);
        $rows = $this->db->affected_rows();
        if ($result && ($rows == 1)){
            return true;
        }else{
            return false;
        }
    }
    /**
     * @abstract 核实手机验证码
     * 
     */
    function validateCode(){
        $starttime=time()-$this->time*60;
        $endtime=time();      
        $sql='select code_id from rr_verify_code 
                where code_moblie='.$this->mobile.' and code_number='.$this->code
                .' and code_jointime <= '.$endtime.' and code_jointime >= '
                .$starttime .' and  code_status=1';
        $query=$this->db->query($sql);
        $rows =$query->num_rows();
        if ($query && ($rows == 1)){
            $data=$query->row_array();
            $this->code_id=$data['code_id'];
            return true;
        }else{
            $this->code_id=0;
            return false;
        }
    }
    /**
     * @abstract 修改验证码状态
     */
    function upVerifyCode(){
        $query=$this->db->update(
                'rr_verify_code',
                array('code_status'=>-1,'code_uptime'=>time()),
                array('code_id'=>$this->code_id));
        $num=$this->db->affected_rows();
        if ($query && ($num == 1)){
            return true;
        }else{
            return false;
        }
    }   
}
?>