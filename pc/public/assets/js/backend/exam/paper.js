define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    $(document).on("fa.event.appendfieldlist", ".btn-append", function(){
        Form.events.selectpicker($(".fieldlist"));
    });

	
    $(function () {
        $("body").delegate(".scoreset","input",function(){
            var totalscore = total();
			var passscore  = Math.ceil(totalscore * 0.6);
            $('[name="row[totalscore]"]').val(totalscore);
			$('[name="row[passscore]"]').val(passscore);
        });
        $("body").delegate(".btn-choose","click",function(){
            select();
        });
    });
	function select(){
		    var that = this;
			var multiple = $(this).data("multiple") ? $(this).data("multiple") : false;
			var mimetype = $(this).data("mimetype") ? $(this).data("mimetype") : '';
			var type =$("#" + $(this).attr("ref")).val();
			//var type =$(this).attr("ref");
			parent.Fast.api.open("exam/paper/select?element_id=" + $(this).attr("ref") + "&type=" + type +"&multiple=" + multiple + "&mimetype=" + mimetype,"查题", {
				callback: function (data) {
					var button = $("#" + $(that).attr("id"));//当前按钮的id
					var maxcount = $(button).data("maxcount");//最大数量
					var input_id = $(button).data("input-id") ? $(button).data("input-id") : "";//指定元素的id
					maxcount = typeof maxcount !== "undefined" ? maxcount : 0;
					if (input_id && data.multiple) {
						var urlArr = [];
						var inputObj = $("#" + input_id);
						var value = $.trim(inputObj.val());
						if (value !== "") {
							urlArr.push(inputObj.val());
						}
						urlArr.push(data.url);
						var result = urlArr.join(",");
						if (maxcount > 0) {
							var nums = value === '' ? 0 : value.split(/\,/).length;
							var files = data.url !== "" ? data.url.split(/\,/) : [];
							var remains = maxcount - nums;
							if (files.length > remains) {
								Toastr.error(__('You can choose up to %d file%s', remains));
								return false;
							}
						}
						inputObj.val(result).trigger("change");
					} else {
						$("#" + input_id).val(data.url).trigger("change");//触发onchange事件
					}
				}
			});
			return false;
    }
    function total() {
        var total = 0;
        $('.score').each(function () {
            total+=$(this).val() * $(this).parent().prev().children('.quantity').val();
        });

        if(isNaN(total)){
                total = 0;
        }
        return total;
    }
    var Controller = {
        index: function () {
			$(".btn-edit").data("area",["100%","100%"]);
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'exam/paper/index' + location.search,
                    add_url: 'exam/paper/add',
                    edit_url: 'exam/paper/edit',
                    del_url: 'exam/paper/del',
                    multi_url: 'exam/paper/multi',
                    table: 'exam_paper',
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
                        {field: 'grade_id', title: __('Grade_id'), visible: true, searchList: gradeList},
                        {field: 'subject_id', title: __('Subject_id'), visible: true, searchList: sujectList},
                        {field: 'section_id', title: __('Section_id'), visible: true, searchList: sectionList },
                        {field: 'name', title: __('Name'),align: 'left'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'operate', 
						     title: __('Operate'), 
							 table: table, 
							 events: Table.api.events.operate, 
						     buttons: [
						      {
						        name: 'preview',
						        title: __('Preview'),
						        classname: 'btn btn-xs btn-warning btn-addtabs',
						        icon: 'fa fa-leaf',
						        url: 'exam/paper/preview'
						       }
						    ],
  					    	formatter: Table.api.formatter.operate,
						
						}
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
			
            Controller.api.bindevent();
        },
		select: function () {		
			// 初始化表格参数配置
			Table.api.init({
			    extend: {
			        index_url: 'exam/paper/select',
			    }
			});
			var urlArr = [];
			
			var table = $("#table");
			
			table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function (e, row) {
			    if (e.type == 'check' || e.type == 'uncheck') {
			        row = [row];
			    } else {
			        urlArr = [];
			    }
			    $.each(row, function (i, j) {
			        if (e.type.indexOf("uncheck") > -1) {
			            var index = urlArr.indexOf(j.url);
			            if (index > -1) {
			                urlArr.splice(index, 1);
			            }
			        } else {
			            urlArr.indexOf(j.url) == -1 && urlArr.push(j.url);
			        }
			    });
			});
			
			// 初始化表格
			table.bootstrapTable({
			    url: $.fn.bootstrapTable.defaults.extend.index_url,
			    sortName: 'id',
			    showToggle: false,
			    showExport: false,
			    columns: [
			        [
						{checkbox: true},
						{field: 'id', title: __('Id')},
						{field: 'grade_id', title: __('Grade_id'), visible: true, searchList: gradeList},
						{field: 'subject_id', title: __('Subject_id'), visible: true, searchList: sujectList},
						{field: 'section_id', title: __('Section_id'), visible: true, searchList: sectionList },
						{field: 'title', title: __('Title'),align: 'left'}
					//	{field: 'operate', 
					//		title: __('Operate'), 
					//		events: {
					//	        'click .btn-chooseone': function (e, value, row, index) {
					//	            var multiple = Backend.api.query('multiple');
					//	            multiple = multiple == 'true' ? true : false;
					//	            Fast.api.close({id: row.id, multiple: multiple});
					//	        },
					//	    }, 
					//		formatter: function () {
					//	        return '<a href="javascript:;" class="btn btn-danger btn-chooseone btn-xs"><i class="fa fa-check"></i> ' + __('Choose') + '</a>';
					//	    }
					//	}
			        ]
			    ]
			});
			
			// 选中多个
			$(document).on("click", ".btn-choose-multi", function () {
			     var arr = [];
			     $.each(table.bootstrapTable("getAllSelections"), function (i, j) {
			         arr.push(j.url);
			    });
			    var multiple = Backend.api.query('multiple');
			    multiple = multiple == 'true' ? true : false;
			    Fast.api.close({id: arr.join(","), multiple: multiple});
			});
			
			// 为表格绑定事件
			Table.api.bindevent(table);

		},
		
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});