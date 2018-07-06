<!--广告统计-->
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
                <select name="platform" class="field-platform" type="select" lay-filter="platform" id="platform">
                    <option value="">请选择</option>
                    <option value="1" {if condition="$selected_platform eq 1"} selected {/if} >PC</option>
                    <option value="2" {if condition="$selected_platform eq 2"} selected {/if}>APP</option>
                    <option value="3" {if condition="$selected_platform eq 3"} selected {/if}>移动 web</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">选择广告:</label>
            <div class="layui-input-inline" style="width:300px;">
                <select name="adv" class="field-adv" class="" type="select" lay-filter="adv" id="statistics_adv">
                    <option value=""></option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="">
                <input type="text"   name="btime" id="serving_time_begin_1" class="my_input my_c field-btime" placeholder="开始时间"  value="{:input('btime')}" /> -
                <input type="text"    name="etime" id="serving_time_end_1" class="my_input my_c field-etime" placeholder="结束时间" value="{:input('etime')}"  />
            </div>
        </div>
        <div class="layui-form-item">
            <input lay-filter = "nextStep" type="submit" class="layui-btn" lay-submit="" value="查询" />
            <a href="{:url()}" class="layui-btn layui-btn-primary">重置</a>
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
                <th  style="text-align: center;" class="w100">CPM消耗</th>
                <th  style="text-align: center;" clas="w100">总点击量</th>
                <th  style="text-align: center;" class="w100">CPC消耗</th>
                <th style="text-align: center">创建时间</th>
            </tr>
            </thead>
            <tbody>
            {volist name="data_list" id="vo"}
            <tr>
                <td style="text-align: center;" >{$vo['advertisementid']}</td>
                <td style="text-align:center">{$vo['sitename']}</td>
                <td style="text-align: center;" >{$vo['title']}</td>
                <td style="text-align: center;" >{$vo['my_sum']}</td>
                <td style="text-align: center;" >{$vo['cost']}</td>
                <td style="text-align: center;" >{$vo['click_sum']?:0}</td>
                <td style="text-align: center;" >{$vo['cost_sum_click']?:0}</td>
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
        legend: {
            data:['曝光量', '点击量']
        },
        xAxis: {
            type: 'category',
            data: {$x_data},
            name : '日期',
            axisLabel :{
                interval:0,
                rotate:40,
            }
        },
        yAxis: {
            type: 'value',
            name : '曝光/点击量',
        },
        series: [{
            // data: [820, 932, 901, 934, 1290, 1330, 1320],
            name:"曝光量",
            data: {$y_data},
            type: 'line',
            label: {
                normal: {
                    show: true,
                    position: 'top'
                }
            },
        },
            {
                name:"点击量",
                // data: [820, 932, 901, 934, 1290, 1330, 1320],
                data: {$y_data_click},
                type: 'line',
                label: {
                    normal: {
                        show: true,
                        position: 'top'
                    }
                },
            }
        ],
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
    layui.use(['form'], function() {
        var $ = layui.jquery, form = layui.form;

        var btime = layui.jquery("#serving_time_begin_1").val();
        var etime = layui.jquery("#serving_time_end_1").val();
        if(btime > etime)
        {
            layer.msg("结束时间不能晚于开始时间", {icon:5});
            return false;
        }

        form.on('select(platform)', function(data) { // 事件监听
            var platform = data.value;
            $('#statistics_adv option').html("");
            form.render('select');
            $.ajax({
                method: "POST",
                url: "{:url('admin/statistics/getMyAdsAjax')}",
                data: {platform: platform}
            }).done(function(data){
                eval("var data=" + data + "");
                $.each(data, function(i, item){
                    var option = $("<option>").val(item.id).text(item.title);
                    $('#statistics_adv').append(option);
                    form.render('select');
                })
            })
        });
        var platform = $("#platform").val();
        if(platform){
            $.ajax({
                method: "POST",
                url: "{:url('admin/statistics/getMyAdsAjax')}",
                data: {platform: platform}
            }).done(function(data){
                eval("var data=" + data + "");
                $.each(data, function(i, item){
                    var option = $("<option>").val(item.id).text(item.title);
                    $('#statistics_adv').append(option);
                })
                var selected_adv = {:input('adv') ?: 0};
                if(selected_adv)
                {
                    input = $('.field-adv').find('option[value="'+ selected_adv +'"]');
                    input.prop("selected", true);
                }
                form.render('select');
            })
        }
    });
</script>