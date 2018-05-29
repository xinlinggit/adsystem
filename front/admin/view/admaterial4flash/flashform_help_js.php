<script>
    // 加载所有广告位
    function flashLoadAdPosition()
    {
        var $ = layui.jquery, form = layui.form;
        var adsite = $("#flash_adsiteid").val();
        $('#flash_adsenseid option').html("");
        form.render('select');
        $.ajax({
            method: "POST",
            url: "{:url('admin/admaterial/getAdPosition')}",
            data: {adsiteid: adsite, material_type: "flash"}
        }).done(function(data){
            eval("var data=" + data + "");
            var options = '';
            $.each(data, function(i, item){
                var option = $("<option>").val(item.width + '|' + item.height + '|' + item.id).text(item.sensename);
                $('#flash_adsenseid').append(option);

                // 在编辑状态时，选中广告位的值
                var selected_flash_adsenseid = $("#selected_flash_adsenseid").val();
                if(selected_flash_adsenseid)
                {
                    $("#flash_adsenseid").find("option[value = '"+selected_flash_adsenseid+"']").attr("selected","selected");
                }

                form.render('select');
            })
        })
    }

    // 检查 flash 点击 url 的格式
    function check_flash_click_url()
    {
        var url_input = document.getElementById("flash_click_url");
        var url_val = url_input.value;
        if(url_val == '')
        {
            return true;
        }
        if((url_val.indexOf('http://') == -1) && (url_val.indexOf('https://')))
        {
            layer.msg("请输入合法的 URL 地址", {icon: 5});
            url_input.focus();
            return false;
        } else {
            return true;
        }
    }

    // 根据选择的广告位填充素材的宽高
    function flashLoadWidthHeight(data)
    {
        var val_arr = data.value.split('|');
        var width = val_arr[0];
        var height = val_arr[1];
        document.getElementById('flash_material_width').value = width;
        document.getElementById('flash_material_height').value = height;
    }

    layui.use(['form'], function() {
        var $ = layui.jquery, form = layui.form;
        var couplet_form = document.getElementById('couplet_form');
        form.on('select(flash_adsiteid)', function(data) {
            flashLoadAdPosition();
        });
        form.on('select(flash_adsenseid)', function(data) {
            flashLoadWidthHeight(data);
        });

        // 编辑时，填充广告位列表
        flashLoadAdPosition();
    });
</script>
<script>
    layui.use('upload', function(){
        var $ = layui.jquery;
        var upload = layui.upload;

        //执行实例
        upload.render({
            elem: '#flash_upload_btn' //绑定元素
            ,field:'image'
            ,url: "{:url('admin/admaterial4flash/doImg')}" //上传接口
            ,done: function(res){
                document.getElementById("flash_url").value = res.url;
            }
            ,accept: 'file'
            ,error: function(){
            }
        });
    });
</script>