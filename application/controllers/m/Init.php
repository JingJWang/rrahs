<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Init extends RR_Controller {
    
    public function index(){
        $this->load->view('welcome_message');
    
    }
    function  getOpenid(){
        $this->wxLogin();
    }
    //销毁session
    function delSession(){
        echo '开始清空登录信息.....';
        session_destroy();
        echo '登录已经信息清空.....';
    }
    function selsession(){
        var_dump($_SESSION);
    }
   
   

    
    
}
