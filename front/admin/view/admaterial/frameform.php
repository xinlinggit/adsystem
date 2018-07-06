<style>
	.layui-form-pane .layui-form-label {width:200px;}
</style>
<form class="layui-form layui-form-pane" action="{:url()}" method="post" id="editForm">
    <div class="layui-tab-item layui-show layui-form-pane">
        {include file="admaterial/module_choose_section" /}
    </div>
</form>
<div id="text_form" style="">
	{include file="admaterial/textform" /}
</div>
<div id="image_form" style="display:none">
    {include file="admaterial4image/imageform" /}
</div>
<div id="flash_form" style="display:none">
    {include file="admaterial4flash/flashform" /}
</div>
<div id="info_form" style="display:none">
    {include file="admaterial4info/infoform" /}
</div>
{include file="block/layui" /}
<!-- 文字 begin -->
{include file="admaterial/textform_help_js"}
<!-- 文字 end -->

<!-- 图片 begin -->
{include file="admaterial4image/imageform_help_js"}
<!-- 图片 end -->

<!-- flash begin -->
{include file="admaterial4flash/flashform_help_js"}
<!-- flash end -->

<!--  信息流  begin  -->
{include file="admaterial4info/infoform_help_js"}
<!--  信息流  end  -->
<script>
    function hide(obj){
        obj.style.display = "none";
    }

    function show(obj){
        obj.style.display = "block";
    }
    layui.use(['form'], function() {
        var $ = layui.jquery, form = layui.form;
        var text_form = document.getElementById('text_form');
        var image_form = document.getElementById('image_form');
        var flash_form = document.getElementById('flash_form');
        var info_form = document.getElementById('info_form');
        form.on('select(choose_type)', function(data) {
			if(data.value == 1){
                hide(flash_form);
                hide(image_form);
                hide(info_form);
                show(text_form);
			}
            else if(data.value == 2){
                hide(flash_form);
                show(image_form);
                hide(text_form);
                hide(info_form);
            }else if(data.value == 3){
                show(flash_form);
                hide(image_form);
                hide(text_form);
                hide(info_form);
            }
            else if(data.value == 4){
                hide(flash_form);
                hide(image_form);
                hide(text_form);
                show(info_form);
            }
        });
    });
</script>