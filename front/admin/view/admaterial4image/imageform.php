<link href="__STATIC__/myCommon.css?v=0.1" rel="stylesheet">
<style>
    .layui-form-pane .layui-form-label {width:200px;}
</style>
<form class="layui-form layui-form-pane" action="{if condition='$edit eq 1'}{:url('admin/Admaterial4image/edit')} {else/}{:url('admin/Admaterial4image/addimage')}{/if}" method="post" id="editForm" enctype="multipart/form-data">
	<div class="layui-tab-item layui-show layui-form-pane">
<!--		{include file="admaterial/module_choose_section" /}-->
		<div class="layui-form-item">
			<label class="layui-form-label layui-form-text">素材名称:</label>
			<div class="layui-input-inline">
				<input type="text" class="layui-input" name="material_title" lay-verify="required" autocomplete="off" placeholder="请输入素材名称" {if condition="$imageform"}value="{$imageform.material_title}"{/if}>
			</div>
		</div>
        <div class="layui-form-item">
            <label class="layui-form-label">选择站点:</label>
            <div class="layui-input-inline">
                <select type="select" name="adsiteid" lay-filter="image_adsiteid" id="image_adsiteid">
                <option></option>
                {volist name="adsite" id = "site"}
                <option value="{$site['id']}" {if condition="isset($imageform.adsiteid) AND ($site.id eq $imageform.adsiteid)"} selected {/if}>{$site.sitename}</option>
                {/volist}
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">适配广告位 :</label>
            <div class="layui-input-inline">
                <select type="select" name="adsenseid" lay-filter="image_adsenseid" id="image_adsenseid">
                <option value=""></option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">匹配广告位的尺寸:</label>
            <div class="">
                <label class="">&nbsp;宽&nbsp;</label>
                <input type="number" class="my_input" name="width" id ="image_material_width" lay-verify="required|number" autocomplete="off" placeholder="" {if condition="$imageform"}value="{$imageform.width}"{/if} readonly>
                <label class="">&nbsp;高&nbsp;</label>
                <input type="number" class="my_input" name="height" id = "image_material_height" lay-verify="required|number"  autocomplete="off" placeholder="" {if condition="$imageform"}value="{$imageform.height}"{/if} readonly>
                <input type="hidden" name="adaptation" value="1" />
                <div class="layui-inline my_text_center my_wd400 my_l">该尺寸默认匹配选中广告位，不可手工编辑</div>
            </div>
        </div>
        <div class="layui-form-item">
            <lable class="layui-form-label layui-form-text">图片地址:</lable>
            <div class="">
                <button type="button" class="layui-btn" id="img_upload_btn" style="margin-top:0px;">
                    <i class="layui-icon">&#xe67c;</i>上传图片
                </button>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" name="image_url" id="image_url" {if condition="isset($imageform.image_url)"} value="{$imageform.image_url}" {/if}>
                </div>
            </div>
        </div>
		<div class="layui-form-item">
			<label class="layui-form-label">图片描述:</label>
			<div class="layui-input-inline">
				<input type="text" class="layui-input" name="image_description" autocomplete="off" placeholder="" {if condition="$imageform"}value="{$imageform.image_description}"{/if}>
			</div>
		</div>
        <div class="layui-form-item">
            <label class="layui-form-label">点击图片链接:</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="click_url" lay-verify="required|url" autocomplete="off" placeholder="" {if condition="$imageform"}value="{$imageform.click_url}"{/if}>
            </div>
            <div class="my_text_center my_wd400 my_l">地址请以 http:// 或者 https:// 开头</div>
        </div>
		<div class="layui-form-item">
			<label class="layui-form-label">目标窗口:</label>
			<div class="">
				<input type="radio" class="field-open_target" name="open_target" value="_blank" title="新窗口"  {if condition="($edit eq 0) OR $imageform AND $imageform.open_target eq '_blank'"} checked {/if}>
				<input type="radio" class="field-open_target" name="open_target" value="_parent" title="原窗口" {if condition="$imageform AND $imageform.open_target eq '_parent'"} checked {/if}>
			</div>
		</div>
	</div>
	<div class="layui-form-item">
		<div class="layui-input-block">
            <input type="submit" class="layui-btn" value="提交" lay-submit=""/>
            <input type="hidden" class="field-material_type" name="material_type" value="2">
            {if condition="$edit eq 1"}
            <input type="hidden" value="<?= $imageform['width'] .'|'. $imageform['height'] .'|' . $imageform['adsenseid'] ?>" id="selected_adsenseid"/>
			<input type="hidden" class="field-id" name="id" value="{if condition="isset($imageform.id)"}{$imageform.id}{/if}">
            {/if}
            <input type="hidden" class="" name="material_id" value="{if condition="isset($imageform.material_id)"}{$imageform.material_id | default = 0}{/if}">
			<a href="{:url('admin/admaterial/lists')}" class="layui-btn layui-btn-primary ml10"><i class="aicon ai-fanhui"></i>返回列表页</a>
		</div>
	</div>
</form>
{if condition="$edit eq 1"}
{include file="block/layui" /}
{include file="admaterial4image/imageform_help_js"}
<script>
    var formData = {:json_encode([])};
</script>
<script src="__ADMIN_JS__/footer.js"></script>
{/if}