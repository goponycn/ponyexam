define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {
            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echart'));

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '题库数量汇总',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: [__('Questions')]
                },
                toolbox: {
                    show: false,
                    feature: {
                        magicType: {show: true, type: ['stack', 'tiled']},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: true,
					name: '题型',    // 轴名称
					axisTick: {show: false},

                    data: Questiondata.column
                },
                yAxis: {
					splitLine: {show: true}
					
				},
                grid: [{
                    left: 100,
                    top:  100,
                    right: 100,
                    bottom: 100
                }],
				
                series: [{
                    name: __('Questions'),
                    type: 'bar',
			
					silent: true,
					barGap: '-100%', // Make series be overlap
					z: 10,
					legendHoverLink: true,  // 是否启用图列 hover 时的联动高亮
					label: {   // 图形上的文本标签
					        show: false,
					        position: 'insideTop', // 相对位置
					        rotate: 0,  // 旋转角度
					        color: '#eee'
					},
					itemStyle: {    // 图形的形状
					        color: 'blue',
						},
					barWidth: 50,  // 柱形的宽度
					barCategoryGap: '20%',  // 柱形的间距
                    smooth: true,
                    data: Questiondata.data
                }]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);

    
            $(window).resize(function () {
                myChart.resize();
            });

            $(document).on("click", ".btn-checkversion", function () {
                top.window.$("[data-toggle=checkupdate]").trigger("click");
            });

            $(document).on("click", ".btn-refresh", function () {
                setTimeout(function () {
                    myChart.resize();
                }, 0);
            });

        }
    };

    return Controller;
});