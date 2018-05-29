<link href="__STATIC__/myCommon.css?v=0.1" rel="stylesheet">
<style>
    .layui-form-pane .layui-form-label {width:200px;}
</style>
<form class="layui-form layui-form-pane" action="{if condition='$edit eq 1'}{:url('admin/Admaterial4info/edit')} {else/}{:url('admin/Admaterial4info/addimage')}{/if}" method="post" id="editForm" enctype="multipart/form-data">
    <div class="layui-tab-item layui-show layui-form-pane">
        <div class="layui-form-item">
            <label class="layui-form-label layui-form-text">素材名称:</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="material_title" lay-verify="required" autocomplete="off" placeholder="请输入素材名称" {if condition="$infoform"}value="{$infoform.material_title}"{/if}>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">类型:</label>
            <div class="">
<!--                <input type="radio" class="field-type" lay-filter="type" name="type" value="1" title="文字"  {if condition="$infoform AND $infoform.type eq '1'"} checked {/if}>-->
<!--                <input type="radio" class="field-type" lay-filter="type" name="type" value="2" title="图片" {if condition="$infoform AND $infoform.type eq '2'"} checked {/if}>-->
                <input type="radio" class="field-type" lay-filter="type" name="type" value="3" title="图文" {if condition="($edit eq 0) OR $infoform AND $infoform.type eq '3'"} checked {/if}>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">选择站点:</label>
            <div class="layui-input-inline">
                <select type="select" name="adsiteid" lay-filter="info_adsiteid" id="info_adsiteid">
                    <option></option>
                    {volist name="adsite" id = "site"}
                    <option value="{$site['id']}" {if condition="isset($infoform.adsiteid) AND ($site.id eq $infoform.adsiteid)"} selected {/if}>{$site.sitename}</option>
                    {/volist}
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">信息流图片可选尺寸:</label>
            <div class="my_text_center my_wd400 my_l" id="size_optional">

            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">信息流选中尺寸:</label>
            <div class="">
                <label class="">&nbsp;宽&nbsp;</label>
                <input type="number" class="my_input" name="width" id ="info_width" lay-verify="required|number" autocomplete="off" placeholder="" {if condition="$infoform"}value="{$infoform.width}"{/if} readonly>
                <label class="">&nbsp;高&nbsp;</label>
                <input type="number" class="my_input" name="height" id = "info_height" lay-verify="required|number"  autocomplete="off" placeholder="" {if condition="$infoform"}value="{$infoform.height}"{/if} readonly>
                <input type="hidden" name="adaptation" value="1" />
            </div>
        </div>
        <div class="layui-form-item" id="info_pic_box" style="display: block;">
            <lable class="layui-form-label layui-form-text">图片地址:</lable>
            <div class="">
                <button type="button" class="layui-btn" id="info_upload_btn" style="margin-top:0px;">
                    <i class="layui-icon">&#xe67c;</i>上传图片
                </button>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" name="image_url" id="info_url" {if condition="isset($infoform.image_url)"} value="{$infoform.image_url}" {/if}>
                </div>
            </div>
        </div>
        <div class="layui-form-item" id="info_pic_desc_box" style="display: block">
            <label class="layui-form-label">文字描述:</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" id="info_desc" name="image_description" autocomplete="off" placeholder="" {if condition="$infoform"}value="{$infoform.image_description}"{/if}>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">点击图片链接:</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="click_url" lay-verify="required|url" autocomplete="off" placeholder="" {if condition="$infoform"}value="{$infoform.click_url}"{/if}>
            </div>
            <div class="my_text_center my_wd400 my_l">地址请以 http:// 或者 https:// 开头</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">目标窗口:</label>
            <div class="">
                <input type="radio" class="field-open_target" name="open_target" value="_blank" title="新窗口"  {if condition="($edit eq 0) OR $infoform AND $infoform.open_target eq '_blank'"} checked {/if}>
                <input type="radio" class="field-open_target" name="open_target" value="_parent" title="原窗口" {if condition="$infoform AND $infoform.open_target eq '_parent'"} checked {/if}>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="submit" class="layui-btn" value="提交" lay-submit=""/>
            <input type="hidden" class="field-material_type" name="material_type" value="4">
            {if condition="$edit eq 1"}
            <input type="hidden" value="<?= $infoform['width'] .'|'. $infoform['height'] .'|' . $infoform['adsenseid'] ?>" id="selected_adsenseid"/>
            <input type="hidden" class="field-id" name="id" value="{if condition="isset($infoform.id)"}{$infoform.id}{/if}">
            {/if}
            <input type="hidden" class="" name="material_id" value="{if condition="isset($infoform.material_id)"}{$infoform.material_id | default = 0}{/if}">
            <a href="{:url('admin/admaterial/lists')}" class="layui-btn layui-btn-primary ml10"><i class="aicon ai-fanhui"></i>返回列表页</a>
        </div>
    </div>
</form>
{if condition="$edit eq 1"}
{include file="block/layui" /}
{include file="admaterial4info/infoform_help_js"}
<script>
    var formData = {:json_encode([])};
</script>
<script src="__ADMIN_JS__/footer.js"></script>
{/if}
