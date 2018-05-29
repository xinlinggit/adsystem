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
		width: 200px;
	}
	.layui-input, .layui-textarea {
		width:auto;
	}
</style>
<div class="page-toolbar9 clear" style="margin-bottom:5px;">
	<div class="page-filter9 fr">
		<form class="layui-form layui-form-pane" action="{:url()}" method="get">
			<div class="layui-inline">
				<label class="layui-form-label" style="width:100px;">审核状态:</label>
				<select name="type">
					<option value="" >所有</option>
					<option value="1"  {if condition="$selected_type eq 1"}selected{/if}>充值</option>
					<option value="2"  {if condition="$selected_type eq 2"}selected{/if}>退款</option>
				</select>
			</div>
			<div class="layui-inline" style="overflow: hidden;width:500px;">
				<label class="layui-form-label fl" style="display:inline-block;float:left;margin-top:5px;width:100px;">操作时间</label>
				<input type="text" name="time" value="{:input('time')}" class="layui-input" id="time">
			</div>
			<div class="layui-inline" style="overflow: hidden;width:500px;">
				<input type="submit" class="layui-btn fl" value = "搜索" style="display:inline-block;float:left;margin-left:27px;"/>
				<a href="{:url('record?time=&type=')}" class="layui-btn layui-btn-primary">重置</a>
			</div>
		</form>
	</div>
	<form class="page-list-form">
</div>
<!-- 工具栏 --end -->
<form id="pageListForm">
	<div class="layui-form">
		<table class="layui-table mt10" lay-even="" lay-skin="row">
			<thead>
			<tr>
				<th style="text-align: center;">编号</th>
				<th style="text-align: center;">操作时间</th>
				<th style="text-align: center;">记录类型</th>
				<th style="text-align: center;">金额（元）</th>
				<th style="text-align: center;">余额（元）</th>
				<th style="text-align: center;">备注</th>
			</tr>
			</thead>
			<tbody>
			{volist name="data" id="d"}
			<tr>
				<td>{$d.id}</td>
				<td>{$d.operate_time}</td>
				<td>{if condition="$d.type eq 1"}充值{else /}转账{/if}</td>
				<td>{:round($d.money / 100, 2)}</td>
				<td>{$d.blance}</td>
				<td>{$d.remark}</td>
			</tr>
			{/volist}
			</tbody>
		</table>
		{$pages}
	</div>
</form>
{include file="block/layui" /}
<script type="text/javascript">

    layui.use('laydate', function(){
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#time', //指定元素
	        range: true,
        });
    });

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

        $(document).on('click', '.layui-tab-close', function() {
            $('.layui-nav-child a[data-id="'+$(this).parent('li').attr('lay-id')+'"]').css({color:'rgba(255,255,255,.7)'});
        });
        $('.remark').on('mouseover', function(){
            var that = this;
            layer.tips($(this).attr("data"), that); //在元素的事件回调体中，follow直接赋予this即可
        });
    });

</script>