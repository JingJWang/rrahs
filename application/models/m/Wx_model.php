<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Wx_model extends CI_Model{
    
    public function __construct(){
        parent::__construct();
        $this->load->database();
    }
    function upaccesstoken(){
        $this->db->trans_begin();
        $this->db->update('rr_wxaccess',array(
                'access_name'=>$this->token,
                'access_content'=>$this->token_content,
                'access_uptime'=>time()
        ),array('access_id'=>1));  
        $token=$this->db->affected_rows();
        $this->db->update('rr_wxaccess',array(
                'access_name'=>$this->ticket,
                'access_content'=>$this->ticket_content,
                'access_uptime'=>time()
        ),array('access_id'=>2));
        $ticket=$this->db->affected_rows();
        if ($this->db->trans_status() === FALSE || $ticket !=1 || $token != 1){
            $this->db->trans_rollback();
            return  false;
        }else{
            $this->db->trans_commit();
            return  true;
        }
   }
   /**
    * @abstract 读取微信token
    * @return boolean 读取失败 返回false | 读取成功  返回结果 string
    */
   function getToken(){
        $sql='select access_id,access_content from rr_wxaccess where access_id=1';
        $result=$this->db->query($sql);
        $row=$this->db->affected_rows();
        if($row < 1){
            return false;
        }
        $data=$result->row_array();
        return  $data['access_content'];
   }
    
}