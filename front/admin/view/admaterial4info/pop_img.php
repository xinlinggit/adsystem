<!--图片添加弹出层-->
<!DOCTYPE html>
<html>
<head>
    <title>{$_admin_menu_current['title']}-后台首页</title>
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link rel="stylesheet" href="__ADMIN_JS__/layui/css/layui.css">
    <link rel="stylesheet" href="__ADMIN_CSS__/style.css?v={:time()}">
</head>
<body class="pb50">
<link href="__STATIC__/myCommon.css?v=0.1" rel="stylesheet">
<style>
    .layui-form-pane .layui-form-label {width:200px;}
</style>
<form class="layui-form layui-form-pane" action="" method="post" id="editForm" enctype="multipart/form-data" style="margin-left:30px;">
    <div class="layui-tab-item layui-show layui-form-pane" style="margin-top:20px;">
        <div class="layui-form-item">
            <label class="layui-form-label layui-form-text">素材名称:</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="material_title" lay-verify="required" autocomplete="off" placeholder="请输入素材名称" />
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">类型:</label>
            <div class="">
                <input type="radio" class="field-type" lay-filter="type" name="" value="3" title="图片" checked />
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">选中尺寸:</label>
            <div class="">
                <label class="">&nbsp;宽&nbsp;</label>
                <input type="number" class="my_input" name="width" id ="info_width" lay-verify="required|number" autocomplete="off" placeholder="" value="{$width}" readonly />
                <label class="">&nbsp;高&nbsp;</label>
                <input type="number" class="my_input" name="height" id = "info_height" lay-verify="required|number"  autocomplete="off" placeholder="" value="{$height}" readonly />
                <input type="hidden" name="adaptation" value="1" />
            </div>
        </div>
        <div class="layui-form-item" id="info_pic_box" style="display: block;">
            <lable class="layui-form-label layui-form-text">图片地址:</lable>
            <div class="">
                <button type="button" class="layui-btn" id="info_pop_upload_btn" style="margin-top:0px;">
                    <i class="layui-icon">&#xe67c;</i>上传图片
                </button>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" name="image_url" id="info_pop_url" lay-verify="required|url" placeholder="素材大小建议不超过 100k " />
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">点击图片链接:</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="click_url" lay-verify="required|url" autocomplete="off" placeholder="" />
            </div>
        </div>
        <div class="layui-form-item" style="display:none">
            <label class="layui-form-label">目标窗口:</label>
            <div class="">
                <input type="radio" class="field-open_target" name="open_target" value="_blank" title="新窗口"  checked/>
<!--                <input type="radio" class="field-open_target" name="open_target" value="_parent" title="原窗口" />-->
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <a class="layui-btn" id="popConfirm" lay-submit lay-filter="add">添加</a>
            <input type="hidden" class="field-material_type" name="material_type" value="2">
            <input type="hidden" name="adsiteid" value="{$adsite}" />
        </div>
    </div>
</form>
{include file="admin@block/layui" /}
<script>
    // ajax 提交表单数据，并回传参数
    layui.use('form', function(){
        var form = layui.form;
        form.on('submit(add)', function(data){
            var param = data.field;
            layui.jquery.post('{:url("admin/admaterial4info/addimg")}',param,function(data){
                // TODO: 获取图片的长宽，不符合尺寸的时候提示。
                var data = JSON.parse(data);
                if(data.code == 1){
                    // 触发父级页面函数
                    parent.{$callback}(data);
                    parent.layer.closeAll();
                    parent.layer.msg('添加成功', {icon:1});
                }else{
                    parent.layer.msg(data.msg, {icon:5});
                }
            });
        })
    })

    // 上传文件
    layui.use('upload', function(){
        var $ = layui.jquery;
        var upload = layui.upload;

        //执行实例
        upload.render({
            elem: '#info_pop_upload_btn' //绑定元素
            ,field:'image'
            ,url: "{:url('admin/admaterial4info/doImg')}" //上传接口
            ,data:{"width":{$width}, "height":{$height}}
            ,done: function(res){
                if(res.code == 1)
                {
                    document.getElementById("info_pop_url").value = res.url;
                } else if (res.code == 0) {
                    layer.msg(res.msg, {icon:5});
                }
            }
            ,accept: 'file'
            ,error: function(){
            }
        });
    });
</script>
</body>
</html>