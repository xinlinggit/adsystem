<link href="__STATIC__/myCommon.css?v=0.1" rel="stylesheet">
<style>
    .layui-form-pane .layui-form-label {width:200px;}
</style>
<form class="layui-form layui-form-pane" action="{if condition='$material_id_choosed neq 0'}{:url('admin/Adserving/edit')} {else/}{:url('admin/Adserving/add')}{/if}" method="post" id="editForm" enctype="multipart/form-data" onsubmit="return check_ad_form()">
    <!--素材-->
    <div class="layui-form">
        <table class="layui-table mt10" lay-even="" lay-skin="row">
            <colgroup>
                <col width="50">
            </colgroup>
            <thead>
            <tr>
                <th>选择</th>
                <th>素材ID</th>
                <th>广告素材名称</th>
                <th>素材尺寸</th>
                <th>素材类型</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {volist name="data_list" id="vo"}
            <tr>
                <td><input type="radio" name="materialid" class="layui-checkbox checkbox-ids field-materialid" {if condition="isset($material_id_choosed)  AND ($vo['id'] eq $material_id_choosed)"} checked {/if} value="{$vo.id}" required="true" lay-skin="primary"></td>
                <td>{$vo['id']}</td>
                <td>{$vo['material_title']}</td>
                <td>{$vo.width}*{$vo.height}</td>
                <td>
                    {switch name="$vo.material_type"}
                    {case value="1"}文字链广告{/case}
                    {case value="2"}图片广告{/case}
                    {case value="3"}Flash广告{/case}
                    {/switch}
                </td>
                <td>
                    <a href="{$preview_url . $vo['id']}" target="_blank" class="layui-btn layui-btn-primary">预览</a>
                </td>
            </tr>
            {/volist}
            </tbody>
        </table>
        {$pages}
    </div>
    <div class="layui-form-item">
        <input type="hidden" name="sensetype" value id="sensetype" />
        <input type="submit" class="layui-btn" value="完成投放" />
        <input type="hidden" name="extra_param" {if condition="isset($extra_param)"} value="{$extra_param}" {/if}/>
        <input type="reset" class="layui-btn layui-btn-primary" value="重置" />
    </div>
    <!--素材-->
</form>
{include file="block/layui" /}
<script>
    function check_ad_form()
    {

    }
</script>