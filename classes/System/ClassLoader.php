<?php
class ClassLoader{
	private static $map = false;
	public static $baseDir = '';
	private static $directories = [];

	public static function GenerateMap(){
		self::$map = [];

		foreach(self::$directories as $dir){
			self::SearchDir($dir);
		}
	}
	private static function SearchDir($dir){
		$results = glob($dir."*");

		foreach($results as $result){
			if (is_dir($result)){
				self::SearchDir($result."/");
			}else{
				if (strpos($result, '.php') !== false){
					$exploded = explode('/', $result);
					$fn = $exploded[count($exploded)-1];
					$className = str_ireplace('.php', '', $fn);

					if (isset(self::$map[$className])){
						throw new Exception('Ambiguity between class files "'.self::$map[$className].'" and "'.$result.'", make sure classes have an unique name.');
					}

					self::$map[$className] = $result;
				}
			}
		}
	}

	public static function SetDirectory($baseDir){
		self::$directories = [];
		self::$baseDir = $baseDir;
		self::IncludeDirectory($baseDir);
	}
	public static function IncludeProject($project){
		self::IncludeDirectory(GetProjectDirectory($project).self::$baseDir);
	}
	private static function IncludeDirectory($dir){
		$idx = array_search($dir, self::$directories);
		if ($idx===false){
			array_push(self::$directories, $dir);
		}
	}

	public static function Load($class){
		if (!self::$map) self::GenerateMap();
		if (isset(self::$map[$class])){
			require_once(self::$map[$class]);
		}
	}
	public static function Exists($class){
		return isset(self::$map[$class]);
	}
}