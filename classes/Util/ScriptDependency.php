<?php
class ScriptDependency {
	protected $includedScripts;
	protected $queuedScripts;
	protected $id;

	public $root;
	public $cssRoot;
	protected $dependencies = [];

	protected static $current;

	public static function Current(){
		if (!self::$current) self::$current = new ScriptDependency();
		return self::$current;
	}

	public function __construct() {
		$this->includedScripts = [];
		$this->queuedScripts   = [];
		$this->id = rand(0, 9999999);

		$this->root = '';
		$this->cssRoot = '';
		$this->dependencies = [];
	}
	
	public function LoadData($file){
		$json = new JSONData($file);
		$data = $json->GetData();

		if (isset($data['ScriptsRoot'])){
			$this->root = $data['ScriptsRoot'];
		}
		if (isset($data['CssRoot'])){
			$this->cssRoot = $data['CssRoot'];
		}

		foreach ($data['Dependencies'] as $k => $v){
			$this->dependencies[$k] = $v;
		}
	}
	
	
	public function SetRoot($root){
		$this->root = $root;
	}
	

	public function AddDependency($script, $dependencies){
		$this->dependencies[$script] = $dependencies;

		return $this;
	}
	public function AddDependencies($dependencyLis){
		foreach ($dependencyLis as $script=>$dependencies){
			$this->AddDependency($script, $dependencies);
		}

		return $this;
	}

	
	
	public function IsIncluded($script){
		return array_search($script, $this->includedScripts)!==false;
	}
	public function QueueIndex($script){
		return array_search($script, $this->queuedScripts);
	}
	public function IsQueued($script){
		return $this->QueueIndex($script)!==false;
	}
	public function Queue($script){
		if (!$this->IsQueued($script))
			array_push($this->queuedScripts, $script);
	}
	public function Dequeue($script){
		$idx = $this->QueueIndex($script);

		if ($idx!==false){
			array_splice($this->queuedScripts, $idx, 1);
		}
	}

	public function IncludeScript($script){
		if (func_num_args() > 1){
			$scripts = [];
			$args = func_get_args();
			$count = func_num_args();
			for ($i = 0; $i < $count; $i++){
				$scripts[] = $this->IncludeScript($args[$i]);
			}

			return implode("\n", $scripts);
		}
		
		$result = [];

		$start = str_split($script)[0];
		
		////// BUNDLES
		if ($start === "+") {
			$bundle = ScriptBundleManager::Current()->GetBundle($script);

			foreach ($bundle->Scripts as $bundleFile){
				$this->ExcludeScript($bundleFile->Name);
			}
			
			foreach ($bundle->Css as $css){
				$result[] = $this->IncludeScript($css);
			}

			$path = $bundle->Path;

			$id = $this->id;
			$includedCount = count($this->includedScripts);

			array_push($result, "<script type=\"text/javascript\" src=\"/$path\" id='dependency-$id-$includedCount'></script>");

			$this->ExcludeScript($script);

			return implode("\n", $result);
		}else if ($start == '$'){
			$script = substr($script, 1);
		}

		////// \BUNDLES
		
		
		if ($this->IsIncluded($script)) return '';

		$dependencies = $this->dependencies;

		$this->Queue($script);

		if (isset($dependencies[$script])){
			$dependencies = $dependencies[$script];

			foreach ($dependencies as $currentScript){
				if ($this->IsQueued($currentScript)){
					throw new \Exception("Script circular dependency detected between $currentScript and $script, fix thy dependency tree!");
				}

				if (!$this->IsIncluded($currentScript)){
					$result[] = $this->IncludeScript($currentScript);
				}
			}
		}


		array_push($this->includedScripts, $script);

		if ($start === "#"){
		}else if ($start === "."){
			$root = $this->cssRoot;
			$scriptFix = substr($script, 1);
			$id = $this->id;
			$includedCount = count($this->includedScripts);

			$mtime = $this->GetModTime($root.$scriptFix.'.css');

			array_push($result, "<link rel=\"stylesheet\"  href=\"/$root$scriptFix.css?$mtime\" id='dependency-$id-$includedCount'>");
		}else{
			$root = $this->root;
			$id = $this->id;
			$includedCount = count($this->includedScripts);

			if ($start === '$'){
				$bundle = ScriptBundleManager::Current()->GetBundle('$'.$script);
				$path = $bundle->Path;

				array_push($result, "<script type=\"text/javascript\" src=\"/$path\" id='dependency-$id-$includedCount'></script>");
			}else{
				$mtime = $this->GetModTime($root.$script.'.js');

				array_push($result, "<script type=\"text/javascript\" src=\"/$root$script.js?$mtime\" id='dependency-$id-$includedCount'></script>");
			}
		}

		$this->Dequeue($script);

		return implode("\n", $result);
	}

	public function GetModTime($file){
		$directory = GetProjectDirectory();
		$directoryShared = GetProjectDirectory('_shared');

		if (file_exists($file)){
			return filemtime($file);
		}else if (file_exists($directory.$file)){
			return filemtime($directory.$file);
		}else if (file_exists($directoryShared.$file)){
			return filemtime($directoryShared.$file);
		}

		return 0;
	}

	public function GetFlatList($script){
		if (substr($script, 0, 1) == '$'){
			return $this->dependencies[$script];
		}


		$list = [];
		$dependencies = $this->dependencies;

		if (isset($dependencies[$script])){
			$dependencies = $dependencies[$script];

			foreach ($dependencies as $currentScript){
				$newList = $this->GetFlatList($currentScript);
				foreach ($newList as $item){
					if (array_search($item, $list) === false){
						$list[] = $item;
					}
				}
			}
		}
		$list[] = $script;

		return $list;
	}

	public function ExcludeScript($script){
		if (func_num_args() > 1){
			$args = func_get_args();
			$count = func_num_args();
			for ($i = 0; $i < $count; $i++){
				$this->ExcludeScript($args[$i]);
			}

			return $this;
		}

		array_push($this->includedScripts, $script);
		return $this;
	}
}
