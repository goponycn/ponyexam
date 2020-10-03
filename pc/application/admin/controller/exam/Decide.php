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
use app\common\model\ExamUser; 
use think\Db;
/**
 * 试卷批阅
 *
 * @icon fa fa-circle-o
 */
class Decide extends Backend
{
    
    /**
     * Decide模型对象
     * @var \app\admin\model\exam\Decide
     */
    protected $model = null;
    protected $decideList = null;
    protected $passList = null;
    public function _initialize()
    {
        parent::_initialize();
		$this->searchFields='usernickname';
        $this->model = new \app\admin\model\exam\Decide;
        $this->decideList = $this->model->getDecideList();
        $this->passList = $this->model->getPassList();
		$this->view->assign("decideList", $this->decideList);
		$this->view->assign("passList", $this->passList);
		
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
			    if ($v['isdecide']==0){
					$v['ispass'] = '';
					$v["score"] = '';
					$v["decidetime"]='';
				} else {					
			        if (array_key_exists($v['ispass'],$this->passList)){
			           $v['ispass'] = $this->passList[$v['ispass']];			
			        } else {
			    	   $v['ispass'] = '-';
			        }	
								
				    if(substr($v["score"], -2) == '.0'){
				       $v["score"] = explode('.',$v["score"])[0];
				    };				
				}
                
				if (array_key_exists($v['isdecide'],$this->decideList)){
			           $v['isdecide'] = $this->passList[$v['isdecide']];			
			    } else {
			    	   $v['isdecide'] = '-';
			    }				    

			}		

            foreach ($list as $row) {
                $row->visible(['id','examname','papername','score','usernickname','isdecide','ispass','decidetime']);
                
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
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
		ExamUser::autoDecide( $row['id']); 
		
	    $row = $this->model->get($row['id']);
		$exam=Db::name('exam')->where('id','=',$row['exam_id'])->find();
		if (!$exam)
	        $this->error(__('No Results were found'));
		
        $questions = json_decode($exam['questions'], true);
        $answers = json_decode($row['answers'], true);
		$scorelist = json_decode($row['scorelist'], true);
		
		foreach ($questions as $key1 => $value1) {
				    $q=$value1['question'];
					foreach ($q as $key2 => $value2) {
							$n= $value2['id'];
							if  (!isset($answers[$n]))
								    $answers[$n]= '';

							if  (!isset($scorelist[$n])){
								$scorelist[$n]['decide']=0;
								$scorelist[$n]['score']=0;
								$scorelist[$n]['comment']='';
							}
							if ($value2['type'] == 3) {
								if ($value2['answer']=='A'){
								  $questions[$key1]['question'][$key2]['answer']=__('Right');
								}
								if ($value2['answer']=='B'){
								  $questions[$key1]['question'][$key2]['answer']=__('Wrong');
								}						
							}
					}
		}

		$this->view->assign("row", $row);
		$this->view->assign("exam", $exam);
		
		$this->view->assign("questions", $questions);
	    $this->view->assign("answers", $answers);
	    $this->view->assign("scorelist", $scorelist);
	    return parent::edit($ids);
	}
	
	public function  score()
	{
	   //保存
	   $id =(int) $this->request->request('id', 0);
	   $map['id']=array('=',$id);
	   
	   $row = Db::name('exam_user')->where($map)->find();
	   if (!$row)
	       $this->error(__('No Results were found'));
	
	   $this->request->filter(['strip_tags', 'trim']);
	   if ($this->request->isAjax()) {
		   $scorelist = $this->request->post("scorelist/a");   		   
		   if ($scorelist) {
		       $scorelist = $this->preExcludeFields($scorelist);
	  		   $success = ExamUser:: manualDecide( $id,$scorelist);
			   $result = array("success" => $success );
			   return json($result);
	      }
		}  
	}
}
