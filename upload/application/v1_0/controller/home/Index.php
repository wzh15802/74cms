<?php
namespace app\v1_0\controller\home;

class Index extends \app\v1_0\controller\common\Base
{
    public function _initialize()
    {
        parent::_initialize();
    }
    public function index()
    {
        if (in_array(config('platform'),['wechat','mobile'])) {
            $index_module = model('MobileIndexModule')->column(
                'alias,is_display,plan_id'
            );
            $return['module_rule'] = $index_module;
        }

        //菜单
        if (
            !in_array(config('platform'),['wechat','mobile']) ||
            $index_module['menu']['is_display'] == 1
        ) {
            $data['menu_list'] = $this->getMenu();
        }
        //公告
        if (
            !in_array(config('platform'),['wechat','mobile']) ||
            $index_module['notice']['is_display'] == 1
        ) {
            $data['notice_list'] = $this->getNotice();
        }
        //名企
        if (
            !in_array(config('platform'),['wechat','mobile']) ||
            $index_module['famous']['is_display'] == 1
        ) {
            $data['famous_list'] = $this->getFamous();
        }
        //热门职位
        if (
            !in_array(config('platform'),['wechat','mobile']) ||
            $index_module['hotword']['is_display'] == 1
        ) {
            $data['hotword_list'] = $this->getHotword();
        }
        //新闻资讯
        if (
            !in_array(config('platform'),['wechat','mobile']) ||
            $index_module['article']['is_display'] == 1
        ) {
            $data['article_list'] = $this->getArticle();
        }
        $return['data'] = $data;
        $this->ajaxReturn(200, '获取数据成功', $return);
    }
    /**
     * 获取首页导航菜单
     */
    protected function getMenu()
    {
        $list = model('MobileIndexMenu')->getCache();
        return $list;
    }
    /**
     * 获取公告
     */
    protected function getNotice()
    {
        $list = cache('index_notice_list');
        if (!$list) {
            $list = model('Notice')
                ->field('id,title,link_url')
                ->where('is_display', 1)
                ->order('sort_id desc,id desc')
                ->select();
            cache('index_notice_list', $list, 3600);
        }
        return $list;
    }
    /**
     * 热门职位
     */
    protected function getHotword()
    {
        $list = cache('index_hotword_list');
        if (!$list) {
            $list = model('Hotword')
                ->field('word,hot')
                ->order('hot desc')
                ->limit(16)
                ->select();
            cache('index_hotword_list', $list, 3600);
        }
        return $list;
    }
    /**
     * 名企
     */
    protected function getFamous()
    {
        $return = cache('index_famous_list');
        if (!$return) {
            $famous_enterprises_setmeal = config(
                'global_config.famous_enterprises'
            );
            $famous_enterprises_setmeal =
                $famous_enterprises_setmeal == ''
                    ? []
                    : explode(',', $famous_enterprises_setmeal);
            if (empty($famous_enterprises_setmeal)) {
                $this->ajaxReturn(200, '获取数据成功', ['items' => []]);
            }
            $list = model('Company')
                ->where('setmeal_id', 'in', $famous_enterprises_setmeal)
                ->field('id,logo,companyname')
                ->order('refreshtime desc')
                ->limit(9)
                ->select();
            $job_list = $comid_arr = $logo_id_arr = $logo_arr = [];
            foreach ($list as $key => $value) {
                $comid_arr[] = $value['id'];
                $value['logo'] > 0 && ($logo_id_arr[] = $value['logo']);
            }
            if (!empty($logo_id_arr)) {
                $logo_arr = model('Uploadfile')->getFileUrlBatch($logo_id_arr);
            }
            if (!empty($comid_arr)) {
                $job_data = model('Job')
                    ->where('company_id', 'in', $comid_arr)
                    ->where('is_display', 1)
                    ->column('id,company_id,jobname', 'id');
                foreach ($job_data as $key => $value) {
                    $job_list[$value['company_id']][] = $value['jobname'];
                }
            }
            $return = [];
            foreach ($list as $key => $value) {
                $arr = $value->toArray();
                $arr['logo'] = isset($logo_arr[$value['logo']])
                    ? $logo_arr[$arr['logo']]
                    : default_empty('logo');
                $arr['jobnum'] = isset($job_list[$value['id']])
                    ? count($job_list[$arr['id']])
                    : 0;
                $return[] = $arr;
            }
            cache('index_famous_list', $return, 3600);
        }

        return $return;
    }
    /**
     * 新闻资讯
     */
    protected function getArticle()
    {
        $return = cache('index_article_list');
        if (!$return) {
            $list = model('Article')
                ->field('id,title,thumb,link_url,click,addtime,source')
                ->where('is_display', 1)
                ->limit(5)
                ->order('sort_id desc,id asc')
                ->select();
            $thumb_id_arr = $thumb_arr = [];
            foreach ($list as $key => $value) {
                $value['thumb'] > 0 && ($thumb_id_arr[] = $value['thumb']);
            }
            if (!empty($thumb_id_arr)) {
                $thumb_arr = model('Uploadfile')->getFileUrlBatch(
                    $thumb_id_arr
                );
            }
            $return = [];
            foreach ($list as $key => $value) {
                $arr = $value->toArray();
                $arr['thumb'] = isset($thumb_arr[$arr['thumb']])
                    ? $thumb_arr[$arr['thumb']]
                    : default_empty('thumb');
                $arr['source_text'] = $arr['source'] == 1 ? '转载' : '原创';
                $return[] = $arr;
            }
            cache('index_article_list', $return, 3600);
        }
        return $return;
    }
    public function ajaxSearchLocation(){
        $alias = input('get.alias/s','joblist','trim');
        $input = [
            'keyword'=>input('get.keyword/s',null)
        ];
        $path = 'index/index/index';
        if($alias=='joblist'){
            $path = 'index/job/index';
        }else if($alias=='resumelist'){
            $path = 'index/resume/index';
        }else if($alias=='companylist'){
            $path = 'index/company/index';
        }else if($alias=='articlelist'){
            $path = 'index/article/index';
        }
        $this->ajaxReturn(200,'获取数据成功',config('global_config.sitedomain').url($path,$input));
    }
}
