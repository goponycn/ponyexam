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

namespace app\admin\controller\question;

use app\common\controller\Backend;

/**
 * 习题管理
 *
 * @icon fa fa-question
 */
class Judge extends Backend
{
    
    /**
     * Question模型对象
     * @var \app\admin\model\exam\Question
     */
    protected $model = null;
	protected $typeList = null;
    protected $subjectList = null;
	protected $sectionList = null;
    protected $gradeList = null;
    protected $difficultyList = null;
    protected $autodecideList = null;
	
    public function _initialize()
    {
        parent::_initialize();
		$this->searchFields='title';
        $this->model = new \app\admin\model\question\Question;
        $this->view->assign("statusList", $this->model->getStatusList());
		$this->typeList = $this->model->getTypeList();
		$this->subjectList = $this->model->getSubjectList();
		$this->sectionList = $this->model->getSectionList();
		$this->gradeList = $this->model->getGradeList();
		$this->difficultyList = $this->model->getDifficultyList();
		$this->autodecideList = $this->model->getAutodecideList();
		$this->answerList = $this->model->getAnswerList();
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
        //当前是否为关联查询
        $this->relationSearch = false;
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
					->where('type', '=', 3)
                    ->count();

            $list = $this->model                   
                    ->where($where)
					->where('type', '=', 3)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
					
            foreach ($list as $k => &$v) {
				if (array_key_exists($v['type'],$this->typeList)){
                       $v['type'] = $this->typeList[$v['type']];			
				} else {
					   $v['type'] = '-';
				}	
				if (array_key_exists($v['subject_id'],$this->subjectList)){
                       $v['subject_id'] = $this->subjectList[$v['subject_id']];			
				} else {
					   $v['subject_id'] = '-';
				}	

            }
			


            foreach ($list as $row) {
                $row->visible(['id','subject_id','type','title','createtime','weigh','status']);
                
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
		$this->view->assign("typeList", $this->typeList );
	    $this->view->assign("subjectList", $this->subjectList);		
        return $this->view->fetch();
    }
	
	/**
	 * 编辑
	 */
	public function add()
	{
	    $this->view->assign('gradeList', build_select('row[grade_id]', $this->gradeList, null, ['class' => 'form-control selectpicker']));
	    $this->view->assign('subjectList', build_select('row[subject_id]', $this->subjectList, null, ['class' => 'form-control selectpicker']));
	    $this->view->assign('sectionList', build_select('row[section_id]', $this->sectionList, null, ['class' => 'form-control selectpicker']));
	    $this->view->assign('difficultyList', build_select('row[difficulty]', $this->difficultyList, null, ['class' => 'form-control selectpicker']));
	    $this->view->assign('typeList', build_select('row[type]', $this->typeList, null, ['class' => 'form-control selectpicker']));
	    $this->view->assign("autodecideList", $this->autodecideList);		
	    $this->view->assign("answerList", $this->answerList);		

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
	    $this->view->assign('subjectList', build_select('row[subject_id]', $this->subjectList, $row['subject_id'], ['class' => 'form-control selectpicker']));
	    $this->view->assign('sectionList', build_select('row[section_id]', $this->sectionList, $row['section_id'], ['class' => 'form-control selectpicker']));
	    $this->view->assign('difficultyList', build_select('row[difficulty]', $this->difficultyList, $row['difficulty'], ['class' => 'form-control selectpicker']));
	    $this->view->assign("autodecideList", $this->autodecideList);		
	    $this->view->assign("answerList", $this->answerList);		

	    return parent::edit($ids);
	}
}
