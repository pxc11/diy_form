<?php
/**
 * wjsw_form模块微站定义
 *
 * @author wangjiasiwei
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class pc_formModuleSite extends WeModuleSite
{
    private $result = [
        'code' => 1,
        'msg' => "",
        "data" => null
    ];

    public function doWebList()
    {
        global $_GPC , $_W;
        $op = $_GPC['op'];
        if ($op == "post") {
            $page = $_GPC['page'];
            $limit = $_GPC['limit'];
            $query = load()->object('query');
            $row = $query->from('pc_form_type')->page($page, $limit)->getall();
            $count = $query->from('pc_form_type')->count();
            $data = [
                "code" => 0,
                "count" => $count,
                "data" => $row
            ];
            echo json_encode($data);
            exit();
        } else {
            $http=$_W['sitescheme'];
            include $this->template("form_list");
        }

    }

    public function doWebTypeAdd()
    {
        global $_GPC;
        $op = $_GPC['op'];
        if ($op == "post") {
            $data = [
                "title" => $_GPC['title'],
                'is_show' => $_GPC['is_show'] ?: 0,
                'create_time' => (string)date("Y-m-s H:i:s", time())
            ];
            if (empty($data['title'])) {
                $this->result['code'] = 0;
                $this->result['msg'] = "请输入表单名称";
                exit(json_encode($this->result));
            }
            $res = pdo_insert("pc_form_type", $data);
            if (!$res) {
                $this->result['code'] = 0;
                $this->result['msg'] = "添加失败";
            } else {
                $this->result['code'] = 1;
                $this->result['msg'] = "添加成功";
            }
            exit(json_encode($this->result));
        } else {
            include $this->template("form_type_add");
        }
    }

    public function doWebTypeDel()
    {
        global $_GPC;
        $id = $_GPC['id'];
        if (empty($id)) {
            $this->result['code'] = 0;
            $this->result['msg'] = "找不到数据";
            exit(json_encode($this->result));
        }
        pdo_begin();
        $res = pdo_delete("pc_form_type", ['id' => $id]);
        if(!$res){
            pdo_rollback();
            $this->oupout(0,"删除失败");
        }
        $res= pdo_delete("pc_form_project", ["type_id" => $id]);
        if(!$res){
            pdo_rollback();
            $this->oupout(0,"删除失败");
        }
        $res=pdo_delete("pc_form_data", ["type_id" => $id]);
        if(!$res){
            pdo_rollback();
            $this->oupout(0,"删除失败");
        }

        pdo_commit();
        $this->oupout(1,"删除成功");
    }

    public function doWebTypeShow()
    {
        global $_GPC;
        $id = (int)$_GPC['id'];
        $is_show = $_GPC['is_show'] ?: 0;
        if (empty($id)) {
            $this->result['code'] = 0;
            $this->result['msg'] = "找不到数据";
            exit(json_encode($this->result));
        }
        $res = pdo_update("pc_form_type", ["is_show" => $is_show], ['id' => $id]);
        if ($res) {
            $this->result['code'] = 1;
            $this->result['msg'] = "更改成功";
        } else {
            $this->result['code'] = 0;
            $this->result['msg'] = "更改失败";
        }
        exit(json_encode($this->result));
    }

    public function doWebProject()
    {
        global $_GPC;
        $id = $_GPC['id'];
        $list = pdo_fetchall("select * from " . tablename("pc_form_project") . " where type_id = $id order by sort desc, id asc");
        $form_name = pdo_get("pc_form_type", ["id" => $id], ["title"])["title"];
        include $this->template("form_project");
    }

    public function doWebProjectShow()
    {
        global $_GPC;
        $id = (int)$_GPC['id'];
        $is_show = $_GPC['is_show'] ?: 0;
        if (empty($id)) {
            $this->result['code'] = 0;
            $this->result['msg'] = "找不到数据";
            exit(json_encode($this->result));
        }
        $res = pdo_update("pc_form_project", ["is_show" => $is_show], ['id' => $id]);
        if ($res) {
            $this->result['code'] = 1;
            $this->result['msg'] = "更改成功";
        } else {
            $this->result['code'] = 0;
            $this->result['msg'] = "更改失败";
        }
        exit(json_encode($this->result));
    }

    public function doWebProjectRequired()
    {
        global $_GPC;
        $id = (int)$_GPC['id'];
        $is_required = $_GPC['is_required'] ?: 0;
        if (empty($id)) {
            $this->result['code'] = 0;
            $this->result['msg'] = "找不到数据";
            exit(json_encode($this->result));
        }
        $res = pdo_update("pc_form_project", ["is_required" => $is_required], ['id' => $id]);
        if ($res) {
            $this->result['code'] = 1;
            $this->result['msg'] = "更改成功";
        } else {
            $this->result['code'] = 0;
            $this->result['msg'] = "更改失败";
        }
        exit(json_encode($this->result));
    }

    public function doWebProjectAdd()
    {
        global $_GPC;
        $op = $_GPC['op'];
        if ($op == "post") {
            $data = [
                "type_id" => $_GPC["id"],
                "project_type" => $_GPC["project_type"],
                "sort" => $_GPC["sort"],
                "title" => $_GPC["title"],
                "is_show" => $_GPC["is_show"] ?: 0,
                "is_required" => $_GPC["is_required"] ?: 0,
                "content" => trim($_GPC["content"]),
                "create_time" => date("Y-m-d H:i:s")
            ];
            if (empty($data['type_id'])) {
                $this->result['code'] = 0;
                $this->result['msg'] = "表单不存在";
                exit(json_encode($this->result));
            }

            if (empty($data['title'])) {
                $this->result['code'] = 0;
                $this->result['msg'] = "请填写题目";
                exit(json_encode($this->result));
            }
            $is_have_title=pdo_get("pc_form_project",["title"=>$data['title'],"type_id"=>$data['type_id']],["id"]);
            if(count($is_have_title)>1){
                $this->oupout(0,"不能有重复问题的选项，添加失败");
            }
            if (empty($data['content']) && $data['project_type'] != "输入框" &&$data['project_type'] != "时间框") {
                $this->result['code'] = 0;
                $this->result['msg'] = "请填写选项";
                exit(json_encode($this->result));
            }
            $res = pdo_insert("pc_form_project", $data);
            if ($res) {
                $this->result['code'] = 1;
                $this->result['code'] = 1;
                $this->result['msg'] = "添加成功";
            } else {
                $this->result['code'] = 0;
                $this->result['msg'] = "添加失败";
            }
            exit(json_encode($this->result));
        } else {
            $id = $_GPC['id'];
            $form_name = pdo_get("pc_form_type", ["id" => $id], ["title"])["title"];
            include $this->template("form_project_add");
        }
    }

    public function doWebProjectEdit()
    {
        global $_GPC;
        $op = $_GPC['op'];
        if ($op == "post") {
            $data = [
                "project_type" => $_GPC["project_type"],
                "sort" => $_GPC["sort"],
                "title" => $_GPC["title"],
                "is_show" => $_GPC["is_show"] ?: 0,
                "is_required" => $_GPC["is_required"] ?: 0,
                "content" => trim($_GPC["content"])
            ];
            $id=$_GPC['id'];
            if(empty($id)){
                $this->result['code'] = 0;
                $this->result['msg'] = "该项目不存在";
                exit(json_encode($this->result));
            }

            if (empty($data['title'])) {
                $this->result['code'] = 0;
                $this->result['msg'] = "请填写题目";
                exit(json_encode($this->result));
            }
            $type_id=pdo_get("pc_form_project",["id"=>$id],["type_id"])["type_id"];
            $is_have_title=pdo_get("pc_form_project",["title"=>$data['title'],"type_id"=>$type_id],["id"]);
            if(count($is_have_title)>1){
                $this->oupout(0,"不能有重复问题的选项，添加失败");
            }

            if (empty($data['content']) && $data['project_type'] != "输入框" &&$data['project_type'] != "时间框") {
                $this->result['code'] = 0;
                $this->result['msg'] = "请填写选项";
                exit(json_encode($this->result));
            }
            $res = pdo_update("pc_form_project", $data,['id'=>$id]);
            if ($res) {
                $this->result['code'] = 1;
                $this->result['msg'] = "更新成功";
            } else {
                $this->result['code'] = 0;
                $this->result['msg'] = "更新失败";
            }
            exit(json_encode($this->result));
        } else {
            $id = $_GPC['id'];
            $item=pdo_getall("pc_form_project",["id"=>$id])[0];
            $form_name = pdo_get("pc_form_type", ["id" => $item['type_id']], ["title"])["title"];
            include $this->template("form_project_edit");
        }
    }

    public function doWebProjectDel(){
        global $_GPC;
        $id=$_GPC["id"];
        if(!$id){
            $this->oupout(0,"无数据");
        }
        $res=pdo_delete("pc_form_project",['id'=>$id]);
        if($res){
            $this->oupout(1,"删除成功");
        }else{
            $this->oupout(0,"删除失败");
        }
    }

    private function oupout($code,$msg="",$data=""){
        $this->result['code'] = $code;
        $this->result['msg'] = $msg;
        $this->result['data'] = $data;
        exit(json_encode($this->result));
    }

    public function doWebTypeTitleEdit(){
        global $_GPC;
        $id=$_GPC["id"];
        $title=$_GPC["title"];
        if(!$id){
            $this->oupout(0,"无数据");
        }
        if(!$title){
            $this->oupout(0,"不能为空");
        }
        $res=pdo_update("pc_form_Type",['title'=>$title],["id"=>$id]);
        if($res){
            $this->oupout(1,"更新成功");
        }else{
            $this->oupout(0,"更新失败");
        }

    }

    public function doMobileForm(){
        global $_GPC;

        $op=$_GPC["op"];
        if($op=="post"){
             $type_id=$_POST["type_id"];
            $list=pdo_fetchall("select * from ".tablename("pc_form_project")." where type_id = ".$type_id." and is_show = 1 ");
            if(!$list){
                $this->oupout(0,"表单失效");
            }
            $data=[];
            foreach ($list as $v){
                if($v["is_required"]==1&&empty($_POST[$v['title']])){
                    $this->oupout(0,"请输入".$v['title']);
                }
                $data[$v['title']]=$_POST[$v['title']];

            }
            $s_data=[
                "type_id"=> $type_id,
                "data"=>json_encode($data),
                "create_time"=>date("Y-m-d H:i:s")
            ];
            $res=pdo_insert("pc_form_data",$s_data);
            if($res){
                $this->oupout(1,"提交成功");
            }else{
                $this->oupout(0,"提交失败！");
            }


        }else{
            $id=$_GPC["id"];
            $form=pdo_get("pc_form_type",['id'=>$id],["is_show","title"]);
            if(!$form["is_show"]){
                message("该表单失效","","error");
            }else{
                $list=pdo_fetchall("select * from ".tablename("pc_form_project")." where type_id = ".$id." and is_show = 1 order by sort desc , id asc");
                if(!$list){
                    message("该表单失效","","error");
                }
                $form_title=$form["title"];

                include $this->template("form_display");
            }

        }

    }
}