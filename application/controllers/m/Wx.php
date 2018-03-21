<?php
header("Content-type:text/html;charset=utf-8");
defined('BASEPATH') OR exit('No direct script access allowed');
define('TOKEN','rrahszhang');
/**
 * 
 * @author xiaotao
 * 微信模块
 */
class Wx extends CI_Controller {
    /**
     * @abstract  校验是否是验证token
     */
    function index(){
        if (!isset($_GET['echostr'])) {
            $this->responseMsg();
        }else{
            $this->valid();
        }
    }
    /**
     * @abstract 验证码token
     */
    public function valid(){
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    /**
     * @abstract  功能描述:校验接受请求类型
     */
    public function responseMsg(){
        $postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            $result = "";
            switch ($RX_TYPE){
                //事件类型
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                //文本类型
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
            }
            echo $result;
        }else {
            echo "";
            exit;
        }
    }
    /**
     * @abstract 功能描述:请求类型为事件类型
     */
    private function receiveEvent($object){
        $object->Event;//事件
        $content="";//内容
        $type="";//返回内容类型
        switch ($object->Event){
                //扫描场景二维码事件
            case "SCAN":
                $type=2;
                $this->load->model('c/Manage_model');
                $this->Manage_model->manage_openid=$object->FromUserName;
                $this->Manage_model->manage_fid=$object->EventKey;
                $this->Manage_model->manage_role=20;
                $res=$this->Manage_model->reviewedUser();
                if($res){
                    $content='账号审核完成,并已绑定当前微信账号!';
                }else{
                    $content='账号审核出现异常!';
                }
                
        }
        if($type == '1'){
            $result = $this->transmitTWNews($object, $content);
        }else{
            $result = $this->transmitText($object, $content);
        }
        return $result;
    }
    /**
     * @abstract  返回文本类型消息
     * @param unknown $object
     * @return string
     */
    public function receiveText($object){
        $keyword=preg_match('/^[a-zA-Z]+/s',trim($object->Content),$data);
        $key=strtolower($data['0']);
        switch ($key) {
            case 'regist':
                $content='<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx780b312e2fc3d66f&redirect_uri=http%3a%2f%2fm.rrahs.com%2fc/manage/register&response_type=code&scope=snsapi_base&state=#wechat_redirect">系统用户注册</a>';
                $result = $this->transmitText($object, $content);
                break;
            default:
                $content='欢迎关注!';
                $result = $this->transmitText($object, $content);
                break;
        }
        return $result;
    }
    /**
     * 功能描述:返回文本信息
     */
    private function transmitText($object, $content)    {
        if (!isset($content) || empty($content)){
            return "";
        }
        $textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[%s]]></Content>
					</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }
    /**
     * 功能描述:多条图文
     */
    private function transmitTWNews($object, $newsArray){
        if(!is_array($newsArray)){
            return;
        }
        $itemTpl = "<item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url></item>";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <ArticleCount>%s</ArticleCount>
        <Articles>
        $item_str</Articles>
        </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }
    /**
     * 校验token
     */
    /*
     * 功能描述:校验TOKEN
     */
    private function checkSignature(){
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * @abstract  更新微信access_token 与  ticket
     * linux 通过linux服务器crontab 2个小时自动请求一次
     */
    function tokenTicket(){
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.
                $this->config->item('appid').'&secret='.$this->config->item('appsecret');
        $result=often_helper::remote($url, $data='');
        $token=json_decode($result);
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$token->access_token;
        $result=often_helper::remote($url, $data='');
        $ticket=json_decode($result);
        $this->load->model('m/Wx_model');
        $this->Wx_model->token='accessToken';
        $this->Wx_model->ticket='jsApiTicket';
        $this->Wx_model->token_content=$token->access_token;
        $this->Wx_model->ticket_content=$ticket->ticket;
        $resp=$this->Wx_model->upaccesstoken();
        if($resp){
            often_helper::output('21010');
        }else{
            often_helper::output('21011');
        }
    }
   /**
    *  @abstract 微信菜单
    * @return boolean
    */
    function upmenu(){
         
        $content='{
    "button": [
            {
    
                        "type": "view",
                        "name": "手机回收",
                        "url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx780b312e2fc3d66f&redirect_uri=http%3a%2f%2fm.rrahs.com%2fm%2fDigital%2fShowBrand&response_type=code&scope=snsapi_base&state=#wechat_redirect"
            
            },
       
        {
          "name": "活动中心",
          "sub_button":[
                  {
                    "type": "view",
                    "name": "以旧换新",
                    "url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx780b312e2fc3d66f&redirect_uri=http%3a%2f%2fm.rrahs.com%2fview%2fadv%2frenew.html&response_type=code&scope=snsapi_base&state=#wechat_redirect"
                  }
                ,{
                    "type": "view",
                    "name": "公益活动",
                    "url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx780b312e2fc3d66f&redirect_uri=http%3a%2f%2fm.rrahs.com%2fview%2fadv%2fadv.html&response_type=code&scope=snsapi_base&state=#wechat_redirect"
                  }
           ] 
        },
        {
           "name": "用户中心",
          "sub_button":[
                 {
                "type": "view",
                 "name": "订单查询",
                 "url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx780b312e2fc3d66f&redirect_uri=http%3a%2f%2fm.rrahs.com%2fm%2fOrder%2forderSee&response_type=code&scope=snsapi_base&state=#wechat_redirect"
                },
                 {
                "type": "view",
                 "name": "关于我们",
                 "url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx780b312e2fc3d66f&redirect_uri=http%3a%2f%2fm.rrahs.com%2fview%2fadv%2fabout.html&response_type=code&scope=snsapi_base&state=#wechat_redirect"
                }
          ]      
        }
    ]
    }';
        $this->load->model('m/Wx_model');
        $token=$this->Wx_model->getToken();
        if($token === false){
            return false;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$token;
        $result = often_helper::remote($url, $content);
        var_dump($result);
    }
    /**
     * @abstract 更新用户资料
     */
    function upWxUserInfo(){
        //查询用户表 更新表内没有资料用户的信息
        $this->load->model('m/User_model');
        //获取未更新资料的微信用户
        $data=$this->User_model->userInfo();
        if(empty($data['user_wxname']) && $data !== false && is_array($data)){
            $this->load->model('m/Wxcode_model');
            $this->Wxcode_model->openid=$data['user_openid'];
            $info=$this->Wxcode_model->userinfo();
        }else{
            exit();
        }
        if($info['subscribe'] == 0){
            $this->User_model->openid=$data['user_openid'];
            $this->User_model->nickname='';
            $this->User_model->subscribe=$info['subscribe'];
            $res=$this->User_model->upWxInfo();
        }else{
            $this->User_model->openid=$data['user_openid'];
            $this->User_model->nickname=$info['nickname'];
            $this->User_model->subscribe=$info['subscribe'];
            $res=$this->User_model->upWxInfo();
        }
        exit();
    }    
    
    
    
    
    
}