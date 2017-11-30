<?PHP
/* Settings module
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2013
 */
class ServerSettings {
    private $json;
    private $store;
	private $fileName = "settings.{environment}json";

	public static $Environment;
	public static $Project;

    public function __construct($file=null) {
        if ($file) $this->fileName = $file;

        $this->json = new JSONData(DNAParser::getInstance()->Format($this->fileName, ['environment'=>'']));
    }

    public function Load($target=null){
        if (is_string($target)){
            $target = new JSONData($target);
        }else if (!$target){
            $target = $this->json;
        }

	    $data = $target->GetData();

        if (!$this->store){
            $this->store = [];
        }

        if ($data){
	        foreach($data as $k => $v){
		        $this->store[$k] = $v;
	        }
        }
    }

	public function LoadEnvironment($env=null){
		if (!$env) $env = $this::$Environment;
		if ($env) $env .= '.';

		$data = new JSONData(DNAParser::getInstance()->Format($this->fileName, ['environment'=>$env]));

		$this->Load($data);
	}

	public function LoadProject($project='', $env=''){
		if (!$project) $project = $this::$Project;
		if ($env) $env .= '.';

		$data = new JSONData(GetProjectDirectory($project).DNAParser::getInstance()->Format($this->fileName, ['environment'=>$env]));
		$this->Load($data);
	}

	/**
	 * @param string|JSONData $target
	 */
    public function Save($target=null){
        if ($this->store){
            if (is_string($target)){
                $target = new JSONData($target);
            }else if (!$target){
                $target = $this->json;
            }

            $target->SetData($this->store);
        }
    }

    public function Get($key){
        if ($key){
            if (isset($this->store[$key]))
                return $this->store[$key];
        }else{
	        return $this->store;
        }
	    
	    return null;
    }

    public function Set($key, $value){
        if ($key){
            if (!$this->store) $this->store = [];
            $this->store[$key] = $value;
        }
    }

	/** @var ServerSettings $current */
	public static $current;
	public static function GetCurrent($key){
		return self::$current->Get($key);
	}
	public static function SetCurrent($key, $val){
		self::$current->Set($key, $val);
	}
	public static function SaveCurrent($target=null){
		self::$current->Save($target);
	}


	private static $vars = [];
	public static function Variable($key, $val=null){
		if (func_num_args() == 1){
			return isset(self::$vars[$key]) ? self::$vars[$key] : null;
		}else{
			self::$vars[$key] = $val;

			return null;
		}
	}

}
?>