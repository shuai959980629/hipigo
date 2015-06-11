<?php
/**
 * 
 * @copyright(c) 2014-11-12
 * @author zxx
 * @version Id:subject.php
 */

class Subject extends WX_Controller
{

    /*
     * zxx
     * 专题 吼 页
     */
    public function subject_activity(){
        if($_POST){
            $name = $this->input->post('name');//名字
            $image = $this->input->post('image');//图片
            $id_open = $this->input->post('id_open');

            $fp = file_get_contents(DOCUMENT_ROOT."/wapi/subject.txt");
            $h = json_decode('['.$fp.']',true);
            $count = 0;
            if($h){
                $count = count($h);
            }
            $data = array(
                'id'=>$count,
                'name'=>$name,
                'image'=>$image,
                'id_open'=>$id_open,
                'created'=>date('Y-m-d H:i:s',time())
            );
            //需存储的信息
            if($fp){
                file_put_contents(DOCUMENT_ROOT.'/wapi/subject.txt',',',FILE_APPEND);
            }
            file_put_contents(DOCUMENT_ROOT.'/wapi/subject.txt',json_encode($data),FILE_APPEND);
            die(json_encode(array('state'=>1,'url'=>'http://'.DOMAIN_URL.'/subject/subject_share?id='.$count)));
        }else{
            //读取id_open 第一步，获取code
            $open_id = $_GET['openid'];
            if(!$open_id){
                Header('Location:http://'.DOMAIN_URL.'/subject/subject_3/subject');
                die;
            }

//            $fp = file_get_contents(DOCUMENT_ROOT."/wapi/subject.txt");
//            $h = json_decode('['.$fp.']',true);
//            $num = 0;
//            foreach ($h as $va) {
//                if($va){
//                    if($open_id == $va['id_open']){
//                        $num = 1;
//                    }
//                }
//            }
//
//            $this->smarty->assign('num',$num);
            $this->smarty->assign('open_id',$open_id);
            $this->smarty->view('subject_activity');
        }
    }

    /*
     * zxx
     * 专题  分享页
     */
    function subject_share(){
        $id = $_GET['id'];

        //获取当前用户openid
        $open_id = $_GET['openid'];
//        var_dump($_COOKIE['id_open_tmp'],$open_id);
        if( ! $open_id || $_COOKIE['id_open_tmp']!=$open_id) {
            Header('Location:http://'.DOMAIN_URL.'/subject/subject_share_3/subject_share?id='.$id);
            die;
        }

        $fp = file_get_contents(DOCUMENT_ROOT."/wapi/subject.txt");
        $h = json_decode('['.$fp.']',true);
        $arr = array();
        foreach ($h as $va) {
            if($va){
                if($id == $va['id']){
                    $arr = $va;
                }
            }
        }
        $arr['oid'] = $open_id;//当前用户的open_id

        $title = '';
        if(strpos($arr['image'],"1") != false){
            $title = '<b>'.$arr['name'].'</b>的身心永远都属于姐姐。';
        }elseif(strpos($arr['image'],"2") != false){
            $title = '<b>'.$arr['name'].'</b>，如果有必要相遇就终究会遇到，地球人把这种事情叫做命运。';
        }elseif(strpos($arr['image'],"3") != false){
            $title = '我是异性恋，<b>'.$arr['name'].'</b>也是喜欢女生的。';
        }elseif(strpos($arr['image'],"4") != false){
            $title = '如果上天再给我一个机会的话，我会对<b>'.$arr['name'].'</b>说三个字:放过我吧。';
        }
//        var_dump($arr);
        $this->smarty->assign($arr);
        setcookie('id_open_tmp', '', time() - (60 * 60 * 24 * 30), '/', '.hipigo.cn');
        $_COOKIE['id_open_tmp'] = '';
        unset($_COOKIE['id_open_tmp']);

        $this->smarty->assign('title',$title);
        $this->smarty->view('subject_share');
    }



    /*
     * zxx
     * 读取openid第二步 分享页 获取openid
     */
    public function subject_share_2($number) {
        $id = $_GET['id'];
        //获取授权用户openid
        $id_open = $this->get_open_ids($number);
        setcookie('id_open_tmp', $id_open, time() + (60 * 60 * 24 * 30), '/', '.hipigo.cn');
        if(!$id_open) {
            $id_open = 0;
        }
        $url = 'http://'.DOMAIN_URL.'/subject/subject_share?openid='.$id_open.'&id='.$id;
        Header('Location:'.$url);
    }

    /*
     * zxx
     * 读取openid第二步 分享页 获取openid
     */
    public function subject_share_3($type) {
        $id = $_GET['id'];
        $this->get_open_ids(1,$type,$id);
    }


    /*
     * zxx
     * 读取openid第二步 获取openid
     */
    public function subject_2($number) {
        //获取授权用户openid
        $id_open = $this->get_open_ids($number);
        if(!$id_open) {
            $id_open = 0;
        }
        $url = 'http://'.DOMAIN_URL.'/subject/subject_activity?openid='.$id_open;
        Header('Location:'.$url);
    }

    /*
     * zxx
     * 读取openid第二步 获取openid
     */
    public function subject_3($type) {
        $this->get_open_ids(1,$type);
    }


    function test(){
//        $fp = $myfile = fopen(DOCUMENT_ROOT.'/wapi/subject1.txt', "w");
        $fp = file_get_contents(DOCUMENT_ROOT."/wapi/subject.txt");
        var_dump($fp);
        var_dump(json_decode('['.$fp.']',true));
        $h = json_decode('['.$fp.']',true);
        $arr = array();
        foreach ($h as $va) {
            if($va){
                if(1 == $va['id']){
                    $arr = $va;
                }
            }
        }
        var_dump($arr);die;
        $count = 0;
        if($h){
            $count = count($h);
        }
        var_dump($count);
        die;
        $data = array(
            'id'=>$count,
            'name'=>'思密达',
            'image'=>'image/dd.jpg',
            'id_open'=>'qwedszdgfdh24vbeqwcvcjljsdvfq2w',
            'created'=>date('Y-m-d H:i:s',time())
        );
        if($fp){
            file_put_contents(DOCUMENT_ROOT.'/wapi/subject.txt',',',FILE_APPEND);
        }
        file_put_contents(DOCUMENT_ROOT.'/wapi/subject.txt',json_encode($data),FILE_APPEND);
//        $myfile = fopen(DOCUMENT_ROOT.'/wapi/subject1.txt', "w") or die("Unable to open file!");
//        fwrite($myfile, json_encode($data));
//        fclose($myfile);
    }

}