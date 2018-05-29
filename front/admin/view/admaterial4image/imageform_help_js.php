<script>
    // 加载所有广告位
    function imageLoadAdPosition()
    {
        var $ = layui.jquery, form = layui.form;
        var adsite = $("#image_adsiteid").val();
        $('#image_adsenseid option').html("");
        form.render('select');
        $.ajax({
            method: "POST",
            url: "{:url('admin/admaterial/getAdPosition')}",
            data: {adsiteid: adsite, material_type: "image"}
        }).done(function(data){
            var data = JSON.parse(data);
            var options = '';
            $.each(data, function(i, item){
                var option = $("<option>").val(item.width + '|' + item.height + '|' + item.id).text(item.sensename);
                $('#image_adsenseid').append(option);

                // 在编辑状态时，选中广告位的值
                var selected_adsenseid = $("#selected_adsenseid").val();
                if(selected_adsenseid)
                {
                    $("#image_adsenseid").find("option[value = '"+selected_adsenseid+"']").attr("selected","selected");
                }
                form.render('select');
            })
        })
    }

    // 根据选择的广告位填充素材的宽高
    function imageLoadWidthHeight(data)
    {
        var val_arr = data.value.split('|');
        var width = val_arr[0];
        var height = val_arr[1];
        document.getElementById('image_material_width').value = width;
        document.getElementById('image_material_height').value = height;
    }

    layui.use(['form'], function() {
        var $ = layui.jquery, form = layui.form;
        form.on('select(image_adsiteid)', function(data) {
            imageLoadAdPosition();
        });
        form.on('select(image_adsenseid)', function(data) {
            imageLoadWidthHeight(data);
        });
        // 在编辑的时候，选中广告位选项

        imageLoadAdPosition();
    });
</script>
<script>
    layui.use('upload', function(){
        var $ = layui.jquery;
        var upload = layui.upload;

        //执行实例
        upload.render({
            elem: '#img_upload_btn' //绑定元素
            ,field:'image'
            ,url: "{:url('admin/admaterial4image/doImg')}" //上传接口
            ,done: function(res){
                document.getElementById("image_url").value = res.url;
            }
            ,accept: 'file'
            ,error: function(){
            }
        });
    });
</script>