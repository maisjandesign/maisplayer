<?php
if(!defined('XMLCMS')) exit();
//Вывод в переменную $echo

include_once dirname(__FILE__)."/header.php";
$echo="";
if($_GET["cmd"]=="setdefault"){
	$readSets=getPluginMetaKey("settings");
	if(deletePluginMetaKey("settings",$readSets[0]["id"])) $echo.="<b>Настройки плагина устновлены с version.xml</b><br>";
	else $echo.="<b>Ошибка стирания настроек с базы данных</b><br>";
}
if($_SERVER["REQUEST_METHOD"]=="POST"){
	$sets=$_POST;
	
	$sets["allowread"]=implode(",",$_POST["allowread"]);
	$sets["allowwrite"]=implode(",",$_POST["allowwrite"]);
	$sets["allowupload"]=implode(",",$_POST["allowupload"]);
	$sets["allowdelete"]=implode(",",$_POST["allowdelete"]);
	$sets["pl"]=$_POST["pl"];
	$readSets=getPluginMetaKey("settings");
	if(savePluginMetaKey("settings",json_encode($sets),$readSets[0]["id"])) $echo.="<b>Настройки плагина сохранены</b><br>";
	else $echo.="<b>Ошибка сохранения</b><br>";
	$echo.="<a href='/?do=/admin&act=plugin'>Плагины</a><hr>";
	$PLUGIN=getInfoPlugin($_GET["bid"]); 
}

	get_role();
	$echo.="<a href='/?do=/admin&act=plugin&bid=$PLUGIN[id]&cmd=setdefault'>Стереть настройки и использовать установки с version.xml</a><br><br>";
	
	$echo.="<form id='formxml' action='' method='POST'>";
	
	$d=scandir(dirname(__FILE__).'/..');
	
	$echo.="<hr>Дополнительно искать в плагинах:<br>";
	foreach($d as $k=>$v){
		if(is_dir(dirname(__FILE__)."/../$v")&&$v!="."&&$v!=".."&&$v!="advert"&&$v!="chatbox"&&$v!="search"&&$v!="userlink"){
			$inf=getInfoPlugin($v);
			if(empty($PLUGIN["settings"]["pl"]["$v"]["url"])) $PLUGIN["settings"]["pl"]["$v"]["url"]="$inf[link]&cmd=search&search=%s";
			$echo.="<div style='border:1px solid gray; padding:10px; margin:10px;'> <input type='checkbox' name='pl[$v][enabled]'".(isset($inf["enabled"])&&$PLUGIN["settings"]["pl"]["$v"]["enabled"]?" checked":"")." value='1' title='$inf[description]' /> <a href='/?do=/plugin&bid=$v'>$v</a> $inf[name] $inf[version]<br><i>$inf[description]</i><br>
			URL поиска (%s вместо запроса) <input name='pl[$v][url]' value='".$PLUGIN["settings"]["pl"]["$v"]["url"]."' style='width:60%' />
			</div>";
		}
	}
	
	$echo.="<input type='hidden' name='showonmenu' value='0' /> <input type='checkbox' name='showonmenu'".($PLUGIN["settings"]["showonmenu"]?" checked":"")." value='1' /> Отобразить ссылку $PLUGIN[name] в Меню<br>";
	$echo.="<input type='hidden' name='showonmain' value='0' /> <input type='checkbox' name='showonmain'".($PLUGIN["settings"]["showonmain"]?" checked":"")." value='1' /> Отобразить ссылку $PLUGIN[name] на Главной странице<br>
	<b>Просмотр плагина</b> (Гость - все неавторизованные, Пользователь - все авторизованные)<br>";
	$c="";
	foreach($ROLE as $k=>$v){
		$c.="<option value='$k'".(is_access([$k],explode(",",$PLUGIN["settings"]["allowread"]))?" selected":"").">$v</option>";
	}
	$echo.="
	<select multiple name='allowread[]'style='height:180px;' title='Удерживайте ctrl чтобы отметить несколько!'>
	<option value='0'".(is_access([0],explode(",",$PLUGIN["settings"]["allowread"]))?" selected":"").">Всем кроме заблокированных</option>
	$c
	</select><br>
	<b>Добавление ссылок</b><br>";
	$c="";
	foreach($ROLE as $k=>$v){
		$c.="<option value='$k'".(is_access([$k],explode(",",$PLUGIN["settings"]["allowwrite"]))?" selected":"").">$v</option>";
	}
	$echo.="
	<select multiple name='allowwrite[]'style='height:180px;' title='Удерживайте ctrl чтобы отметить несколько!'>
	<option value='0'".(is_access([0],explode(",",$PLUGIN["settings"]["allowwrite"]))?" selected":"").">Всем кроме заблокированных</option>
	$c
	</select><br>
	<b>Загрузка файлов плейлистов и иконок</b><br>";
	$c="";
	foreach($ROLE as $k=>$v){
		$c.="<option value='$k'".(is_access([$k],explode(",",$PLUGIN["settings"]["allowupload"]))?" selected":"").">$v</option>";
	}
	$echo.="
	<select multiple name='allowupload[]'style='height:180px;' title='Удерживайте ctrl чтобы отметить несколько!'>
	<option value='0'".(is_access([0],explode(",",$PLUGIN["settings"]["allowupload"]))?" selected":"").">Всем кроме заблокированных</option>
	$c
	</select><br>
	<b>Удаление ссылок</b><br>";
	$c="";
	foreach($ROLE as $k=>$v){
		$c.="<option value='$k'".(is_access([$k],explode(",",$PLUGIN["settings"]["allowdelete"]))?" selected":"").">$v</option>";
	}
	$echo.="
	<select multiple name='allowdelete[]'style='height:180px;' title='Удерживайте ctrl чтобы отметить несколько!'>
	<option value='0'".(is_access([0],explode(",",$PLUGIN["settings"]["allowdelete"]))?" selected":"").">Всем кроме заблокированных</option>
	$c
	</select>";
				
					
	$echo.="<br><input type='submit' value='Сохранить' />
	</form><br>";
