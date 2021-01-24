<?php
if(!defined('XMLCMS')) exit();
include_once dirname(__FILE__)."/header.php";
$divpc="";
$MyRoles=getArrayAvailableUserRoles();
$AllowRead=is_access($MyRoles,explode(",",$PLUGIN["settings"]["allowread"]));
$AllowWrite=is_access($MyRoles,explode(",",$PLUGIN["settings"]["allowwrite"]));
$AllowUpload=is_access($MyRoles,explode(",",$PLUGIN["settings"]["allowupload"]));
$AllowDelete=is_access($MyRoles,explode(",",$PLUGIN["settings"]["allowdelete"]));
$divpc.="$PLUGIN[title]<br>";
if($AllowRead){ // Открываем плагин только тем кому разрешено чтение
	if(!isset($_GET["cmd"])){ // Главная страница плагина - парсера
		$Channels[]=["title"=>"Поиск приколов","search_on"=>"Введите текст для поиска", "playlist_url"=>"$PLUGIN[link]&cmd=search"];
		$Channels[]=["title"=>"Все видео приколы","playlist_url"=>"$PLUGIN[link]&cmd=parsepage&link=".urlencode("/lenta/video/?show=0")];
		$Channels[]=["title"=>"Отобранные видео приколы","playlist_url"=>"$PLUGIN[link]&cmd=parsepage&link=".urlencode("/lenta/video/?show=1")];	
	}
	elseif($_GET["cmd"]=="search"){
		if($_GET["page"]>1) {
			$getURL="https://prikol.i.ua/search/video/?p=$_GET[page]&query=".urlencode($_GET["search"]);
			$nextPage=++$_GET["page"];
		}
		else {
			$getURL="https://prikol.i.ua/search/video/?query=".urlencode($_GET["search"]);
			$nextPage=2;
		}
		$pagesrc=file_get_contents($getURL);
		preg_match_all("/<h2><a href=\"(.*?)\">(.*?)<.*?<video .*?src=\"(.*?)\"/",$pagesrc,$ResArr); // Регулярным выражением выбираем нужные строки с заголовком видео и ссылкой на него. Подобрать регулярное выражение можно на сайте https://regex101.com/
		for($i=0;$i<count($ResArr[0]);$i++){
			$Channels[]=["title"=>$ResArr[2][$i],"stream_url"=>fEncrypt("https:".$ResArr[3][$i])];
		}
		if(count($Channels)>6){
			$_PL["next_page_url"]="$PLUGIN[link]&cmd=$_GET[cmd]&search=".urlencode("$_GET[search]")."&page=$nextPage";
			$Channels[]=["title"=>"Еще результаты","playlist_url"=>$_PL["next_page_url"]];
		}		
	}
	elseif($_GET["cmd"]=="parsepage"){ // Парсим страницу сайта и отдаем ее в массив $Channels[]
		if($_GET["page"]>1) {
			$getURL="https://prikol.i.ua$_GET[link]&p=$_GET[page]";
			$nextPage=++$_GET["page"];
		}
		else {
			$getURL="https://prikol.i.ua$_GET[link]";
			$nextPage=2;
		}
		$pagesrc=file_get_contents($getURL);
		preg_match_all("/<h2><a href=\"(.*?)\">(.*?)<.*?<video .*?src=\"(.*?)\"/",$pagesrc,$ResArr); // Регулярным выражением выбираем нужные строки с заголовком видео и ссылкой на него. Подобрать регулярное выражение можно на сайте https://regex101.com/
		for($i=0;$i<count($ResArr[0]);$i++){
			$Channels[]=["title"=>$ResArr[2][$i],"stream_url"=>fEncrypt("https:".$ResArr[3][$i])];
		}
		$_PL["next_page_url"]="$PLUGIN[link]&cmd=$_GET[cmd]&link=".urlencode("$_GET[link]")."&page=$nextPage";
		$Channels[]=["title"=>"Следующая страница","playlist_url"=>$_PL["next_page_url"]];		
	}

	if(count($Channels)<1) {
		$div.="Ссылок нет.<br>";
		$Channels[]=["title"=>"Ссылок нет."];
	}
	
}
else $div.="У вас недостаточно прав для просмотра этой страницы $PLUGIN[name]! Авторизуйтесь или обратитесь к администратору сайта!<br>";
