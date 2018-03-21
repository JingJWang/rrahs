<?php
header('Content-type:text/html;charset=utf-8');
defined('BASEPATH') OR exit('No direct script access allowed');
class Wxcode_model extends CI_Model {
    //微信基础接口表    
    private $table_wxuser='h_token';  
        
    function __construct(){
        parent::__construct();       
        
    }    
    /**
     * 功能描述:js SDK配置参数 获取
     */
    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => $this->config->item('APPID'),
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }    
    /**
     * 功能描述:js SDK配置参数 获取
     */
    public function getSignPackageAjax($url) {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        // $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$url";
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => $this->config->item('APPID'),
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }
    /**
     * 功能描述:获取随机字符串
     * 参数描述:$length 随机字符串长度
     */
    function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    /**
     * 功能描述:获得ApiTicket
     */
      public function getJsApiTicket() {
    	$sql='select access_content from rr_wxaccess where access_id=2';
        $query=$this->db->query($sql);
        $row=$this->db->affected_rows();
    	if($query == false || $row < 1){
    	    return false;
    	}  
    	$response=$query->row_array();
    	return  $response['access_content'];
      }
	  /**
	   * 功能描述:获得AccessToken
	   */
      public function getAccessToken() {    
        $sql='select access_content from rr_wxaccess where access_id=1';
    	$query=$this->db->query($sql);
    	$row=$this->db->affected_rows();
    	if($query == false || $row < 1){
    	    return false;
    	}
    	$response=$query->row_array();
    	return  $response['access_content'];
      }
      /**
       * 功能描述:根据openid获取用户详细信息
       */
      public function userinfo(){
          $access_token=$this->getAccessToken();
          $url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$this->openid.'&lang=zh_CN';
          $ch=curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $output = curl_exec($ch);
          curl_close($ch);
          $data = json_decode($output, true);
          return $data;
      }
      /**
       * 功能描述:通过code 取得用户的openid
       */
      function getOpenid($code){
          $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->config->item('appid').'&secret='.$this->config->item('appsecret').'&code='.$code.'&grant_type=authorization_code';
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL,$url);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $result = curl_exec($ch);
          $wxdata=json_decode($result,true);
          if(array_key_exists('errcode', $wxdata)){
               $openid=$wxdata;
          }else{
               $openid=$wxdata['openid'];
          }          
          return $openid;
      }
      /**
       * 功能描述:通过code 取得用户的openid
       */
      function getOpenid_token($code){
          $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->config->item('APPID').'&secret='.$this->config->item('APPSECRETt').'&code='.$code.'&grant_type=authorization_code';
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL,$url);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $result = curl_exec($ch);
          $wxdata=json_decode($result,true);
          $message = array();
          if(array_key_exists('errcode', $wxdata)){
               $message['openid']='';
               $message['access_token']='';
          }else{
              $message['openid']=$wxdata['openid'];
              $message['access_token']=$wxdata['access_token'];
          }          
          return $message;
      }
      
      /**
       *功能描述:根据openid推送信息
       *参数描述:$openid 微信openid   $content内容
       */
      public function sendmessage($content){
          if(empty($content)){
              return false;
          }else{
              $data=$content;
          }
          $access_token=$this->getAccessToken();
          $url='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
          $curl=curl_init();
          curl_setopt($curl,CURLOPT_URL,$url);
          curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
          curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
          curl_setopt($curl,CURLOPT_POST,1);
          curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
          $info=curl_exec($curl);
          curl_close($curl);
          $info=json_decode($info,true);
          return  $info;
      }
      /**
       * 功能描述:snsapi_userinfo方式获取用户信息
       */
      public function get_snsapi_userinfo($token,$openid){
          $url="https://api.weixin.qq.com/sns/userinfo?access_token=".$token."&openid=".$openid."&lang=zh_CN";
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL,$url);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $result = curl_exec($ch);
          $data=json_decode($result);
          $info['openid']=$data->openid;
          $info['nickname']=$data->nickname;
          $info['sex']=$data->sex;
          $info['headimgurl']=$data->headimgurl;
          $info['province']=$data->province;
          $info['country']=$data->country;
          return $info;
      }
      /**
       * 设置分组
       * @param     string       openid
       * @param     int          groupid       要分到相应组的id
       * @return    
       */
      public function setPacket($openid,$groupid){
          $access_token=$this->getAccessToken();
          $url = 'https://api.weixin.qq.com/cgi-bin/groups/getid?access_token='.$access_token;
          $data = '{"openid":"'.$openid.'"}';
          $info = $this->httpGetdata($url,$data);
          $jsoninfo=json_decode($info,true);
          if (isset($jsoninfo['errcode'])) {
              return false;//未关注的用户
          }
          if ($jsoninfo['groupid']<=$groupid && $jsoninfo['groupid']>=105) {
              return true;
          }
          $url = 'https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token='.$access_token;
          $data = '{"openid":"'.$openid.'","to_groupid":'.$groupid.'}';
          $info = $this->httpGetdata($url,$data);
          $jsoninfo=json_decode($info,true);
          if ($jsoninfo['errcode']!=0) {
              return false;
          }
          return true;
      }
      /**
       * 功能描述:curl get方法  有data参数
       * 参数描述:$url 请求的地址
       * 返回数据:$res  请求结果 
       */
      public function httpGetdata($url,$data){
        $curl=curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
        if(!empty($data)){
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        $info=curl_exec($curl);
        curl_close($curl);
        return $info;
      }
      /**
       * 功能描述:curl get方法
       * 参数描述:$url 请求的地址
       * 返回数据:$res  请求结果
       */
      public function httpGet($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
      }
      /**
       *功能描述:根据openid定时推送模板信息
       *参数描述:$openid 微信openid   $content内容
       */
      public function sendMessagepush($content){
      	if(empty($content)){
      		return false;
      	}else{
      		$data=$content;
      	}
      	$access_token=$this->getAccessToken();
      	$url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
      	$curl=curl_init();
      	curl_setopt($curl,CURLOPT_URL,$url);
      	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
      	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
      	curl_setopt($curl,CURLOPT_POST,1);
      	curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
      	curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
      	$info=curl_exec($curl);
      	curl_close($curl);
      	$info=json_decode($info,true);
      	return  $info;
      }
}