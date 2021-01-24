<?php

$MyRoles=getArrayAvailableUserRoles();
$AllowRead=$MODULE["settings"]["allowread"];
$AllowWrite=$MODULE["settings"]["allowwrite"];
$AllowUpload=is_access($MyRoles,explode(",",$MODULE["settings"]["allowupload"]));
$AllowDelete=is_access($MyRoles,explode(",",$MODULE["settings"]["allowdelete"]));


$randomize=explode("|","один|два|три|четыре|пять|шесть|семь|восемь|девять");
$randomize2=explode("|","десять|двадцать|тридцать|сорок|пятьдесят|шестьдесят|семьдесят|восемьдесят|девяносто");
$n1=rand(0,count($randomize)-1);
$n2=rand(0,count($randomize2)-1);
$text_randomize="Решите пример: $randomize2[$n2] плюс $randomize[$n1]";
$hash_randomize=md5($secret.((($n2+1)*10)+$n1+1));
if(!empty($_GET["text"])) $div.=$_GET["text"];
if($_GET["act"]=="logout"){
	if($logged){
		mysql_query("delete from {$dbprefix}sessions where sid='$userinfo[sid]'");
		setcookie("sid","");
		$_PL["setcookie"]["sid"]="";
		$logged=false;
		$userinfo=null;
		$div.="Вышли с аккаунта!<br>";
	}
	else $div.="Ошибка, вы не авторизованы!<br>";
	if($_ISPC) header("Location: $siteurl/");
	else {
		$_CH[]=["location"=>1,"title"=>"Нажмите для переадресации...","playlist_url"=>"$siteurl/"];
	}
}
if($_GET["act"]=="mylinks"&&$logged){
	$GLMYCH=getPluginMetaKey("[MYMENU_$userinfo[id]]",true);
	$MYCH=$GLMYCH[0]["src"];
	if($_GET["cmd"]=="del"){
		array_splice($MYCH["channels"],$_GET["pid"],1);
		//unset($MYCH["channels"][$_GET["pid"]]);
		savePluginMetaKey("[MYMENU_$userinfo[id]]",json_encode($MYCH),$GLMYCH[0]["id"]);
		$div="Ссылка #$_GET[pid] удалена!";
	}
	if($_GET["cmd"]=="save"){
		
		if(empty($_GET["pid"])&&$_GET["pid"]!="0") $MYCH["channels"][]=$_GET["mych"];		
		else $MYCH["channels"][$_GET["pid"]]=$_GET["mych"];			
		savePluginMetaKey("[MYMENU_$userinfo[id]]",json_encode($MYCH),$GLMYCH[0]["id"]);
		$div="Сохранено! ".$_GET["mych"]["playlist_url"];
			
	}
	if($_GET["cmd"]=="savemain"){
		$MYCH["mylinkonmain"]=$_GET["mylinkonmain"]?"1":"0";
		$MYCH["mylinkonmenu"]=$_GET["mylinkonmenu"]?"1":"0";
		savePluginMetaKey("[MYMENU_$userinfo[id]]",json_encode($MYCH),$GLMYCH[0]["id"]);
		$div="Сохранено! ";			
	}
	if($_GET["cmd"]=="add"){
		$Channels[]=["title"=>"URL:".$MYCH["channels"][$_GET["pid"]]["playlist_url"],"search_on"=>"URL: ", "playlist_url"=>"payd_mych[playlist_url]","value"=>$MYCH["channels"][$_GET["pid"]]["playlist_url"]];
		$Channels[]=["title"=>"TITLE:".$MYCH["channels"][$_GET["pid"]]["title"],"search_on"=>"TITLE: ","playlist_url"=>"payd_mych[title]","value"=>$MYCH["channels"][$_GET["pid"]]["title"]];
		$Channels[]=["title"=>"ICON:".$MYCH["channels"][$_GET["pid"]]["logo_30x30"],"search_on"=>"ICON: ","playlist_url"=>"payd_mych[logo_30x30]","value"=>$MYCH["channels"][$_GET["pid"]]["logo_30x30"]]; 
		
		$Channels[]=["logo_30x30"=>"$siteurl/include/templates/images/submit.png","type"=>"button","title"=>"OK","playlist_url"=>"$MODULE[link]&act=mylinks&cmd=save&pid=$_GET[pid]&mych[playlist_url]=payd_mych[playlist_url]&mych[title]=payd_mych[title]&mych[logo_30x30]=payd_mych[logo_30x30]","description"=>""];
		
	}
	else{
		$Channels[]=["title"=>"Отобразить на главной сайта","search_on"=>$MYCH["mylinkonmain"]?"checked":"unchecked","playlist_url"=>"payd_mylinkonmain","type"=>"checkbox","value"=>"1"];
		$Channels[]=["title"=>"Отобразить в меню","search_on"=>$MYCH["mylinkonmenu"]?"checked":"unchecked","playlist_url"=>"payd_mylinkonmenu","type"=>"checkbox","value"=>"1"];
		$Channels[]=["type"=>"button","title"=>"Сохранить","playlist_url"=>"$MODULE[link]&act=mylinks&cmd=savemain&mylinkonmain=payd_mylinkonmain&mylinkonmenu=payd_mylinkonmenu","description"=>""];
		foreach($MYCH["channels"] as $k=>$v){
			$v["menu"][]=["title"=>"Удалить ссылку","playlist_url"=>"$MODULE[link]&act=mylinks&cmd=del&pid=$k"];
			$v["menu"][]=["title"=>"Редактировать ссылку","playlist_url"=>"$MODULE[link]&act=mylinks&cmd=add&pid=$k"];
			$Channels[]=$v;
		}
		$Channels[]=["title"=>"Добавить новую ссылку","logo_30x30"=>"$siteurl/include/templates/images/dot.png","playlist_url"=>"$MODULE[link]&act=mylinks&cmd=add"];
	}
	$Channels[]=["title"=>"Назад в профиль","logo_30x30"=>"$siteurl/include/templates/images/dot.png","playlist_url"=>"$MODULE[link]&act=profile"];
}
if($_GET["act"]=="settings"){
	if($logged&&$userinfo["role"]=="10"){
		$readSets=getPluginMetaKey("settings");
		if($_SERVER["REQUEST_METHOD"]=="POST"){
			$sets["allowwrite"]=$_POST["allowwrite"];
			$sets["extrarole"]=$_POST["extrarole"];
			$sets["extradays"]=$_POST["extradays"];
			if(savePluginMetaKey("settings",json_encode($sets),$readSets[0]["id"])) $div.="<b>Настройки модуля сохранены</b><br>";
			else $div.="<b>Ошибка сохранения</b><br>";
			$div.="Перейти в <a href='/?do=/admin&act=plugin'>Плагины и модули</a><hr><br>";
			$MODULE=getInfoModule($_GET["bid"]); 
		}
		$div.="Настройки модуля авторизации!<br>";
		$div.="<form id='formxml' action='' method='POST'>";
					
		$div.="<input type='hidden' name='allowwrite' value='0' /> <input type='checkbox' name='allowwrite'".($MODULE["settings"]["allowwrite"]?" checked":"")." value='1' /> Разрешить регистрацию на сайте<br>
		Роль после регистрации:  <br><b>Пользователь</b><br>
		Дополнительная роль после регистрации: <br>";
		get_role();
		$c="";
		foreach($ROLE as $k=>$v){
			if($k>4&&$k<9)
				$c.="<option value='$k'".(($MODULE["settings"]["extrarole"]==$k)?" selected":"").">$v</option>";
		}
		$div.="<select name='extrarole' style=''>
		<option value=''>Нет.</option>
		$c 
		</select> На дней: (0-навсегда) <input type='number' name='extradays' style='width:50px;' value='".(int)$MODULE["settings"]["extradays"]."' /><br>
		";
		
		$div.="<br><input type='submit' value='Сохранить' />
		</form><br>";

	}
	else $div.="Ошибка, вы не авторизованы или у вас недостаточно прав для изменения настроек модуля авторизации!<br>";
}
if($_GET["act"]=="profile"){
	if($logged){
		if($_GET["cmd"]=="change"){
			$re=[];
			for($i=0;$i<3;$i++){
				$m=strtolower($_GET["set_box_mac$i"]);
				if(empty($m)) continue;
				if(preg_match("/[0-9a-z]{12,24}/i",$m)){
					$qq=q("select * from {$dbprefix}users where mac like '%".mysql_real_escape_string("$m")."%' and id<>$userinfo[id]",1);
					
					//$qd=q("select * from {$dbprefix}device where mac = '".mysql_real_escape_string("$m")."'",1);
					if(!empty($qq["id"])) $err.="<br><span style='color:red;'>box_mac $i $m уже есть в другом аккаунте (".preg_replace("/(...).*(...)$/","$1*$2",$qq["email"]).")!</span><br>";
					else  {
						$err.="<br><span style='color:green;'>box_mac $i $m сохранен!</span><br>";
						$re[]=$m;
					}
				}
				else $err.="<br><span style='color:red;'>box_mac $m должен быть из 12-16 символов 0-9a-z!</span><br>";
			}
			$userinfo["mac"]=implode("|",$re);
			mysql_query("update {$dbprefix}users set mac='".mysql_real_escape_string(implode("|",$re))."' where id='".mysql_real_escape_string("$userinfo[id]")."'");
			if(strlen($_GET["password"])<6) $err.="<br><span style='color:green;'>Пароль оставлен без изменений</span><br>";
			elseif($_GET["password"]!=$_GET["password2"]) $err.="<br><span style='color:red;'>Пароли различаются!</span><br>";
			else{
				mysql_query("update {$dbprefix}users set password='".mysql_real_escape_string(md5($secret.$_GET["password"]))."' where id='".mysql_real_escape_string("$userinfo[id]")."'");
				$Channels[]=["location"=>1,"title"=>"Новый пароль задан! Нажмите для переадресации...","playlist_url"=>"$MODULE[link]&act=profile&text=".urlencode("Новый пароль задан!")];	
			}
			if(empty($err)) $Channels[]=["location"=>1,"title"=>"Нажмите для переадресации...","playlist_url"=>"$MODULE[link]&act=profile"];
		}
			if($_GET["cmd"]=="close"){
				if($_GET["lid"]=="all") mysql_query("delete from {$dbprefix}sessions where userid='".mysql_real_escape_string("$userinfo[id]")."' and sid<>'".mysql_real_escape_string("$userinfo[sid]")."'");
				else mysql_query("delete from {$dbprefix}sessions where userid='".mysql_real_escape_string("$userinfo[id]")."' and id='".mysql_real_escape_string("$_GET[lid]")."'");
				$div.="Сессия закрыта!";
			}
			if($_GET["cmd"]=="delid"){
				mysql_query("update {$dbprefix}users set hash='',forkplayerid='' where email='".mysql_real_escape_string("$userinfo[email]")."'");
				$userinfo["hash"]="";
			}
			$Channels[]=["title"=>"Мой логин: $userinfo[login]","playlist_url"=>"","description"=>""];
			$Channels[]=["title"=>"Мой email: $userinfo[email]","playlist_url"=>"","description"=>""];
			$Channels[]=["title"=>"Доступные мне роли сайта: ".implode(",",getArrayAvailableUserRoles()),"playlist_url"=>"","description"=>""];
			$Channels[]=["title"=>"Мои ссылки","description"=>"Настроить отображение / Добавить свои ссылки","playlist_url"=>"$MODULE[link]&act=mylinks"];
			
			$macs=explode("|",$userinfo["mac"]);
			$add="";
			for($i=0;$i<3;$i++){
				$v=$macs[$i];
				$Channels[]=["title"=>"Мой box_mac","value"=>"$v","playlist_url"=>"payd_box_mac$i","description"=>"Доступ к этому сайту по мак адресу ".($i+1)."<br>","search_on"=>"Введите box_mac"];
				$add.="&set_box_mac$i=payd_box_mac$i";
				if($i>2||empty($v)) break;
			}
			
			$Channels[]=["title"=>"Задать новый пароль","description"=>"минимум 6 символов, оставьте пустым чтоб не менять ","type"=>"password","playlist_url"=>"payd_password","search_on"=>"Введите пароль"];
			$Channels[]=["title"=>"Подтвердите новый пароль:","type"=>"password","playlist_url"=>"payd_password2","description"=>"","search_on"=>"Введите повторно пароль"];		
			$Channels[]=["logo_30x30"=>"$siteurl/include/templates/images/submit.png","title"=>"Сохранить изменения","playlist_url"=>"$MODULE[link]&act=profile&cmd=change&password=payd_password&password2=payd_password2$add","description"=>""];
			
			
			
			
			
			if($userinfo["hash"]!=""){
				$Channels[]=["logo_30x30"=>"$siteurl/include/templates/images/fid.png","title"=>"Отвязать ForkPlayer ID $userinfo[forkplayerid] от аккаунта $userinfo[login]","playlist_url"=>"$MODULE[link]&act=profile&cmd=delid","description"=>""];
			}
			else $Channels[]=["logo_30x30"=>"$siteurl/include/templates/images/fid.png","title"=>"Привязать ForkPlayer ID","playlist_url"=>"http://forkplayer.tv/xml/account.php?do=ID&redirect_uri=".urlencode($MODULE[link]."&act=forkplayerid&cmd=add"),"description"=>""];
			
			$Channels[]=["logo_30x30"=>"$siteurl/include/templates/images/dot.png","title"=>"Выйти из всех авторизаций кроме текущей","playlist_url"=>"$MODULE[link]&act=profile&cmd=close&lid=all","description"=>"Закрыть все сессии, оставить только текущую"];
			
			$q=q("select * from {$dbprefix}sessions where userid='".mysql_real_escape_string("$userinfo[id]")."' order by time desc");
			for($i=0;$i<count($q);$i++){
				$Channels[]=["logo_30x30"=>"$siteurl/include/templates/images/dot.png","title"=>date("d.m.Y H:i",$q[$i]["time"])." ".$q[$i]["ip"]." Выйти?","playlist_url"=>"$MODULE[link]&act=profile&cmd=close&lid=".$q[$i]["id"],"description"=>"Последний вход на сайт ".date("d.m.Y H:i",$q[$i]["time"])."<br>IP: ".$q[$i]["ip"]."<br>User-agent: ".$q[$i]["ua"]."<br>Mac: ".$q[$i]["mac"]];
			}
		
	}
	else $div.="Ошибка, вы не авторизованы!<br>";
}

if($_GET["act"]=="submit"){
	$div.=auth_user($_GET["login"],$_GET["password"]);
}
if($_GET["act"]=="forkplayerid"){
	$info=json_decode(base64_decode($_GET["code"]),true);
	if(!empty($info["email"])){
		if($logged){
			mysql_query("update {$dbprefix}users set hash='', forkplayerid='' where forkplayerid='".mysql_real_escape_string("$info[email]")."'");
			$q=q("select * from {$dbprefix}users where email='".mysql_real_escape_string("$info[email]")."'",1);
			
			if(isset($q[0])&&$q["id"]!=$userinfo["id"]) $err.="<br>Email <span style='color:red;'>$info[email]</span> зарегистрирован на другом аккаунте, выйдите с этого аккаунта и повторите попытку<br>";
			else{
				mysql_query("update {$dbprefix}users set hash='".mysql_real_escape_string("$info[hash]")."', forkplayerid='".mysql_real_escape_string("$info[email]")."' where id='".mysql_real_escape_string($userinfo["id"])."'");
				$div.="Привязка ForkPlayer ID <span style='color:red;'>$info[email]</span> прошла успешно!<br>";
			}
			//$_PL["cmd"]="reload();";
		}
		else{
			$q=q("select * from {$dbprefix}users where email='".mysql_real_escape_string("$info[email]")."' or forkplayerid='".mysql_real_escape_string("$info[email]")."'",1);
			if(isset($q[0])&&$q["hash"]==$info["hash"]&&strlen($info["hash"])==32){
				auth_user_hash($info["email"],$info["hash"]);
			}
			elseif(isset($q[0])&&$q["forkplayerid"]==$info["email"]){
				mysql_query("update {$dbprefix}users set hash='', forkplayerid='' where id='$q[id]'");
				$err.="<br>ForkPlayer ID <span style='color:red;'>$info[email]</span> был отвязан от $q[email]!<br>";
			}
			elseif(isset($q[0])&&$q["email"]==$info["email"]&&preg_match("/".substr($info["email"],0,strpos($info["email"],"@"))."\d{6}/",$q["login"])){
				mysql_query("update {$dbprefix}users set hash='".mysql_real_escape_string("$info[hash]")."', forkplayerid='".mysql_real_escape_string("$info[email]")."' where id='$q[id]'");
				$div.="<br>ForkPlayer ID <span style='color:red;'>$info[email]</span> был заново привязан к вашему аккаунту!<br>";
				auth_user_hash($info["email"],$info["hash"]);
			}
			elseif(isset($q[0])) $err.="<br>Email <span style='color:red;'>$info[email]</span> уже зарегистрирован на этом сайте! Чтобы привязать ваш ForkPlayer ID авторизуйтесь, войдите в профиль и нажмите привязать ForkPlayer ID<br>";
			elseif($AllowWrite){
				$usrlogin=substr($info["email"],0,strpos($info["email"],"@")).rand(100000,999999);
				$usrpassw=randtext(4).rand(100000,999999);
				if($MODULE["settings"]["extrarole"]>4&&$MODULE["settings"]["extrarole"]<9) {
					$exr=$MODULE["settings"]["extrarole"];
					if((int)$MODULE["settings"]["extradays"]<1) $exd=0;
					else $exd=time()+$MODULE["settings"]["extradays"]*24*3600;
				}
				else {
					$exr=null;
					$exd=null;
				}
				if(register_user($usrlogin,$info["email"],$usrpassw,$initial[1],$exr,$exd)==1){
					mysql_query("update {$dbprefix}users set hash='".mysql_real_escape_string("$info[hash]")."', forkplayerid='".mysql_real_escape_string("$info[email]")."' where email='".mysql_real_escape_string($info["email"])."'");
					$div.="Регистрация прошла успешно!<br>";
					auth_user($usrlogin,$usrpassw);
				}
				else $div.="Ошибка записи в базу данных!<br>";
			}
			else $div.="Регистрация отключена на этом сайте<br>";
		}
	}
	else $err.="<br><span style='color:red;'>Неверные переданные данные!</span><br>"; 
}
if($_GET["act"]=="register"&&!$logged){
	if($AllowWrite){
		$Channels[]=["title"=>"Логин:","playlist_url"=>"payd_login","description"=>"","search_on"=>"Придумайте логин"];
		$Channels[]=["title"=>"Email:","type"=>"email","playlist_url"=>"payd_email","description"=>"","search_on"=>"Почта"];
		$Channels[]=["title"=>"Придумайте пароль (минимум 6 символов)","type"=>"password","playlist_url"=>"payd_password","description"=>"","search_on"=>"Введите пароль"];
		$Channels[]=["title"=>"Введите еще раз пароль:","type"=>"password","playlist_url"=>"payd_password2","description"=>"","search_on"=>"Введите пароль"];		
		$Channels[]=["title"=>"$text_randomize","type"=>"number","playlist_url"=>"payd_key","description"=>"$text_randomize","search_on"=>"Введите число цифрами"];
		$Channels[]=["title"=>"Зарегистрироваться","playlist_url"=>"$MODULE[link]&act=submitregister&hash=$hash_randomize&login=payd_login&email=payd_email&password=payd_password&password2=payd_password2&key=payd_key","description"=>""];
	}
	else $div.="Регистрация отключена на этом сайте<br>";
		
}
if($_GET["act"]=="submitregister"){
	if(md5($secret.$_GET["key"])!=$_GET["hash"]) $err.="<br><span style='color:red;'>Введенные проверочные цифры неверные!</span><br>";
	elseif(!preg_match("/^[a-z0-9_]+$/i",$_GET["login"])||strlen($_GET["login"])<4)  $err.="<br><span style='color:red;'>Логин $_GET[login] должен состоять только из латинницы и цифр и не быть короче 4 символов!</span><br>";
	else if(!filter_var($_GET["email"], FILTER_VALIDATE_EMAIL))  $err.="<br><span style='color:red;'>Email $_GET[email] не верный!</span><br>";
	elseif(strlen($_GET["password"])<6) $err.="<br><span style='color:red;'>Пароль меньше 6 символов!</span><br>";
	elseif($_GET["password"]!=$_GET["password2"]) $err.="<br><span style='color:red;'>Пароли различаются!</span><br>";
	else{		
		$q=q("select * from {$dbprefix}users where login='".mysql_real_escape_string($_GET["login"])."'",1);	
		if(isset($q[0])) $err.="<br><span style='color:red;'>Логин $_GET[login] уже зарегистрирован на сайте!</span><br>";
		
		$q=q("select * from {$dbprefix}users where login='".mysql_real_escape_string($_GET["email"])."'",1);
		if(isset($q[0])) $err.="<br><span style='color:red;'>Email $_GET[email] уже зарегистрирован на сайте!</span><br>";
		
		if($err=="") {
			if($MODULE["settings"]["extrarole"]>4&&$MODULE["settings"]["extrarole"]<9) {
				$exr=$MODULE["settings"]["extrarole"];
				if((int)$MODULE["settings"]["extradays"]<1) $exd=0;
				else $exd=time()+$MODULE["settings"]["extradays"]*24*3600;
			}
			else {
				$exr=null;
				$exd=null;
			}
			
			if(register_user($_GET["login"],$_GET["email"],$_GET["password"],$_GET["mac"],$exr,$exd)==1){
				$div.="Регистрация прошла успешно!<br>";
				auth_user($_GET["login"],$_GET["password"]);
			}
			else $div.="Ошибка записи в базу данных!<br>";
		}
	}
}

if($_GET["act"]=="login"){
	if(!$logged) {
		if($AllowRead){
			$Channels[]=["title"=>"Логин:","playlist_url"=>"payd_login","description"=>"","search_on"=>"Введите логин"];
			$Channels[]=["title"=>"Пароль:","type"=>"password","playlist_url"=>"payd_password","description"=>"","search_on"=>"Введите пароль"];
			$Channels[]=["title"=>"Войти","playlist_url"=>"$MODULE[link]&act=submit&login=payd_login&password=payd_password","description"=>""];
			$Channels[]=["title"=>"Вход/Регистр. через ForkPlayer ID","playlist_url"=>"http://forkplayer.tv/xml/account.php?do=ID&redirect_uri=".urlencode($MODULE[link]."&act=forkplayerid"),"description"=>""];
			$Channels[]=["title"=>"Забыли пароль","playlist_url"=>"$MODULE[link]&act=remind","description"=>""];
			$Channels[]=["title"=>"Регистрация","playlist_url"=>"$MODULE[link]&act=register","description"=>""];		
		}
		else $div.="Авторизация отключена на этом сайте<br>";	
	}
}
if(!empty($err)) $_PL["error"]=$err;
if(!empty($div)) $_PL["notify"]=$div;