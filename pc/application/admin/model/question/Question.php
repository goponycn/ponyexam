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

namespace app\admin\model\question;

use think\Model;


class Question extends Model
{
    // 表名
    protected $name = 'question';
    
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
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });

		self::beforeInsert(function ($row) {
			 if (isset($row['content'])) {
				 $row->title = trim($row['content']);
				 if (strlen($row->title)>50){
				 	        $row->title = trim(my_substr($row->title,0,50)). '...';					
				 } 
			 }
			 $auth = \app\admin\library\Auth::instance();	
			 $row->operator = $auth->nickname; 
		});
		
		self::beforeUpdate(function ($row) {
		    $changed = $row->getChangedData();
		    if (isset($changed['content'])) {
		        if ($changed['content']) {
					$row->title = trim($changed['content']);
					if (strlen($row->title)>50){
						        $row->title = trim(my_substr($row->title,0,50)). '...';					
					} 					
		        } else {
		            unset($row->content);
		        }
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
	    return ['1' => __('Easy'), '2' => __('Moderate'),  '3' => __('Difficult')];
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

    public function getAutodecideList()
    {
        return [ '1' => __('Auto'),'2' => __('manual')];
    }
	
	public function getAnswerList()
	{
	    return [ 'A' => __('Right'),'B' => __('Wrong')];
	}
	
    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

   //随机组卷选题
    public static function getQuestionIds($quantity=0,$type=0,$gradeid=0,$subjectid=0,$sectionid=0,$difficulty=0,$custom='')
    {
		$custom_ids=explode(",", $custom);
		$map['status']='normal';
		if ($type>0){
			$map['type']=$type;
		}		
		if ($gradeid>0){
			$map['grade_id']=$gradeid;
		}
		if ($subjectid>0){
			$map['subject_id']=$subjectid;
		}
		if ($sectionid>0){
			$map['section_id']=$sectionid;
		}
		if ($difficulty>0){
			$map['difficulty']=$difficulty;
		}
		
		$i=0;
		$ids=[];
		//自选题
		foreach ($custom_ids as  $v) { 
			if  (!in_array($v,$ids)){
    			array_push($ids, $v);
	    		$i++;
		    	if ($i >=$quantity) return shuffle($ids);	
			}
        }

	    //机选题
		$all_ids = self::where($map)->column('id');
		if (count($all_ids) <= $quantity) {
			foreach ($all_ids as  $v) {
				if  (!in_array($v,$ids)){
					array_push($ids, $v);
					$i++;
			    	if ($i >=$quantity) return shuffle($ids);	
				}
			}
		} else {
			$rand_ids = array_rand($all_ids, $quantity);
			if (is_array($rand_ids)){
    			foreach ($rand_ids as  $v) { 
					if  (!in_array($all_ids[$v],$ids)){
						array_push($ids, $all_ids[$v]);
						$i++;
						if ($i >=$quantity) return shuffle($ids);	
					}
	        	}
			} else {
				if  (!in_array($all_ids[$rand_ids],$ids)){
					array_push($ids, $all_ids[$v]);
					$i++;
				}
			}	
		}
		shuffle($ids);
		return $ids;
    }

   //组卷选题数量检查
    public static function getQuestionTotal($type=0,$gradeid=0,$subjectid=0,$sectionid=0,$difficulty=0)
    {
		$map['status']='normal';
		if ($type>0){
			$map['type']=$type;
		}		
		if ($gradeid>0){
			$map['grade_id']=$gradeid;
		}
		if ($subjectid>0){
			$map['subject_id']=$subjectid;
		}
		if ($sectionid>0){
			$map['section_id']=$sectionid;
		}
		if ($difficulty>0){
			$map['difficulty']=$difficulty;
		}		
	    $total = self::where($map)->count();
        return $total;
    }
	
	//自选题ID检查
	 public static function checkQuestionId($type=0,$gradeid=0,$subjectid=0,$sectionid=0,$difficulty=0,$id=0)
	 {
			$map['status']='normal';
			if ($type>0){
				$map['type']=$type;
			}		
			if ($gradeid>0){
				$map['grade_id']=$gradeid;
			}
			if ($subjectid>0){
				$map['subject_id']=$subjectid;
			}
			if ($sectionid>0){
				$map['section_id']=$sectionid;
			}
			if ($difficulty>0){
				$map['difficulty']=$difficulty;
			}
			$map['id']=$id;
		    return self::where($map)->count();
	}
}
