define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
					index_url: 'question/multiple/index' + location.search,
					add_url: 'question/multiple/add',
					edit_url: 'question/multiple/edit',
					del_url: 'question/multiple/del',
					multi_url: 'question/multiple/multi',
					import_url: 'question/single/import',					
                    table: 'question',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'subject_id', title: __('Subject_id'), visible: true, searchList: sujectList},
                        {field: 'title', title: __('Title'),align: 'left'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

			// 导出
			var submitForm = function (ids, layero) {
			    var options = table.bootstrapTable('getOptions');
			    var search = options.queryParams({});
			    $("input[name=search]", layero).val(options.searchText);
			    $("input[name=ids]", layero).val(ids);
			    $("input[name=filter]", layero).val(search.filter);
			    $("input[name=op]", layero).val(search.op);
			    $("form", layero).submit();
			};
			
			$(document).on("click", ".btn-export", function () {
			    var ids = Table.api.selectedids(table);
			    var page = table.bootstrapTable('getData');
			    var all = table.bootstrapTable('getOptions').totalRows;
			    Layer.confirm("请选择导出的范围<form action='" + Fast.api.fixurl("question/multiple/export") + "' method='post' target='_blank'><input type='hidden' name='ids' value='' /><input type='hidden' name='filter' ><input type='hidden' name='op'><input type='hidden' name='search'></form>", {
			        title: '导出数据',
			        btn: ["选中(" + ids.length + "条)", "本页(" + page.length + "条)", "全部(" + all + "条)"],
			        success: function (layero, index) {
			            $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
			        }
			        , yes: function (index, layero) {
						$("#ids").val(ids.join(","));
			            submitForm(ids.join(","), layero);
			            return false;
			        }
			        ,
			        btn2: function (index, layero) {
			            var ids = [];
			            $.each(page, function (i, j) {
			                ids.push(j.id);
			            });
			            submitForm(ids.join(","), layero);
			            return false;
			        }
			        ,
			        btn3: function (index, layero) {
			            submitForm("all", layero);
			            return false;
			        }
			    })
			});

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
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