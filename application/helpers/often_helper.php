<?php
class often_helper{
    //json 输出方法
    public static  function  output($s,$d='',$u='',$m=''){
        echo json_encode(array('status'=>$s,'data'=>$d,'url'=>$u,'msg'=>$m));
        exit();
    }
    //curl 请求方法
    public static function remote($url,$data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
        
    } 
    //替换掉敏感字符
    public static function  safe_replace($string){
        $serach=array('/',',','&','%20','%27','%2527','*','"',';','<','>','{','}',' ');
        $replace=array('', '', '','','','','','','','','','','','');
        return str_replace($serach, $replace, $string);
    }  
    public static function alert($msg='',$u='',$status=''){
        echo '<script language="JavaScript">';
        if(!empty($msg)){
            echo ' alert("'.$msg.'");';
        }
        if($status == 0){
            //跳转
           echo 'location.replace("'.$u.'") ';
        }else if($status == 1){
            //当前页面
            echo "";
        }else{
            echo "history.back();";
        }
        echo '</script>';
        exit();
    }  
    //生成6位随机数字 
    public static function random($par=''){
        return rand(100000, 999999);
    }
    //日志函数
    public static function outLog($para){
        //日志存放路径 
        $path    = 'logs/';
        //日志名称
        $name    = date('Y-m-d').$para['name'].'.log';               
        //日志格式
        switch ($para['method']){
            case 'req':
                $standard= '请求时间:'.date('Y-m-d H:i:s').'请求IP地址:'.$_SERVER['REMOTE_ADDR'].'请求地址:'.$_SERVER['REQUEST_URI'];
                $content = var_export($_REQUEST,true);
                break;
            case 'resp':
                $standard= '返回时间:'.date('Y-m-d H:i:s').'返回内容:';
                $content = var_export($para['content'],true);
                break;
        }
        //写入文件
        $status=file_put_contents($name,$standard."\r\n".$content."\r\n",FILE_APPEND);
    }
}