<script src="__STATIC__/js/echarts.min.js"></script>
<div id="my_charts" style="width: 1200px;height:400px;"></div>
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('my_charts'));

    // 指定图表的配置项和数据
    var option = {
        xAxis: {
            type: 'category',
            // data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
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