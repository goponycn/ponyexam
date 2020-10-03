define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
	function score() {
		var params = $("#paper").serialize();
		$.ajax({
			url: "exam/decide/score",
			async: true,
			type: 'post',
			dataType: 'json',
			data: params,
			success: function(data) {
				if (data.success==1){
					parent.location.reload();
				    //window.location.href = "/exam/decide/index";
				} else {
					alert('部分试题批阅未完成!');
				}
			},
			error: function(err) { //如果确定能正确运行,可不写
				alert('试卷批阅保存失败!');
			},
		});
	}

    function total() {
        var total = 0;
        $('.score').each(function () {
            total+=Number($(this).val()) ;
        });

        if(isNaN(total)){
                total = 0;
        }
		$("#totalscore").html(total);
    }
	
	function gotoquestion(index) {
		$("body,html").animate({
			scrollTop: $('#q_' + index).offset().top  - $("#questionTabs").height() - $(
				"#questionTabContent").height() -10 
		}, 0);
	}

	function markscore() {
		$('.qindex').removeClass("btn-info");
		$('.qindex').removeClass("btn-warning");
		$('.qindex').removeClass("btn-danger");
		$('.qindex').addClass("btn-default");
		$('#paper :checked').each(function() {
			var rel = $(this).attr('rel');
			var v   = $(this).attr('value');
			$('#qs_' + rel).removeClass("btn-default");
			
			if (v==1){
    			$('#qs_' + rel).addClass("btn-info");			
			} else if (v==2){
    			$('#qs_' + rel).addClass("btn-warning");			
			} else if (v==3){
    			$('#qs_' + rel).addClass("btn-danger");			
			}
			
		});
	}

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'exam/decide/index' + location.search,
                    add_url: 'exam/decide/add',
                    edit_url: 'exam/decide/edit',
                    del_url: 'exam/decide/del',
                    multi_url: 'exam/decide/multi',
                    table: 'exam_user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'usernickname', title: __('Usernickname')},
                        {field: 'examname', title: __('Examname'),align: 'left'},
                        {field: 'papername', title: __('Papername'),align: 'left'},
                        {field: 'isdecide', title: __('Isdecide'),visible: true, searchList: decideList},
                        {field: 'score', title: __('Score'), operate:'BETWEEN'},
                        {field: 'ispass', title: __('Ispass'), visible: true, searchList: passList},
                        {field: 'decidetime', title: __('Decidetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

           table.on('post-body.bs.table',function(){
                $(".btn-editone").data("area",["100%","100%"]);
            })
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
			markscore();
			total();
			// 选取第一个标签页
			$('#questionTabs a:first').tab('show');
			
			$(document).on("click", ".qindex", function() {
				var index = $(this).attr('rel');
				gotoquestion(index);
			});
			
			$(document).on("click", ":radio", function() {
				markscore();

				if ($(this).val()==1){
					$('#s_' +  $(this).attr('rel')).val($('#s_'+$(this).attr('rel')).attr('max'));
				}
				if ($(this).val()==2){
					$('#s_' +  $(this).attr('rel')).val($('#s_'+$(this).attr('rel')).attr('max') /2);
				}
				if ($(this).val()==3){
					$('#s_' +  $(this).attr('rel')).val(0);
				}
				total();
			});
			$(document).on("click", "#score", function() {
				score();
			});
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});