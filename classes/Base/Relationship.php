<?php
abstract class Relationship {
	public $Model;
	public $LocalKey = 'Id';
	public $LocalForeignKey;
	public $ExternalKey = 'Id';
	public $ExternalForeignKey;
	public $ReadOnly = false;
	public $Autoload = false;

	public $OnDelete = 'RESTRICT';
	public $OnUpdate = 'RESTRICT';

	public $filters = [];

	public $Order;

	/**
	 * @param $obj BaseModel
	 * @return BaseModel[]
	 */
	public abstract function Select($obj);

	public abstract function SelectMany(&$listOfObjects, $shipKey);

	/**
	 * @param $obj BaseModel
	 * @param $data BaseModel[]
	 * @return mixed
	 */
	public abstract function Save($obj, $data);

	public function Autoload(){
		if (func_num_args() === 1){
			$this->Autoload = func_get_arg(0) ? true : false;
			return $this;
		}
		return $this->Autoload;
	}

	public function Filters(){
		if (func_num_args() === 1){
			$this->filters = func_get_arg(0);
			return $this;
		}
		return $this->filters;
	}

	public function OnDelete($val){
		$this->OnDelete = $val;
		
		return $this;
	}
	public function OnUpdate($val){
		$this->OnUpdate = $val;

		return $this;
	}
}