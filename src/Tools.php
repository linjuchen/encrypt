<?php

namespace Cmmia\Encrypt;

class Tools
{

    private $key;
    private $iv;

    public function __construct($key = '', $iv = '')
    {
        $this->key = $key;
        $this->iv = $iv;
    }

    public function encode($text)
    {
        $encrypted = openssl_encrypt($text, 'aes-256-cbc', base64_decode($this->key), OPENSSL_RAW_DATA, base64_decode($this->iv));
        return array(urlencode(base64_encode($encrypted)), md5($this->iv . "+_+" . $text));
    }

    public function newQBody(string $body, int $qNum): string
    {
        $body = str_replace("【题文】", "", $body);
        for ($i = 1; $i <= $qNum; $i++) {
            $body = str_replace(array("【小题" . $i . "】"), array("[[QOrDeR]]"), $body);
        }
        return $body;
    }

    public function decode($text, $hash)
    {
        $text = openssl_decrypt(base64_decode(urldecode($text)), 'aes-256-cbc', base64_decode($this->key), OPENSSL_RAW_DATA, base64_decode($this->iv));
        if (md5($this->iv . "+_+" . $text) !== $hash) {
            return 0;
        }
        return $text;
    }

    public function kpUrl(int $subjectId, int $kpId)
    {//知识点对应的url
        $kpIdStr = sprintf("%010d", $kpId);
        return "/k" . $this->getUriBySubjectId($subjectId) . substr($kpIdStr, 0, 5) . "/" . md5($subjectId . "_" . $kpId . "k_k" . $subjectId . "_" . $kpId . "_" . $kpIdStr) . substr($kpIdStr, -5) . ".html";
    }

    public function cUrl(int $subjectId, int $cId)
    {//章节对应的url
        $cIdStr = sprintf("%010d", $cId);
        return "/c" . $this->getUriBySubjectId($subjectId) . substr($cIdStr, 0, 5) . "/" . md5($subjectId . "_" . $cId . "c_c" . $subjectId . "_" . $cId . "_" . $cIdStr) . substr($cIdStr, -5) . ".html";
    }

    public function qUrl($subjectId, $questionId)
    {//题目编号加密
        $questionIdStr = sprintf("%010d", $questionId);
        return "/q" . $this->getUriBySubjectId($subjectId) . substr($questionIdStr, 0, 5) . "/" . md5($subjectId . "_" . $questionId . "q_q" . $subjectId . "_" . $questionId . "_" . $questionIdStr) . substr($questionIdStr, -5) . ".html";
    }

    public function pUrl($subjectId, $shijuanId)
    {//试卷编号加密
        $shijuanIdStr = sprintf("%010d", $shijuanId);
        return "/p" . $this->getUriBySubjectId($subjectId) . substr($shijuanIdStr, 0, 5) . "/" . md5($subjectId . "_" . $shijuanId . "p_p" . $subjectId . "_" . $shijuanId . "_" . $shijuanIdStr) . substr($shijuanIdStr, -5) . ".html";
    }

    public function cpUrl($subjectId, $shijuanId)
    {//组卷试卷编号加密
        $shijuanIdStr = sprintf("%010d", $shijuanId);
        return "/cp" . $this->getUriBySubjectId($subjectId) . substr($shijuanIdStr, 0, 5) . "/" . md5($subjectId . "_" . $shijuanId . "cp_cp" . $subjectId . "_" . $shijuanId . "_" . $shijuanIdStr) . substr($shijuanIdStr, -5) . ".html";
    }

    public function anyQorP(string $uri, string $type): int
    {
        $uri = explode("/", $uri);
        if (count($uri) === 6 && empty($uri[0]) && $uri[1] === $type) {
            $subjectId = $this->getSubjectIdByUri("/" . $uri[2] . "/" . $uri[3] . "/");
            if (is_int($subjectId)) {
                $strArr = explode(".", $uri[5]);
                $str = $strArr[0];//去掉.html
                $id = (int)($uri[4] . substr($str, -5));
                if (md5($subjectId . "_" . $id . $type . "_" . $type . $subjectId . "_" . $id . "_" . $uri[4] . substr($str, -5)) === substr($str, 0, 32)) {
                    return $id;
                }
            }
        }
        return 0;
    }

    public function qId($uri): int
    {//获取试题id
        return $this->anyQorP($uri, "q");
    }

    public function pId($uri): int
    {//获取试卷id
        return $this->anyQorP($uri, "p");
    }

    public function cpId($uri): int
    {//组卷获取试卷id
        return $this->anyQorP($uri, "cp");
    }

    public function getUriBySubjectId($subjectId): string
    {
        switch ($subjectId) {
            case 1:
                $uri = "/xiaoxue/yuwen/";
                break;//语文
            case 2:
                $uri = "/xiaoxue/shuxue/";
                break;//数学
            case 3:
                $uri = "/xiaoxue/yingyu/";
                break;//英语
            case 4:
                $uri = "/xiaoxue/kexue/";
                break;//科学
            case 5:
                $uri = "/xiaoxue/daodefazhi/";
                break;//道德与法治
            case 6:
                $uri = "/chuzhong/yuwen/";
                break;//语文
            case 7:
                $uri = "/chuzhong/shuxue/";
                break;//数学
            case 8:
                $uri = "/chuzhong/yingyu/";
                break;//英语
            case 9:
                $uri = "/chuzhong/wuli/";
                break;//物理
            case 10:
                $uri = "/chuzhong/huaxue/";
                break;//化学
            case 11:
                $uri = "/chuzhong/shengwu/";
                break;//生物
            case 12:
                $uri = "/chuzhong/dili/";
                break;//地理
            case 13:
                $uri = "/chuzhong/daodefazhi/";
                break;//道德与法治
            case 14:
                $uri = "/chuzhong/lishi/";
                break;//历史
            case 15:
                $uri = "/chuzhong/lishishehui/";
                break;//历史与社会
            case 16:
                $uri = "/chuzhong/kexue/";
                break;//科学
            case 17:
                $uri = "/chuzhong/xinxijishu/";
                break;//信息技术
            case 18:
                $uri = "/gaozhong/yuwen/";
                break;//语文
            case 19:
                $uri = "/gaozhong/shuxue/";
                break;//数学
            case 20:
                $uri = "/gaozhong/yingyu/";
                break;//英语
            case 21:
                $uri = "/gaozhong/wuli/";
                break;//物理
            case 22:
                $uri = "/gaozhong/huaxue/";
                break;//化学
            case 23:
                $uri = "/gaozhong/shengwu/";
                break;//生物
            case 24:
                $uri = "/gaozhong/zhengzhi/";
                break;//政治
            case 25:
                $uri = "/gaozhong/lishi/";
                break;//历史
            case 26:
                $uri = "/gaozhong/dili/";
                break;//地理
            case 27:
                $uri = "/gaozhong/xinxijishu/";
                break;//信息技术
            case 28:
                $uri = "/gaozhong/tongyongjishu/";
                break;//通用技术
            case 29:
                $uri = "/xiaoxue/aoshu/";
                break;//小学奥数
            default :
                $uri = "";
        }
        return $uri;
    }

    public function getSubjectIdByUri($uri): int
    {
        switch ($uri) {
            case "/xiaoxue/yuwen/":
                $subjectId = 1;
                break;//语文
            case "/xiaoxue/shuxue/":
                $subjectId = 2;
                break;//数学
            case "/xiaoxue/yingyu/":
                $subjectId = 3;
                break;//英语
            case "/xiaoxue/kexue/":
                $subjectId = 4;
                break;//科学
            case "/xiaoxue/daodefazhi/":
                $subjectId = 5;
                break;//道德与法治
            case "/chuzhong/yuwen/":
                $subjectId = 6;
                break;//语文
            case "/chuzhong/shuxue/":
                $subjectId = 7;
                break;//数学
            case "/chuzhong/yingyu/":
                $subjectId = 8;
                break;//英语
            case "/chuzhong/wuli/":
                $subjectId = 9;
                break;//物理
            case "/chuzhong/huaxue/":
                $subjectId = 10;
                break;//化学
            case "/chuzhong/shengwu/":
                $subjectId = 11;
                break;//生物
            case "/chuzhong/dili/":
                $subjectId = 12;
                break;//地理
            case "/chuzhong/daodefazhi/":
                $subjectId = 13;
                break;//道德与法治
            case "/chuzhong/lishi/":
                $subjectId = 14;
                break;//历史
            case "/chuzhong/lishishehui/":
                $subjectId = 15;
                break;//历史与社会
            case "/chuzhong/kexue/":
                $subjectId = 16;
                break;//科学
            case "/chuzhong/xinxijishu/":
                $subjectId = 17;
                break;//信息技术
            case "/gaozhong/yuwen/":
                $subjectId = 18;
                break;//语文
            case "/gaozhong/shuxue/":
                $subjectId = 19;
                break;//数学
            case "/gaozhong/yingyu/":
                $subjectId = 20;
                break;//英语
            case "/gaozhong/wuli/":
                $subjectId = 21;
                break;//物理
            case "/gaozhong/huaxue/":
                $subjectId = 22;
                break;//化学
            case "/gaozhong/shengwu/":
                $subjectId = 23;
                break;//生物
            case "/gaozhong/zhengzhi/":
                $subjectId = 24;
                break;//政治
            case "/gaozhong/lishi/":
                $subjectId = 25;
                break;//历史
            case "/gaozhong/dili/":
                $subjectId = 26;
                break;//地理
            case "/gaozhong/xinxijishu/":
                $subjectId = 27;
                break;//信息技术
            case "/gaozhong/tongyongjishu/":
                $subjectId = 28;
                break;//通用技术
            case "/xiaoxue/aoshu/":
                $subjectId = 29;
                break;//小学奥数
            default :
                $subjectId = -1;
        }
        return $subjectId;
    }

    public
    function forceDirectory($dir)
    {//创建目录
        return is_dir($dir) or (self::forceDirectory(dirname($dir)) and mkdir($dir, 0777));
    }

    protected
    function getIdFromString($str)
    {
        $id = "";
        $str = substr($str, -10);
        for ($i = 0; $i < strlen($str); $i++) {
            if (is_numeric($str[$i])) {
                $id .= $str[$i];
            }
        }
        return $id;
    }

    public
    function picUrl(int $id): string
    {
        $filePath = "/img/" . sprintf("%03d", $id / 999) . "/" . sprintf("%03d", $id / 666) . "/" . sprintf("%03d", $id / 333) . "/";
        return $filePath . $id . ".png";
    }

    public
    function mp3Url(int $id): string
    {
        $filePath = "/mp3/" . sprintf("%03d", $id / 999) . "/" . sprintf("%03d", $id / 666) . "/" . sprintf("%03d", $id / 333) . "/";
        return $filePath . $id . ".mp3";
    }

    public
    function idE(int $id): string
    {
        $hash = md5($id . "XJQ");
        $mod = $id % 10 === 0 ? 1 : $id % 10;
        return substr($hash, 0, $mod) . $id . $mod;
    }

    public
    function idD(string $str): int
    {
        $mod = (int)substr($str, -1);
        $id = (int)substr($str, $mod, strlen($str) - $mod - 1);
        $hash = md5($id . "XJQ");
        if (substr($hash, 0, $mod) !== substr($str, 0, $mod)) {
            $id = 0;
        }
        return $id;
    }
}
