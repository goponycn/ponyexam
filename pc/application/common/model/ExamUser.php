<?php

namespace app\common\model;


use think\Model;
use think\Db;
/**
 * 地区数据模型
 */
class ExamUser extends Model
{

    // 表名,不含前缀
    protected $name = 'exam_user';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    public static function autoDecide($id=0)
    {
        $isdecide=1;
		$totalscore = 0;
		$ispass = 0;
		$arr=[];
		
        $map['id']=array('=',$id);
        $map['isdecide']=array('=',0);
        $row = Db::name('exam_user')->where($map)->find();
        if (!$row) { return; }
		if ($row['state']==0) { return; }		
		if ($row['isdecide']==1) { return; }
		
		$exam=Db::name('exam')->where('id','=',$row['exam_id'])->find();
		if (!$exam){ return; }
        
		$passscore = (int) $exam['passscore'];
		$questions = json_decode($exam['questions'], true);
		$answers = json_decode($row['answers'], true);;
	    foreach ($questions as $key1 => $value1) {
			         $q=$value1['question'];
                     $score = $value1['score'];
				     foreach ($q as $key2 => $value2) {
						$n= $value2['id'];
						$realanswer =$value2['answer'];
						$auto = $value2['autodecide'];
						if  (!isset($answers[$n])){
			   			    $answers[$n]= '';							
						}
						if ($auto == '1'){
							if (trim($answers[$n])==trim($realanswer)){
								$totalscore +=  $score;
								$arr[$n]['decide']=1;
								$arr[$n]['score']=$score;
								$arr[$n]['comment']='';  
							} else {
								$arr[$n]['decide']=3;
								$arr[$n]['score']=0;
								$arr[$n]['comment']='';  
							}
						} else {
							$arr[$n]['decide']=0;
							$arr[$n]['score']=0;
							$arr[$n]['comment']='';  
							$isdecide=0;						
						}
				      }
	    }
	    if ($totalscore >= $passscore) {
					$ispass =1;					
		}
	    $scorelist = json_encode($arr);
		$decidetime = time();
		if ($isdecide == 0){
					$totalscore = 0;
					$ispass =0;
					$decidetime = 0;
		}
		Db::name('exam_user')->where($map)->update(['scorelist'=>$scorelist,'score'=>$totalscore,'isdecide'=>$isdecide,'ispass'=>$ispass,'decidetime'=> $decidetime]);
			 	
	}
	
	
	public static function  manualDecide($id=0,$scorelist)
	{
	    $isdecide=1;
		$totalscore = 0;
		$ispass = 0;
		$arr=[];
		
	    $map['id']=array('=',$id);
	    $row = Db::name('exam_user')->where($map)->find();
	    if (!$row) { return; }
		
		$exam=Db::name('exam')->where('id','=',$row['exam_id'])->find();
		if (!$exam){ return; }
		
		$questions = json_decode($exam['questions'], true);
		foreach ($questions as $key1 => $value1) {
			         $q=$value1['question'];
	                 $score = $value1['score'];
				     foreach ($q as $key2 => $value2) {
						$n= $value2['id'];
						
						if  (!isset($scorelist[$n]['decide'])){
			   			    $arr[$n]['decide']= '0';
						}else{ 
							$arr[$n]['decide']= $scorelist[$n]['decide'];							
						}
						
						if  (!isset($scorelist[$n]['score'])){
			   			    $arr[$n]['score']= '0';
						}else{
							$arr[$n]['score']= (int) $scorelist[$n]['score'];							
						}
						
						if  (!isset($scorelist[$n]['comment'])){
			   			    $arr[$n]['comment']= '';							
						} else {
							$arr[$n]['comment']= $scorelist[$n]['comment'];	
						}
						
						if (($arr[$n]['score']>$score) or ($arr[$n]['decide'] == 1 )){
							$arr[$n]['score'] = $score ;
						}
						
						if (($arr[$n]['score']<0) or ($arr[$n]['decide'] == 3 )){
						    $arr[$n]['score'] = 0; 
						}

						if (($arr[$n]['decide']>3) or ($arr[$n]['decide']<1)){
							$arr[$n]['decide'] = 0;
							$arr[$n]['score'] = 0; 
						}
						
						if ($arr[$n]['decide']==0){
							 $isdecide = 0;
						}
						$totalscore += $arr[$n]['score'];

            }
	
	    }

	    if ($totalscore >= $exam['passscore']) {
					$ispass =1;					
		}
	    $scorelist = json_encode($arr);
		$decidetime = time();

		$auth = \app\admin\library\Auth::instance();	
		Db::name('exam_user')->where($map)->update(['scorelist'=>json_encode($arr),'score'=>$totalscore,'isdecide'=> $isdecide,'ispass'=>$ispass,'operator'=>$auth->nickname,'decidetime'=> $decidetime]);
		return  $isdecide; 	
	}
}
