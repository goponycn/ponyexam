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

namespace app\index\controller;

use think\Config;
use think\Cookie;
use think\Hook;
use think\Session;
use think\Validate;
use think\Db;
use app\common\controller\Frontend;
use app\common\model\ExamUser; 

class Exam extends Frontend
{

    protected $noNeedLogin = [];
    protected $noNeedRight = '*';
    protected $layout = 'default';

    public function exam()
    {
       //考试列表
	   $map['eu.isdecide'] = array('=','0');
	   $map['eu.user_id'] = array('=',$this->auth->id);
	   
	   
	   $examList = Db::name('exam_user')
	               ->alias("eu")
				   ->join('exam e','e.id = eu.exam_id')
	       		   ->field('eu.id id,eu.examname examname,eu.papername papername,eu.state state,e.starttime starttime,e.stoptime stoptime,e.times times')
	               ->where($map)
	               ->order('eu.id desc')->paginate(10);
	   		   
	   $this->view->assign('examList', $examList);
	   return $this->view->fetch();		
    }
	
	
	public function page()
	{
	   //考试
	   $this->request->filter(['strip_tags', 'trim']);
	   $id =(int) $this->request->request('id', 0);
	   
	   $map['user_id']=array('=',$this->auth->id);
	   $map['id']=array('=',$id);
	   $map['state']=array('<=',1);
	   $row = Db::name('exam_user')->where($map)->find();
	   if (!$row)
	       $this->error(__('No Results were found'));
	  
	  
	   $exam=Db::name('exam')->where('id','=',$row['exam_id'])->find();
	   if (!$exam)
	       $this->error(__('No exams were found'));
	   $starttime = $exam['starttime'];
	   if (($starttime - time())>0){
		   $this->error(__('Exam is not started'));
	   }
	   $stoptime = $exam['stoptime'];
	   $lefttime = $stoptime - time();
	   if ($lefttime < 0) {
		   $lefttime = 0;
		   $this->error(__('Exam is stopped'));
	   }
	   $questions = json_decode($exam['questions'], true);
	   
	   $answers = json_decode($row['answers'], true);;
	   foreach ($questions as $key1 => $value1) {
		         $q=$value1['question'];
	   			foreach ($q as $key2 => $value2) {
					$n= $value2['id'];
					if  (!isset($answers[$n]))
		   			    $answers[$n]= '';
	   			}
	   }
	   $ip = request()->ip();
	   Db::name('exam_user')->where($map)->update(['state'=>'1','begintime'=> time(),'ip'=>$ip]);

       $this->view->assign('row', $row);
	   $this->view->assign('exam', $exam);
	   $this->view->assign('lefttime', $lefttime);	   
	   $this->view->assign('answers', $answers);
	   $this->view->assign('questions', $questions);
	   $this->view->engine->layout(false);
	   return $this->view->fetch();	
	}
	
	public function  finish()
	{
	   //交卷
	   $id =(int) $this->request->request('id', 0);
	   
	   $map['user_id']=array('=',$this->auth->id);
	   $map['id']=array('=',$id);
	   $map['state']=array('=',1);
	   
	   $row = Db::name('exam_user')->where($map)->find();
	   if (!$row)
	       $this->error(__('No Results were found'));
	  
	   $this->request->filter(['strip_tags', 'trim']);
	   if ($this->request->isAjax()) {
	        if (!isset($this->request->post()['answers'])) {
	            return 0;
	        }
	        $answers = $this->request->post()['answers'];
	        foreach ($answers as $key => $value) {
	                if (is_array($value)) {
	                    $answers[$key] = implode('', $value);
	                }
	        }
	        
	        Db::name('exam_user')->where($map)->update(['state'=>'2','endtime'=> time(),'answers'=>  json_encode($answers)]);
			ExamUser::autoDecide($id);
	        	   
	   }	   
	   return 0;		
	}
	
	public function  save()
	{
	   //保存
	   $id =(int) $this->request->request('id', 0);
	   $map['user_id']=array('=',$this->auth->id);
	   $map['id']=array('=',$id);
	   $map['state']=array('=',1);
	   
	   $row = Db::name('exam_user')->where($map)->find();
	   if (!$row) return 0;

	   $this->request->filter(['strip_tags', 'trim']);
	   if ($this->request->isAjax()) {
	       if (!isset($this->request->post()['answers'])) {
	           return 0;
	       }
	       $answers = $this->request->post()['answers'];

	       foreach ($answers as $key => $value) {
	               if (is_array($value)) {
	                   $answers[$key] = implode('', $value);
	               }
	       }
	       
		   Db::name('exam_user')->where($map)->update(['answers'=>  json_encode($answers)]);	   
	       return $answers;
	   }
	   return 0;	   
	}
	
    public function history()
    {
       //历史成绩单
	   $examList = Db::name('exam_user')
	               ->alias("eu")
	   			   ->join('exam e','e.id = eu.exam_id')
	       		   ->field('eu.id id,eu.examname examname,eu.papername papername,e.starttime starttime,eu.score score')
	               ->where(['eu.user_id' => $this->auth->id,'eu.isdecide'=>'1'])
	               ->order('eu.id desc')->paginate(10);
	   $list =  $examList->items();

	   foreach ( $list as $k => &$v) {
		       if(substr($v["score"], -2) == '.0'){
			       $v["score"] = explode('.',$v["score"])[0];
			   };				
		}
	    $this->view->assign('list', $list);
	    $this->view->assign('examList', $examList);
		
	    return $this->view->fetch();
    }

}
