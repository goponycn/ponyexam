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

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet; 
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\Db;
use think\Config;
/**
 * 习题管理
 *
 * @icon fa fa-question
 */
class Question extends Backend
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
	protected $type = 0;
	
    public function _initialize()
    {
        parent::_initialize();
		$this->searchFields='title';
        $this->model = new \app\admin\model\question\Question();
        $this->view->assign("statusList", $this->model->getStatusList());
		$this->typeList = $this->model->getTypeList();
		$this->subjectList = $this->model->getSubjectList();
		$this->sectionList = $this->model->getSectionList();
		$this->gradeList = $this->model->getGradeList();
		$this->difficultyList = $this->model->getDifficultyList();
		$this->autodecideList = $this->model->getAutodecideList();
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
					->where('type', '=', $this->type)
                    ->count();

            $list = $this->model                   
                    ->where($where)
					->where('type', '=', $this->type)
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

	    return parent::edit($ids);
	}
	
	/**
	 * 导入
	 */
	public function import()
	{
	    Config::set('default_return_type', 'json');
	    $file = $this->request->file('file');
	    if (!($file)) {
	        exit();
	    }
	
	    //判断是否已经存在附件
	    $sha1 = $file->hash();
	    $extparam = $this->request->post();
	
	    $upload = Config::get('upload');
	
	    preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
	    $type = strtolower($matches[2]);
	    $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
	    $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
	    $fileInfo = $file->getInfo();
	    $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
	    $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
	
	    $mimetypeArr = explode(',', strtolower($upload['mimetype']));
	    $typeArr = explode('/', $fileInfo['type']);
	
	    //禁止上传PHP和HTML文件
	    if (in_array($fileInfo['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
	        $this->error(__('Uploaded file format is limited'));
	    }
	    //验证文件后缀
	    if ($upload['mimetype'] !== '*' &&
	        (
	            !in_array($suffix, $mimetypeArr)
	            || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
	        )
	    ) {
	        $this->error(__('Uploaded file format is limited'));
	    }
	 		
		

		$info = $file->move(ROOT_PATH . 'runtime' . DS . 'temp');
		if($info){ 
		      $filename = ROOT_PATH . 'runtime' . DS . 'temp' . DS . $info->getsaveName();
	          if (!file_exists($filename)) {
	              $this->error(__('No results were found'));
	          } 
		} else {
			 $this->error(__('No results were found'));
		}
		
	    //实例化reader
	    $ext = pathinfo($filename, PATHINFO_EXTENSION);
	    if (!in_array($ext, [ 'xls', 'xlsx'])) {
	        $this->error(__('Unknown data format'));
	    }
        
		if ($ext === 'xls') {
	        $reader = new Xls();
	    } else {
	        $reader = new Xlsx();
	    }

	
	    //加载文件   
	    try {
	        if (!$PHPExcel = $reader->load($filename)) {
	            $this->error(__('Unknown data format'));
	        }
	        $worksheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
	        $allColumn = $worksheet->getHighestDataColumn(); //取得最大的列号     
	        $allRow = $worksheet->getHighestRow(); //取得一共有多少行
	        $maxColumnNumber = Coordinate::columnIndexFromString($allColumn);
	        if ($maxColumnNumber<10) {
	             $this->error(__('Unknown data format'));
	        }
			

			for ($i = 2; $i <= $allRow; $i++) {
			    $row = [];
		        $id = (int) $worksheet->getCellByColumnAndRow('1', $i )->getValue();
			    $type = (int) array_search($worksheet->getCellByColumnAndRow('2', $i)->getValue(),$this->typeList);
				$grade_id = (int) array_search($worksheet->getCellByColumnAndRow('3', $i)->getValue(),$this->gradeList);
	            $subject_id = (int) array_search($worksheet->getCellByColumnAndRow('4',$i)->getValue(),$this->subjectList);
			    $section_id = (int) array_search($worksheet->getCellByColumnAndRow('5', $i)->getValue(),$this->sectionList);
			    $difficulty = (int) array_search($worksheet->getCellByColumnAndRow('6', $i)->getValue(),$this->difficultyList);
			    $autodecide = (int) array_search($worksheet->getCellByColumnAndRow('7', $i)->getValue(),$this->autodecideList);
		        $quantity = (int) $worksheet->getCellByColumnAndRow('8', $i )->getValue();
		        $content = $worksheet->getCellByColumnAndRow('9', $i)->getValue();
                $content = $content ? $content:'';
				$answer =  $worksheet->getCellByColumnAndRow('10', $i)->getValue();
	            $answer = $answer ? $answer:'';
				$analysis =  $worksheet->getCellByColumnAndRow('11', $i)->getValue();
	            $analysis = $analysis ? $analysis:'';	
				$attachment =  $worksheet->getCellByColumnAndRow('12', $i)->getValue();
				$attachment = $attachment ? $attachment:'';				
	            $title = trim($content);
	            if (strlen($title)>50){
	            	$title = trim(my_substr($title,0,50)). '...';					
				}
            	$auth = \app\admin\library\Auth::instance();	
	            $operator = $auth->nickname; 
			    
				$row = array(
			          'id' => $id,
			          'type' => $type,
			          'grade_id' => $grade_id,
			          'subject_id' => $subject_id,
			          'section_id' => $section_id ,
			          'difficulty' => $difficulty,
			          'autodecide' => $autodecide,
			          'quantity' => $quantity,
			          'content' => $content,
			          'answer' => $answer,
			          'analysis' => $analysis,
					  'attachment' => $attachment,
				      'title' => $title,
					  'operator' => $operator,
					  'createtime' => time(),
					  'updatetime' => time(),					  
			    );
				
				if ($id == 0){
					unset($row['id']);					
					$this->model->insert($row);
				} else {
      				$q=Db::name('question')->where('id','=',$id)->find();
    				if (!$q){
						$this->model->insert($row);
				    }else{
						unset($row['createtime']);
						$this->model->update($row);
					}			
				}			
		    }
					
	    } catch (Exception $exception) {
	        $this->error($exception->getMessage());
	    }
	    $this->success();
	}
	
	/**
	 * 导出
	*/
	public function export() 
	{
	  if ($this->request->isPost()) {
		set_time_limit(0);
		$search = $this->request->post('search');
		$ids = $this->request->post('ids');
		$filter = $this->request->post('filter');
		$op = $this->request->post('op');
		
		$whereIds = $ids == 'all' ? '1=1' : ['id' => ['in', explode(',', $ids)]];
		$this->request->get(['search' => $search, 'ids' => $ids, 'filter' => $filter, 'op' => $op]);
		list($where, $sort, $order, $offset, $limit) = $this->buildparams();
	  	$list = $this->model             
			    ->where($where)
			    ->where($whereIds)			   
				->where('type', '=', $this->type)
		        ->order('id', 'DESC')
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
			if (array_key_exists($v['section_id'],$this->sectionList)){
		           $v['section_id'] = $this->sectionList[$v['section_id']];			
			} else {
				   $v['section_id'] = '-';
			}	
			if (array_key_exists($v['grade_id'],$this->gradeList)){
			       $v['grade_id'] = $this->gradeList[$v['grade_id']];			
			} else {
				   $v['grade_id'] = '-';
			}	
			if (array_key_exists($v['difficulty'],$this->difficultyList)){
			       $v['difficulty'] = $this->difficultyList[$v['difficulty']];			
			} else {
				   $v['difficulty'] = '-';
			}	
			if (array_key_exists($v['autodecide'],$this->autodecideList)){
			       $v['autodecide'] = $this->autodecideList[$v['autodecide']];			
			} else {
				   $v['autodecide'] = '-';
			}	
		}
		
	    try {
		    $spreadsheet = new Spreadsheet();
		    $worksheet = $spreadsheet->getActiveSheet();
			
	    	$worksheet->getCell( 'A1' )->setValue( 'ID' );
		    $worksheet->getCell( 'B1' )->setValue( '题型' );
		    $worksheet->getCell( 'C1' )->setValue( '学习级别' );
		    $worksheet->getCell( 'D1' )->setValue( '科目' );
		    $worksheet->getCell( 'E1' )->setValue( '知识单元' );
		    $worksheet->getCell( 'F1' )->setValue( '难易程度' );
		    $worksheet->getCell( 'G1' )->setValue( '自动评分' );
		    $worksheet->getCell( 'H1' )->setValue( '选项数量' );
		    $worksheet->getCell( 'I1' )->setValue( '题目' );
		    $worksheet->getCell( 'J1' )->setValue( '答案' );
		    $worksheet->getCell( 'K1' )->setValue( '解析' );
			$worksheet->getCell( 'L1' )->setValue( '图片' );
		    $i = 2;
		    foreach ( $list as $row ) {
		        $worksheet->getCell( 'A' . $i )->setValue( $row->id );
		        $worksheet->getCell( 'B' . $i )->setValue( $row->type);			
		        $worksheet->getCell( 'C' . $i )->setValue( $row->grade_id );
		        $worksheet->getCell( 'D' . $i )->setValue( $row->subject_id );
		        $worksheet->getCell( 'E' . $i )->setValue( $row->section_id );
		        $worksheet->getCell( 'F' . $i )->setValue( $row->difficulty );
		        $worksheet->getCell( 'G' . $i )->setValue( $row->autodecide );
			    $worksheet->getCell( 'H' . $i )->setValue( $row->quantity );
			    $worksheet->getCell( 'I' . $i )->setValue( $row->content );
			    $worksheet->getCell( 'J' . $i )->setValue( $row->answer );
			    $worksheet->getCell( 'K' . $i )->setValue( $row->analysis );
				$worksheet->getCell( 'L' . $i )->setValue( $row->attachment );
		        $i ++;
		    }
			
		    $filename = 'pony' . date('YmdHis') . ".xlsx";
		    $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);		

		    header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		    header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		    header( 'Cache-Control: max-age=0');
		    $writer->save( "php://output" );
		    $spreadsheet->disconnectWorksheets();
		    unset($spreadsheet);
			exit();
			
	    } catch ( \Exception $e ) {
		    $this->error($e->getMessage());
	    }
	 }
  }
}
