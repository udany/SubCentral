<?php
use MatthiasMullie\Minify as Minify;

class ScriptBundleManager {
	public $fileName = 'ScriptsInfo.json';
	public $root = 'static/';
	public $dir = 'bundles/';

	public $data = [];

	protected static $current;
	public static function Current(){
		if (!self::$current) self::$current = new ScriptBundleManager();
		return self::$current;
	}


	public function __construct() {
		$this->CreateDirectory();
		$this->file = new JSONData($this->GetDirectory().$this->fileName);
		$this->LoadData();
	}

	public function LoadData(){
		if ($this->file->data){
			$this->data = $this->file->data;
		}
	}

	public function SaveData(){
		foreach ($this->data as $k=>$v){
			if ($v instanceof BaseModel){
				$this->data[$k] = $v->Serialize();
			}
		}

		$this->file->SetData($this->data);
	}


	public function CreateDirectory(){
		if (!is_dir($this->GetDirectory())){
			mkdir($this->GetDirectory());
		}
	}
	public function GetDirectory(){
		return $this->root . $this->dir;
	}
	
	public function IsBundled($name){
		if (substr($name, 0, 1)=='+'){
			$name = substr($name, 1);
		}

		return isset($this->data[$name]);
	}

	public function Bundle($name) {
		$list = ScriptDependency::Current()->GetFlatList($name);
		$jsRoot = ScriptDependency::Current()->root;

		$js = new Minify\JS();

		$bundle = new ScriptBundle();
		$bundle->Name = $name;
		$bundle->Date = time();

		foreach ($list as $item){
			$start = str_split($item)[0];

			if ($start === '#' || $start === '+' || $start === '$'){
				// Do nothing
			}else if ($start === '.'){
				$bundle->Css[] = $item;
			}else{
				$itemPath = $jsRoot.$item.'.js';

				$js->add($itemPath);
				$script = new ScriptBundleFile();
				$script->Name = $item;
				$script->Date = filemtime($itemPath);
				$bundle->Scripts[] = $script;
			}

		}


		if (substr($name, 0, 1)=='+'){
			$name = substr($name, 1);
		}

		$sanitizedName = str_ireplace('/', '.', $name);

		$js->minify($this->GetDirectory().$sanitizedName.'.js');

		$bundle->Path = $this->GetDirectory().$sanitizedName.'.js';

		$this->data[$name] = $bundle->Serialize();
		$this->SaveData();
	}

	/**
	 * @param $name
	 *
	 * @return ScriptBundle
	 * @throws Exception
	 */
	public function GetBundle($name){
		if (!$this->IsBundled($name)){
			$this->Bundle($name);
		}else{
			$bundle = $this->data[$name];
			$jsRoot = ScriptDependency::Current()->root;

			if (!($bundle instanceof ScriptBundle)){
				$bundle = ScriptBundle::FromArray($bundle);
				$this->data[$name] = $bundle;
			}

			$rebuild = false;

			foreach ($bundle->Scripts as $bundleFile){
				$filePath = $jsRoot.$bundleFile->Name.'.js';
				$mod = filemtime($filePath);
				if ($mod > $bundleFile->Date){
					$rebuild = 1;
				}
			}

			if($rebuild){
				$this->Bundle($name);
			}
		}

		if (substr($name, 0, 1)=='+'){
			$name = substr($name, 1);
		}

		$b = $this->data[$name];

		if (!($b instanceof ScriptBundle)){
			$b = ScriptBundle::FromArray($b);
			$this->data[$name] = $b;
		}

		return $b;
	}
}


class ScriptBundle extends BaseModel{
	public $Name;
	public $Path;
	/** @var ScriptBundleFile[]  */
	public $Scripts = [];
	public $Css = [];
	public $Date;
}
ScriptBundle::SetFields([
	new Field('Name'),
	new Field('Path'),
	new ModelArrayField('Scripts'),
	new JsonField('Css'),
	new IntegerField('Date')
]);
class ScriptBundleFile extends BaseModel {
	public $Name;
	public $Date;
}
ScriptBundleFile::SetFields([
	new Field('Name'),
	new IntegerField('Date')
]);