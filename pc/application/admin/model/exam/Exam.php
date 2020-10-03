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

namespace app\admin\model\exam;

use think\Model;
use think\DB;

class Exam extends Model
{

    

    

    // 表名
    protected $name = 'exam';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'starttime_text',
        'stoptime_text'
    ];
    

    protected static function init()
    {
    	self::beforeInsert(function ($row) {    		 
    		 $auth = \app\admin\library\Auth::instance();	
    		 $row->operator = $auth->nickname; 
    	});
    	
    	self::beforeUpdate(function ($row) {
    	   $auth = \app\admin\library\Auth::instance();
    	   $row->operator = $auth->nickname; 
    	});
    }




    public function getStarttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['starttime']) ? $data['starttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStoptimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['stoptime']) ? $data['stoptime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setStarttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setStoptimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }
	
	public function getStateList()
	{
	    return ['0' => __('State Stop'), '1' => __('State Start')];
	}
	
	public function start($id=0)
	{
		 $row = $this->get($id);
		 if (!$row) return false;
		 
		 //考试时间
		 $stoptime = 0;
		 $times =0 ;
		 $totalscore = 0;
		 $passscore = 0;				
		 $paperName="";
		 $paper = Db::name('exam_paper')->where('id','=',$row['paper_id'])->find();

		 if ($paper) {
		 		$times = $paper['times']; 
		 	    $stoptime = $row['starttime'] +  $times * 60;
		 	    $paperName = $paper['name']; 
				$totalscore = $row['totalscore'];
				$passscore = $row['passscore'];				
		 }
		 //试题组合
		 $setting = json_decode($paper['setting'], true);
   		 $questionids = json_decode($paper['questions'], true);
		 $questions = [];
		 foreach ($questionids as $key1 => $value1) {
		    $questions[$key1]['n'] =  chinese_number($key1+1);			
		    $questions[$key1]['title'] = $setting[$key1]['title'];
			$questions[$key1]['score'] = $setting[$key1]['score'];
			$questions[$key1]['quantity'] = $setting[$key1]['quantity'];			
			foreach ($value1 as $key2 => $value2) {
			      $questions[$key1]['question'][$key2]['n']= $key2 + 1 ;
				  $questions[$key1]['question'][$key2]['id']= $value2;
				  $questions[$key1]['question'][$key2]['content'] ='';
				  $arr =  Db::name('question')->where('id','=',$value2)->find();
				  if ($arr){
				         $questions[$key1]['question'][$key2]['content']= nl2br(str_replace(chr(32),'&nbsp;',$arr['content']));;
				         $questions[$key1]['question'][$key2]['type']= $arr['type'];
			             $questions[$key1]['question'][$key2]['autodecide']= $arr['autodecide'];
						 $questions[$key1]['question'][$key2]['quantity']= $arr['quantity'];
						 $questions[$key1]['question'][$key2]['answer']= $arr['answer'];
						 $questions[$key1]['question'][$key2]['difficulty']= $arr['difficulty'];
				  }
			}
		 }
		 
		 //考生列表
		 $users=[];
		 if ($row['teams'] and $row['users']){
    		 $teamIds = explode(',', $row['teams']);
	    	 $userIds = explode(',', $row['users']);
       		 $users = Db::name('user')->field('id,nickname')->where('id','in',$userIds)->whereOr('team_id','in',$teamIds)->select();			 
		 } else {
			  if ($row['teams']) {
				 $teamIds = explode(',', $row['teams']);				
				 $users = Db::name('user')->field('id,nickname')->where('team_id','in',$teamIds)->select();			  
			  } 
			  if ( $row['users']) {
				 $userIds = explode(',', $row['users']); 
			     $users = Db::name('user')->field('id,nickname')->where('id','in',$userIds)->select();			  
			 }
		 }
		 foreach ($users as $key => $value) {
			 $list['exam_id'] = $row['id'];
			 $list['examname'] = $row['name'];
			 $list['paper_id'] = $row['paper_id'];
			 $list['papername'] = $paperName;
			 $list['user_id'] = $value['id'];
			 $list['usernickname'] = $value['nickname'];
			 Db::name('exam_user')->insert($list);
		 }
		 $this->where('id','=',$id)->update(['state'=>'1','stoptime'=> $stoptime,'times'=>$times,'totalscore'=>$totalscore,'passscore'=>$passscore,'questions'=>json_encode($questions)]);
         Db::name('exam_paper')->where('id','=',$row['paper_id'])->update(['state'=>'1']);
		 return true;		 	   
	}
	
	
	public function cancel($id=0)
	{
		 $row = $this->get($id);
		 if (!$row) return false;		 
		 //取消考生列表
		 Db::name('exam_user')->where('exam_id','=',$id)->delete();;
		 $this->where('id','=',$id)->update(['state'=>'0','stoptime'=> 0,'times'=>0,'questions'=>'']);

		 return true;
	}	 
}
