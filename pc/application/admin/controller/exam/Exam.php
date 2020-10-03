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
use app\admin\model\exam\Paper;
use think\Db;
/**
 * 考试管理
 *
 * @icon fa fa-circle-o
 */
class Exam extends Backend
{
    
    /**
     * Exam模型对象
     * @var \app\admin\model\exam\Exam
     */
    protected $model = null;
    protected $stateList = null;
	
    public function _initialize()
    {
        parent::_initialize();
 		$this->searchFields='name';
        $this->model = new \app\admin\model\exam\Exam;
		$this->stateList = $this->model->getStateList();
		$this->view->assign("stateList", $this->stateList);

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

			
            $paperIds = array_column($list, 'paper_id'); 
		    $paperList = Db::name('exam_paper')->where('id','in',$paperIds)->column('id,name');
			foreach ($list as $k => &$v) {
				if (array_key_exists($v['paper_id'],$paperList)){
			           $v['paper_id'] = $paperList[$v['paper_id']];			
				} else {
					   $v['paper_id'] = '-';
				}	
			    if (array_key_exists($v['state'],$this->stateList)){
			           $v['state'] = $this->stateList[$v['state']];			
			    } else {
			    	   $v['state'] = '-';
			    }				    
			}		


            foreach ($list as $row) {
                $row->visible(['id','name','paper_id','starttime','stoptime','state']);
                
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
	public function add()
	{
		
	
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
	
	
	    return parent::edit($ids);
	}
	
	/**
	 * 删除
	 */
	public function del($ids = NULL)
	{
	    $row = $this->model->get($ids);
	    if (!$row)
	        $this->error(__('No Results were found'));
	    if ($row['state']<>'0')	{
		   $this->error(__('Exam Has Been Activated'));
	    }
	
	    return parent:: del($ids);
	}
	
	/**
	 * 启用
	 */
	public function start($ids = NULL)
	{
	    $row = $this->model->get($ids);
	    if (!$row)
	        $this->error(__('No Results were found'));
		if ($row['state']<>'0')	{
			$this->error(__('Exam Has Been Activated'));
		}

		if (!$row['paper_id'])	{
			$this->error(__('Paper Is Null'));
		}

		
	    if ($this->request->isAjax()) {
			$this->model->start($ids);
	        $this->success(__('Exam Activated Success',$ids), null, ['id' => $ids]);
	    }
		
	    
	}
	
	/**
	 * 取消
	 */
	public function cancel($ids = NULL)
	{
	    $row = $this->model->get($ids);
	    if (!$row)
	          $this->error(__('No Results were found'));
	    if ($row['state']<>'1')	{
		      $this->error(__('Exam Has Been Unactivated'));
	    }
		
	    if ($this->request->isAjax()) {
    		  $map['state']  = array('>',0);
	    	  $map['exam_id']  = array('in',$ids);
			  $arr=Db::name('exam_user')->where($map)->find();
			  if ($arr)	{
			        $this->error(__('Exam Has Been Used'));
			  }
		      $this->model->cancel($ids);
	          $this->success(__('Exam Unactivated Success',$ids), null, ['id' => $ids]);
	    }	
	}
	
	/**
	 * 获取班级列表
	 * @internal
	 */
	public function getteamlist()
	{
		$this->request->filter(['strip_tags', 'trim']);
	    $word = (array)$this->request->request("q_word/a");
	    $word = implode('', $word);
	
		$where['status']='normal';
	    if ($word<>''){
		   $where['name']=$word;
	    }		

		if($this->request->request("keyValue")){
			$ids = explode(",", $this->request->request("keyValue"));
			$where['id']=array('in',$ids);
		}

	    $list= Db::name('team')->field('id,name')->where($where)->select();
	    $pageNumber = $this->request->request("pageNumber");
	    $pageSize = $this->request->request("pageSize");
	    return json(['list' => array_slice($list, ($pageNumber - 1) * $pageSize, $pageSize), 'total' => count($list)]);
	}
	
	/**
	 * 获取学员列表
	 * @internal
	 */
	public function getuserlist()
	{
		$this->request->filter(['strip_tags', 'trim']);
	    $word = (array)$this->request->request("q_word/a");
	    $word = implode('', $word);
	
		$where['status']='normal';
	    if ($word<>''){
		   $where['name']=$word;
	    }		
		
		if($this->request->request("keyValue")){
			$ids = explode(",", $this->request->request("keyValue"));
			$where['id']=array('in',$ids);
		}
	
        $list= Db::name('user')->field('id,nickname name')->where($where)->select();
	    $pageNumber = $this->request->request("pageNumber");
	    $pageSize = $this->request->request("pageSize");
	    return json(['list' => array_slice($list, ($pageNumber - 1) * $pageSize, $pageSize), 'total' => count($list)]);
	}
	
	/**
	 * 获取试卷列表
	 * @internal
	 */
	public function getpaperlist()
	{
		$this->request->filter(['strip_tags', 'trim']);
	    $word = (array)$this->request->request("q_word/a");
	    $word = implode('', $word);
	
		$where['status']='normal';
	    if ($word<>''){
		   $where['name']=$word;
	    }		

		if($this->request->request("keyValue")){
			$ids = explode(",", $this->request->request("keyValue"));
			$where['id']=array('in',$ids);
		}

	
	    $list= Db::name('exam_paper')->field('id,name')->where($where)->select();
	    $pageNumber = $this->request->request("pageNumber");
	    $pageSize = $this->request->request("pageSize");
	    return json(['list' => array_slice($list, ($pageNumber - 1) * $pageSize, $pageSize), 'total' => count($list)]);
	}

}
