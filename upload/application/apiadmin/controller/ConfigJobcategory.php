<?php
namespace app\apiadmin\controller;

class ConfigJobcategory extends \app\common\controller\Backend
{
    public function index()
    {
        $pid = input('get.pid/d', 0, 'intval');
        $list = model('CategoryJob')
            ->where('pid', $pid)
            ->order('sort_id desc,id asc')
            ->select();
        foreach ($list as $key => $value) {
            $children = model('CategoryJob')->getCache($value['id']);
            $list[$key]['hasChildren'] = $children ? true : false;
        }
        $this->ajaxReturn(200, '获取数据成功', $list);
    }
    public function options()
    {
        $list = model('CategoryJob')->getCache('0');
        $return = [];
        foreach ($list as $key => $value) {
            $arr = [];
            $arr['value'] = $key;
            $arr['label'] = $value;
            $arr['level'] = 1;
            $children = model('CategoryJob')->getCache($key);
            if ($children) {
                foreach ($children as $k => $v) {
                    $arr['children'][] = [
                        'value' => $k,
                        'label' => $v,
                        'level' => 2
                    ];
                }
            }
            $return[] = $arr;
        }
        $this->ajaxReturn(200, '获取数据成功', $return);
    }
    public function add()
    {
        $input_data = [
            'parentid' => input('post.parentid/a'),
            'name' => input('post.name/s', '', 'trim'),
            'sort_id' => input('post.sort_id/d', 0, 'intval'),
            'level' => input('post.level/d', 0, 'intval')
        ];
        $input_data['pid'] =
            isset($input_data['parentid']) && is_array($input_data['parentid'])
                ? (!empty($input_data['parentid'])
                    ? end($input_data['parentid'])
                    : 0)
                : 0;
        unset($input_data['parentid']);
        $result = model('CategoryJob')
            ->validate(true)
            ->allowField(true)
            ->save($input_data);
        if (false === $result) {
            $this->ajaxReturn(500, model('CategoryJob')->getError());
        }
        model('AdminLog')->record(
            '添加职位分类。分类ID【' .
                model('CategoryJob')->id .
                '】;分类名称【' .
                $input_data['name'] .
                '】',
            $this->admininfo
        );
        $this->ajaxReturn(200, '保存成功');
    }
    public function edit()
    {
        $id = input('get.id/d', 0, 'intval');
        if ($id) {
            $info = model('CategoryJob')->find($id);
            if (!$info) {
                $this->ajaxReturn(500, '数据获取失败');
            }
            if ($info['pid'] > 0) {
                $parent = model('CategoryJob')
                    ->where('id', $info['pid'])
                    ->column('pid');
                if ($parent[0] > 0) {
                    $info['parentid'] = [$parent[0], $info['pid']];
                } else {
                    $info['parentid'] = [$info['pid']];
                }
            } else {
                $info['parentid'] = [];
            }
            $this->ajaxReturn(200, '获取数据成功', ['info' => $info]);
        } else {
            $input_data = [
                'id' => input('post.id/d', 0, 'intval'),
                'parentid' => input('post.parentid/a'),
                'name' => input('post.name/s', '', 'trim'),
                'sort_id' => input('post.sort_id/d', 0, 'intval'),
                'level' => input('post.level/d', 0, 'intval')
            ];
            $id = intval($input_data['id']);
            if (!$id) {
                $this->ajaxReturn(500, '请选择数据');
            }
            $input_data['pid'] =
                isset($input_data['parentid']) &&
                is_array($input_data['parentid'])
                    ? (!empty($input_data['parentid'])
                        ? end($input_data['parentid'])
                        : 0)
                    : 0;
            unset($input_data['parentid']);
            $result = model('CategoryJob')
                ->validate(true)
                ->allowField(true)
                ->save($input_data, ['id' => $id]);
            if (false === $result) {
                $this->ajaxReturn(500, model('CategoryJob')->getError());
            }
            model('AdminLog')->record(
                '编辑职位分类。分类ID【' .
                    $id .
                    '】;分类名称【' .
                    $input_data['name'] .
                    '】',
                $this->admininfo
            );
            $this->ajaxReturn(200, '保存成功');
        }
    }
    public function delete()
    {
        $id = input('post.id/a');
        if (!$id) {
            $this->ajaxReturn(500, '请选择数据');
        }
        $children = model('CategoryJob')
            ->where('pid', 'in', $id)
            ->select();
        if ($children) {
            $child_ids = [];
            foreach ($children as $key => $value) {
                $child_ids[] = $value['id'];
            }
            model('CategoryJob')
                ->where('pid', 'in', $child_ids)
                ->delete();
            model('CategoryJob')
                ->where('pid', 'in', $id)
                ->delete();
        }
        model('CategoryJob')->destroy($id);
        model('AdminLog')->record(
            '删除职位分类。分类ID【' . implode(',', $id) . '】',
            $this->admininfo
        );
        $this->ajaxReturn(200, '删除成功');
    }
    public function tablesave()
    {
        $inputdata = input('post.');
        if (!$inputdata) {
            $this->ajaxReturn(500, '提交数据为空');
        }
        $sqldata = [];
        foreach ($inputdata as $key => $value) {
            if (!$value['id']) {
                continue;
            }
            $arr['id'] = $value['id'];
            $arr['sort_id'] = $value['sort_id'] == '' ? 0 : $value['sort_id'];
            $arr['pid'] = $value['pid'];
            $arr['name'] = $value['name'];
            $arr['level'] = $value['level'];
            $sqldata[] = $arr;
        }
        $validate = \think\Loader::validate('CategoryJob');
        foreach ($sqldata as $key => $value) {
            if (!$validate->check($value)) {
                $this->ajaxReturn(500, $validate->getError());
            }
        }
        model('CategoryJob')
            ->isUpdate()
            ->saveAll($sqldata);
        model('AdminLog')->record('批量保存职位分类', $this->admininfo);
        $this->ajaxReturn(200, '保存成功');
    }
}
