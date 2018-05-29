<script>
    // 加载所有广告位
    function loadAdPosition()
    {
        var $ = layui.jquery, form = layui.form;
        var adsite = $("#adsiteid").val();
        $('#text_adsenseid option').html("");
        form.render('select');
        $.ajax({
            method: "POST",
            url: "{:url('admin/admaterial/getAdPosition')}",
            data: {adsiteid: adsite, material_type: "text"}
        }).done(function(data){
            eval("var data=" + data + "");
            var options = '';
            $.each(data, function(i, item){
                var option = $("<option>").val(item.width + '|' + item.height + '|' + item.id).text(item.sensename);
                $('#text_adsenseid').append(option);

                // 在编辑状态时，选中广告位的值
                var selected_text_adsenseid = $("#selected_text_adsenseid").val();
                if(selected_text_adsenseid)
                {
                    $("#text_adsenseid").find("option[value = '"+selected_text_adsenseid+"']").attr("selected","selected");
                }
                form.render('select');
            })
        })
    }

    // 根据选择的广告位填充素材的宽高
    function loadWidthHeight(data)
    {
        var val_arr = data.value.split('|');
        var width = val_arr[0];
        var height = val_arr[1];
        document.getElementById('material_width').value = width;
        document.getElementById('material_height').value = height;
    }

    layui.use(['form'], function() {
        var $ = layui.jquery, form = layui.form;
        var couplet_form = document.getElementById('couplet_form');
        form.on('select(adsiteid)', function(data) {
            loadAdPosition();
        });
        form.on('select(adsenseid)', function(data) {
            loadWidthHeight(data);
        });

        // 编辑时，填充广告位列表
        loadAdPosition();
    });
</script>
<script>
    layui.use(['form','jquery', 'element'], function() {
        var $ = layui.jquery, form = layui.form;
        form.on('checkbox(uncheck_font_style_default)', function(data) {

        });
        // TODO:
        $(document).on('click', '.layui-tab-close', function() {
            $('.layui-nav-child a[data-id="'+$(this).parent('li').attr('lay-id')+'"]').css({color:'rgba(255,255,255,.7)'});
        });
    });
</script>