<?php
class DynamicFile {
	public $Name;
	public $Path;
	public $Project;
	public $Data;
	private $exists = -1;

	public function __construct($path, $name, $project=null, $data=null) {
		$this->Name = $name;
		$this->Path = $path;
		$this->Project = $project;
		$this->Data = $data;
	}

	public function GetName(){
		if (is_callable($this->Name)){
			return call_user_func($this->Name, $this->Data);
		}else{
			return $this->Name;
		}
	}

	public function GetDirectory(){
		return GetProjectDirectory($this->Project).$this->Path;
	}
	public function GetPath(){
		return $this->GetDirectory().$this->GetName();
	}
	public function GetUrl(){
		return GetProjectUrl('', '', $this->Project).$this->Path.$this->GetName();
	}
	public function Exists($recheck=false){
		if ($this->exists === -1 || $recheck){
			if ($recheck) clearstatcache();
			
			$this->exists = file_exists($this->GetPath());
		}
		return $this->exists;
	}
}