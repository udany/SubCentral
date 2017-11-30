<?php
##//////////////////////////////////////////////////////////////////
#//                           DIRECTORIES                       ///#
//////////////////////////////////////////////////////////////////##
function GetParamsString($params){
	if (is_array($params)){
		$str = [];
		foreach($params as $k=>$v){
			$str[] = urlencode($k)."=".urlencode($v);
		}
		$params = implode("&", $str);
	}
	if (strlen($params) && $params[0]!="?"){
		$params = "?".$params;
	}
	return $params;
}
function GetProjectDirectory($project=''){
    return DIR_PROJECTS.'/'.($project ? $project : ServerSettings::$Project).'/';
}
$_settingsCache = [];

function GetProjectUrl($page="", $params="", $useThisProject=""){
	$useThisProject = $useThisProject ? $useThisProject : ServerSettings::$Project;

	if (ServerSettings::$Project === $useThisProject){
		$settings = ServerSettings::$current;
	}else{
		if (!isset($_settingsCache[$useThisProject])){
			$settings = new ServerSettings();
			$settings->Load();
			$settings->LoadEnvironment();
			$settings->LoadProject($useThisProject);
			$settings->LoadProject($useThisProject, ServerSettings::$Environment);
			$_settingsCache[$useThisProject] = $settings;
		}else{
			$settings = $_settingsCache[$useThisProject];
		}
	}

	if ($page == ServerSettings::GetCurrent('DefaultPage')) $page = "";


	$baseURL = '';

	if ($settings->Get('ProjectUrl')){
		$baseURL = $settings->Get('ProjectUrl');
	}else {
		if ($useThisProject == $settings->Get('DefaultProject')) {
			$baseURL = SITE_URL;
		} else {
			$baseURL = SITE_URL . GetProjectDirectory($useThisProject);
		}
	}

	$params = GetParamsString($params);
    return $baseURL.($page ? $page."/" : "").$params;
}

function GetDynamicDirectory($p=''){
    return GetProjectDirectory($p).DIR_DYNAMIC.'/';
}

function GetDynamicUrl($useThisProject=""){
    return GetProjectUrl('', '', $useThisProject).DIR_DYNAMIC.'/';
}

function GetActionUrl($Controller, $Action="", $params = "", $useThisProject=""){
	$params = GetParamsString($params);
    return GetProjectUrl('', '', $useThisProject).'do/'.$Controller.".".($Action ? $Action : $Controller)."/".$params;
}

function View($view, $viewBag=[]){
	$file = GetProjectDirectory()."pages/".$view.".php";
	$pre = GetProjectDirectory().'pages/pre/'.$view.'.php';

	if (file_exists($file)){
		if (file_exists($pre)){
			include($pre);
		}
		IncludeViewCss($view);
		IncludeViewJs($view);
		include($file);
	}
}

function IncludeViewCss($view){
	$path = "pages/css/".$view;
	IncludeFiles($path, 'css', '<link rel="stylesheet" type="text/css" href="{0}"/>'."\n");
}

function IncludeViewJs($view){
	$path = "pages/scripts/".$view;
	IncludeFiles($path, 'js', '<script type="text/javascript" src="{0}"></script>'."\n");
}

function IncludeFiles($path, $extension, $includeText){
	$projectDir = GetProjectDirectory();
	$projectUrl = GetProjectUrl();
	$path = $projectDir.$path;

	$files = glob($path.".*.".$extension);
	if (file_exists($path.".".$extension)) array_push($files, $path.".".$extension);

	foreach($files as $file){
		$file = str_ireplace($projectDir, '', $file);
		$file = $projectUrl.$file;
		echo DNAParser::getInstance()->Format($includeText, [$file])."\n";
	}
}