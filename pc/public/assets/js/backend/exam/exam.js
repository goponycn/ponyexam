define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'exam/exam/index' + location.search,
                    add_url: 'exam/exam/add',
                    edit_url: 'exam/exam/edit',
                    del_url: 'exam/exam/del',
                    multi_url: 'exam/exam/multi',
                    table: 'exam',
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
                        {field: 'name', title: __('Name'),align: 'left'},
                        {field: 'paper_id', title: __('Paper_id') ,align: 'left',operate: false},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'stoptime', title: __('Stoptime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'state', title: __('State'),visible: true, searchList: stateList},
                        {field: 'operate',
                            title: __('Operate'), 
     	                    table: table, 
     	                    events: Table.api.events.operate, 
                            buttons: [
                            {
							name: 'ajax',
	                           name: 'start',
                               title: __('Start'),
                               classname: 'btn btn-xs btn-primary btn-ajax',
                               icon: 'fa fa-play',
							   confirm: '确认启用考场？',
                               url: 'exam/exam/start',
			                   visible: function (row) {
    			                  if (row.state===__('State Start')){
	     		                      return false;
		     	                  }
			                      return true;
			                    },
								success: function (data, ret) {
								     table.bootstrapTable('refresh', {});
							         return true;								
								 },
								 error: function (data, ret) {
								     console.log(data, ret);
								     Layer.alert(ret.msg);
								     return false;
								 }							
                            },
							{
							   name: 'cancel',
							   title: __('Cancel'),
							   classname: 'btn btn-xs btn-danger btn-ajax',
							   icon: 'fa fa-pause',
							   confirm: '确认取消考场？',
							   url: 'exam/exam/cancel',
							   visible: function (row) {
							      if (row.state===__('State Start')){
							          return true;
							      }
							      return false;
							    },
								success: function (data, ret) {
								     table.bootstrapTable('refresh', {});
								     return true;								
								 },
								 error: function (data, ret) {
								     console.log(data, ret);
								     Layer.alert(ret.msg);
								     return false;
								 }	
							}
							
							
                    ],
     	            formatter: Table.api.formatter.operate,
     
     }               
					]
                ]
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