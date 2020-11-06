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

namespace app\admin\controller\exam;

use app\common\controller\Backend;
use think\Db;

/**
 * 试卷管理
 *
 * @icon fa fa-circle-o
 */
class Paper extends Backend
{
    
    /**
     * Paper模型对象
     * @var \app\admin\model\exam\Paper
     */
    protected $model = null;
	protected $typeList = null;
	protected $subjectList = null;
	protected $sectionList = null;
	protected $gradeList = null;
	protected $difficultyList = null;
	

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\exam\Paper;
        $this->view->assign("statusList", $this->model->getStatusList());
		$this->typeList = $this->model->getTypeList();
		$this->subjectList = $this->model->getSubjectList();
		$this->sectionList = $this->model->getSectionList();
		$this->gradeList = $this->model->getGradeList();
		$this->difficultyList = $this->model->getDifficultyList();
		
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

				if (array_key_exists($v['subject_id'],$this->subjectList)){
                       $v['subject_id'] = $this->subjectList[$v['subject_id']];			
				} else {
					   $v['subject_id'] = '-';
				}	

				if (array_key_exists($v['section_id'],$this->sectionList)){
                       $v['section_id'] = $this->sectionList[$v['section_id']];			
				} else {
					   $v['section_id'] = '-';
				}	

            }
            foreach ($list as $row) {
                $row->visible(['id','grade_id','subject_id','section_id','name','createtime','status']);
                
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
		$this->view->assign("gradeList", $this->gradeList );
		$this->view->assign("subjectList", $this->subjectList);		
		$this->view->assign("sectionList", $this->sectionList);		
        return $this->view->fetch();
    }
	
	/**
	 * 选择
	 */
	public function select()
	{
	    if ($this->request->isAjax()) {
			$question = new \app\admin\controller\question\Question();
	        return $question->index();
	    }
		$this->view->assign("gradeList", $this->gradeList );
		$this->view->assign("subjectList", $this->subjectList);		
		$this->view->assign("sectionList", $this->sectionList);		
	    return $this->view->fetch();
	}
	
	/**
	 * 添加
	 */
	public function add()
	{		
		$this->request->filter(['strip_tags', 'trim']);		
		if ($this->request->isPost()) {
				$row = $this->request->post("row/a", [], 'strip_tags');
		        if ($row) {
		            $row = $this->preExcludeFields($row);

    			    $grade_id = intval($row['grade_id']);
    			    $subject_id= intval($row['subject_id']);
	    		    $section_id= intval($row['section_id']);
                    $passscore = intval($row['passscore']);
		            $setting = json_decode($row['setting'], true);
		            if (count($setting) < 1) {
		                $this->error(__('Error Setting'));
		            }
					
					$totalscore =0;
		            foreach ($setting as $key => $value) {						
		                $type = intval($value['type']);
		                $quantity = intval($value['quantity']);
		                if ($quantity <= 0) {
		                    $this->error(__('Error Quantity',$key + 1));
		                }
		                $score = intval($value['score']);
		                if ($score <= 0) {
		                    $this->error(__('Error Score',$key + 1));
		                }
		                
		                if (array_key_exists('questions',$value)){
		                	$q =  trim($value['questions']);						
		                    if ($q){
		                        $questions = explode(",", $q);  
		                	    if (count($questions)>$quantity){
		                		    $this->error(__('Error Custom Questions Quantity',$key + 1));
		                	    }
		                	    foreach($questions as $question){ 
		                		    $check= \app\admin\model\question\Question::checkQuestionId($type,$grade_id,$subject_id,$section_id,0,$question);
		                		    if (!$check){
		                		        $this->error(__('Error Custom Questions id',$question));
		                		    }
		                	    }
		                	}	
		                }
						$total= \app\admin\model\question\Question::getQuestionTotal($type,$grade_id,$subject_id,$section_id);
						if ($total < $quantity) {
							 $this->error(__('Error Not Enough',$key + 1));
						}
						$totalscore +=  $quantity * $score;		
		            }
					if ($totalscore<$passscore) {
						$this->error(__('Error Pass Score'));
					}
					$this->model->create($row);
					$this->success();
		       }
			   $this->error();
		}   
	    $this->view->assign('gradeList', build_select('row[grade_id]', $this->gradeList, null, ['class' => 'form-control selectpicker']));
	    $this->view->assign('subjectList', build_select('row[subject_id]', $this->subjectList, null, ['class' => 'form-control selectpicker']));
	    $sectionList=$this->sectionList;
	    $sectionList['0']=__('All');
	    $this->view->assign('sectionList', build_select('row[section_id]', $sectionList, null, ['class' => 'form-control selectpicker']));	   
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
		$this->request->filter(['strip_tags', 'trim']);
		if ($this->request->isPost()) {
		        $row = $this->request->post("row/a");
		        if ($row) {
		            $row = $this->preExcludeFields($row);
		
				    $grade_id = intval($row['grade_id']);
				    $subject_id= intval($row['subject_id']);
				    $section_id= intval($row['section_id']);
		            $passscore = intval($row['passscore']);
		            $setting = json_decode($row['setting'], true);
		            if (count($setting) < 1) {
		                $this->error(__('Error Setting'));
		            }
					$totalscore =0;
		            foreach ($setting as $key => $value) {
						$type = intval($value['type']);	
		                $quantity = intval($value['quantity']);
		                if ($quantity <= 0) {
		                    $this->error(__('Error Quantity',$key + 1));
		                }
		                $score = intval($value['score']);
	                    if ($score <= 0) {
	                        $this->error(__('Error Score',$key + 1));
	                    }

						if (array_key_exists('questions',$value)){
    						$q =  trim($value['questions']);						
						    if ($q){
						        $questions = explode(",", $q);  
							    if (count($questions)>$quantity){
								    $this->error(__('Error Custom Questions Quantity',$key + 1));
							    }
							    foreach($questions as $question){ 
								    $check= \app\admin\model\question\Question::checkQuestionId($type,$grade_id,$subject_id,$section_id,0,$question);
								    if (!$check){
								        $this->error(__('Error Custom Questions id',$question));
								    }
							    }
							}	
						}

						$total= \app\admin\model\question\Question::getQuestionTotal($type,$grade_id,$subject_id,$section_id);
						if ($total < $quantity) {
							 $this->error(__('Error Not Enough',$key + 1));
						}
						$totalscore +=  $quantity * $score;		
		            }
					if ($totalscore<$passscore) {
						     $this->error(__('Error Pass Score'));
					}
		       }
			   $this->success();
		}   	
	    $this->view->assign('gradeList', build_select('row[grade_id]', $this->gradeList, $row['grade_id'], ['class' => 'form-control selectpicker']));
	    $this->view->assign('subjectList', build_select('row[subject_id]', $this->subjectList, $row['subject_id'], ['class' => 'form-control selectpicker']));
	    $sectionList=$this->sectionList;
		$sectionList['0']=__('All');
		$this->view->assign('sectionList', build_select('row[section_id]', $sectionList, $row['section_id'], ['class' => 'form-control selectpicker']));
	
	    return parent::edit($ids);
	}
	
	/**
	 * 预览
	 */
	public function preview($ids = NULL)
	{
	    $row = $this->model->get($ids);
	    if (!$row)
	        $this->error(__('No Results were found'));
			
	    $setting = json_decode($row['setting'], true);
	    $this->view->assign("row", $row);
	    $questionids = json_decode($row['questions'], true);
		$questions = [];
	    foreach ($questionids as $key1 => $value1) {
	        $questions[$key1]['n'] =  chinese_number($key1+1);			
	        $questions[$key1]['title'] = $setting[$key1]['title'];
			$questions[$key1]['score'] = $setting[$key1]['score'];
			foreach ($value1 as $key2 => $value2) {
			      $questions[$key1]['question'][$key2]['n']= $key2 + 1 ;
				  $questions[$key1]['question'][$key2]['id']= $value2;
				  $questions[$key1]['question'][$key2]['content'] ='';
				  $where['id']=$value2;
				  $arr = \app\admin\model\question\Question::where($where)->find();
				  if ($arr){
				         $questions[$key1]['question'][$key2]['content']= nl2br(str_replace(chr(32),'&nbsp;',$arr['content']));;
					     $questions[$key1]['question'][$key2]['attachment'] = $arr['attachment'];
				  }
			}	
	    }
   	
	    $this->view->assign("questions", $questions);
	
	    return $this->view->fetch();
						
	}	
	/**
	 * 题型
	 */
	public function typelist()
	{
		$list=[array("id"=>"1","name"=>__('Single')),array("id"=>"2","name"=>__('Multiple')),array("id"=>"3","name"=>__('Judge')),array("id"=>"4","name"=>__('Fill')),array("id"=>"9","name"=>__('Other'))];
     	$result = array( "list" => $list,"total" => 5);
        return json($result);	
    }	
}
