<link href="__STATIC__/myCommon.css?v=0.1" rel="stylesheet">
<form class="layui-form layui-form-pane" action="{if condition='$edit eq 1'}{:url('admaterial/edit')}{else/}{:url('admaterial/add')}{/if}" method="post" id="editForm">
	<div class="layui-tab-item layui-show layui-form-pane">
		<div class="layui-form-item">
			<label class="layui-form-label layui-form-text">素材名称:</label>
			<div class="layui-input-inline">
				<input type="text" class="layui-input" name="material_title" lay-verify="required" autocomplete="off" placeholder="请输入素材名称" {if condition="$textform"}value="{$textform.material_title}"{/if}>
			</div>
		</div>
        <div class="layui-form-item" style="display: none">
            <label class="layui-form-label">字体大小:</label>
            <div class="layui-input-inline">
                <input type="number" class="layui-input" id="font_size" lay-filter="font_size" name="font_size" lay-verify="" autocomplete="off" placeholder="" {if condition="isset($edit) AND ($edit eq 0)"} value = "14" {else/} {if condition="isset($textform.font_size)"}value="{$textform.font_size|default='14'}"{/if}{/if}>
            </div>
            <div class="my_text_center my_wd50 my_c">px</div>
        </div>
        <div class="layui-form-item" style="display: none">
            <label class="layui-form-label">默认文字颜色:</label>
            <div class="layui-input-inline">
                <input type="color" class="my_color" name="font_color"  autocomplete="off"  lay-verify="" placeholder="" {if condition="$textform"}value="{$textform.font_color|default='#000000'}"{/if}>
            </div>
        </div>
        <div class="layui-form-item" style="display: none">
            <label class="layui-form-label">默认文字样式:</label>
            <div class="layui-input-block">
                <input type="checkbox" name="font_decoration" lay-filter="uncheck_font_style_default" title="下划线" value = 'underline' {if condition="$textform AND $textform.font_decoration eq 'underline'"} checked {/if}>
                <input type="checkbox" name="font_weight" title="加粗" value = 'bold' {if condition="$textform AND $textform.font_weight eq 'bold'"} checked {/if} >
                <input type="checkbox" name="font_style" title="斜体" value = 'italic' {if condition="$textform AND $textform.font_style eq 'italic'"} checked {/if}>
            </div>
        </div>
        <div class="layui-form-item" style="display: none">
            <label class="layui-form-label">悬停文字颜色:</label>
            <div class="layui-input-inline">
                <input type="color" class="my_color" name="hover_font_color"  autocomplete="off"  lay-verify="" placeholder="" {if condition="$textform"}value="{$textform.hover_font_color|default='#000000'}"{/if}>
            </div>
        </div>
        <div class="layui-form-item" style="display: none">
            <label class="layui-form-label">悬停文件样式:</label>
            <div class="layui-input-block">
                <input type="checkbox" name="hover_font_decoration" title="下划线" value = 'underline' {if condition="$textform AND $textform.hover_font_decoration eq 'underline'"} checked {/if}>
                <input type="checkbox" name="hover_font_weight" title="加粗" value = 'bold' {if condition="$textform AND $textform.hover_font_weight eq 'bold'"} checked {/if} >
                <input type="checkbox" name="hover_font_style" title="斜体" value = 'italic' {if condition="$textform AND $textform.hover_font_style eq 'italic'"} checked {/if}>

            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">选择站点 :</label>
            <div class="layui-input-inline">
                <select type="select" name="adsiteid" lay-filter="adsiteid" id="adsiteid">
                <option></option>
                {volist name="adsite" id = "site"}
                <option value="{$site['id']}"  {if condition="isset($textform.adsiteid) AND ($site.id eq $textform.adsiteid)"} selected {/if}>{$site.sitename}</option>
                {/volist}
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">适配广告位 :</label>
            <div class="layui-input-inline">
                <select class="" type="select" name="adsenseid" lay-filter="adsenseid" id="text_adsenseid">
                <option value=""></option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">匹配广告位的尺寸:</label>
            <div class="layui-input-inblock">
                <label class="">&nbsp;宽&nbsp;</label>
                <input type="number" class="my_input" name="width" id="material_width" autocomplete="off"  lay-verify="required|number" placeholder="" {if condition="$textform"}value="{$textform.width|default='200'}"{/if} readonly>
                <label class="">&nbsp;高&nbsp;</label>
                <input type="number" class="my_input" name="height"  id="material_height" autocomplete="off"  lay-verify="required|number" placeholder="" {if condition="$textform"}value="{$textform.height|default='200'}"{/if} readonly>
                <div class="layui-inline my_text_center my_wd400 my_l">该尺寸默认匹配选中广告位，不可手工编辑</div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">文字内容:</label>
            <div class="layui-input-inline">
                <textarea name="material_content" placeholder="请输入内容" class="layui-textarea" lay-verify="required">{if condition="$textform"}{$textform.material_content}{/if}</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">点击文字链接:</label>
            <div class="layui-input-inline">
                <input type="url" class="layui-input" name="click_url"  lay-verify="required|url" autocomplete="off" placeholder="" {if condition="$textform"}value="{$textform.click_url}"{/if}>
            </div>
            <div class="my_text_center my_wd400 my_l">地址请以 http:// 或者 https:// 开头</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">水平位置:</label>
            <div class="layui-input-block">
                <input type="radio" name="horizon_position" value="left" title="左" {if condition="$textform AND $textform.horizon_position eq 'left'"} checked {/if}>
                <input type="radio" name="horizon_position" value="center" title="中"  {if condition="(isset($edit) AND ($edit eq 0)) OR ($textform AND ($textform.horizon_position eq 'center'))"} checked {/if}>
                <input type="radio" name="horizon_position" value="right" title="右" {if condition="$textform AND $textform.horizon_position eq 'right'"} checked {/if}>
            </div>
        </div>
		<div class="layui-form-item" style="display: none">
			<label class="layui-form-label">边距值:</label>
			<div class="layui-input-inline">
				<input type="number" class="layui-input" name="margin" autocomplete="off" lay-verify="number" placeholder="" {if condition="isset($edit) AND ($edit eq 0)"} value="10" {else/} {if condition="isset($textform.margin)"}value="{$textform.margin|default = 10}"{/if}{/if}>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">目标窗口:</label>
			<div class="">
				<input type="radio" class="field-status" name="open_target" value="_blank" title="新窗口"  {if condition="(isset($edit) AND ($edit eq 0)) OR ($textform AND ($textform.open_target eq '_blank'))"} checked {/if}>
				<input type="radio" class="field-status" name="open_target" value="_parent" title="原窗口" {if condition="$textform AND $textform.open_target eq '_parent'"} checked {/if}>
			</div>
		</div>
	</div>
	<div class="layui-form-item">
		<div class="layui-input-block">
            {if condition ="$edit eq 1"}
            <input type="hidden" value="<?= $textform['width'] .'|'. $textform['height'] .'|' . $textform['adsenseid'] ?>" id="selected_text_adsenseid"/>
            <input type="hidden" class="field-id" name="id" value="{if condition="isset($textform.id)"}{$textform.id}{/if}">
            {/if}
            <input type="hidden" class="field-material_id" name="material_id" value="{if condition="isset($textform.material_id)"}{$textform.material_id | default = 0}{/if}">
            <input type="hidden" class="field-material_type" name="material_type" value="1">
            <input type="submit" class="layui-btn" value="提交" lay-submit="" />
            <a href="{:url('admin/admaterial/lists')}" class="layui-btn layui-btn-primary ml10"><i class="aicon ai-fanhui"></i>返回列表页</a>
		</div>
	</div>
</form>
{if condition="$edit eq 1"}
{include file="block/layui" /}
{include file="admaterial/textform_help_js"}
<script>
    var formData = {:json_encode($textform)};
</script>
<script src="__ADMIN_JS__/footer.js"></script>
{/if}
