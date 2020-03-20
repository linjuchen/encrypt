<?php

namespace Cmmia\Encrypt;

/**
 * 分页
 * Class FenYe.
 */
class FenYe
{
    /**
     * @var int 数据库查询得到的总记录条数
     */
    private $totalRows;

    /**
     * @var int 网站每一页显示的列表条数
     */
    private $pageSize = 20;

    /**
     * @var string 当前页面的 URL 地址
     */
    private $route;

    /**
     * @var int 计算出来的总的页码数
     */
    private $pageAmount;

    /**
     * @var int 当前页码
     */
    private $currentPage;

    /**
     * @var int 页码的左右偏移量。比如当前页码为5，则在5的左右各显示几个数字链接，默认为3个，则效果为2,3,4,5,6,7,8
     */
    private $offset = 4;

    /**
     * @var string URL中当前页码的参数名称。通过$_GET['page']获取当前页码时候的名字，默认为page。
     *             例如：http://bidianer.com?page=2
     */
    private $pageParam = 'page';

    /**
     * @var string 当前页码链接的高亮类名
     */
    private $activeClassName = 'active';

    /**
     * @var string 首页链接的文字提示
     */
    private $indexPageLabel = '首页';

    /**
     * @var string 上一页链接的文字提示
     */
    private $prevPageLabel = '上一页';

    /**
     * @var string 下一页链接的文字提示
     */
    private $nextPageLabel = '下一页';

    /**
     * @var string 最后一页链接的文字提示
     */
    private $endPageLabel = '尾页';

    /**
     * @var string 分页链接最外层的div的类
     */
    private $class = 'btn-item';
    private $request;
    /**
     * 初始化分页类
     * 同时需要设置一些必填的参数.
     *
     * @param $param
     */
    public function __construct($param)
    {
        $this->request = context()->getRequest();
        $param['currentPage'] = (int)($this->request->get($this->pageParam,1));
        $this->getConfig($param);
        $this->checkCurrentPage();
    }

    /**
     * 获取配置.
     *
     * @param array $config
     *
     * @throws Exception
     *
     * @return void
     */
    private function getConfig($config)
    {
        if (!is_array($config)) {
            throw new Exception('配置选项必须为数组');
        }

        foreach ($config as $key => $value) {
            if (isset($config[$key]) && $config[$key]) {
                $this->$key = $value;
            }
        }
    }

    /**
     * 创建分页链接.
     *
     * @param int  $style  默认为 1 ：获取链接全部组件
     *                     $style == 2 ：仅获取数字链接
     *                     $style == 3 ：仅获取上一页，下一页
     *                     $style == 4 ：仅获取上一页、下一页、数字链接，不包含首尾页
     * @param bool $output 为TRUE时，返回分页链接
     * @param bool $output 为FALSE时，直接输出分页链接
     *
     * @return mixed
     */
    public function pagination($style = 1, $output = true)
    {
        $this->getBaseRoute();
        $outResult = [];
        //获取全部组件
        if ('1' == $style) {
            if(!empty($this->indexPage())){
                $outResult['f'][]=$this->indexPage();
            }
            if(!empty($this->prevPage())){
                $outResult['f'][]=$this->prevPage();
            }
            foreach($this->pageNumber() as $v){
                $outResult['s'][] = $v;
            }
            if(!empty($this->nextPage())){
                $outResult['t'][]=$this->nextPage();
            }
            
            if (!empty($this->endPage())) {
                $outResult['t'][]=$this->endPage();
            }
        } elseif ('2' == $style) {
            //获取纯数字链接
            $page = $this->pageNumber();
        } elseif ('3' == $style) {
            //只获取上一页下一页
            $page = $this->prevPage().$this->nextPage();
        } elseif ('4' == $style) {
            //上一页、下一页、数字链接
            $page = $this->prevPage().$this->pageNumber().$this->nextPage();
        }

        return $outResult;
    }

    /**
     * 获取每页的记录数量.
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * 获取数据库检索出来的数据总数.
     */
    public function getTotalRows()
    {
        return $this->totalRows;
    }

    /**
     * 获取当前页码
     *
     * @return int 当前页码，经过真伪判断的
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * 计算出所有的页数
     * 可以类外面直接调用此方法返回页码总数.
     *
     * @return int 页码的总数
     */
    public function getPageAmount()
    {
        $this->pageAmount = ceil($this->totalRows / $this->pageSize);
        if ($this->pageAmount <= 0) {
            $this->pageAmount = 1;
        }

        return $this->pageAmount;
    }

    /**
     * 获取用户当前URL的基准链接
     * 1、如果链接携带参数，则在链接之后加&page=
     * 2、如果不携带参数，则直接加?page=.
     *
     * @return bool
     */
    private function getBaseRoute()
    {
        $uri = $this->request->getUri();
        $queryString = $uri->getQuery()??"";
        //return $uri->getScheme() . '://' . $uri->getHost() . $uri->getPath() . $queryString;
        
        $currentUrl = $uri->getScheme() . '://' . $uri->getHost() . $uri->getPath();
        $prevUrl = $currentUrl;
        if (!$queryString) {
            $this->route = $prevUrl.'?'.$this->pageParam.'=';
            return false;
        }

        $queryArr = explode('&', $queryString);
        $paramString = [];
        foreach ($queryArr as $value) {
            $param = explode('=', $value);
            if ($param['0'] != $this->pageParam) {
                $paramString[] = implode('=', $param);
            }
        }
        $baseQueryString = implode('&', $paramString);
        if ($baseQueryString) {
            $this->route = $prevUrl.'?'.$baseQueryString.'&'.$this->pageParam.'=';
        } else {
            $this->route = $prevUrl.'?'.$this->pageParam.'=';
        }
    }

    /**
     * 验证当前页码的真伪性
     * 如果当前页码小于1或者没有，则默认当前页码为1
     * 如果当前页码大于页码总数，则默认当前页码为页码总数.
     */
    private function checkCurrentPage()
    {
        $this->getPageAmount();
        if ($this->currentPage < 1 || !$this->currentPage) {
            $this->currentPage = 1;
        } elseif ($this->currentPage > $this->pageAmount) {
            $this->currentPage = $this->pageAmount;
        }
    }

    /**
     * 获取首页链接.
     *
     * @return string|bool
     */
    private function indexPage()
    {
        if (1 == $this->currentPage) {
            return false;
        }

        return ["url"=>$this->route."1","name"=>$this->indexPageLabel];
    }

    /**
     * 获取尾页链接.
     *
     * @return bool|string
     */
    private function endPage()
    {
        if ($this->currentPage == $this->pageAmount) {
            return false;
        }
        return ["url"=>$this->route.$this->pageAmount,"name"=>$this->endPageLabel];
    }

    /**
     * 获取上一页的链接.
     *
     * @return string|bool
     */
    private function prevPage()
    {
        if (1 == $this->currentPage) {
            return false;
        }

        return ["url"=>$this->route.($this->currentPage - 1),"name"=>$this->prevPageLabel];
    }

    /**
     * 获取下一页的链接.
     *
     * @return string|bool
     */
    private function nextPage()
    {
        if ($this->currentPage == $this->pageAmount) {
            return false;
        }

        return ["url"=>$this->route.($this->currentPage + 1),"name"=>$this->nextPageLabel];
    }

    /**
     * 获取中间数字页码的链接.
     *
     * @return string
     */
    private function pageNumber()
    {
        $left = [];
        $right = [];

        // 如果总记录的条数“大于”所有链接的数量时候
        if ($this->pageAmount > ($this->offset * 2 + 1)) {
            //当前页码距离首页的距离
            $leftNum = $this->currentPage - 1;

            //当前页码距离尾页的距离
            $rightNum = $this->pageAmount - $this->currentPage;

            //当当前页码距离首页距离不足偏移量offset时候，在右边补齐缺少的小方块
            if ($leftNum < $this->offset) {
                //左边的链接
                for ($i = $leftNum; $i >= 1; --$i) {
                    $left[]= ["url"=>$this->route.($this->currentPage - $i),"name"=>($this->currentPage - $i),"isNow"=>0];
                }

                //右边的链接
                for ($j = 1; $j <= ($this->offset * 2 - $leftNum); ++$j) {
                    $right[]= ["url"=>$this->route.($this->currentPage + $j),"name"=>($this->currentPage + $j),"isNow"=>0];
                }
            } elseif ($rightNum < $this->offset) {
                //左边的链接
                for ($i = ($this->offset * 2 - $rightNum); $i >= 1; --$i) {
                    $left[]= ["url"=>$this->route.($this->currentPage - $i),"name"=>($this->currentPage - $i),"isNow"=>0];
                }

                //右边的链接
                for ($j = 1; $j <= $rightNum; ++$j) {
                    $right[]= ["url"=>$this->route.($this->currentPage + $j),"name"=>($this->currentPage + $j),"isNow"=>0];
                }
            } else {
                //当前链接左边的链接
                for ($i = $this->offset; $i >= 1; --$i) {
                    $left[]= ["url"=>$this->route.($this->currentPage - $i),"name"=>($this->currentPage - $i),"isNow"=>0];
                }

                //当前链接右边的链接
                for ($j = 1; $j <= $this->offset; ++$j) {
                    $right[]= ["url"=>$this->route.($this->currentPage + $j),"name"=>($this->currentPage + $j),"isNow"=>0];
                }
            }
            $left[] = ["url"=>$this->route.$this->currentPage,"name"=>$this->currentPage,"isNow"=>1];
            foreach($right as $v){
                $left[] = $v;
            }
            return $left;
        } else {
            $allLink = [];
            //当页码总数小于需要显示的链接数量时候，则全部显示出来
            for ($j = 1; $j <= $this->pageAmount; ++$j) {
                $allLink[]=["url"=>$this->route.$j,"name"=>$j,"isNow"=>$j == $this->currentPage?1:0];
            }

            return $allLink;
        }
    }
}
