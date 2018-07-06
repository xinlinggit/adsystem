<!DOCTYPE html>
<html>
<head>
    <title>聚汇通-广告主平台 -  Powered by {:config('hisiphp.name')}</title>
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link rel="stylesheet" href="__ADMIN_JS__/layui/css/layui.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__ADMIN_CSS__/style.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/typicons/min.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/font-awesome/min.css?v={:config('hisiphp.version')}">
    <style type="text/css">
        .hs-iframe{width:100%;height:100%;}
        .layui-tab{position:absolute;left:0;top:0;height:100%;width:100%;z-index:10;margin:0;border:none;overflow:hidden;}
        .layui-tab-content{padding:0 0 0 10px;height:100%;}
        .layui-tab-item{height:100%;}
        .footer{position:fixed;left:0;bottom:0;z-index:998;}
    </style>
    <!--[if lt IE 9]>
    <script type="text/javascript">
        (function(window) {
            var theUA = window.navigator.userAgent.toLowerCase();
            if ((theUA.match(/msie\s\d+/) && theUA.match(/msie\s\d+/)[0]) || (theUA.match(/trident\s?\d+/) && theUA.match(/trident\s?\d+/)[0])) {
                var ieVersion = theUA.match(/msie\s\d+/)[0].match(/\d+/)[0] || theUA.match(/trident\s?\d+/)[0];
                if (ieVersion < 9) {
                    var str = "您的浏览器版本太旧了, 请升级浏览器:(";
                    var str2 = "推荐使用:<a href='https://www.baidu.com/s?ie=UTF-8&wd=%E8%B0%B7%E6%AD%8C%E6%B5%8F%E8%A7%88%E5%99%A8' target='_blank' style='color:blue;'>谷歌</a>,"
                            + "<a href='https://www.baidu.com/s?ie=UTF-8&wd=%E7%81%AB%E7%8B%90%E6%B5%8F%E8%A7%88%E5%99%A8' target='_blank' style='color:blue;'>火狐</a>,"
                            + "其他双核极速模式";
                    document.writeln("<pre style='text-align:center;color:#fff;background-color:#009688; height:100%;border:0;position:fixed;top:0;left:0;width:100%;z-index:1234'>" +
                            "<h2 style='padding-top:200px;margin:0'><strong>" + str + "<br/></strong></h2><h2>" +
                            str2 + "</h2><h2 style='margin:0'><strong>如果你的使用的是双核浏览器,请切换到极速模式访问)<br/></strong></h2></pre>");
                    document.execCommand("Stop");
                };
            }
        })(window);
    </script>
    <![endif]-->
</head>
<body>
{php}
$ca = strtolower(request()->controller().'/'.request()->action());
{/php}
<div class="layui-layout layui-layout-admin">
    <div class="layui-header" style="z-index:999!important;">
        <div class="fl header-logo">聚汇通-广告主平台</div>
        <div class="fl header-fold"><a href="javascript:;" title="打开/关闭左侧导航" class="aicon ai-caidan" id="foldSwitch"></a></div>
        <ul class="layui-nav fl nobg main-nav">
            {volist name="_admin_menu" id="vo"}
                {if condition="($_admin_menu_parents['pid'] eq $vo['id'] and $ca neq 'plugins/run') or ($ca eq 'plugins/run' and $vo['id'] eq 3)"}
               <li class="layui-nav-item layui-this2">
                {else /}
                <li class="layui-nav-item">
                {/if} 
                <a href="javascript:;">{$vo['title']}</a></li>
            {/volist}
        </ul>
        <ul class="layui-nav fr nobg head-info" lay-filter="">
            <li class="layui-nav-item">
                <a href="javascript:void(0);">{$admin_user['nick']}&nbsp;&nbsp;</a>
                <dl class="layui-nav-child">
                    <dd><a href="{:url('admin/publics/logout')}">退出登陆</a></dd>
                </dl>
            </li>
            {if condition="$admin_user.uid eq 1"}
            <li class="layui-nav-item"><a href="{:url('admin/index/clear')}" class="j-ajax" refresh="yes">清缓存</a></li>
            {/if}
            <li class="layui-nav-item"><a href="javascript:void(0);" id="lockScreen">锁屏</a></li>
        </ul>
    </div>
    <div class="layui-side layui-bg-black" id="switchNav">
        <div class="layui-side-scroll">
            {volist name="_admin_menu" id="v"}
            {if condition="($_admin_menu_parents['pid'] eq $v['id'] and $ca neq 'plugins/run') or ($ca eq 'plugins/run' and $v['id'] eq 3)"}
            <ul class="layui-nav layui-nav-tree">
            {else /}
            <ul class="layui-nav layui-nav-tree" style="display:none;">
            {/if}
                {volist name="v['childs']" id="vv" key="kk"}
                {php}
                    if ( ($authentication === false) && ($vv['title'] == '广告管理'))
                    {
                        continue;
                    }

                {/php}
                {if condition="$vv['title'] neq '快捷菜单'"}
                <li class="layui-nav-item {if condition="$kk eq 1"}layui-nav-itemed{/if}">
                    <a href="javascript:;"><i class="{$vv['icon']}"></i>{$vv['title']}<span class="layui-nav-more"></span></a>
                    <dl class="layui-nav-child">
                        {if condition="$vv['title'] eq '快捷菜单'"}
                            <dd><a class="admin-nav-item" data-id="0" href="{:url('admin/index/welcome')}"><i class="aicon ai-shouye"></i> 后台首页</a></dd>
                            {volist name="vv['childs']" id="vvv"}
                            <dd><a class="admin-nav-item" data-id="{$vvv['id']}" href="{:url($vvv['url'].'?'.$vvv['param'])}"><i class="{$vvv['icon']}"></i> {$vvv['title']}</a><i data-href="{:url('menu/del?ids='.$vvv['id'])}" class="layui-icon j-del-menu">&#xe640;</i></dd>
                            {/volist}
                        {else /}
                            {volist name="vv['childs']" id="vvv"}
                            <dd><a class="admin-nav-item" data-id="{$vvv['id']}" href="{if condition="strpos('http', $vvv['url']) heq false"}{:url($vvv['url'].'?'.$vvv['param'])}{else /}{$vvv['url']}{/if}"><i class="{$vvv['icon']}"></i> {$vvv['title']}</a></dd>
                            {/volist}
                        {/if}
                    </dl>
                </li>
                {/if}
                {/volist}
            </ul>
            {/volist}
        </div>
    </div>
    <div class="layui-body" id="switchBody">
        <div class="layui-tab layui-tab-card" lay-filter="hisiTab" lay-allowClose="true">
            <ul class="layui-tab-title">
                <li lay-id="0" class="layui-this2">后台首页</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    <iframe lay-id="0" src="{:url('index/welcome')}" width="100%" height="100%" frameborder="0" scrolling="yes" class="hs-iframe"></iframe>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-footer footer">
        <span class="fl">Powered by <a href="{:config('hisiphp.url')}" target="_blank">{:config('hisiphp.name')}</a> v{:config('hisiphp.version')}</span>
        <span class="fr"> © 2017-2018 <a href="{:config('hisiphp.url')}" target="_blank">{:config('hisiphp.copyright')}</a> All Rights Reserved.</span>
    </div>
</div>
{include file="block/layui" /}
<script type="text/javascript">
    layui.use(['jquery', 'element', 'layer'], function() {
        var $ = layui.jquery, element = layui.element, layer = layui.layer;
        $('.layui-tab-content').height($(window).height() - 145);
        var tab = {
                add: function(title, url, id) {
                    element.tabAdd('hisiTab', {
                        title: '<i class="layui-icon j-ajax" data-href="{:url('admin/menu/quick')}?id='+id+'">&#xe600;&nbsp;</i>'+title,
                        content: '<iframe width="100%" height="100%" lay-id="'+id+'" frameborder="0" src="'+url+'" scrolling="yes" class="x-iframe"></iframe>',
                        id: id
                    });
                }, change: function(id) {
                  element.tabChange('hisiTab', id);
                }
            };
        var layidarr=[];
        $('.admin-nav-item').click(function(event) {
            var that = $(this);
            // 将所有 class = "admin-nav-item" 的父 <dd></dd> 标签的 class 中的 "layui-this" 去掉，高亮自己的
            var dd_nodes = $(".admin-nav-item").parent('dd');
            $.each(dd_nodes, function(i, val){
                $(val).removeClass('layui-this');
            })
            that.parent('dd').addClass('layui-this');
            // 仅保留一个 tab
            element.tabDelete('hisiTab', 0);
            if(layidarr){
                element.tabDelete('hisiTab', layidarr);
            }
            that.css({color:'#fff'});
            tab.add(that.text(), that.attr('href'), that.attr('data-id'));
            tab.change(that.attr('data-id'));
            layidarr=that.attr('data-id');
            event.stopPropagation();
            return false;
        });
        $(document).on('click', '.layui-tab-close', function() {
            $('.layui-nav-child a[data-id="'+$(this).parent('li').attr('lay-id')+'"]').css({color:'rgba(255,255,255,.7)'});
        });
    });
</script>
</body>
</html>