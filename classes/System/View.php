<?php

class View {
	private static $globalViewBag = [];

	static function SetGlobal($key, &$value){
		self::$globalViewBag[$key] = $value;
	}
	static function SetGlobalVal($key, $value){
		self::$globalViewBag[$key] = $value;
	}
	static function &GetGlobal($key){
		return isset(self::$globalViewBag[$key]) ? self::$globalViewBag[$key] : null;
	}

	/**
	 * Strips the path from the view and returns it
	 *
	 * @param $view
	 *
	 * @return array|string
	 */
	static function ViewPath(&$view){
	    if (!$view) $view = "";
		if (is_string($view)) $view = explode('-', $view);
		$path = array_splice($view, 0, count($view)-1);
		$path = "pages/".implode('/', $path).(count($path) ? '/' : '');

		$view = $view[0];

		return $path;
	}

	static function Title($view, $project=''){
		$path = self::ViewPath($view);

		$titleFile = GetProjectDirectory($project).$path.$view.'.title.php';

		if (file_exists($titleFile)) {
			include($titleFile);
		}else if (self::GetGlobal('pageTitle')){
			echo self::GetGlobal('pageTitle');
		}
	}

	static function Config($view, $viewBag=[], $project=''){
		$path = self::ViewPath($view);

		foreach ($viewBag as $key => &$val) {
			if (!is_int($key))
				$$key = $val;
		}
		foreach (self::$globalViewBag as $key => &$val) {
			if (!is_int($key))
				$$key = $val;
		}

		$conf = GetProjectDirectory($project).$path.$view.'.conf.php';

		if (file_exists($conf)) {
			include($conf);
		}
	}

	static function Load($view, $viewBag=[], $project=''){
		$path = self::ViewPath($view);

		$file = GetProjectDirectory($project).$path.$view.'.php';
		$pre = GetProjectDirectory($project).$path.$view.'.pre.php';
		if (file_exists($file)) {
			foreach ($viewBag as $key => &$val) {
				if (!is_int($key))
					$$key = $val;
			}
			foreach (self::$globalViewBag as $key => &$val) {
				if (!is_int($key))
					$$key = $val;
			}

			if (file_exists($pre)) {
				include( $pre );
			}

			self::IncludeFiles($path.$view, 'css', '<link rel="stylesheet" type="text/css" href="{0}"/>'."\n");
			self::IncludeFiles($path.$view, 'js', '<script type="text/javascript" src="{0}"></script>'."\n");

			include($file);
		}
	}

	static function IncludeFiles($path, $extension, $includeText, $project=''){
		$projectDir = GetProjectDirectory($project);
		$projectUrl = GetProjectUrl('', '', $project);
		$path = $projectDir.$path;

		$files = glob($path.".*.".$extension);
		if (file_exists($path.".".$extension)) array_push($files, $path.".".$extension);

		foreach($files as $file){
			$mtime = filemtime($file);
			$file = str_ireplace($projectDir, '', $file);
			$file = $projectUrl.$file;
			echo DNAParser::getInstance()->Format($includeText, [$file.'?='.$mtime])."\n";
		}
	}
}