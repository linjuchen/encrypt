<?php

namespace Cmmia\Encrypt;
/**
 * 分页
 * Class Pagination
 * @package Cmmia\Encrypt
 */
class Pagination
{
    //数组的大小
    public $totalCount;
    //每页的数目
    public $defaultPageSize;
    //当前页
    public $pageNow;
    //上一页
    public $pagePrev;
    //下一页
    public $pageNext;
    //总页数
    public $pageCount;
    //配置
    public $options = ['simple' => false, 'style' => 1, 'allCounts' => false, 'nowAllPage' => false, 'toPage' => false, 'prev_mark' => '«', 'next_mark' => '»'];
    private $request;

    public function __construct(int $totalCount, $defaultPageSize = 8, $options = [])
    {
        $this->request = context()->getRequest();
        $this->totalCount = $totalCount;
        $this->defaultPageSize = $defaultPageSize;
        $this->options = array_merge($this->options, $options);
        //获取总页数
        $this->getPageCount();
        $this->getPage();
    }

    /**
     * 得到page
     * @return [type] [description]
     */
    protected function getPage()
    {
        $page = $this->request->get('page') ?? 1;
        if ($page < 1) {
            $page = 1;
        }
        if ($page >= $this->pageCount) {
            $page = $this->pageCount;
        }
        $this->pagePrev = $this->pageNext = $this->pageNow = $page;
        if ($this->hasMore($page)) {
            $this->pageNext = $page + 1;
        }
        if ($this->hasMore($page, 'prev')) {
            $this->pagePrev = $page - 1;
        }
    }

    /**
     * 获取中间的页码
     * @return [type] [description]
     */
    protected function getLinks()
    {
        $pageCount = $this->pageCount;
        $pageNow = $this->pageNow;
        $pageLink = '';
        $side = 2;
        $window = $side * 2;
        $block = [
            'first' => [],
            'last' => [],
            'slider' => []
        ];
        if ($this->pageCount < $window + 6) {
            $block['first'] = $this->getPageRange(1, $this->pageCount);
        } elseif ($this->pageNow <= $window) {
            $block['first'] = $this->getPageRange(1, $window + 2);
            $block['last'] = $this->getPageRange($this->pageCount - 1, $this->pageCount);
        } elseif ($this->pageNow > ($this->pageCount - $window)) {
            $block['first'] = $this->getPageRange(1, 2);
            $block['last'] = $this->getPageRange($this->pageCount - ($window + 2), $this->pageCount);
        } else {
            $block['first'] = $this->getPageRange(1, 2);
            $block['slider'] = $this->getPageRange($this->pageNow - $side, $this->pageNow + $side);
            $block['last'] = $this->getPageRange($this->pageCount - 1, $this->pageCount);
        }
        if (is_array($block['first'])) {
            $pageLink .= $this->getUrlLinks($block['first']);
        }
        if (is_array($block['slider']) && !empty($block['slider'])) {
            $pageLink .= $this->getDots();
            $pageLink .= $this->getUrlLinks($block['slider']);
        }
        if (is_array($block['last']) && !empty($block['last'])) {
            $pageLink .= $this->getDots();
            $pageLink .= $this->getUrlLinks($block['last']);
        }
        return $pageLink;
    }

    /**
     * 生成html链接
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    protected function getUrlLinks($url)
    {
        $pageLink = '';
        foreach ($url as $k => $v) {
            if ($k == $this->pageNow) {
                $pageLink .= $this->getActivePageWrapper($k);
            } else {
                $pageLink .= $this->getAvailablePageWrapper($v, $k);
            }
        }
        return $pageLink;
    }

    /**
     * 获取分页的分段的范围
     * @param  [type] $start [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    protected function getPageRange($start, $end)
    {
        $urls = [];
        for ($page = $start; $page <= $end; $page++) {
            $urls[$page] = $this->url($page);
        }
        return $urls;
    }

    /**
     * 渲染分页样式
     * @return [type] [description]
     */
    public function render()
    {
        if ($this->options['simple']) {
            return sprintf(
                $this->getStyle() . '<ul class="hbb-pagination">%s  %s</ul>',
                $this->getPrevPage(),
                $this->getNextPage()
            );
        }
        $page = $this->getStyle() . '<ul class="hbb-pagination">' . $this->getPrevPage() . $this->getLinks() . $this->getNextPage();
        if ($this->options['allCounts']) {
            $page .= $this->getAllCounts();
        }
        if ($this->options['nowAllPage']) {
            $page .= $this->getNowAllPage();
        }
        if ($this->options['toPage']) {
            $page .= $this->getToPage();
        }
        $page .= '</ul>';
        return $page;
    }

    /**
     * 获取总的页数
     * @return [type] [description]
     */
    public function getPageCount()
    {
        $this->pageCount = ceil($this->totalCount / $this->defaultPageSize);
        return $this->pageCount;
    }

    /**
     * 生成上一页的按钮
     * @param string $mark [description]
     * @return [type]       [description]
     */
    protected function getPrevPage()
    {
        $mark = $this->options['prev_mark'];
        //如果是第一页则不可点击
        if ($this->pageNow == 1) {
            if (!$this->options['simple']) {
                return '';
            }
            return $this->getDisabledTextWrapper($mark);
        }
        $url = $this->url($this->pagePrev);
        return $this->getAvailablePageWrapper($url, $mark);
    }

    /**
     * 生成下一页的按钮
     * @param string $mark [description]
     * @return [type]       [description]
     */
    protected function getNextPage()
    {
        $mark = $this->options['next_mark'];
        //如果是第一页则不可点击
        if ($this->pageNow == $this->pageNext) {
            if (!$this->options['simple']) {
                return '';
            }
            return $this->getDisabledTextWrapper($mark);
        }
        $url = $this->url($this->pageNext);
        return $this->getAvailablePageWrapper($url, $mark);
    }

    /**
     * [生成可点击按钮]
     * @param  [type] $url  [description]
     * @param  [type] $page [description]
     * @return [type]       [description]
     */
    protected function getAvailablePageWrapper($url, $page)
    {
        return '<li><a href="' . htmlentities($url) . '">' . $page . '</a></li>';
    }

    /**
     * 生成禁用按钮
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<li class="disabled"><span>' . $text . '</span></li>';
    }

    /**
     * 生成一个激活的按钮
     * @param string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<li class="active"><span>' . $text . '</span></li>';
    }

    /**
     * 生成省略号
     * @return [type] [description]
     */
    protected function getDots()
    {
        return $this->getDisabledTextWrapper('...');
    }

    /**
     * 得到总的条数
     * @return [type] [description]
     */
    protected function getAllCounts()
    {
        return '<span class="page-total">共' . $this->totalCount . '条</span>';
    }

    /**
     * 得到当前的页码跟总页码
     * @return [type] [description]
     */
    protected function getNowAllPage()
    {
        return '<span class="page-all">第' . $this->pageNow . '页/共' . $this->pageCount . '页</span>';
    }

    protected function getToPage()
    {
        return '<span class="hbb-page">到第<input  value="' . $this->pageNow . '" id="to-page-input" class="" type="text">页<button type="button" class="hbb-button hbb-to-page" id="hbb-button">确定</button></span>
             <script>
                button = document.getElementById(`hbb-button`)
                button.onclick = function(){
                page = document.getElementById(`to-page-input`).value
                baseUrl = "' . $this->getBaseUrl() . '";
                query = ' . json_encode($this->getParams($this->pageNow)) . ';
                query["page"] = page
                params = ""
                k = 0
                for(i in query){
                    if(k == 0){
                        params += "?"+i+"="+query[i];
                    }else{
                        params += "&"+i+"="+query[i];
                    }
                    k++
                }
                url = baseUrl + params
                window.location.href = url
            }
             </script>
     
         ';
    }

    /**
     * 获取当前的url
     */
    protected function getUrl()
    {
        $uri = $this->request->getUri();
        $query = empty($uri->getQuery()) ? "" : "?" . $uri->getQuery();
        return $uri->getScheme() . '://' . $uri->getHost() . $uri->getPath() . $query;
    }

    /**
     * 获取baseUrl
     */
    protected function getBaseUrl()
    {
        $uri = $this->request->getUri();
        return $uri->getScheme() . '://' . $uri->getHost() . $uri->getPath();
    }

    /**
     * 获取params
     */
    protected function getParams($page = 1)
    {

        $getUrl = $this->getUrl();
        $parse = parse_url($getUrl);
        $query = [];
        //获取参数
        if (isset($parse['query'])) {
            parse_str($parse['query'], $query);
        }
        //替换page参数
        $query['page'] = $page;
        return $query;
    }

    /**
     * 生成url
     * @param  [type] $page [description]
     * @return [type]       [description]
     */
    protected function url($page)
    {
        if ($page < 1) {
            $page = 1;
        }
        //获取url
        $baseUrl = $this->getBaseUrl();
        $param = http_build_query($this->getParams($page));
        //生成url
        $url = $baseUrl . '?' . $param;

        return $url;

    }

    protected function getStyle()
    {
        $style = $this->options['style'];
        $css = file_get_contents(alias('@base') . "/resource/static/css/page/style{$style}.css");
        return "<style>{$css}</style>";
    }

    /**
     * 判断是否有页面
     * @param  [type]  $page [description]
     * @param string $type [description]
     * @return boolean       [description]
     */
    protected function hasMore($page, $type = "next")
    {
        if ($type == 'next') {
            return $page < $this->pageCount;
        } else {
            return $page > 1;
        }
    }

    /**
     * 获取偏移量
     * @return [type] [description]
     */
    public function offset()
    {
        $page = $this->pageNow;
        return ($page - 1) * $this->defaultPageSize;
    }

    /**
     * 获取分类页的数目
     * @return [type] [description]
     */
    public function limit()
    {
        return $this->defaultPageSize;
    }
}
