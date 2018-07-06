<link href="__STATIC__/myCommon.css?v=0.1" rel="stylesheet">
<!-- 工具栏 begin -->
<style>
	.clear:after {
		content:"";display:table;clear:both
	}
	.layui-select-title {
		width: 300px;
	}
	.layui-select-title .layui-input {
		width: 150px;
	}
	.layui-input, .layui-textarea {
		width:auto;
	}
</style>
<div class="page-toolbar9 clear" style="margin-bottom:5px;">
	<div class="page-filter9 fr">
		<form class="layui-form layui-form-pane" action="{:url('lists')}" method="get">
			<div class="layui-inline" style="overflow: hidden;width:600px;">
				<input type="text" name="q" value="{:input('get.q')}" lay-verify="required" placeholder="请输入广告名称或ID" autocomplete="off" class="layui-input fl" style="display:inline-block;float:left;margin-top: 5px;">
				<input type="submit" class="layui-btn fl" value = "搜索" style="display:inline-block;float:left;margin-left:27px;"/>
                <a href="{:url('lists?q=')}" class="layui-btn">重置</a>
                <a href="javascript:void(0)" class="layui-btn layui-btn-primary advance_search" id="advance_search" lay-filter="advance_search">更多搜索条件</a>
			</div>
		</form>
	</div>
	<form class="page-list-form">
		<div class="layui-btn-group fl">
<!--			<a href="{:url('add')}" class="layui-btn layui-btn-primary"><i class="aicon ai-tianjia"></i>新增广告</a>-->
			<a href="{:url('addinfo')}" class="layui-btn layui-btn-primary"><i class="aicon ai-tianjia"></i>新增广告</a>
<!--			<a data-href="{:url('delall')}" refresh="yes" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-clear"></i>批量删除</a>-->
		</div>
</div>
<!-- 工具栏 --end -->
<form id="pageListForm">
	<div class="layui-form">
		<table class="layui-table mt10" lay-even="" lay-skin="row">
			<colgroup>
				<col width="50">
			</colgroup>
			<thead>
			<tr>
				<th style="text-align: center;"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
				<th  style="text-align: center;" class="w50">广告ID</th>
				<th  style="text-align: center;" class="w100">广告名称</th>
				<th  style="text-align: center;" class="w100">投放形式</th>
				<th  style="text-align: center;" class="w100">所属站点</th>
				<th  style="text-align: center;" class="w50">素材ID</th>
<!--				<th style="text-align: center;">广告位尺寸</th>-->
				<th style="text-align: center;">预算方式</th>
                <th style="text-align: center;">启用状态</th>
				<th style="text-align: center;">投放状态</th>
                <th style="text-align: center;">投放时段</th>
				<th style="text-align: center;">创建时间</th>
				<th style="text-align: center;">操作</th>
			</tr>
			</thead>
			<tbody>
			{volist name="data_list" id="vo"}
			<tr>
				<td style="text-align: center;"><input type="checkbox" name="ids[]" class="layui-checkbox checkbox-ids" value="{$vo.id}" lay-skin="primary"></td>
				<td style="text-align: center;">{$vo['id']}</td>
				<td style="text-align: center;">{$vo['title']}</td>
				<td style="text-align: center;">{if condition="$vo['project_type'] eq 1"} 文字 {elseif condition="$vo['project_type'] eq 2"} 图片 {elseif condition="$vo['project_type'] eq 3"} 图文 {/if}</td>
				<td style="text-align: center;">{$vo['sitename']}</td>
				<td style="text-align: center;">{$vo['materialid']}</td>
				<td style="text-align: center;">
					{if condition = "$vo['spending'] eq 2"}
						竞价： ￥{$vo['price'] / 100} / CPM
                    {elseif condition = "$vo['spending'] eq 3"}
                        竞价： ￥{$vo['price'] / 100} / CPC
					{else/}
						包时段<span></span>
					{/if}
				</td>
                <td style="text-align: center;">
                    {if condition="isset($vo.fake_running_status) && $vo.fake_running_status eq 1"}
                        请手动启用
                    {elseif condition="$vo.running_status eq 1"}
                        启用
                    {elseif condition="isset($vo.fake_running_status) && $vo.fake_running_status eq 4"}
                        审核未通过
                    {elseif condition="isset($vo.fake_running_status) && $vo.fake_running_status eq 3"}
                        待审核
                    {else /}
                        停用
                    {/if}
                </td>
				<td style="text-align: center;">
					{switch name="$vo.status"}
						{case value="1"}暂停{/case}
						{case value="2"}即将投放{/case}
						{case value="3"}投放中{/case}
						{case value="4"}已结束{/case}
					{/switch}
				</td>
                {php}
                $show_time = str_replace(',', ' <br />至<br /> ', $vo['time']);
                {/php}
                <td style="text-align: center;">{$show_time}</td>
				<td style="text-align: center;">
					{$vo.create_time}
				</td>
				<td style="text-align: center;">
                    {if condition="$vo['adv_type'] neq '1'"}
                    <a href="{:url('edit?id=' . $vo['id'] . '&adsiteid=' .$vo['adsiteid'].'&material_id='.$vo['materialid'])}" class="layui-btn layui-btn-primary">修改</a>
                    {else /}
                    <a href="{:url('editinfo?id=' . $vo['id'] . '&adsiteid=' .$vo['adsiteid'].'&material_id='.$vo['materialid'])}" class="layui-btn layui-btn-primary">修改</a>
                    {/if}
                    <a href="{:url('changerunningstatus?id='.$vo['id'].'&running_status='.$vo['running_status'])}" class="layui-btn layui-btn-primary j-ajax">{if condition="$vo.running_status eq 2"}启用{else/} 停止 {/if} </a>
					<a href="{$review_url . $vo['materialid']}" target="_blank" class="layui-btn layui-btn-primary">预览</a>
<!--                    <a href="{:url('del?id='.$vo['id'])}" class="layui-btn layui-btn-primary " reresh="yes">删除</a>-->
				</td>
			</tr>
			{/volist}
			</tbody>
		</table>
		{$pages}
	</div>
</form>
<!--  高级搜索  begin -->
<div class="page-filter9 fr" id="advance_search_content" style="visibility: hidden;">
    <form class="layui-form layui-form-pane" action="{:url('lists')}" method="get">
        <div class="layui-inline" style="overflow: ;width:480px;">
            <div class="layui-form-item">
                <label class="layui-form-label fl" style="display:inline-block;float:left;">投放状态</label>
                <div class="layui-input-inline">
                    <select name="status" lay-verify = "required" type="select">
                        <!-- 2: 即将投放 3: 投放中4: 已投完-->
                        <option>全部</option>
                        <option value="2" {if condition="input('get.status') eq 2"} selected {/if}>即将投放</option>
                        <option value="3" {if condition="input('get.status') eq 3"} selected {/if}>投放中</option>
                        <option value="4" {if condition="input('get.status') eq 4"} selected {/if}>已结束</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label fl" style="display:inline-block;float:left;">搜索</label>
                <input type="text" name="q" value="{:input('get.q')}" lay-verify="required" placeholder="请输入广告名称或ID" autocomplete="off" class="layui-input fl" style="display:inline-block;float:left;">
            </div>
<!--            <div class="layui-form-item">-->
<!--                <label class="layui-form-label fl" style="display:inline-block;float:left;">所属的广告位</label>-->
<!--                <input type="text" name="adposition" value="{:input('get.adposition')}" lay-verify="required" placeholder="" autocomplete="off" class="layui-input fl" style="display:inline-block;float:left;">-->
<!--            </div>-->
            <div class="layui-form-item">
                <label class="layui-form-label fl" style="display:inline-block;float:left;">素材ID</label>
                <input type="text" name="materialid" value="{:input('get.materialid')}" lay-verify="required" placeholder="请输入素材ID" autocomplete="off" class="layui-input fl" style="display:inline-block;float:left;">
            </div>
            <div class="layui-form-item">
                <input type="text"  lay-verify="required" value="{:input('get.begin_time')}" name="begin_time" id="begin_time" class="my_input my_wd200 my_c field-btime" placeholder="创建开始时间" /> -
                <input type="text"  lay-verify="required" value="{:input('get.end_time')}"  name="end_time" id="end_time" class="my_input my_wd200 my_c field-btime" placeholder="创建结束时间" />
            </div>
            <div class="layui-form-item">
                <input type="submit" class="layui-btn" value="搜索" />
                <a href="{:url('lists?q=')}" class="layui-btn">重置</a>
            </div>
        </div>
    </form>
</div>
<!--  高级搜索  end -->
{include file="block/layui" /}
<script type="text/javascript">
    layui.use(['jquery', 'element', 'layer'], function() {
        var $ = layui.jquery, element = layui.element, layer = layui.layer;
        $('.layui-tab-content').height($(window).height());
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
        $('.advance_search').click(function(event){
            var search_form = document.getElementById("advance_search_content").style.visibility = 'visible';
            layer.open({
                type: 1,
                title: '高级搜索',
                area:['500px','400px'],
                content: $("#advance_search_content"),
                cancel: function(index, layero){
                    document.getElementById("advance_search_content").style.visibility = 'hidden'
                },
                success: function(layero, index){
                    // document.getElementById("advance_search_content").style.visibility = 'hidden'
                }
            });
        })
        layui.use('laydate', function() {
            // TODO: 结束时间需要大于开始时间，多段时间段不能交叉
            var laydate = layui.laydate;

            //执行一个laydate实例
            laydate.render({
                elem: '#begin_time' //指定元素
                , type: 'date'
            });
            //执行一个laydate实例
            laydate.render({
                elem: '#end_time' //指定元素
                , type: 'date'
            });
        });
        $(document).on('click', '.layui-tab-close', function() {
            $('.layui-nav-child a[data-id="'+$(this).parent('li').attr('lay-id')+'"]').css({color:'rgba(255,255,255,.7)'});
        });
    });
</script>