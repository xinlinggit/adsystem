<form action="{:url('admin/authentication/add')}" method="post" class="layui-form" onsubmit="return checkform()">
    <div class="layui-form-item">
        <label class="layui-form-label">姓名</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-username" name="username" lay-verify="required" autocomplete="off" placeholder="" >
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">公司名</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-company" name="company" lay-verify="required" autocomplete="off" placeholder="" >
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">行业</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-industry" name="industry" lay-verify="required" autocomplete="off" placeholder="" >
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">联系电话</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-phone" name="phone" lay-verify="required" autocomplete="off" placeholder="" >
        </div>
    </div>
    <div class="layui-form-item">
        <button type="button" class="layui-btn" id="ID_card_front">
            <i class="layui-icon">&#xe67c;</i>身份证（正）
        </button>
        <input type="hidden" id="ID_card_front_url" name="ID_card_front_url"/>
        <img class = "ID_card_front_url" width="400px" height="400px" />
    </div>
    <div class="layui-form-item">
        <button type="button" class="layui-btn" id="ID_card_end">
            <i class="layui-icon">&#xe67c;</i>身份证（反）
        </button>
        <input type="hidden" id="ID_card_end_url"  name="ID_card_end_url"/>
        <img class = "ID_card_end_url"  width="400px" height="400px" />
    </div>
    <div class="layui-form-item">
        <button type="button" class="layui-btn" id="business_card_front">
            <i class="layui-icon">&#xe67c;</i>营业执照&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </button>
        <input type="hidden" id="business_license_front_url"  name="business_license_front_url"/>
        <img class = "business_license_front_url" width="400px" height="400px"  />
    </div>
    <div class="layui-form-item">
        <input type="submit" class="layui-btn" value="提交审核" />
    </div>
</form>
{include file="block/layui" /}
<script>

    function checkform(){
        var $ = layui.jquery;
        // 检查所有的文件是否都已选择，否则提示
        var ID_card_front_url = $("#ID_card_front_url").val();
        var ID_card_end_url = $("#ID_card_end_url").val();
        var business_license_front_url = $("#business_license_front_url").val();
        if((!ID_card_front_url) || (!ID_card_end_url) || (!business_license_front_url))
        {
            layer.msg('请检查上传文件', {icon:5});
            return false;
        }
    }

    layui.use('upload', function(){
        var $ = layui.jquery;
        var upload = layui.upload;

        //执行实例
        var uploadInst = upload.render({
            elem: '#ID_card_front' //绑定元素
            ,field:'image'
            ,url: "{:url('admin/authentication/doImg')}" //上传接口
            ,done: function(res){
                //上传完毕回调
                $("#ID_card_front_url").val(res.url);
                $(".ID_card_front_url").attr("src", res.url);
            }
            ,error: function(){
                //请求异常回调
                alert('身份证（正）上传失败，请重试。');
            }
        });
        upload.render({
            elem: '#ID_card_end' //绑定元素
            ,field:'image'
            ,url: "{:url('admin/authentication/doImg')}" //上传接口
            ,done: function(res){
                //上传完毕回调
                $("#ID_card_end_url").val(res.url);
                $(".ID_card_end_url").attr("src", res.url);
            }
            ,error: function(){
                //请求异常回调
                alert('身份证（反）上传失败，请重试。');
            }
        });
        upload.render({
            elem: '#business_card_front' //绑定元素
            ,field:'image'
            ,url: "{:url('admin/authentication/doImg')}" //上传接口
            ,done: function(res){
                //上传完毕回调
                $("#business_license_front_url").val(res.url);
                $(".business_license_front_url").attr("src", res.url);
            }
            ,error: function(){
                //请求异常回调
                alert('营业执照（正）上传失败，请重试。');
            }
        });
        upload.render({
            elem: '#business_card_end' //绑定元素
            ,field:'image'
            ,url: "{:url('admin/authentication/doImg')}" //上传接口
            ,done: function(res){
                //上传完毕回调
                $("#business_license_end_url").val(res.url);
                $(".business_license_end_url").attr("src", res.url);
            }
            ,error: function(){
                //请求异常回调
                alert('营业执照（反）上传失败，请重试。');
            }
        });
    });
</script>