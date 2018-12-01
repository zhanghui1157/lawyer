<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Org\Wechat;
/**
 * Description of WXMessage
 *
 * @author 陈近荣
 */
class WXMessage {
    const MSG_TYPE_TEXT = 'text';
    const MSG_TYPE_IMAGE='image';
    const MSG_TYPE_LINK='link';
    const MSG_TYPE_LOCATION = 'location';
    const MSG_TYPE_EVENT='event';//Event Push only supports Wechat 4.5 or above. Will be coming soon    
    const REPLY_TYPE_MUSIC='music';
    const REPLY_TYPE_TEXT = 'text';
    const REPLY_TYPE_NEWS = 'news';
    private $_postData;
    private $_token;
    
    public function __construct()
    {
        if (! defined('TOKEN'))
            throw new Exception('Token is required');
        
        if (method_exists($this, 'errorHandler'))
            set_error_handler(array($this, 'errorHandler'));
        
        if (method_exists($this, 'exceptionHandler'))
            set_exception_handler(array($this, 'exceptionHandler'));
        
        $this->_token = TOKEN;
        $this->parsePostRequestData();
    }
    
    public function run()
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            if ($this->_postData && $this->beforeProcess($this->_postData) === true) {
                $this->processRequest($this->_postData);
                $this->afterProcess();
            }
            else
                throw new Exception('POST data is wrong or beforeProcess method doesn\'t return true');
        }
        else
            $this->sourceCheck();
        exit(0);
    }
    
    /**
     * check text msg
     * @return boolean
     */
    public function isTextMsg()
    {
        return $this->_postData->MsgType == self::MSG_TYPE_TEXT;
    }
    
    /**
     * check location
     * @return boolean
     */
    public function isLocationMsg()
    {
        return $this->_postData->MsgType == self::MSG_TYPE_LOCATION;
    }
    
    /**
     * check image
     * @return boolean
     */
    public function isImageMsg(){
        return $this->_postData->MsgType == self::MSG_TYPE_IMAGE;
    }

    /**
     * check links
     * @return boolean
     */
    public function isLinkMsg(){
        return $this->_postData->MsgType == self::MSG_TYPE_LINK;
    }
    
    /**
     * check event push
     * @return boolean
     */
    public function isEventMsg(){
        return $this->_postData->MsgType == self::MSG_TYPE_EVENT;
    }
    
    /**
     * generate text msg string
     * @param string $content
     * @return string xml
     */
    public function outputText($content)
    {
        $textTpl = '<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                <FuncFlag>0</FuncFlag>
            </xml>';
    
        $text = sprintf($textTpl, $this->_postData->FromUserName, $this->_postData->ToUserName, time(), self::REPLY_TYPE_TEXT, $content);
        return $text;
    }
    
    /**
     * generate text & images msg string
     * @param string $content
     * @param arrry $posts article array. Every item is an article array, the keys are in consistent of the official instructions.
     * @return string xml
     */
    public function outputNews($posts = array())
    {
        $textTpl = '<xml>
             <ToUserName><![CDATA[%s]]></ToUserName>
             <FromUserName><![CDATA[%s]]></FromUserName>
             <CreateTime>%s</CreateTime>
             <MsgType><![CDATA[%s]]></MsgType>
             <ArticleCount>%d</ArticleCount>
             <Articles>%s</Articles>
             <FuncFlag>1<FuncFlag>
         </xml>';
        
        $itemTpl = '<item>
             <Title><![CDATA[%s]]></Title>
             <Discription><![CDATA[%s]]></Discription>
             <PicUrl><![CDATA[%s]]></PicUrl>
             <Url><![CDATA[%s]]></Url>
         </item>';
        
        $items = '';
        foreach ((array)$posts as $p) {
            if (is_array($p))
                $items .= sprintf($itemTpl, $p['title'], $p['discription'], $p['picurl'], $p['url']);
            else
                throw_exception('$posts data structure wrong');
        }
        
        $text = sprintf($textTpl, $this->_postData->FromUserName, $this->_postData->ToUserName, time(), self::REPLY_TYPE_NEWS,  count($posts), $items);
        return $text;
    }
    
    public function outputMusic($musicpost){
        $textTpl = '<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType> 
            <Music>%s</Music>
            <FuncFlag>0</FuncFlag>
        </xml>';
        
        $musicTpl = '
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <MusicUrl><![CDATA[%s]]></MusicUrl>
            <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
            ';
        $music = '';        
        if (is_array($musicpost)){
            $music .= sprintf($musicTpl, $musicpost['title'], $musicpost['discription'], $musicpost['musicurl'], $musicpost['hdmusicurl']);
        }else{
            throw_exception('$posts data structure wrong');
        }
        
    
        $text = sprintf($textTpl, $this->_postData->FromUserName, $this->_postData->ToUserName, time(), self::REPLY_TYPE_MUSIC, $music);
        return $text;
         
    }

    /**
     * Prase the received post arra
     * @return SimpleXMLElement
     */
    public function parsePostRequestData()
    {
        $rawData = $GLOBALS['HTTP_RAW_POST_DATA'];
        $data = simplexml_load_string($rawData, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($data !== false)
            $this->_postData = $data;
    
        return $data;
    }
    
    /**
     * return the received post array
     * @return object
     */
    public function getPostData()
    {
        return $this->_postData;
    }
    
    protected function beforeProcess($postData)
    {
        return true;
    }
    
    protected function afterProcess()
    {
    }

    protected function processRequest($data)
    {
        throw_exception('This method must be rewrite');
    }
    
    /**
     * check url source is correct
     * @return boolean
     */
    private function checkSignature()
    {
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];    
        $params = array($this->_token, $timestamp, $nonce);
        sort($params);
        $sig = sha1(implode($params));    
        return $sig == $signature;
    }    
    private function sourceCheck()
    {
        if ($this->checkSignature()) {
            $echostr = $_GET['echostr'];
            echo $echostr;
        }else{
            E('认证失败');
        }    
        exit(0);
    }
}