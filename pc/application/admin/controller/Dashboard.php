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

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;
use think\Db;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $seventtime = \fast\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');
        $total=array();
        $total['user']=Db::name('user')->count();
        $total['page']=Db::name('exam_paper')->count();
        $total['pagetips']=Db::name('exam_paper')->where('state','=',0)->count();
		$total['exam']=Db::name('exam')->count();
        $total['examtips']=Db::name('exam')->where('state','=',0)->count();
		
		$total['decide']=Db::name('exam_user')->count();
		$map['state']=array('>',0);
		$map['isdecide']=array('=',0);
		$total['decidetips']=Db::name('exam_user')->where($map)->count();
		
		$question['单选题']=Db::name('question')->where('type','=',1)->count();
		$question['多选题']=Db::name('question')->where('type','=',2)->count();
		$question['判断题']=Db::name('question')->where('type','=',3)->count();
		$question['填空题']=Db::name('question')->where('type','=',4)->count();
		$question['其他题']=Db::name('question')->where('type','=',9)->count();

        $this->view->assign('total',$total);
        $this->view->assign('question',$question);
		
		$this->view->assign([
            'totaluser'        => 35200,
            'totalviews'       => 219390,
            'totalorder'       => 32143,
            'totalorderamount' => 174800,
            'todayuserlogin'   => 321,
            'todayusersignup'  => 430,
            'todayorder'       => 2324,
            'unsettleorder'    => 132,
            'sevendnu'         => '80%',
            'sevendau'         => '32%',
            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'addonversion'       => $addonVersion,
            'uploadmode'       => $uploadmode
        ]);

        return $this->view->fetch();
    }

}
