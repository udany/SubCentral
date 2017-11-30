<?php
function Export(){
    global $project;
    Database::getInstance()->Export($project."_db".time().".sql", '', '/var/www/html/');
}

function Project(){
    global $project;
	$projectToExport = isset($_GET['project']) ? $_GET['project'] : $project;

    $path = "temp_".time()."/";

    mkdir($path);

    FileSystem::Copy("actions/", $path."actions/");
    FileSystem::Copy("classes/", $path."classes/");
    FileSystem::Copy("facebook/", $path."facebook/");
    FileSystem::Copy("static/", $path."static/");

    mkdir($path."projects/");
    FileSystem::Copy("projects/$projectToExport/", $path."projects/main/");

    FileSystem::Copy(".htaccess", $path.".htaccess");

    $files = glob("*.*");
    foreach($files as $file){
        FileSystem::Copy($file, $path.$file);
    }

    FileSystem::Zip($path, $projectToExport.'_'.time().".zip", $path);

    FileSystem::RemoveDir($path);

    die();
}

function UpdateHtaccess(){
	$domains = ServerSettings::GetCurrent('Domains');

	$htaccess = FileSystem::Read('.htaccess');
	$htaccess = explode("#AUTOGENERATED", $htaccess);

	$rules = [];
	$rules[] = '';
	foreach ($domains as $domain=>$project){
		$domain = implode('\.', explode('.', $domain));

		$rules[] = 'RewriteCond %{HTTP_HOST} ^(www\.)?'.$domain.'$';
		$rules[] = 'RewriteCond %{DOCUMENT_ROOT}/projects/'.$project.'%{REQUEST_URI}   -f';
		$rules[] = 'RewriteRule ^.+[^.php]$ projects/'.$project.'%{REQUEST_URI} [L]';
		$rules[] = '';
	}

	$htaccess[1] = implode("\n", $rules);

	$htaccess = implode("#AUTOGENERATED", $htaccess);

	FileSystem::Write('.htaccess', $htaccess);
}