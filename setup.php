<?PHP
date_default_timezone_set("GMT");
ob_start();

##//////////////////////////////////////////////////////////////////
#//                       ERROR HANDLING                        ///#
//////////////////////////////////////////////////////////////////##
/**
 * @param Exception $exception
 */
function exception_handler($exception) {
	global $json;


	LogMessage("EXCEPTION ". $exception->getCode() .": ".$exception->getMessage(), null, true);

	if (!$json){
		if ($_GET['action'])
			echo '<link rel="stylesheet" type="text/css" href="style.css"/> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '<div class="error"><strong>EXCEPTION ', $exception->getCode(), '</strong><br />', $exception->getMessage(),'</div>';
	}else{
		$result['status'] = false;
		$result['code'] = $exception->getCode();
		$result['message'] = $exception->getMessage();

		echo json_encode($result);
	}
}
set_exception_handler('exception_handler');

// Set error handling
function error_handler($errorNumber, $errorMessage, $errorFile, $errorLine){
	global $json;

	if (!(error_reporting() & $errorNumber)) {
		// This error code is not included in error_reporting
		return;
	}

	switch ($errorNumber) {
		case E_USER_ERROR:
			$message = "Error [$errorNumber] $errorMessage. (Line $errorLine | File $errorFile)";
			break;

		case E_USER_WARNING:
			$message = "Warning [$errorNumber] $errorMessage. (Line $errorLine | File $errorFile)";
			break;

		case E_USER_NOTICE:
			$message = "Notice [$errorNumber] $errorMessage. (Line $errorLine | File $errorFile)";
			break;

		default:
			$message = "Unexpected Error [$errorNumber] $errorMessage. (Line $errorLine | File $errorFile)";
			break;
	}


	LogMessage("ERROR: ".$message, null, true);

	if (!$json){
		if ($_GET['action'])
			echo '<link rel="stylesheet" type="text/css" href="style.css"/> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '<div class="error"><strong>ERRO</strong><br />', $message,'</div>';
	}else{
		$result['status'] = false;
		$result['message'] = $message;

		echo json_encode($result);
		die();
	}

	return true;
}
set_error_handler("error_handler");


function fatal_handler() {
	$errfile = "unknown file";
	$errstr  = "shutdown";
	$errno   = E_CORE_ERROR;
	$errline = 0;

	$error = error_get_last();

	if( $error !== NULL) {
		$errorNumber   = $error["type"];
		$errorMessage  = $error["message"];
		$errorFile = $error["file"];
		$errorLine = $error["line"];

		$message = "Fatal Error [$errorNumber] $errorMessage. (Line $errorLine | File $errorFile)";

		LogMessage("ERROR: ".$message, null, true);
	}
}
register_shutdown_function("fatal_handler");


##//////////////////////////////////////////////////////////////////
#//                          SYSTEM                             ///#
//////////////////////////////////////////////////////////////////##
ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);
$environment = file_get_contents('env.txt');

// CONSTANTS
/// Filesystem
//// Files
define('LOG_FILE', 'log.txt');

//// Directories
define('DIR_PROJECTS', 'projects');
define('DIR_DYNAMIC', 'dynamic');

/// PARAMETERS
//// Global
define('PARAM_TIMESTAMP', 'timestamp');
define('PARAM_SESSION', 'session');
define('PARAM_SESSION_HASH', 'sessionHash');

require_once("functions.php");

require_once("classes/Util/JSONData.php");
require_once("classes/Util/ServerSettings.php");
require_once("classes/Singleton.php");
require_once("classes/Util/DNAParser.php");
require_once("classes/Util/Log.php");
require_once("classes/Util/ScriptDependency.php");
require_once("classes/Base/Model.php");

##//////////////////////////////////////////////////////////////////
#//                          SETTINGS                           ///#
//////////////////////////////////////////////////////////////////##
$settings = new ServerSettings();
$settings->Load();

if ($environment){
	$settings::$Environment = $environment;
	$settings->LoadEnvironment();
}
ServerSettings::$current = $settings;

##//////////////////////////////////////////////////////////////////
#//                           PROJECT                           ///#
//////////////////////////////////////////////////////////////////##
$httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
$domain = str_ireplace('www.', '', $httpHost);

$domains = $settings->Get('Domains');

if (isset($domains[$domain])){
	$project = $domains[$domain];
}else if(isset($_GET['project'])){
	$project = $_GET['project'];
}else{
	http_response_code(400);
	die('Domain not mapped');
}

ServerSettings::$Project = $project;

# Load project settings
$settings->LoadProject($project);
if (ServerSettings::$Environment) $settings->LoadProject($project, ServerSettings::$Environment);

# URL
if (isset($domains[$domain])){
	$settings->Set('ProjectUrl',
        ($settings->Get('https') ? 'https://' : 'http://').($settings->Get('www') ? 'www.' : '').$domain.'/');

	//print_r(($settings->Get('https') ? 'https://' : 'http://') . ($settings->Get('www') ? 'www.' : '') . $domain . '/');
	//die();
}

if ($settings->Get('forceWww')){
    if (strpos($httpHost, 'www')===false){
        $uri = $_SERVER['REQUEST_URI'];
        $uri = ($settings->Get('https') ? 'https://' : 'http://').'www.'.$httpHost.$uri;

        Redirect($uri);
    }
}


# Constants
if ($settings->Get('ProjectUrl')) {
	define('SITE_URL', $settings->Get('ProjectUrl'));
}else{
	define('SITE_URL', $settings->Get('SiteUrl'));
}

define('DEBUG_ENABLED', $settings->Get('Debug'));
define('DEBUGBT_ENABLED', $settings->Get('BackTrace'));
define('QUERY_DUMP_ENABLED', $settings->Get('QueryDump'));

define('MYSQL_BIN',$settings->Get('MySQLBinary'));

##//////////////////////////////////////////////////////////////////
#//                       CLASS AUTO LOAD                       ///#
//////////////////////////////////////////////////////////////////##
// Auto load classes
require_once("classes/System/ClassLoader.php");
ClassLoader::SetDirectory($settings->Get('ClassFolder'));
ClassLoader::IncludeProject($project);

spl_autoload_register(function ($class_name) {
	ClassLoader::Load($class_name);
});


##//////////////////////////////////////////////////////////////////
#//                         DATABASE                            ///#
//////////////////////////////////////////////////////////////////##
Database::$DefaultAddress = $settings->Get("DatabaseAddress");
Database::$DefaultUser = $settings->Get("DatabaseUser");
Database::$DefaultPassword = $settings->Get("DatabasePassword");
Database::$DefaultDatabase = $settings->Get("DatabaseName");
Database::$IgnoreTimezone = $settings->Get("DatabaseIgnoreTimezone") ? true : false;


##//////////////////////////////////////////////////////////////////
#//                           SESSION                           ///#
//////////////////////////////////////////////////////////////////##
session_name($project."PHPSESSION");
function SessionHash($id, $time){
    return hash("sha256", $id . "session" . $time);
}
if (isset($_POST[PARAM_SESSION])){
    $hash = SessionHash($_POST[PARAM_SESSION], $_POST[PARAM_TIMESTAMP]);

    if ($_POST[PARAM_SESSION_HASH] == $hash){
        session_id($_POST[PARAM_SESSION]);
        session_start();
    }
}else if (session_id() == ""){
    session_start();
    if (!isset($_SESSION['logged'])){
        $_SESSION['logged'] = 0;
    }
}


##//////////////////////////////////////////////////////////////////
#//                    GENERAL USE FUNCTIONS                    ///#
//////////////////////////////////////////////////////////////////##
function RequirePostFields(){
    $arg_list = func_get_args();
    for ($i = 0; $i < count($arg_list); $i++) {
        if (!isset($_POST[$arg_list[$i]])){
            throw new Exception("Post parameter " . $arg_list[$i] . " may not be unset");
        }
    }
}
function RequireFields(){
    $arg_list = func_get_args();
    for ($i = 0; $i < count($arg_list); $i++) {
        if (!isset($_REQUEST[$arg_list[$i]])){
            throw new Exception("Request parameter " . $arg_list[$i] . " may not be unset");
        }
    }
}
function RequireNonEmptyFields(){
    $arg_list = func_get_args();
    for ($i = 0; $i < count($arg_list); $i++) {
        if (!isset($_REQUEST[$arg_list[$i]])){
            throw new Exception("Request parameter " . $arg_list[$i] . " may not be unset");
        }
        if ($_REQUEST[$arg_list[$i]] === ''){
            throw new Exception("Request parameter " . $arg_list[$i] . " may not be empty");
        }
    }
}

function LogMessage($msg, $file = '', $stack=false){
    Log::Write($msg, $file, $stack);
}

function Redirect($target, $js = false){
    if (!$js){
        @ob_clean();
        if(strpos($target, "http") === false) {
            header("Location: " . SITE_URL . $target);
        } else {
            header("Location: $target");
        }
    }else{
        echo '<script type="text/javascript">window.location = "'.$target.'"; </script>';
    }
}

function defineIfNull($name, $val){
    if (!defined($name)){
        define($name, $val);
    }
}


##//////////////////////////////////////////////////////////////////
#//                           FACEBOOK                          ///#
//////////////////////////////////////////////////////////////////##
if($settings->Get('FacebookAppId')) {
    defineIfNull('FACEBOOK_APP_ID', $settings->Get('FacebookAppId'));
    defineIfNull('FACEBOOK_SECRET', $settings->Get('FacebookAppSecret'));
    defineIfNull('FACEBOOK_LOGIN_REDIRECT', 'do/Facebook.LoginRedirect');

    include('facebook/autoload.php');
    define('FACEBOOK_SDK_V4_SRC_DIR', 'facebook/src/Facebook/');

    $user = FacebookHandler::getInstance()->GetFacebookUser();
}


##//////////////////////////////////////////////////////////////////
#//                       PROJECT SETTINGS                      ///#
//////////////////////////////////////////////////////////////////##
ScriptDependency::Current()->LoadData('static/Dependency.json');

if (file_exists(GetProjectDirectory().'setup.php')){
    require_once(GetProjectDirectory().'setup.php');
}


##//////////////////////////////////////////////////////////////////
#//                            ROUTING                          ///#
//////////////////////////////////////////////////////////////////##
// Defines the current page
if (!isset($page)){
    if (!isset($_GET['p'])){
        $page = $settings->Get('DefaultPage');
    }else{
        $page = $_GET['p'];
    }
}

if (!$settings->Get("UsesViews")){
	//Null css/js
	$pageCss = '';
	$pageJs = '';
	// Checks for specific CSS and JS
	if (file_exists(GetProjectDirectory().'pages/css/'.$page.'.css')){
		$pageCss = GetProjectUrl().'pages/css/'.$page.'.css';
	}
	if (file_exists(GetProjectDirectory().'pages/scripts/'.$page.'.js')){
		$pageJs = GetProjectUrl().'pages/scripts/'.$page.'.js';
	}

	if (file_exists(GetProjectDirectory().'pages/pre/'.$page.'.php')){
		include(GetProjectDirectory().'pages/pre/'.$page.'.php');
	}
}



##//////////////////////////////////////////////////////////////////
#//                        BOOTSTRAPPING                        ///#
//////////////////////////////////////////////////////////////////##
// Making sure Log class is loaded
class_exists('Log');




##//////////////////////////////////////////////////////////////////
#//                         MIGRATIONS                          ///#
//////////////////////////////////////////////////////////////////##
// Runs any pending migrations
MigrationManager::Check('_shared');
MigrationManager::Check($project);