<?php
class Controller {
	public static function CallFromRequest(){
		global $jsonPretty;

		if(isset($_GET["action"]) && $_GET["action"]) {
			$temp = explode('.', $_GET["action"]);
			if (count($temp) == 2){
				$controller = $temp[0];
				$method = $temp[1];
			}else{
				$controller = $_GET["action"];
				$method = $_GET["action"];
			}

			ServerSettings::Variable('Controller', $controller);
			ServerSettings::Variable('Method', $method);

			$result = self::Call($controller, $method);

			print_r(json_encode($result, $jsonPretty ? JSON_PRETTY_PRINT : 0));
		}
	}
	
	public static function Call($controller, $method, $project=null){
		if (!$project) $project = ServerSettings::$Project;

		if (file_exists(GetProjectDirectory($project)."actions/".$controller.".php")){
			// Try the project folder
			include_once(GetProjectDirectory($project)."actions/".$controller.".php");
		}else if (file_exists(GetProjectDirectory('_shared')."actions/".$controller.".php")){
			// Try the shared project files
			include_once(GetProjectDirectory('_shared')."actions/".$controller.".php");
		}else if (file_exists("actions/".$controller.".php")){
			// Try the global controllers
			include_once("actions/".$controller.".php");
		}else{
			throw new Exception("Couldn't locate the controller requested (". $controller .").", 403);
		}

		if (!function_exists($method)){
			throw new Exception("Couldn't locate the action requested (". $method .").", 403);
		}

		return $method();
	}
}