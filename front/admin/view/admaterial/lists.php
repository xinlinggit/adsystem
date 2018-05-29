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
        <form class="layui-form layui-form-pane" action="{:url('lists')}" method="get">
<!--            <div class="layui-inline">-->
<!---->
<!--                <label class="layui-form-label" style="width:100px;">素材类型:</label>-->
<!--                    <select name="material_type">-->
<!--                        <option value="">所有</option>-->
<!--                        <option value="1" {if condition="$material_type_selected eq 1"}selected{/if}>文字</option>-->
<!--                        <option value="2" {if condition="$material_type_selected eq 2"}selected{/if}>图片</option>-->
<!--                        <option value="3"  {if condition="$material_type_selected eq 3"}selected{/if}>Flash</option>-->
<!--                    </select>-->
<!--            </div>-->
            <div class="layui-inline">
                <label class="layui-form-label" style="width:100px;">审核状态:</label>
                <select name="status">
                    <option value="" >所有</option>
                    <option value="3"  {if condition="$status_selected eq 3"}selected{/if}>已通过</option>
                    <option value="4"  {if condition="$status_selected eq 4"}selected{/if}>未通过</option>
                    <option value="5"  {if condition="$status_selected eq 5"}selected{/if}>待审核</option>
                </select>
            </div>
            <div class="layui-inline" style="overflow: hidden;width:500px;">
                <label class="layui-form-label fl" style="display:inline-block;float:left;margin-top:5px;width:100px;">搜索</label>
                <input type="text" name="q" value="{:input('get.q')}" lay-verify="required" placeholder="素材名称或 ID" autocomplete="off" class="layui-input fl" style="display:inline-block;float:left;margin-top: 5px;">
                <input type="submit" class="layui-btn fl" value = "搜索" style="display:inline-block;float:left;margin-left:27px;"/>
                <a href="{:url('lists?material_type=&status=&q=')}" class="layui-btn">重置</a>
            </div>
        </form>
    </div>
    <form class="page-list-form">
        <div class="layui-btn-group fl">
<!--            <a href="{:url('add')}" class="layui-btn layui-btn-primary"><i class="aicon ai-tianjia"></i>添加广告素材</a>-->
            <a data-href="{:url('delall')}" refresh="yes" class="layui-btn layui-btn-primary  confirm j-page-btns"><i class="aicon ai-clear"></i>批量删除</a>
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
				<th style="text-align: center;">素材ID</th>
				<th style="text-align: center;">广告素材名称</th>
<!--				<th style="text-align: center;">状态</th>-->
				<th style="text-align: center;">素材尺寸</th>
				<th style="text-align: center;">素材类型</th>
				<th style="text-align: center;">审核状态</th>
				<th style="text-align: center;">操作</th>
			</tr>
			</thead>
			<tbody>
			{volist name="data_list" id="vo"}
			<tr>
				<td style="text-align: center;"><input type="checkbox" name="ids[]" class="layui-checkbox checkbox-ids" value="{$vo.id}" lay-skin="primary"></td>
				<td style="text-align: center;">{$vo['id']}</td>
				<td style="text-align: center;">{$vo['material_title']}</td>
				<td style="text-align: center;">{$vo.width}*{$vo.height}</td>
				<td style="text-align: center;">
                    {switch name="$vo.material_type"}
                        {case value="1"}文字链广告{/case}
                        {case value="2"}图片广告{/case}
                        {case value="3"}Flash广告{/case}
                        {case value="4"}信息流广告{/case}
                    {/switch}
                </td>
				<td style="text-align: center;">
                    {switch name="$vo.status"}
                        {case value="0"}无{/case}
                        {case value="3"}已通过{/case}
                        {case value="4"}未通过<a href='javascript:void(0)' class='remark' data="{$vo.remark}"><i class="layui-icon">&#xe607;</i></a>{/case}
                        {case value="5"}待审核{/case}
                    {/switch}
                </td>
                <td style="text-align: center;">
                    <a href="{$preview_url . $vo['id']}" target="_blank" class="layui-btn layui-btn-primary">预览</a>
                    <a href="{:url($material_type_action[$vo['material_type']] . '/del?id='.$vo['id'].'&material_id='.$vo['material_id'])}" class="layui-btn layui-btn-primary j-tr-del" reresh="yes">删除</a>
                </td>
			</tr>
			{/volist}
			</tbody>
		</table>
		{$pages}
	</div>
</form>
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

        $(document).on('click', '.layui-tab-close', function() {
            $('.layui-nav-child a[data-id="'+$(this).parent('li').attr('lay-id')+'"]').css({color:'rgba(255,255,255,.7)'});
        });
        $('.remark').on('mouseover', function(){
            var that = this;
            layer.tips($(this).attr("data"), that); //在元素的事件回调体中，follow直接赋予this即可
        });
    });

</script>