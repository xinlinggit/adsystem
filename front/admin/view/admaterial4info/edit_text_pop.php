<!-- 信息流编辑弹出层 -->
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
                <input type="text" class="layui-input field-material_title" name="material_title" lay-verify="required" autocomplete="off" placeholder="请输入素材名称" />
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">类型:</label>
            <div class="">
                <input type="radio" class="field-type" lay-filter="type" name="" value="1" title="文字" checked />
            </div>
        </div>
        <div class="layui-form-item" id="info_pic_desc_box" style="display: block">
            <label class="layui-form-label">标题:</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input field-material_content" id="info_desc" name="material_content" lay-verify = "required" autocomplete="off" placeholder="16 字以内" />
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">点击文字链接:</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input field-click_url" name="click_url" lay-verify="required|url" autocomplete="off" placeholder="" />
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <a class="layui-btn" id="popConfirm" lay-submit lay-filter="edit">确认</a>
            <input type="hidden" class="field-material_type" name="material_type" value="1">
            <input type="hidden" class="adsiteid" name="adsiteid" />
            <input type="hidden" name="sid" class="field-sid" >
            <input type="hidden" name="id" class="field-id" >
        </div>
    </div>
</form>
{include file="admin@block/layui" /}
<script>
    // ajax 提交表单数据，并回传参数
    layui.use('form', function(){
        var form = layui.form;
        form.on('submit(edit)', function(data){
            var param = data.field;
            layui.jquery.post('{:url("admin/admaterial4info/pop_edit_text")}',param,function(data){
                var data = JSON.parse(data);
                if(data.code == 1){
                    // 触发父级页面函数
                    parent.{$callback}(data);
                    parent.layer.closeAll();
                    parent.layer.msg('编辑成功', {icon:1});
                }else{
                    parent.layer.closeAll();
                    parent.layer.msg(data.msg, {icon:5});
                }
            });
        })
    })
</script>
<script>
    var formData = {:json_encode($materialform)};
</script>
<script src="__ADMIN_JS__/footer.js"></script>
</body>
</html>