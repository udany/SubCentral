<?php
class RelationshipInstance {

	public function __construct($ship, $obj) {
		$this->Relationship = $ship;
		$this->Instance = $obj;
	}

	/** @var Relationship */
	public $Relationship;
	private $Instance;
	private $cache = null;

	public function GetCache(){
		return $this->cache ?: [];
	}

	public function Select($forceQuery=false){
		if (!$this->cache || $forceQuery){
			$this->cache = $this->Relationship->Select($this->Instance);
		}
		return $this->cache;
	}

	public function First($forceQuery=false){
		$result = $this->Select($forceQuery);

		return count($result) ? $result[0] : null;
	}

	public function Save($data=null){
		if ($data==null){
			if ($this->cache !== null){
				$data = $this->cache;
			}else{
				return;
			}
		}
		$this->Relationship->Save($this->Instance, $data);
	}

	public function Fill($data){
		if ($data instanceof BaseModel){
			$data = [$data];
		}
		
		$this->cache = $data;
	}

	public function IsLoaded(){
		return $this->cache !== null;
	}
}