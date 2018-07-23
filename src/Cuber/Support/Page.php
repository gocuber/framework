<?php

/**
 * Page
 *
 * 分页类
 */
namespace Cuber\Support;

class Page
{

    private $total = 0;          // 总条数
    private $currpage = 1;       // 当前页数
    private $pagesize = 10;      // 每页条数
    private $pages = '';         // 分页 1,2,3,4,5
    private $style = 2;          // 分页样式 1极简 2简单 3全
    private $totalpage = 0;      // 总页数
    private $pagelist_num = 10;  // 显示多少个页数
    private $get = [];           // $_GET
    private $key = 'page';       // 分页参数key

    public function __construct($total = 0, $currpage = 1, $pagesize = 10, $opt = [])
    {
        $this->total    = (int)$total;
        $this->currpage = (int)$currpage;
        $this->pagesize = (int)$pagesize;

        $this->get = $_GET;
        foreach (['key', 'pagelist_num', 'get', 'style'] as $key) {
            if (isset($opt[$key]) and '' !== $opt[$key]) {
                $this->$key = $opt[$key];
            }
        }

        $this->init();
    }

    private function init()
    {
        if ($this->total < 1) {
            return ;
        }

        if (!empty($this->get) and is_array($this->get)) {
            unset($this->get[$this->key]);
            $this->get[$this->key] = '';
            $urlpre = '';
            foreach ($this->get as $key=>$value) {
                $urlpre .= '&' . htmlspecialchars($key) . '=' . htmlspecialchars($value);
            }
            $urlpre = '?' . trim($urlpre, '&');
        } else {
            $urlpre = '?' . $this->key . '=';
        }

        $this->currpage  = ($this->currpage < 1) ? 1 : $this->currpage;  // 当前页数 默认1
        $this->pagesize  = ($this->pagesize < 1) ? 10 : $this->pagesize; // 每页条数
        $this->totalpage = ceil($this->total/$this->pagesize);           // 总页数
        $this->currpage  = ($this->currpage > $this->totalpage) ? $this->totalpage : $this->currpage; // 当前页数大于总页数

        $uppage       = $this->currpage - 1; // 上一页
        $downpage     = $this->currpage + 1; // 下一页
        $pagelist_num = $this->pagelist_num; // 显示多少个页数
        $left         = floor(($this->pagelist_num-1)/2);    // 左显示多少个页数
        $right        = floor($this->pagelist_num/2);        // 右显示多少个页数

        // 计算开始页和结束页
        if ($this->currpage <= $left) { // 如果当前页左不足以显示页数
            $leftpage  = 1;
            $rightpage = ($pagelist_num<$this->totalpage) ? $pagelist_num : $this->totalpage;
        } elseif (($this->totalpage-$this->currpage) < $right) { // 如果当前页右不足以显示页数
            $leftpage  = ($this->totalpage<$pagelist_num) ? 1 : ($this->totalpage-$pagelist_num+1);
            $rightpage = $this->totalpage;
        } else { //左右可以显示页数
            $leftpage  = $this->currpage - $left;
            $rightpage = $this->currpage + $right;
        }

        // 前$pagelist_num页 后$pagelist_num页
        $f_page = ($this->currpage-$pagelist_num) < 1 ? 1 : ($this->currpage-$pagelist_num);
        $b_page = ($this->currpage+$pagelist_num) > $this->totalpage ? $this->totalpage : ($this->currpage+$pagelist_num);

        $pages = '';

        // 输出分页
        if ($this->currpage != 1) {
            if (3 == $this->style) {
                $homepage = substr($urlpre, 0, -2 - strlen($this->key));
                $homepage = ('' == $homepage) ? '?' : $homepage;

                $pages .= "<li><a href=\"{$urlpre}{$f_page}\">前{$pagelist_num}页</a></li>";
                $pages .= "<li><a href=\"{$homepage}\">首页</a></li>";
                $pages .= "<li><a href=\"{$urlpre}{$uppage}\">上一页</a></li>";
            } elseif (2 == $this->style) {
                $pages .= "<li><a href=\"{$urlpre}{$uppage}\">上一页</a></li>";
            } else {
                $pages .= "<li><a href=\"{$urlpre}{$uppage}\">&laquo;</a></li>";
            }
        } else {
            if (3 == $this->style) {
                $pages .= "<li class=\"disabled\"><a>前{$pagelist_num}页</a></li>";
                $pages .= "<li class=\"disabled\"><a>首页</a></li>";
                $pages .= "<li class=\"disabled\"><a>上一页</a></li>";
            } elseif (2 == $this->style) {
                $pages .= "<li class=\"disabled\"><a>上一页</a></li>";
            } else {
                $pages .= "<li class=\"disabled\"><a>&laquo;</a></li>";
            }
        }

        if (3 == $this->style and $leftpage > 1) {
            $pages .= "<li class=\"disabled\"><a>...</a></li>";
        }

        for ($i = $leftpage; $i <= $rightpage; $i++) {
            if ($i == $this->currpage) {
                $pages .= "<li class=\"active\"><a>$i</a></li>";
            } else {
                $pages .= "<li><a href=\"{$urlpre}{$i}\">$i</a></li>";
            }
        }

        if (3 == $this->style and $rightpage < $this->totalpage) {
            $pages .= "<li class=\"disabled\"><a>...</a></li>";
        }

        if ($this->currpage != $this->totalpage) {
            if (3 == $this->style) {
                $pages .= "<li><a href=\"{$urlpre}{$downpage}\">下一页</a></li>";
                $pages .= "<li><a href=\"{$urlpre}{$this->totalpage}\">尾页</a></li>";
                $pages .= "<li><a href=\"{$urlpre}{$b_page}\">后{$pagelist_num}页</a></li>";
            } elseif (2 == $this->style) {
                $pages .= "<li><a href=\"{$urlpre}{$downpage}\">下一页</a></li>";
            } else {
                $pages .= "<li><a href=\"{$urlpre}{$downpage}\">&raquo;</a></li>";
            }
        } else {
            if (3 == $this->style) {
                $pages .= "<li class=\"disabled\"><a>下一页</a></li>";
                $pages .= "<li class=\"disabled\"><a>尾页</a></li>";
                $pages .= "<li class=\"disabled\"><a>后{$pagelist_num}页</a></li>";
            } elseif (2 == $this->style) {
                $pages .= "<li class=\"disabled\"><a>下一页</a></li>";
            } else {
                $pages .= "<li class=\"disabled\"><a>&raquo;</a></li>";
            }
        }

        $this->pages = $pages;
        return ;
    }

    /**
     * show
     *
     * @return array
     */
    public function show()
    {
        return [
            'pages'     => $this->pages,      // < ... 3 4 5 6 ... >
            'total'     => $this->total,      // 总条数
            'currpage'  => $this->currpage,   // 当前页数
            'pagesize'  => $this->pagesize,   // 每页条数
            'totalpage' => $this->totalpage,  // 总页数
        ];
    }

}
