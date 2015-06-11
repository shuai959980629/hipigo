<?php

/**
 * Class Right 权限
 */
class Right extends Admin_Controller
{

    /**
     * 获取用户菜单、权限
     * @param $userID 用户ID
     * @param $type 类型(0：所有，1：菜单，2：权限)
     */
    public function get_menu($userID,$type = 0)
    {
        $this->load->model('right_model','right');

        $where = 'mu.id_user = '.$userID;
        if( $type > 0 ){
            $where .=' AND r.type = '.$type;
        }
        $rights = $this->right->get_user_right($where);
        $rightList = $this->list_to_tree($rights);
        return $rightList;
    }

    /**
     * 把返回的数据转换成Tree
     * @author rjy
     * @param array $list 要转换的数据
     * @param array $pk 数据的id
     * @param string $pid parent标记字段
     * @return array
     */
    protected  function list_to_tree($list, $pk='id_right',$pid = 'id_parent',$child = 'children',$root=0) {
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }


}



/* End of file right.php */