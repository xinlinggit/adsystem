<style>
    .layui-form-pane .layui-form-label {width:200px;}
</style>
<link href="__STATIC__/myCommon.css?v=0.1" rel="stylesheet">
<form class="layui-form layui-form-pane" action="{if condition='$edit eq 1'}{:url('admin/admaterial4flash/edit')} {else/}{:url('admin/admaterial4flash/addflash')}{/if}" method="post" id="editForm" enctype="multipart/form-data" onsubmit="return check_flash_click_url()">
    <div class="layui-tab-item layui-show layui-form-pane">
<!--        {include file="admaterial/module_choose_section" /}-->
        <div class="layui-form-item">
            <label class="layui-form-label layui-form-text">素材名称:</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="material_title" lay-verify="required" autocomplete="off" placeholder="请输入素材名称" {if condition="$flashform"}value="{$flashform.material_title}"{/if}>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">选择站点 :</label>
            <div class="layui-input-inline">
                <select type="select" lay-filter="flash_adsiteid" name="adsiteid" id="flash_adsiteid">
                <option></option>
                {volist name="adsite" id = "site"}
                <option value="{$site['id']}" {if condition="isset($flashform.adsiteid) AND ($site.id eq $flashform.adsiteid)"} selected {/if}>{$site.sitename}</option>
                {/volist}
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">适配广告位 :</label>
            <div class="layui-input-inline">
                <select class="" type="select"  name="adsenseid" lay-filter="flash_adsenseid" id="flash_adsenseid">
                <option value=""></option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">匹配广告位的尺寸:</label>
            <div class="">
                <label class="">&nbsp;宽&nbsp;</label>
                <input type="text" class="my_input" name="width" id="flash_material_width" lay-verify="required|number" autocomplete="off" placeholder="" {if condition="$flashform"}value="{$flashform.width|default='14'}"{/if} readonly>
                <label class="">&nbsp;高&nbsp;</label>
                <input type="text" class="my_input" name="height" id="flash_material_height" lay-verify="required|number"  autocomplete="off" placeholder="" {if condition="$flashform"}value="{$flashform.height|default='14'}"{/if} readonly>
                <div class="layui-inline my_text_center my_wd400 my_l">该尺寸默认匹配选中广告位，不可手工编辑</div>
            </div>
        </div>
        <div class="layui-form-item">
            <lable class="layui-form-label layui-form-text">图片地址:</lable>
            <div class="">
                <button type="button" class="layui-btn" id="flash_upload_btn" style="margin-top:0px;">
                    <i class="layui-icon">&#xe67c;</i>上传图片
                </button>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" name="image_url" id="flash_url" {if condition="isset($flashform.image_url)"} value="{$flashform.image_url}" {/if}>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">点击 Flash 链接 ( 可选 ) :</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="click_url" id="flash_click_url" autocomplete="off" placeholder="" {if condition="$flashform"}value="{$flashform.click_url}"{/if}>
            </div>
            <div class="my_text_center my_wd400 my_l">地址请以 http:// 或者 https:// 开头</div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="hidden" class="field-material_type" name="material_type" value="3">
            {if condition="$edit eq 1"}
            <input type="hidden" value="<?= $flashform['width'] .'|'. $flashform['height'] .'|' . $flashform['adsenseid'] ?>" id="selected_flash_adsenseid"/>
            <input type="hidden" class="field-id" name="id" value="{if condition="isset($flashform.id)"}{$flashform.id}{/if}">
            {/if}
            <input type="hidden" class="field-material_id" name="material_id" value="{if condition="isset($flashform.material_id)"}{$flashform.material_id}{/if}">
            <input type="submit" class="layui-btn" value="提交" lay-submit=""/>
            <a href="{:url('admin/admaterial/lists')}" class="layui-btn layui-btn-primary ml10"><i class="aicon ai-fanhui"></i>返回列表页</a>
        </div>
    </div>
</form>
{if condition="$edit eq 1"}
{include file="block/layui" /}
{include file="admaterial4flash/flashform_help_js"}
<script>
    var formData = {:json_encode([])};
</script>
<script src="__ADMIN_JS__/footer.js"></script>
{/if}