<?php
try {

    // 1. 手动加载入口文件
    include "../include.php";

    // 2. 准备公众号配置参数
    $config = include "./config.php";

    // 3. 创建接口实例
    // $wechat = \We::WeChatScript($config);
    // $wechat = new \WeChat\Script($config);
    $wechat = \WeChat\Script::instance($config);

    // 4. 获取JSSDK网址签名配置
    $result = $wechat->getJsSign('http://a.com/test.php');

    var_export($result);

} catch (Exception $e) {

    // 出错啦，处理下吧
    echo $e->getMessage() . PHP_EOL;

}