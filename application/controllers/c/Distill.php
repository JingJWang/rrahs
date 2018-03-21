<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class distill extends RR_Controller{
    /**
     * @abstract 效验登录后台的session
     */
    function checkLoginSess(){
        $newSess=array(
            'name'=>'',
            'role'=>''
        );
        if(is_array($_SESSION)){
            if(isset($_SESSION['manage']['manage_name']) && $_SESSION['manage']['manage_name']!=''){
                $newSess['name']=$_SESSION['manage']['manage_name'];
            }
            if(isset($_SESSION['manage']['manage_role']) && $_SESSION['manage']['manage_role']!=''){
                $newSess['role']=$_SESSION['manage']['manage_role'];
            }
        }
        return $newSess;
    }
    /**
     * @abstract 通过用户角色值来获取用户的角色名字
     */
    function getRoleName(){
        $this->load->model('c/Static_model');
        $this->Static_model->roleid=$this->role;
        $res=$this->Static_model->getRoleName();
        return $res;
    }
    /**
     * @abstract 获取后台管理系统-顶部top公共样式内容
     */
    function gonggong(){
        //效验登录后台的session 并获取相应的数据
        $newSess=$this->checkLoginSess();
        //获取角色名字
        $this->role=$newSess['role'];
        $roleName=$this->getRoleName();
        if($roleName){
            $rolename=$roleName['rolename'];
            $roleid=$roleName['roleid'];
        }else{
            $role='';
        }
        $this->assign('roleid',$roleid);
        $this->assign('rolename',$rolename);
        $this->assign('name',$newSess['name']);
    } 
    /**
     * @abstract 后台订单管理-提货列表
     */
    function distills(){
        $this->gonggong();
        $this->display('c/order_distill.html');
    }
    /**
     * @abstract 后台订单管理-提货列表
     */
   function getDistillList(){
        $name=$this->input->post('name',true);
        //获取参数 并效验
        if(empty($name) && preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $name) < 0){
             often_helper::Output('41010','','','非法参数');
        }
        $limit=$this->input->post('limit',true);
        $this->load->model('c/Distill_model');
        $this->Distill_model->name=$name;
        $this->Distill_model->limit=$limit;
        $res=$this->Distill_model->selectList();
        often_helper::output('41010',$res);
    }
    /**
     * 效验提货订单的数据状态
     */
    function checkDistill(){
        $id=$this->input->post('id',true);
        if(!isset($id) || empty($id) || !is_numeric($id) || $id<0){
            often_helper::Output('41011','','','非法参数');
        }
        $this->load->model('c/Distill_model');
        $this->Distill_model->id=$id;
        $resu=$this->Distill_model->getOneDistill();
        //验证该批订单 所属的店铺是否是该提货员
        $this->load->model('c/Order_model');
        $this->Order_model->manage_id=$resu['result']['distill_resp'];
        $this->Order_model->distill_conten=trim($resu['result']['distill_conten'],',');
        $orderid=$this->Order_model->checkOrderID();
        if($orderid === false ){
            often_helper::output('41011','','','校验提货清单出现异常!');
        }
        //更改提货状态及该批次订单状态
        $this->load->model('m/Distill_model');
        $this->Distill_model->orderid=trim($resu['result']['distill_conten'],',');
        $upSus=$this->Distill_model->updateOrderSta();
        if(!$upSus){
            often_helper::output('41011');
        }
        often_helper::output('41010',$upSus);
    }
}