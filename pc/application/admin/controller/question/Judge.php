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
 * 判断题管理
 *
 * @icon fa fa-question
 */
class Judge extends Question
{
    protected $answerList = null;
		
    public function _initialize()
    {
        parent::_initialize();
		$this->type=3;
		$this->answerList = $this->model->getAnswerList();
    }
    
	public function add()
	{
	    $this->view->assign("answerList", $this->answerList);		
	    return parent::add();
	}
		
	/**
	 * 编辑
	 */
	public function edit($ids = NULL)
	{		
	    $this->view->assign("answerList", $this->answerList);		
	    return parent::edit($ids);
	}
	
}
