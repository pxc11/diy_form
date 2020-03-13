<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$op=$_GPC["op"];

$type_id=$_GPC["type_id"];
$form_name=pdo_get("pc_form_type",["id"=>$type_id],["title"])["title"];
if($op=="post"){
    $query = load()->object('query');
    $page=$_GPC["page"];
    $limit=$_GPC["limit"]?:10;
    $list = $query->from('pc_form_data')->page($page, $limit)->where("type_id",$type_id)->orderby("create_time" ,"desc")->getall();
    foreach ($list as $k=>&$v){
        $v["data"]=json_decode($v["data"],true);
    }

    $data=[
        "code"=>0,
        "data"=>$list
    ];
    exit(json_encode($data));
}else{
    include $this->template("form_data");
}

