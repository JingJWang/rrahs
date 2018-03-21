<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Static_model extends CI_Model{
   //管理员表
   private   $rr_manage = 'rr_manage';
   //角色表
   private  $rr_manage_role = 'rr_manage_role';
    
   function __construct(){
       parent::__construct();
       $this->load->database();
   }
    /**
     * @abstract  获取所有管理的数据
     */
    function getManagerList(){
        $where='where a.manage_id !=1 ';
        //效验手机号
        if(!empty($this->mobile)){
            $where.=' and a.manage_mobile='.$this->mobile;
        }else{
            $where.='';
        } 
        //效验起止日期
        if(!empty($this->starttime) && !empty($this->endtime)){
            $this->endtime=(int)$this->endtime+86399;
            $where.=' and a.manage_jointime > '.$this->starttime.' and a.manage_jointime < '.$this->endtime;
        }else{
            $where.='';
        }
        //效验 day week month
        $year = date("Y");
        $month = date("m");
        $day = date("d");
        $daystart = mktime(0,0,0,$month,$day,$year);//当天开始时间戳
        $dayend= mktime(23,59,59,$month,$day,$year);//当天结束时间戳
        $lowtime=mktime(0,0,0,date('m'),1,date('Y'));//本周周一凌晨
        $weektime=time()-((date('w')==0?7:date('w'))-7)*24*3600;//本周日24点
        $monthStime=strtotime(date('Y-m', time()).'-01 00:00:00');//本周月初
        $monthEtime=strtotime(date('Y-m', time()).'-'. date('t', time()).' 23:59:59');//本月月末
        switch($this->option){
    		case 'day':
    			$where.=' and a.manage_jointime > '.$daystart.' and a.manage_jointime < '.$dayend;
    			break;
    		case 'week':
    		    $where.=' and a.manage_jointime > '.$lowtime.' and a.manage_jointime < '.$weektime;
    		    break;
    		case 'month':
    			 $where.=' and a.manage_jointime > '.$monthStime.' and a.manage_jointime < '.$monthEtime;
    		    break;
    		default:;break;
    	}
    	$count_sql='select * from '
    	       .$this->rr_manage.' as a left join '.$this->rr_manage_role.'
                as b on a.manage_role=b.role_role  '.$where;
    	$count_query=$this->db->query($count_sql);
    	$data['CountPage']=$count_query->num_rows();//总条数
    	$data['Page']=ceil($count_query->num_rows()/10);//总页数
        //分页
        if(empty($this->limit)){
            $where = $where . ' limit 0,10';
        }else{
            $start=($this->limit-1)*10;
            //$end=($this->limit+1)*10;
            $end=10;
            $where = $where . ' limit '.$start.','.$end;
        }
        $sql='select 
                a.manage_openid as openid,
                a.manage_id as man_id,
                a.manage_name as name,
                a.manage_mobile as mobile,
                a.manage_number as number,
                a.manage_email as email,
                a.manage_status as status,
                a.manage_role as roleid,
                b.role_name as rolename,
                a.manage_jointime as jointime
                from '.$this->rr_manage.' as a left join '.$this->rr_manage_role.'
                as b on a.manage_role=b.role_role  '.$where;
        $query=$this->db->query($sql);
        if($query->num_rows() <1){
            return false;
        }
        $data['result']=$query->result_array();
        return $data;
    }
    /**
     * 查看管理员个人信息
     */
    function getUserManageOne(){
        $sql='select a.manage_id as man_id,a.manage_name as name,a.manage_mobile as mobile,
                a.manage_number as number,a.manage_email as email,a.manage_status as status,
                a.manage_role as roleid,b.role_name as rolename,a.address_id as addid
                from '.$this->rr_manage.' as a left join '.$this->rr_manage_role.'
                as b on a.manage_role=b.role_role where a.manage_id='.$this->id;
        $query=$this->db->query($sql);
        if($query->num_rows() <1){
            return false;
        }
        $data=$query->row_array();
        return $data;
    }
    /**
     *  @abstract  后台修改角色状态
    */
    function updateManagerRole(){
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
        $sql='update '.$this->rr_manage.' set manage_status='.$this->status.' 
            where manage_id='.$this->id.' and manage_status='.$yuanstatus.' and manage_id!=1';
        $query=$this->db->query($sql);
        $rows = $this->db->affected_rows();
        if($rows==1){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 后台系统用户- 系统用户列表 - 审核 - 用户权限
     */
    function  editManagerRole(){
       $data=array(
           'address_id'=>$this->addrid,
           'manage_fid'=>$this->manid,
           'manage_number'=>$this->number,
           'manage_role'=>$this->roleid,
           'manage_status'=>1,
           'manage_uptime'=>time()
       );
       $id=array('manage_id'=>$this->id);
       $this->db->update($this->rr_manage,$data,$id);
       $rows = $this->db->affected_rows();
       if($rows==1){
           return true;
       }else{
           return false;
       }
    }
    /**
     * @abstract 通过角色值获取用户的角色名字
     */
    function getRoleName(){
        $sql='select b.role_role as roleid,b.role_name as rolename
                from '.$this->rr_manage_role.' as b where b.role_role='.$this->roleid;
        $query=$this->db->query($sql);
        if($query->num_rows() <1){
            return false;
        }
        $data=$query->row_array();
        return $data;
    }
    /**
     * @abstract 获取权限列表
     */
    function getRoleList(){
        $sql='select b.role_id as id,b.role_role as roleid,b.role_name as rolename
                from '.$this->rr_manage_role.' as b where b.role_id!=1';
        $query=$this->db->query($sql);
        if($query->num_rows() <1){
            return false;
        }
        $data=$query->result_array();
        return $data;
    }
    /**
     * 后台系统用户- 系统用户列表 - 修改用户
     */
    function  editUserOne(){
       $data=array(
            'address_id'=>$this->new['addrid'],
            'manage_number'=>$this->new['number'],
            'manage_mobile'=>$this->new['mobile'],
            'manage_role'=>$this->new['roleid'],
            'manage_status'=>1,
            'manage_uptime'=>time()
        );
        $id=array('manage_id'=>$this->new['id']);
        $this->db->update($this->rr_manage,$data,$id);
        $rows = $this->db->affected_rows();
        if($rows==1){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 获取所有店长信息
     */
    function getKeeper(){
        $sql='select a.manage_id as mid,a.manage_name as name
             from '.$this->rr_manage.' as a where a.manage_role='.$this->config->item('role_Shopowner');
        $query=$this->db->query($sql);
        if($query->num_rows() <1){
            return false;
        }
        $data=$query->result_array();
        return $data;
    }
    /**
     * 获取所有的提货员信息
     */
    function getTiHuo(){
        $sql='select * from '.$this->rr_manage.' where manage_role='.$this->config->item('role_Distill');
        $query=$this->db->query($sql);
        if($query->num_rows() <1){
            return false;
        }
        $data=$query->result_array();
        return $data;
    }
}