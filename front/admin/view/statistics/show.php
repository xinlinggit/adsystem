<link href="__STATIC__/myCommon.css?v=0.1" rel="stylesheet">
<script src="__STATIC__/js/echarts.min.js"></script>
<style>
    .layui-form-pane .layui-form-label {width:200px;}
</style>
<form class="layui-form layui-form-pane" action="{:url()}" method="get" id="editForm">
    <div class="layui-tab-item layui-show layui-form-pane">
        <div class="layui-form-item">
            <label class="layui-form-label">选择站点:</label>
            <div class="layui-input-inline" style="width:300px;">
                <select name="adsiteid" class="field-adsiteid" type="select" lay-filter="adsiteid" id="adsiteid">
                    <option value="">请选择</option>
                    {volist name="ad_site" id = "as"}
                    <option value="{$as['adsiteid']}" {if condition="input('get.adsiteid') eq $as['adsiteid']"} selected {/if}>{$as.sitename}</option>
                    {/volist}
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">选择广告位:</label>
            <div class="layui-input-inline" style="width:300px;">
                <select name="adsenseid" class="" type="select" lay-filter="adsenseid" id="adsenseid">
                <option></option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">选择广告:</label>
            <div class="layui-input-inline" style="width:300px;">
                <select name="adv" class="field-adv" class="" type="select" lay-filter="adv" id="adv">
                    <option value=""></option>
                </select>
            </div>
        </div>
<!--        <div class="layui-form-item">-->
<!--            <label class="layui-form-label">选择素材:</label>-->
<!--            <div class="layui-input-inline" style="width:300px;">-->
<!--                <select name="materialid" class="" class="" type="select" lay-filter="" id="">-->
<!--                    <option value=""></option>-->
<!--                    {volist name="materials" id = 'm'}-->
<!--                    <option value="{$m['id']}" {if condition="input('get.materialid') eq $m['id']"} selected {/if}>{$m.material_title}</option>-->
<!--                    {/volist}-->
<!--                </select>-->
<!--            </div>-->
<!--        </div>-->
        <div class="layui-form-item">
            <div class="">
                <input type="text"   name="btime" id="serving_time_begin_1" class="my_input my_c field-btime" placeholder="开始时间" /> -
                <input type="text"    name="etime" id="serving_time_end_1" class="my_input my_c field-etime" placeholder="结束时间" />
            </div>
        </div>
        <div class="layui-form-item">
            <a href="{:url()}" class="layui-btn layui-btn-primary">重置</a>
            <input lay-filter = "nextStep" type="submit" class="layui-btn" lay-submit="" value="查询" />
            <input type="hidden" value="{$selected_adsenseid}" id="selected_adsenseid" />
            <input type="hidden" value="{$selected_adv}" id="selected_adv" />
        </div>
    </div>
</form>
<div id="my_charts" style="width: 1200px;height:400px;"></div>
<form id="pageListForm">
    <div class="layui-form">
        <table class="layui-table mt10" lay-even="" lay-skin="row">
            <colgroup>
                <col width="50">
            </colgroup>
            <thead>
            <tr>
                <th  style="text-align: center;" class="w100">广告ID</th>
                <th  style="text-align: center;" class="">站点</th>
                <th  style="text-align: center;" class="">广告名称</th>
                <th  style="text-align: center;" clas="w100">曝光量</th>
                <th  style="text-align: center;" class="w100">消耗</th>
                <th style="text-align: center">创建时间</th>
            </tr>
            </thead>
            <tbody>
            {volist name="data_list" id="vo"}
            <tr>
                <td style="text-align: center;" >{$vo['id']}</td>
                <td style="text-align:center">{$vo['sitename']}</td>
                <td style="text-align: center;" >{$vo['title']}</td>
                <td style="text-align: center;" >{$vo['sum']}</td>
                <td style="text-align: center;" >{$vo['cost']}</td>
                <td style="text-align: center;">{$vo['time']}</td>
            </tr>
            {/volist}
            </tbody>
        </table>
        {$pages}
    </div>
</form>
{include file="block/layui" /}
<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="width: 600px;height:400px;"></div>
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('my_charts'));

    // 指定图表的配置项和数据
    var option = {
        xAxis: {
            type: 'category',
            data: {$x_data},
            name : '日期',
        },
        yAxis: {
            type: 'value',
            name : '曝光量',
        },
        series: [{
            // data: [820, 932, 901, 934, 1290, 1330, 1320],
            data: {$y_data},
            type: 'line',
            label: {
                normal: {
                    show: true,
                    position: 'top'
                }
            },
        }]
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
</script>
<script>
    layui.use('laydate', function(){
        // TODO: 结束时间需要大于开始时间，多段时间段不能交叉
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#serving_time_begin_1' //指定元素
        });
        //执行一个laydate实例
        laydate.render({
            elem: '#serving_time_end_1' //指定元素
        });
    });

    // 加载广告
    function loadAdv()
    {
        var $ = layui.jquery, form = layui.form;
        var adsenseid = $("#adsenseid").val();
        $('#adv option').html("");
        form.render('select');
        $.ajax({
            method: "POST",
            url: "{:url('admin/statistics/getAd')}",
            data: {adsenseid: adsenseid}
        }).done(function(data){
            eval("var data=" + data + "");
            var selected_adv = $("#selected_adv").val()
            $.each(data, function(i, item){
                var option = '';
                if(item.id == selected_adv)
                {
                    option = $("<option selected='selected'>").val(item.id).text(item.title);
                } else {
                    option = $("<option>").val(item.id).text(item.title);
                }
                $('#adv').append(option);
            })
            form.render('select');
        })
    }

    // 加载广告位
    function loadSense()
    {
        var $ = layui.jquery, form = layui.form;
        var adsiteid = $("#adsiteid").val();
        $('#adsenseid option').html("");
        form.render('select');
        $.ajax({
            method: "POST",
            url: "{:url('admin/statistics/getPosition')}",
            data: {adsiteid: adsiteid}
        }).done(function(data){
            var selected_adsenseid = $("#selected_adsenseid").val();
            eval("var data=" + data + "");
            var option = '';
            $.each(data, function(i, item){
                if(item.id == selected_adsenseid)
                {
                    option = $("<option selected>").val(item.id).text(item.sensename);
                } else {
                    option = $("<option>").val(item.id).text(item.sensename);
                }
                $('#adsenseid').append(option);
            })
            form.render('select');
            loadAdv();
        })
    }

    function selected_adsense()
    {
        var $ = layui.jquery, form = layui.form;
        var selected_adsenseid = $("#adsenseid").find('option[value="{$selected_adsenseid}"]');
        selected_adsenseid.attr("selected", "selected");
        form.render();
    }

    function selected_adv()
    {
        var $ = layui.jquery, form = layui.form;
        var selected_adv = $("#adv").find('option[value="{$selected_adv}"]');
        selected_adv.prop("selected", true);
        form.render('select');
    }

    layui.use(['form'], function() {
        var $ = layui.jquery, form = layui.form;
        form.on('select(adsenseid)', function(data) {
            loadAdv();
        });
        form.on('select(adsiteid)', function(data){
           loadSense();
        });
        loadSense();
        selected_adsense();
        selected_adv();
    });

</script>