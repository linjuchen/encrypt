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

    //迷惑信息  d-none
    public function str_replace_limit($search, $replace, $subject, $limit = -1)
    {
        if (is_array($search)) {
            foreach ($search as $k => $v) {
                $search[$k] = '`'.preg_quote($search[$k], '`').'`';
            }
        } else {
            $search = '`'.preg_quote($search, '`').'`';
        }

        return preg_replace($search, $replace, $subject, $limit);
    }

    public function highlight_html(string $str, string $keyWord): string
    {
        $kw = [];
        $word = '';
        for ($i = 0; $i < mb_strlen($keyWord); ++$i) {
            if (1 != strlen(mb_substr($keyWord, $i, 1))) {
                $kw[] = mb_substr($keyWord, $i, 1);
                $word = '';
            } elseif (' ' !== mb_substr($keyWord, $i, 1)) {
                $word .= mb_substr($keyWord, $i, 1);
                if($i==(mb_strlen($keyWord)-1)){
                    $kw[] = $word;
                }
            } elseif (' ' === mb_substr($keyWord, $i, 1)) {
                $kw[] = $word;
                $word = '';
            }
        }
        $keyWord = implode('|', $kw);

        return preg_replace_callback('#((?:(?!<[/a-z]).)*)([^>]*>|$)#si', function ($m) use ($keyWord) {
            return preg_replace('~('.$keyWord.')~i', '<span style="background:#fff330">$1</span>', $m[1]).$m[2];
        }, $str);
    }

    public function encode($text)
    {
        $encrypted = openssl_encrypt($text, 'aes-256-cbc', base64_decode($this->key), OPENSSL_RAW_DATA, base64_decode($this->iv));

        return [urlencode(base64_encode($encrypted)), md5($this->iv.'+_+'.$text)];
    }

    public function newQBody(string $body, int $qNum): string
    {
        $body = str_replace('【题文】', '', $body);
        for ($i = 1; $i <= $qNum; ++$i) {
            $body = str_replace(['【小题'.$i.'】'], ['[[QOrDeR]]'], $body);
        }

        return $this->replaceBody($body);
    }

    public function singleQBody(string $body, int $qnum): string
    {
        if ($qnum > 1) {
            $cfCount = substr_count($body, '[[QOrDeR]]');
            $index = 1;
            for ($i = 1; $i <= $cfCount; ++$i) {
                $body = $this->str_replace_limit('[[QOrDeR]]', $index.'．', $body, 1);
                if ($i < $cfCount) {
                    ++$index;
                }
            }
        }

        return $body;
    }

    public function replaceBody(string $body): string
    {
        $body = preg_replace('/(.*?)(alt=["\']?.*?["\']?)(\s+|>)/', '$1$3', $body); 
        $body = str_replace(['<font>'], ['<span class="dot">'], $body);
        $body = str_replace(['`'], ["'"], $body);
        $body = str_replace(['<table name="optionsTable"'], ['<table name="XZoptions"'], $body);
        $body = str_replace(['</font>'], ['</span>'], $body);
        $body = str_replace(['中.考.资.源.网','www.zk5u.com中考资源网','www.zk5u.com中%考资源网','中%考资源网','中考资源网','www.zk5u.com','w.w.^w.k.&s.5*u.c.#om','高☆考♂资♀源*网','<sub>高☆考♂资♀源*网</sub>','高☆考♂资♀源*网高☆考♂资♀源*网','<sub>高☆考♂资♀源*网高☆考♂资♀源*网</sub>','高☆考♂资♀源€网','[来源:高&考%资(源#网]','[来源:高&考%资(源#网KS5U.COM]','高*考*资*源*网','高.考.资.源.网','来源：高考资源网','高￥考^资@源*网','高*考#资^源*网','高考资源网','[来源:学*科*网Z*X*X*K]','[来源:Z&#167;xx&#167;k．Com]','[来源:学。科。网Z。X。X。K]','[来源:Z,xx,k','[来源:Z&xx&k.Com]','[来源:Z*xx*k.Com]','W W W K S 5 U . C O M','[来源:Z。xx。k.Com]','[来源:Z+xx+k.Com]','[来源:Z|xx|k.Com]','[:Z+xx+k.Com]','[来源:学&#167;科&#167;网Z&#167;X&#167;X&#167;K]','[来源:Z&#167;xx&#167;k.Com]','[来源:Z#xx#k.','K^S*5U','K|S|5U','k*s*5u','Ks**5u','Ks*5u','ks**5u','ks*5u','<sup> ks*5*u</sup>','*ks*5*u','Ks*5*u','k^s*5#u','k*s*5*u','K*s^5#u','k!s#5^u','k*s#5^u','KS5U','ks5u','<sub>ks5u</sub><br>','<sub>ks5uks5ukks5u</sub><br>','ks5*','ks5<br>u','Ks5','ks5ks','ks5*u','Ks5*u','ks5*u*','学科网', 'ZXXK', 'Zxxk', 'zxxk', 'ZXXK]', '【来源：学科网ZXXK】', '[来源:Zxxk.', '<u>来源:学科网ZXXK]</u>', '<sub>zxxk</sub><br>', '学科网ZXXK]', '[来源：学科网ZXXK]', '<i>来源:学科网ZXXK]</i>', '网ZXXK]', 'ZXXK]', '（来源：Zxxk．Com）', '[来源:Zxxk.Com]', '[来源:学+（&#160;&#160;&#160;）科+网Z+X+X+K]', '学科网[来源:Zxxk.Com]', '<br>学科网<br>', '<u>学科网</u>', '[来源:学+科+网Z+X+X+K]', '[来源:学。科。网Z。', '[来源:学科网]', '[来源:学科网', '[来源:学科', '来源:学|科|网]', '[来源:学_科_网Z_X_X_K]', '（来源：学#科#网）', '[来源:学_科_网]', '源:学_科_网]', '来源:学_科_网]', '[来源:学|科|网', '[来源:学,科,网Z,X,X,K]', '[来源:学&科&网Z&X&X&K]', '[来源:学*科*网Z*X*X*K]', '[来源:学&科&网]', '[来源:学.科.网]', '[来源:学#科#网Z#X#X#K]', '[来源:学,科,网]', '[来源:学。科。网]', '[来源:学*科*网]', '来源:学&#167;科&#167;网]', '学-科网', '来源:学.科.网Z.X.X.K', '学科+网', '[来源:学&#167;科&#167;网]', '[来源:学科网ZXXK]', '[来源:学科网]', '[来源:学#科#网]', '[来源:学+科+网]', '[来源:学|科|网Z|X|X|K]', '[来源:学|科|网]', '[来源:学.科.网Z.X.X.K]', '学！科网'], [''], $body);
        return $body;
    }

    public function decode($text, $hash)
    {
        $text = openssl_decrypt(base64_decode(urldecode($text)), 'aes-256-cbc', base64_decode($this->key), OPENSSL_RAW_DATA, base64_decode($this->iv));
        if (md5($this->iv.'+_+'.$text) !== $hash) {
            return 0;
        }

        return $text;
    }
    public function getKpIdByQUrl(string $hash):int{
        return $this->idD($hash,"kq");
    }
    public function getChapterIdByQUrl(string $hash):int{
        return $this->idD($hash,"cq");
    }
    public function getKpIdByPUrl(string $hash):int{
        return $this->idD($hash,"kp");
    }
    public function getChapterIdByPUrl(string $hash):int{
        return $this->idD($hash,"cp");
    }
    public function kpPUrl(int $subjectId, int $kpId)//知识点对应试卷
    {//知识点对应的url
        if($kpId===0){
            return '/kp'.$this->getUriBySubjectId($subjectId);
        }
        else{
            return '/kp'.$this->getUriBySubjectId($subjectId).$this->idE($kpId,"kp")."/";
        }
    }
    public function kpQUrl(int $subjectId, int $kpId)//知识点对应试题
    {//知识点对应的url
        if($kpId===0){
            return '/kq'.$this->getUriBySubjectId($subjectId);
        }
        else{
            return '/kq'.$this->getUriBySubjectId($subjectId).$this->idE($kpId,"kq")."/";
        }
    }
    public function cUrl(int $subjectId, int $cId)
    {//章节对应的url
        $cIdStr = sprintf('%010d', $cId);

        return '/c'.$this->getUriBySubjectId($subjectId).substr($cIdStr, 0, 5).'/'.md5($subjectId.'_'.$cId.'c_c'.$subjectId.'_'.$cId.'_'.$cIdStr).substr($cIdStr, -5).'.html';
    }

    public function qUrl($subjectId, $questionId)
    {//题目编号加密
        $questionIdStr = sprintf('%010d', $questionId);

        return '/q'.$this->getUriBySubjectId($subjectId).substr($questionIdStr, 0, 5).'/'.md5($subjectId.'_'.$questionId.'q_q'.$subjectId.'_'.$questionId.'_'.$questionIdStr).substr($questionIdStr, -5).'.html';
    }

    public function pUrl($subjectId, $shijuanId)
    {//试卷编号加密
        $shijuanIdStr = sprintf('%010d', $shijuanId);

        return '/p'.$this->getUriBySubjectId($subjectId).substr($shijuanIdStr, 0, 5).'/'.md5($subjectId.'_'.$shijuanId.'p_p'.$subjectId.'_'.$shijuanId.'_'.$shijuanIdStr).substr($shijuanIdStr, -5).'.html';
    }

    public function cpUrl($subjectId, $shijuanId)
    {//组卷试卷编号加密
        $shijuanIdStr = sprintf('%010d', $shijuanId);

        return '/p'.$this->getUriBySubjectId($subjectId).substr($shijuanIdStr, 0, 5).'/'.md5($subjectId.'_'.$shijuanId.'cp_cp'.$subjectId.'_'.$shijuanId.'_'.$shijuanIdStr).substr($shijuanIdStr, -5).'.html';
    }

    public function anyQorP(string $uri, string $type): int
    {
        $uri = explode('/', $uri);
        if (6 === count($uri) && empty($uri[0]) && $uri[1] === $type) {
            $subjectId = $this->getSubjectIdByUri('/'.$uri[2].'/'.$uri[3].'/');
            if (is_int($subjectId)) {
                $strArr = explode('.', $uri[5]);
                $str = $strArr[0]; //去掉.html
                $id = (int) ($uri[4].substr($str, -5));
                if (md5($subjectId.'_'.$id.$type.'_'.$type.$subjectId.'_'.$id.'_'.$uri[4].substr($str, -5)) === substr($str, 0, 32)) {
                    return $id;
                }
            }
        }

        return 0;
    }

    public function qId($uri): int
    {//获取试题id
        return $this->anyQorP($uri, 'q');
    }

    public function pId($uri): int
    {//获取试卷id
        return $this->anyQorP($uri, 'p');
    }

    public function cpId($uri): int
    {//组卷获取试卷id
        return $this->anyQorP($uri, 'cp');
    }

    public function getUriBySubjectId($subjectId): string
    {
        switch ($subjectId) {
            case 1:
                $uri = '/xiaoxue/yuwen/';
                break; //语文
            case 2:
                $uri = '/xiaoxue/shuxue/';
                break; //数学
            case 3:
                $uri = '/xiaoxue/yingyu/';
                break; //英语
            case 4:
                $uri = '/xiaoxue/kexue/';
                break; //科学
            case 5:
                $uri = '/xiaoxue/daodefazhi/';
                break; //道德与法治
            case 6:
                $uri = '/chuzhong/yuwen/';
                break; //语文
            case 7:
                $uri = '/chuzhong/shuxue/';
                break; //数学
            case 8:
                $uri = '/chuzhong/yingyu/';
                break; //英语
            case 9:
                $uri = '/chuzhong/wuli/';
                break; //物理
            case 10:
                $uri = '/chuzhong/huaxue/';
                break; //化学
            case 11:
                $uri = '/chuzhong/shengwu/';
                break; //生物
            case 12:
                $uri = '/chuzhong/dili/';
                break; //地理
            case 13:
                $uri = '/chuzhong/daodefazhi/';
                break; //道德与法治
            case 14:
                $uri = '/chuzhong/lishi/';
                break; //历史
            case 15:
                $uri = '/chuzhong/lishishehui/';
                break; //历史与社会
            case 16:
                $uri = '/chuzhong/kexue/';
                break; //科学
            case 17:
                $uri = '/chuzhong/xinxijishu/';
                break; //信息技术
            case 18:
                $uri = '/gaozhong/yuwen/';
                break; //语文
            case 19:
                $uri = '/gaozhong/shuxue/';
                break; //数学
            case 20:
                $uri = '/gaozhong/yingyu/';
                break; //英语
            case 21:
                $uri = '/gaozhong/wuli/';
                break; //物理
            case 22:
                $uri = '/gaozhong/huaxue/';
                break; //化学
            case 23:
                $uri = '/gaozhong/shengwu/';
                break; //生物
            case 24:
                $uri = '/gaozhong/zhengzhi/';
                break; //政治
            case 25:
                $uri = '/gaozhong/lishi/';
                break; //历史
            case 26:
                $uri = '/gaozhong/dili/';
                break; //地理
            case 27:
                $uri = '/gaozhong/xinxijishu/';
                break; //信息技术
            case 28:
                $uri = '/gaozhong/tongyongjishu/';
                break; //通用技术
            case 29:
                $uri = '/xiaoxue/aoshu/';
                break; //小学奥数
            default:
                $uri = '';
        }

        return $uri;
    }

    public function getSubjectIdByUri($uri): int
    {
        switch ($uri) {
            case '/xiaoxue/yuwen/':
                $subjectId = 1;
                break; //语文
            case '/xiaoxue/shuxue/':
                $subjectId = 2;
                break; //数学
            case '/xiaoxue/yingyu/':
                $subjectId = 3;
                break; //英语
            case '/xiaoxue/kexue/':
                $subjectId = 4;
                break; //科学
            case '/xiaoxue/daodefazhi/':
                $subjectId = 5;
                break; //道德与法治
            case '/chuzhong/yuwen/':
                $subjectId = 6;
                break; //语文
            case '/chuzhong/shuxue/':
                $subjectId = 7;
                break; //数学
            case '/chuzhong/yingyu/':
                $subjectId = 8;
                break; //英语
            case '/chuzhong/wuli/':
                $subjectId = 9;
                break; //物理
            case '/chuzhong/huaxue/':
                $subjectId = 10;
                break; //化学
            case '/chuzhong/shengwu/':
                $subjectId = 11;
                break; //生物
            case '/chuzhong/dili/':
                $subjectId = 12;
                break; //地理
            case '/chuzhong/daodefazhi/':
                $subjectId = 13;
                break; //道德与法治
            case '/chuzhong/lishi/':
                $subjectId = 14;
                break; //历史
            case '/chuzhong/lishishehui/':
                $subjectId = 15;
                break; //历史与社会
            case '/chuzhong/kexue/':
                $subjectId = 16;
                break; //科学
            case '/chuzhong/xinxijishu/':
                $subjectId = 17;
                break; //信息技术
            case '/gaozhong/yuwen/':
                $subjectId = 18;
                break; //语文
            case '/gaozhong/shuxue/':
                $subjectId = 19;
                break; //数学
            case '/gaozhong/yingyu/':
                $subjectId = 20;
                break; //英语
            case '/gaozhong/wuli/':
                $subjectId = 21;
                break; //物理
            case '/gaozhong/huaxue/':
                $subjectId = 22;
                break; //化学
            case '/gaozhong/shengwu/':
                $subjectId = 23;
                break; //生物
            case '/gaozhong/zhengzhi/':
                $subjectId = 24;
                break; //政治
            case '/gaozhong/lishi/':
                $subjectId = 25;
                break; //历史
            case '/gaozhong/dili/':
                $subjectId = 26;
                break; //地理
            case '/gaozhong/xinxijishu/':
                $subjectId = 27;
                break; //信息技术
            case '/gaozhong/tongyongjishu/':
                $subjectId = 28;
                break; //通用技术
            case '/xiaoxue/aoshu/':
                $subjectId = 29;
                break; //小学奥数
            default:
                $subjectId = -1;
        }

        return $subjectId;
    }

    public function forceDirectory($dir)
    {//创建目录
        return is_dir($dir) or (self::forceDirectory(dirname($dir)) and mkdir($dir, 0777));
    }

    protected function getIdFromString($str)
    {
        $id = '';
        $str = substr($str, -10);
        for ($i = 0; $i < strlen($str); ++$i) {
            if (is_numeric($str[$i])) {
                $id .= $str[$i];
            }
        }

        return $id;
    }

    public function picUrl(int $id): string
    {
        $filePath = '/img/'.sprintf('%03d', $id / 999).'/'.sprintf('%03d', $id / 666).'/'.sprintf('%03d', $id / 333).'/';

        return $filePath.$id.'.png';
    }

    public function mp3Url(int $id): string
    {
        $filePath = '/mp3/'.sprintf('%03d', $id / 999).'/'.sprintf('%03d', $id / 666).'/'.sprintf('%03d', $id / 333).'/';

        return $filePath.$id.'.mp3';
    }

    public function idE(int $id,string $key='XJQ'): string
    {
        $hash = md5($id.$key);
        $mod = 0 === $id % 10 ? 1 : $id % 10;

        return substr($hash, 0, $mod).$id.$mod;
    }

    public function idD(string $str,string $key='XJQ'): int
    {
        $mod = (int) substr($str, -1);
        $id = (int) substr($str, $mod, strlen($str) - $mod - 1);
        $hash = md5($id.$key);
        if (substr($hash, 0, $mod) !== substr($str, 0, $mod)) {
            $id = 0;
        }

        return $id;
    }

    /**
     * 汉字笔画笔顺动图.
     */
    public function getHanZiBiHShun(int $id): string
    {
        return '/bishun/'.sprintf('%03d', $id / 999).'/'.sprintf('%03d', $id / 666).'/'.sprintf('%03d', $id / 333).'/'.$id.'.gif';
    }

    /**
     * 汉字笔画笔顺静态图.
     */
    public function getHanZiBiHShunSvg(int $id): string
    {
        return '/bishun/'.sprintf('%03d', $id / 999).'/'.sprintf('%03d', $id / 666).'/'.sprintf('%03d', $id / 333).'/'.$id.'.svg';
    }

    /**
     * 替换特殊字符.
     */
    public function replace_specialChar(string $strParam): string
    {
        $regex = "/\/|\～|\，|\。|\！|\？|\“|\”|\【|\】|\『|\』|\：|\；|\《|\》|\’|\‘|\ |\·|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";

        return preg_replace($regex, ' ', $strParam);
    }
    public function getXkwIdBySubjectId(int $subject_id)
    {
        $arr=[1=>24,2=>23,3=>25,4=>32,5=>31,6=>1,7=>2,8=>3,9=>4,10=>5,11=>6,12=>9,13=>7,14=>8,15=>30,16=>26,17=>29,18=>10,19=>11,20=>12,21=>13,22=>14,23=>15,24=>16,25=>17,26=>18,27=>27,28>28];
        return $arr[$subject_id]??0;
    }

    public function getPicBase64ByPicFile(string $file):string
    {
        $base64_file = '';
        if(file_exists($file)){
            $hand = fopen($file,"rb");
            $content = fread($hand,filesize($file));
            $base64_file = base64_encode($content);
            //$base64_file = str_replace("\\",'',$base64_file);
            //添加base64文件头
            $pos = stripos($base64_file,"base64");
            if($pos && $pos < 50){
                if(substr($base64_file,($pos + strlen("base64")),1) == ","){
                    $pos = $pos + strlen("base64") + 1;
                }else{
                    $pos = $pos + strlen("base64");
                }
            }else{
                $pos = 0;
            }
            $base64_file = "data:image/png;base64," . substr($base64_file,$pos);
        }
        return $base64_file;
    }

    function setKpTree(int $subjectId,array $arr){                                           
        if (!empty($arr['children'])) {        
            echo '<li class="tree-node tree-children"><div class="tree-node-name"> <i class="tree-icon"></i><a href="'.$this->kpQUrl($subjectId, $arr['id']).'" class="tree-anchor font-item" title="'.$arr['name'].'">'.$arr['name'].'</a></div>';                                                
            echo '<ul class="tree-ul">';
            foreach ($arr['children'] as $av) {
                $this->setKpTree($subjectId,$av);
            }
            echo '</ul>';
            echo '</li>';
        } else {
        echo '<li class="tree-node tree-leaf"><div class="tree-node-name"><i class="tree-icon"></i><a href="'.$this->kpQUrl($subjectId, $arr['id']).'" class="tree-anchor font-item" title="'.$arr['name'].'">'.$arr['name'].'</a></div></li>';
        }                                          
    }

    function setChapterTree(string $url,array $arr,int $cpId){           
        if($cpId === $arr['id']){
            $tree_status=" tree-open";
        }
        else{
            $tree_status = "";
        }                                
        if (!empty($arr['children'])) {            
            echo '<li class="tree-node tree-children'.$tree_status.'"><div class="tree-node-name"> <i class="tree-icon"></i><a href="'.$url.$this->idE($arr['id'],"cq").'/" class="tree-anchor font-item" title="'.$arr['name'].'">'.$arr['name'].'</a></div>';                                                
            echo '<ul class="tree-ul">';
            foreach ($arr['children'] as $av) {
                $this->setChapterTree($url,$av,$cpId);
            }
            echo '</ul>';
            echo '</li>';
        } else {
        echo '<li class="tree-node tree-leaf'.$tree_status.'"><div class="tree-node-name"><i class="tree-icon"></i><a href="'.$url.$this->idE($arr['id'],"cq").'/" class="tree-anchor font-item" title="'.$arr['name'].'">'.$arr['name'].'</a></div></li>';
        }                                          
    }
}
