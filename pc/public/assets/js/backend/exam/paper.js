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

    });
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});