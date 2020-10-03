<?php
// +----------------------------------------------------------------------
// | Pony Exam
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2020 https://gitee.com/ponyedu All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Pony <3421518028@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller\general;

use app\common\controller\Backend;

/**
 * 班级管理
 *
 * @icon fa fa-circle-o
 */
class Team extends Backend
{
    //当前是否为关联查询
    protected $relationSearch = false;
    
    /**
     * Team模型对象
     * @var \app\admin\model\general\Team
     */
    protected $model = null;
	protected $gradeList = null;

    public function _initialize()
    {
        parent::_initialize();
		$this->searchFields='name';
        $this->model = new \app\admin\model\general\Team;
        $this->view->assign("statusList", $this->model->getStatusList());
		$this->gradeList = $this->model->getGradeList();
		
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->where($where)
                     ->count();

            $list = $this->model
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
 
            foreach ($list as $k => &$v) {
				if (array_key_exists($v['grade_id'],$this->gradeList)){
                       $v['grade_id'] = $this->gradeList[$v['grade_id']];			
				} else {
					   $v['grade_id'] = '-';
				}	

            }
			
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
		$this->view->assign("gradeList", $this->model->getGradeList());
        return $this->view->fetch();
    }

	/**
	 * 编辑
	 */
	public function add()
	{
	    $this->view->assign('gradeList', build_select('row[grade_id]', $this->gradeList, null, ['class' => 'form-control selectpicker']));
	    return parent::add();
	}
		
	/**
	 * 编辑
	 */
	public function edit($ids = NULL)
	{
	    $row = $this->model->get($ids);
	    if (!$row)
	        $this->error(__('No Results were found'));
	    $this->view->assign('gradeList', build_select('row[grade_id]', $this->gradeList, $row['grade_id'], ['class' => 'form-control selectpicker']));
	    return parent::edit($ids);
	}
	
	/**
	 * 获取班级列表
	 * @internal
	 */
	public function getlist()
	{
	    //搜索关键词,客户端输入以空格分开,这里接收为数组
	    $word = (array)$this->request->request("q_word/a");
	    $word = implode('', $word);
    
		$where['status']='normal';
	    if ($word<>''){
		   $where['name']=$word;
	    }		
        $list= \app\admin\model\general\Team::where($where)->field('id,name')->select();
	    $pageNumber = $this->request->request("pageNumber");
	    $pageSize = $this->request->request("pageSize");
	    return json(['list' => array_slice($list, ($pageNumber - 1) * $pageSize, $pageSize), 'total' => count($list)]);
	}
}
