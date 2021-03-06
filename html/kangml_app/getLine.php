<?php
	require("../system.php");
	$u = trim($_POST["username"]);
	$p = trim($_POST["password"]);
	$db = db(_openvpn_);
	$action = $_GET["action"];
	$info = $db->where(array(_iuser_=>$u,_ipass_=>$p))->find();
	
	if($info){
		if($action == "select_note")
		{
			$note = db("app_note")->where(["id"=>$_POST["select_id"]])->find();
			if(!$note){
				$data["status"] = "error";
				$data["msg"] = "节点不存在";
				die(json_encode($data));
			}
			
			$up = $db->where(["id"=>$info["id"]])->update(["note_id"=>$_POST["select_id"]]);
			
			$data = array(
				'status'=>'success'
			);
			
			die(json_encode($data));
			exit;
		}
		$m = new Map();
		$id = $_POST['id'];	
		$connect_unlock = $m->type("cfg_app")->getValue("connect_unlock",0);
		if($info[_i_] == "1" || $connect_unlock == 1)
		{
			$line = db('line')->where(array('id'=>$id))->find();			
			$content = html_decode($line['content']);
			$config = $m->type("cfg_zs")->getAll();
			$noteoff = $m->type("cfg_app")->getValue("noteoff",0);
			if($noteoff == "1")
			{
				$note_id = $info["note_id"];
				if($def = db("app_note")->where(["id"=>$note_id])->find()){
					$content = str_ireplace("[domain]",$def["ipport"],$content);
				}
				else
				{
					$data["status"] = "error";
					$data["msg"] = "节点不存在";
					die(json_encode($data));
				}
			}
			if($config["onoff"]==1)
			{
				$content = preg_replace("/\<ca\>(.+?)\<\/ca\>/is",html_decode($config["ca"]),$content);
				$content = preg_replace("/\<tls\-auth\>(.+?)\<\/tls\-auth\>/is",html_decode($config["tls"]),$content);
				$content = preg_replace("/\[domain\]/is",html_decode($config["domain"]),$content);
			}
			$content = preg_replace("/\[time\]/is",time(),$content);
			$data = array(
				'status'=>'success',
				'name'=>$line['name'],
				'type'=>$line['type'],
				'content'=>base64_encode($content)
			);
			die(json_encode($data));
		}
		else
		{
			$data = array(
			'status'=>'error',
			'msg'=>"您的身份信息处于未激活状态 不可安装"
			);
			die(json_encode($data));
		}
	}else{
		$data = array(
			'status'=>'error',
			'msg'=>"您的身份信息未能经过验证"
		);
		die(json_encode($data));
	}