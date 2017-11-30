<?php
class Model{
    /**
     * @var Field[][]
     */
    public static $List = [];
    public static $Assoc = [];

    /**
     * @param $name
     * @param $fields Field[]
     * @param $inherit
     */
    public static function AddModel($name, $fields, $inherit=null){
        $assoc = [];

	    if ($inherit === true){
		    $parent = get_parent_class($name);
		    while ($parent){
			    if (isset(Model::$List[$parent])){
				    foreach(Model::$List[$parent] as $field){
					    if (!isset($assoc[$field->Name])) {
						    $assoc[ $field->Name ] = $field;
					    }
				    }
			    }

			    $parent = get_parent_class($parent);
		    }
	    }else if (is_string($inherit) && isset(Model::$List[$inherit])){
		    foreach(Model::$List[$inherit] as $field){
			    $assoc[$field->Name] = $field;
		    }
	    }

        foreach($fields as $field){
            $assoc[$field->Name] = $field;
        }

	    $fields = [];
	    foreach ($assoc as $field){
		    $fields[] = $field;
	    }

        Model::$List[$name] = $fields;
        Model::$Assoc[$name] = $assoc;
    }
}
class Field{
	public $Name;
	public $DBName;
	public $getFunction;
	public $setFunction;
	public $Sensitive;
	public $GroupFunction;
	public $IsInDatabase = true;

	/** @var  FieldDatabaseDescriptor */
	public $DBDescriptor = null;

	public function __construct($name, $dbName=null, $get=null, $set=null, $sensitive=false, $groupFunction=null){
		$this->Name = $name;
		$this->DBName = $dbName ? $dbName : $name;
		$this->getFunction = $get;
		$this->setFunction = $set;
		$this->Sensitive = $sensitive;
		$this->GroupFunction = $groupFunction;
	}
	public function Get($obj, $safe = false){
		if ($this->getFunction){
			return $this->getFunction($obj);
		}
		return $obj->{$this->Name};
	}
	public function Set($obj, $val){
		if ($this->setFunction){
			$this->setFunction($obj, $val);
		}else{
			$obj->{$this->Name} = $val;
		}
	}

	public function __call($method, $args) {
		if(isset($this->$method) && is_callable($this->$method)) {
			return call_user_func_array(
				$this->$method,
				$args
			);
		}

		throw new Exception('Attempted to call non existing method on Field object.');
	}

	public function SetDatabaseDescriptor($type, $length=null, $default=null, $null=false, $autoIncrement=false, $column=null, $unsigned = false){
		$d = new FieldDatabaseDescriptor();
		$d->Name = $this->Name;
		$d->Column = $this->DBName ? $this->DBName : ($column ? $column : $this->Name);
		$d->Type = $type;
		$d->Length = $length;
		$d->Null = $null;
		$d->Default = $default;
		$d->AutoIncrement = $autoIncrement;
		$d->Unsigned = $unsigned;

		$this->DBDescriptor = $d;

		return $this;
	}
	public function GroupFunction($val){
		$this->GroupFunction = $val;

		return $this;
	}
	public function AutoIncrement($val){
		if ($this->DBDescriptor){
			$this->DBDescriptor->AutoIncrement = $val;
		}

		return $this;
	}
	public function Null($val){
		if ($this->DBDescriptor){
			$this->DBDescriptor->Null = $val;
		}

		return $this;
	}
	public function Unsigned($val){
		if ($this->DBDescriptor){
			$this->DBDescriptor->Unsigned = $val;
		}

		return $this;
	}
	public function DefaultSet($val){
		if ($this->DBDescriptor){
			$this->DBDescriptor->Default = $val;
		}

		return $this;
	}
	public function PrimaryKey(){
		if ($this->DBDescriptor){
			$this->DBDescriptor->PrimaryKey = true;
		}

		return $this;
	}
	public function Sensitive($val=null){
		if ($val!==null){
			$this->Sensitive = $val;
			return $this;
		}else{
			return $this->Sensitive;
		}
	}
	public function InDatabase($val=null){
		if ($val!==null){
			$this->IsInDatabase = $val;
			return $this;
		}else{
			return $this->IsInDatabase;
		}
	}
	public function ThisGetFunction(){
		if (func_num_args() === 1){
			$this->getFunction = func_get_arg(0);
			return $this;
		}else{
			return $this->getFunction;
		}
	}
	public function ThisSetFunction(){
		if (func_num_args() === 1){
			$this->setFunction = func_get_arg(0);
			return $this;
		}else{
			return $this->getFunction;
		}
	}
}
class FieldDatabaseDescriptor {
	public $Name;
	public $Type;
	public $Length;
	public $Column;
	public $Null = false;
	public $Default;
	public $AutoIncrement = false;
	public $PrimaryKey = false;
	public $Unsigned = false;

	public function getTypeString(){
		return $this->Type.($this->Length ? "(".$this->Length.")" : "").($this->Unsigned ? ' unsigned':'');
	}

	public function getDefaultValue(){
		if ($this->Default){
			if (is_string($this->Default)){
				return '"'.$this->Default.'"';
			} elseif (is_numeric($this->Default)){
				return $this->Default;
			}
		}
	}
}

class RawField {
	public $Column;
	public $Alias;
	public $Function;

	public function __construct($Column, $Alias = '', $function=''){
		$this->Column = $Column;
		$this->Alias = $Alias;
		$this->Function = $function;
	}

	public function GetFormatted($table=''){
		$r = '`'.$this->Column.'`';
		if ($table) $r = '`'.$table.'`.'.$r;
		if ($this->Function) $r = $this->Function.'('.$r.')';
		if ($this->Alias) $r = $r.' as `'.$this->Alias.'`';

		return $r;
	}
}
class NotNullField extends Field{
	public $default;
	public function __construct($name, $default, $dbName=null, $get=null, $set=null, $sensitive=false, $groupFunction=null){
		$this->default = $default;
		parent::__construct($name, $dbName, $get, $set, $sensitive, $groupFunction);
	}
	public function Set($obj, $val){
		if ($val === null){
			$val = $this->default;
		}
		parent::Set($obj, $val);
	}
	public function Get($obj, $safe = false){
		$val = parent::Get($obj, $safe);
		if ($val === null){
			$val = $this->default;
		}
		return $val;
	}
}
class DateField extends Field{
	public $default;
	public function __construct($name, $default, $dbName=null, $get=null, $set=null, $sensitive=false, $groupFunction=null){
		$this->default = $default;
		parent::__construct($name, $dbName, $get, $set, $sensitive, $groupFunction);
	}
	public function Set($obj, $val){
		if ($val === null){
			$val = $this->default;
		}
		parent::Set($obj, $val);
	}
	public function Get($obj, $safe = false){
		$val = parent::Get($obj, $safe);
		if ($val === null){
			$val = $this->default;
		}
		return $val;
	}
}
class NullableIntegerField extends Field{
	public function Get($obj, $safe = false){
		if ($this->getFunction){
			return $this->getFunction($obj);
		}
		$val = $obj->{$this->Name};
		return $val === null ? null : intval($obj->{$this->Name}, 10);
	}
	public function Set($obj, $val){
		if ($this->setFunction){
			$this->setFunction($obj, intval($val));
		}else{
			$obj->{$this->Name} = $val === null ? null : intval($val);
		}
	}
}
class IntegerField extends Field{
	public function Get($obj, $safe = false){
		if ($this->getFunction){
			return $this->getFunction($obj);
		}
		return intval($obj->{$this->Name}, 10);
	}
	public function Set($obj, $val){
		if ($this->setFunction){
			$this->setFunction($obj, intval($val));
		}else{
			$obj->{$this->Name} = intval($val);
		}
	}
}
class FloatField extends Field{
	public function Get($obj, $safe = false){
		if ($this->getFunction){
			return $this->getFunction($obj);
		}
		return floatval($obj->{$this->Name});
	}
	public function Set($obj, $val){
		if ($this->setFunction){
			$this->setFunction($obj, floatval($val));
		}else{
			$obj->{$this->Name} = floatval($val);
		}
	}
}
class BooleanField extends Field{
	public function Get($obj, $safe = false){
		if ($this->getFunction){
			return $this->getFunction($obj);
		}
		return $obj->{$this->Name} ? 1 : 0;
	}
	public function Set($obj, $val){
		if ($this->setFunction){
			$this->setFunction($obj, intval($val));
		}else{
			$obj->{$this->Name} = $val ? 1 : 0;
		}
	}
}
class NullableBooleanField extends Field{
	public function Get($obj, $safe = false){
		if ($this->getFunction){
			return $this->getFunction($obj);
		}
		return $obj->{$this->Name} === null ? null : ($obj->{$this->Name} ? 1 : 0);
	}
	public function Set($obj, $val){
		if ($this->setFunction){
			$this->setFunction($obj, intval($val));
		}else{
			$obj->{$this->Name} = $val === null ? null : ($val ? 1 : 0);
		}
	}
}
class JsonField extends Field{
	public function Get($obj, $safe = false){
		if ($this->getFunction){
			return $this->getFunction($obj);
		}

		return json_encode($obj->{$this->Name});
	}
	public function Set($obj, $val){
		if ($this->setFunction){
			$this->setFunction($obj, intval($val));
		}else{
			$obj->{$this->Name} = json_decode($val, true);
		}
	}
}
class JsonModelField extends Field{
	public function Get($obj, $safe = false){
		if ($this->getFunction){
			return $this->getFunction($obj);
		}

		$result = $obj->{$this->Name};
		if ($result instanceof BaseModel){
			$result = $result->Serialize($safe);
		}

		return json_encode($result);
	}
	public function Set($obj, $val){
		if ($this->setFunction){
			$this->setFunction($obj, intval($val));
		}else{
			$val = json_decode($val, true);

			if (is_array($val)) {
				$val = BaseModel::FromArray($val);
			}

			$obj->{$this->Name} = $val;
		}
	}
}
class ModelArrayField extends Field{
	public function Get($obj, $safe = false){
		if ($this->getFunction){
			return $this->getFunction($obj);
		}

		$result = [];
		$array = $obj->{$this->Name};

		$this->Sort($array);
		foreach($array as $key => $itm) {
			$result[$key] = $itm->Serialize($safe);
		}

		return $result;
	}
	public function Set($obj, $val){
		if ($this->setFunction){
			$this->setFunction($obj, intval($val));
		}else{
			$array = [];

			foreach($val as $key => $data){
				$c = $this->class;
				$item = BaseModel::FromArray($data);

				$array[$key] = $item;
			}
			$this->Sort($array);

			$obj->{$this->Name} = $array;
		}
	}

	private $sortField;
	private $sortDesc = false;
	public function SortBy($field, $desc = false){
		$this->sortField = $field;
		$this->sortDesc = $desc;

		return $this;
	}

	private function Sort(&$array){
		if (!$this->sortField) return;

		usort($array, function ($a, $b){
			return ($a->{$this->sortField} == $b->{$this->sortField}) ? 0 : (($a->{$this->sortField} < $b->{$this->sortField}) ? -1 : 1);
		});
		if ($this->sortDesc){
			$array = array_reverse($array);
		}
	}
}
class JsonModelArrayField extends ModelArrayField {
	public function Get($obj, $safe = false){
		return json_encode(parent::Get($obj, $safe), JSON_PRETTY_PRINT);
	}
	public function Set($obj, $val){
		$val = json_decode($val, true);
		parent::Set($obj, $val);
	}
}


class ComputedField extends Field{
	private $Method;
	public function __construct($name, $method=null) {
		$this->Method = $method ? $method : $name;

		parent::__construct($name);
	}

	public function Get($obj, $safe = false){
		return $obj->{$this->Method}();
	}
	public function Set($obj, $val){
		$obj->{$this->Method}($val);
	}
}