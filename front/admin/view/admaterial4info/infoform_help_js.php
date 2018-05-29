<script>
    function loadSize(obj)
    {
        var $ = layui.jquery;
        var text = obj.text;
        var size_arr = text.split('*');
        var width = size_arr[0];
        var height = size_arr[1];
        document.getElementById("info_width").value = width;
        document.getElementById("info_height").value = height;
    }

    // 加载所有广告位对应的尺寸
    function infoLoadAdPositionSize()
    {
        var $ = layui.jquery, form = layui.form;
        var adsite = $("#info_adsiteid").val();
        $('#size_optional option').html("");
        $.ajax({
            method: "POST",
            url: "{:url('admin/admaterial/getAdPositionSize')}",
            data: {adsiteid: adsite}
        }).done(function(data){
            var data = JSON.parse(data);
            document.getElementById("size_optional").style.display = 'none';
            $.each(data, function(i, item){
                var span = $("<a style='' onclick='javascript:loadSize(this)'>").text(item.width + '*' + item.height);
                $('#size_optional').append(span);
                $('#size_optional').append("&nbsp;&nbsp;");
            })
            document.getElementById("size_optional").style.display = 'block';
        })
    }

    /**
     * 图片和图文是否是必填
     * @param type
     */
    function check_required_field(type)
    {
        var $ = layui.jquery;
		var info_desc = $("#info_desc");
		var info_url = $("#info_url");
		switch (type)
		{
		    // 文字
			case '1':
                info_desc.attr("lay-verify", "required");
                info_url.attr("lay-verify","");
			    break;
			// 图片
			case "2":
                info_desc.attr("lay-verify", "");
                info_url.attr("lay-verify","required");
			    break;
			// 图文
			case "3":
                info_desc.attr("lay-verify", "required");
                info_url.attr("lay-verify","required");
                break;
			default:
			    // Nothing
				break;
        }
    }

    // 切换图片描述的显示隐藏
    function show_hide_adv_desc(type){
        var $ = layui.jquery, form = layui.form;
        var type = $("input[name = 'type']:checked").val();
        var img_box = document.getElementById("info_pic_box");
        var text_box = document.getElementById("info_pic_desc_box");
        switch (type)
        {
	        case '1':
		        // 文字
		        text_box.style.display = 'block';
		        img_box.style.display = 'none';
	            break;
	        case '2':
	            // 图片
		        text_box.style.display = 'none';
		        img_box.style.display = 'block';
	            break;
	        case '3':
	            // 图文
                text_box.style.display = 'block';
                img_box.style.display = 'block';
	            break;
	        default:
	            // Nothing
		        break;
        }
    }

    layui.use(['form'], function() {
        var $ = layui.jquery, form = layui.form;
        form.on('select(info_adsiteid)', function(data) {
            infoLoadAdPositionSize();
        });

        infoLoadAdPositionSize();

        // 在编辑的时候，选中广告位选项
        // infoLoadAdPosition();

        form.on('radio(type)', function(data){
            // 显示隐藏图片文字表单元素
            show_hide_adv_desc(data.value);

            // 隐藏的表单元素，去掉 required 属性
            check_required_field(data.value);
        });
    });
</script>
<script>
    layui.use('upload', function(){
        var $ = layui.jquery;
        var upload = layui.upload;

        //执行实例
        upload.render({
            elem: '#info_upload_btn' //绑定元素
            ,field:'image'
            ,url: "{:url('admin/admaterial4info/doImg')}" //上传接口
            ,done: function(res){
                document.getElementById("info_url").value = res.url;
            }
            ,accept: 'file'
            ,error: function(){
            }
        });
    });
</script>