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


class Paper extends Model
{
    // 表名
    protected $name = 'exam_paper';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    protected static function init()
    {
  		self::beforeInsert(function ($row) {
		   if ((isset($row['ismade'])) && (isset($row['setting']))) {
			
			 $totalscore=0;	 
			 $setting=json_decode($row['setting'],true);

			 if ($row['ismade']=='1'){
			    $questions=null;
			    $grade_id = intval($row['grade_id']);
			    $subject_id= intval($row['subject_id']);
			    $section_id= intval($row['section_id']);
			    foreach ($setting as $key => $value) {
				    $quantity = intval($value['quantity']);
			 	    $type = intval($value['type']);
				
					$score = intval($value['score']);
					$totalscore +=  $quantity * $score;	
					$custom=$value['questions'];
					$questions[$key]=\app\admin\model\question\Question::getQuestionIds($quantity,$type,$grade_id,$subject_id,$section_id,0,$custom);   
				}
				
			    $row->questions =  json_encode($questions);
			} else {
				     foreach ($setting as $key => $value) {
				         $quantity = intval($value['quantity']);
				         $score = intval($value['score']);
				         $totalscore +=  $quantity * $score;			        
			        }				
			}	
			$row->totalscore =  $totalscore;		 
		    }
			 
			$auth = \app\admin\library\Auth::instance();	
			$row->operator = $auth->nickname; 
		});
		
		self::beforeUpdate(function ($row) {
		    $changed = $row->getChangedData();

		    if (isset($changed['setting'])) {
			   $totalscore=0;
			   $setting=json_decode($row['setting'],true);				
		       if (($changed['setting']) && ($row['ismade']=='1')) {
				    $questions=null;
				    $grade_id = intval($row['grade_id']);
				    $subject_id= intval($row['subject_id']);
				    $section_id= intval($row['section_id']);					
					
				    foreach ($setting as $key => $value) {
					    $quantity = intval($value['quantity']);
				 	    $type = intval($value['type']);
						$score = intval($value['score']);
						$totalscore +=  $quantity * $score;	
						$constomqids=$value['questions'];
					    $questions[$key]= \app\admin\model\question\Question::getQuestionIds($quantity,$type,$grade_id,$subject_id,$section_id,0,$constomqids);
					}
					
				    $row->questions =  json_encode($questions);	
												
		        } else {
					foreach ($setting as $key => $value) {
					     $quantity = intval($value['quantity']);
					     $score = intval($value['score']);
					     $totalscore +=  $quantity * $score;			        
					}	
		            unset($row->questions);
		        }
				$row->totalscore =  $totalscore;
		   }  
		   $auth = \app\admin\library\Auth::instance();
		   $row->operator = $auth->nickname; 
		});
    }
	
	public function getTypeList()
	{
	    return ['1' => __('Single'), '2' => __('Multiple'), '3' => __('Judge'), '4' => __('Fill'), '9' => __('Other')];
	}
		
	public function getDifficultyList()
	{
	    return ['1' => __('Easy'), '2' => __('Moderate'), '3' => __('Difficult')];
	}
	
    public function getGradeList()
    {
        return \app\admin\model\general\Grade::column('id,name');
    }

    public function getSectionList()
    {
        return \app\admin\model\general\Section::column('id,name');
    }

    public function getSubjectList()
    {
       return \app\admin\model\general\Subject::column('id,name');
    }

 	
    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

}
