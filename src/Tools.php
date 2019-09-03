<?php

namespace Cmmia\Encrypt;

class Tools {

    private $key;
    private $iv;

    public function __construct($key, $iv) {
        $this->key = $key;
        $this->iv = $iv;
    }

    public function encode($text) {
        $encrypted = openssl_encrypt($text, 'aes-256-cbc', base64_decode($this->key), OPENSSL_RAW_DATA, base64_decode($this->iv));
        return array(base64_encode($encrypted), md5($this->iv . "+_+" . $text));
    }

    public function decode($text, $hash) {
        $text = openssl_decrypt(base64_decode($text), 'aes-256-cbc', base64_decode($this->key), OPENSSL_RAW_DATA, base64_decode($this->iv));
        if (md5($this->iv . "+_+" . $text) !== $hash) {
            return 0;
        }
        return $text;
    }

    public function qUrl($subjectId, $questionId) {//题目编号加密
        $questionIdStr = sprintf("%010d", $questionId);
        return $this->getUriBySubjectId($subjectId) . "q/" . substr($questionIdStr, 0, 5) . md5($this->key . "_" . $this->iv . "s_" . $subjectId . "_" . $questionId . "_" . $questionIdStr) . substr($questionIdStr, 0, -5) . ".html";
    }

    public function sUrl($subjectId, $shijuanId) {//试卷编号加密
        $shijuanIdStr = sprintf("%010d", $shijuanId);
        return $this->getUriBySubjectId($subjectId) . "s/" . substr($shijuanIdStr, 0, 5) . md5($this->key . "_" . $this->iv . "q_" . $subjectId . "_" . $shijuanId . "_" . $shijuanIdStr) . substr($shijuanIdStr, 0, -5) . ".html";
    }

    public function qId($uri) {//获取试题id
        $uri = explode("/q/", $uri);
        $subjectId = $this->getSubjectIdByUri($uri[0]);
        if (is_int($subjectId)) {
            $questionStr = explode(".", $uri[1]);
            $questionStr = $questionStr[0];
            $questionId = (int) (substr($questionStr, 0, 5) . substr($questionStr, 0, -5));
            if (md5($this->key . "_" . $this->iv . "s_" . $subjectId . "_" . $questionId . "_" . substr($questionStr, 0, 5) . substr($questionStr, 0, -5)) === substr($questionStr, 6, 32)) {
                return $questionId;
            }
        }
        return -1;
    }

    public function sId($uri) {//获取试卷id
        $uri = explode("/s/", $uri);
        $subjectId = $this->getSubjectIdByUri($uri[0]);
        if (is_int($subjectId)) {
            $shijuanStr = explode(".", $uri[1]);
            $shijuanStr = $shijuanStr[0];
            $shijuanId = (int) (substr($shijuanStr, 0, 5) . substr($shijuanStr, 0, -5));
            if (md5($this->key . "_" . $this->iv . "q_" . $subjectId . "_" . $shijuanId . "_" . substr($shijuanStr, 0, 5) . substr($shijuanStr, 0, -5)) === substr($shijuanStr, 6, 32)) {
                return $shijuanId;
            }
        }
        return -1;
    }

    public function getUriBySubjectId($subjectId) {
        switch ($subjectId) {
            case 1:$uri = "/xiaoxue/yuwen/";break;//语文
            case 2:$uri = "/xiaoxue/shuxue/";break;//数学
            case 3:$uri = "/xiaoxue/yingyu/";break;//英语
            case 4:$uri = "/xiaoxue/kexue/";break;//科学
            case 5:$uri = "/xiaoxue/daodefazhi/";break;//道德与法治
            case 6:$uri = "/chuzhong/yuwen/";break;//语文
            case 7:$uri = "/chuzhong/shuxue/";break;//数学
            case 8:$uri = "/chuzhong/yingyu/";break;//英语
            case 9:$uri = "/chuzhong/wuli/";break;//物理	
            case 10:$uri = "/chuzhong/huaxue/";break;//化学	
            case 11:$uri = "/chuzhong/shengwu/";break;//生物	
            case 12:$uri = "/chuzhong/dili/";break;//地理		
            case 13:$uri = "/chuzhong/daodefazhi/";break;//道德与法治	
            case 14:$uri = "/chuzhong/lishi/";break;//历史	
            case 15:$uri = "/chuzhong/lishishehui/";break;//历史与社会	
            case 16:$uri = "/chuzhong/kexue/";break;//科学
            case 17:$uri = "/chuzhong/xinxijishu/";break;//信息技术
            case 18:$uri = "/gaozhong/yuwen/";break;//语文	
            case 19:$uri = "/gaozhong/shuxue/";break;//数学	
            case 20:$uri = "/gaozhong/yingyu/";break;//英语		
            case 21:$uri = "/gaozhong/wuli/";break;//物理		
            case 22:$uri = "/gaozhong/huaxue/";break;//化学
            case 23:$uri = "/gaozhong/shengwu/";break;//生物
            case 24:$uri = "/gaozhong/zhengzhi/";break;//政治	
            case 25:$uri = "/gaozhong/lishi/";break;//历史	
            case 26:$uri = "/gaozhong/dili/";break;//地理		
            case 27:$uri = "/gaozhong/xinxijishu/";break;//信息技术		
            case 28:$uri = "/gaozhong/tongyongjishu/";break;//通用技术		
            case 29:$uri = "/xiaoxue/aoshu/";break;//小学奥数	
            default :$uri = -1;
        }
        return $uri;
    }

    public function getSubjectIdByUri($uri) {
        switch ($uri) {
            case "/xiaoxue/yuwen/":$subjectId = 1;break;//语文
            case "/xiaoxue/shuxue/":$subjectId = 2;break;//数学
            case "/xiaoxue/yingyu/":$subjectId = 3;break;//英语
            case "/xiaoxue/kexue/":$subjectId = 4;break;//科学
            case "/xiaoxue/daodefazhi/":$subjectId = 5;break;//道德与法治
            case "/chuzhong/yuwen/":$subjectId = 6;break;//语文
            case "/chuzhong/shuxue/":$subjectId = 7;break;//数学
            case "/chuzhong/yingyu/":$subjectId = 8;break;//英语
            case "/chuzhong/wuli/":$subjectId = 9;break;//物理	
            case "/chuzhong/huaxue/":$subjectId = 10;break;//化学	
            case "/chuzhong/shengwu/":$subjectId = 11;break;//生物	
            case "/chuzhong/dili/":$subjectId = 12;break;//地理		
            case "/chuzhong/daodefazhi/":$subjectId = 13;break;//道德与法治	
            case "/chuzhong/lishi/":$subjectId = 14;break;//历史	
            case "/chuzhong/lishishehui/":$subjectId = 15;break;//历史与社会	
            case "/chuzhong/kexue/":$subjectId = 16;break;//科学
            case "/chuzhong/xinxijishu/":$subjectId = 17;break;//信息技术
            case "/gaozhong/yuwen/":$subjectId = 18;break;//语文	
            case "/gaozhong/shuxue/":$subjectId = 19;break;//数学	
            case "/gaozhong/yingyu/":$subjectId = 20;break;//英语		
            case "/gaozhong/wuli/":$subjectId = 21;break;//物理		
            case "/gaozhong/huaxue/":$subjectId = 22;break;//化学
            case "/gaozhong/shengwu/":$subjectId = 23;break;//生物
            case "/gaozhong/zhengzhi/":$subjectId = 24;break;//政治	
            case "/gaozhong/lishi/":$subjectId = 25;break;//历史	
            case "/gaozhong/dili/":$subjectId = 26;break;//地理		
            case "/gaozhong/xinxijishu/":$subjectId = 27;break;//信息技术		
            case "/gaozhong/tongyongjishu/":$subjectId = 28;break;//通用技术		
            case "/xiaoxue/aoshu/":$subjectId = 29;break;//小学奥数
            default :$subjectId = -1;
        }
        return $subjectId;
    }

    public function forceDirectory($dir) {//创建目录
        return is_dir($dir) or ( self::forceDirectory(dirname($dir)) and mkdir($dir, 0777));
    }

    protected function getIdFromString($str) {
        $id = "";
        $str = substr($str, -10);
        for ($i = 0; $i < strlen($str); $i++) {
            if (is_numeric($str[$i])) {
                $id .= $str[$i];
            }
        }
        return $id;
    }

}
