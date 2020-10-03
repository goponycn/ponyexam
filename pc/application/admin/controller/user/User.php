<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use think\Db;
/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;


    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
		$this->searchFields='username';
        $this->model = model('User');
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
                $v->hidden(['password', 'salt']);
            }
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
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }

	/**
	 * 获取学员列表
	 * @internal
	 */
	public function getlist()
	{
	    //搜索关键词,客户端输入以空格分开,这里接收为数组
	    $word = (array)$this->request->request("q_word/a");
	    $word = implode('', $word);
	
		$where['status']='normal';
	    if ($word<>''){
		   $where['name']=$word;
	    }		
		$user = new \app\admin\model\User();
	    $list= $user->where($where)->select();
        $list= Db::name('user')->field('id,nickname name')->where($where)->select();
		
	    $pageNumber = $this->request->request("pageNumber");
	    $pageSize = $this->request->request("pageSize");
	    return json(['list' => array_slice($list, ($pageNumber - 1) * $pageSize, $pageSize), 'total' => count($list)]);
	}
}
