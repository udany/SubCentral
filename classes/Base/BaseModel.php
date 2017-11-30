<?php
/* BaseModel class for the DNA Framework system
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2016
 */
abstract class BaseModel {
    /** @var BaseDataAccess */
    protected static $dataAccess = [];
    protected static $databaseTable;
    protected static $insertWithId;
	protected static $updateOnDuplicate;

	public static $Identifier = ['Id'];

	/** @var  IdToHash */
	protected static $IdToHash;

	public static function IdToHash($id, $minLen = 0, $padSeed = 0){
		if (!self::$IdToHash){
			self::$IdToHash = new IdToHash(ServerSettings::GetCurrent('IdToHash'));
		}

		return self::$IdToHash->encode($id, $minLen, $padSeed);
	}
	public static function IdFromHash($hash){
		if (!self::$IdToHash){
			self::$IdToHash = new IdToHash(ServerSettings::GetCurrent('IdToHash'));
		}

		return self::$IdToHash->decode($hash);
	}
	
	public function IdAsHash($minLen = 0, $padSeed = 0){
		if (!self::$IdToHash){
			self::$IdToHash = new IdToHash(ServerSettings::GetCurrent('IdToHash'));
		}

		return self::$IdToHash->encode($this->Id, $minLen, $padSeed);
	}


	/**
	 * @param $k
	 * @param [$v]
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function Attr($k){
		$f = $this->GetField($k);

		$isSet = func_num_args() == 2;

		if ($f){
			if ($isSet){
				$f->Set($this, func_get_arg(1));
			}else{
				return $f->Get($this);
			}
		}else{
			if ($isSet) {
				$this->$k = func_get_arg(1);
			}else{
				if (property_exists($this, $k)){
					return $this->$k;
				}else{
					throw new Exception('Unknown property');
				}
			}
		}
	}

	/**
	 * @return BaseDataAccess
	 */
	public static function GetDataAccess(){
		$thisClass = get_called_class();
		if (!self::$dataAccess[$thisClass]) {
			$daClass = $thisClass . 'DataAccess';
			if (!class_exists($daClass)) {
				$daClass = 'BaseDataAccess';
				self::$dataAccess[$thisClass] = new $daClass(
					get_called_class(),
					static::$databaseTable,
					static::$insertWithId,
					static::$updateOnDuplicate);
			}else{
				self::$dataAccess[$thisClass] = new $daClass();
			}
		}

		return self::$dataAccess[$thisClass];
	}

	/**
	 * @return Field[]
	 */
	public static function GetFields(){
		return Model::$List[get_called_class()];
	}
	
	public static function SetFields($fields, $inherit=null){
		Model::AddModel(get_called_class(), $fields, $inherit);
	}




	/** @var  Relationship[] */
	protected static $relationships = [];

	public static function SetRelationships($ships){
		$class = get_called_class();

		self::$relationships[$class] = $ships;
	}

	/**
	 * @return Relationship[]
	 */
	public static function GetRelationships(){
		$class = get_called_class();
		return self::$relationships[$class] ? self::$relationships[$class] : [];
	}



	public $Exists = false;
    public $Id = 0;
	
    public function __construct($id=null) {
	    $this->fillRelationshipProperties();

	    if (is_array($id)){
		    $this->FillFromArray($id);
	    }else if ($id){
		    $this->Get($id);
	    }
    }
	protected function fillRelationshipProperties(){
		foreach (self::GetRelationships() as $key => $ship){
			$this->{$key} = new RelationshipInstance($ship, $this);
		}
	}

	/**
	 * @param $key
	 *
	 * @return RelationshipInstance|null
	 */
	public function getRelationshipInstance($key){
		if (property_exists($this, $key)){
			return $this->{$key};
		}
		return null;
	}



	/**
	 * @param $name
	 * @return Field
	 * @throws Exception
	 */
	public function GetField($name){
		$fields = Model::$List[get_class($this)];
		if (!$fields) throw new Exception("Couldn't locate field $name for class".get_class($this));

		foreach($fields as $field){
			if ($field->Name == $name) return $field;
		}
		return null;
	}

    public function FillFromArray($array, $fields=[]){
        if (gettype($array)=="array"){
	        $fieldsCount = count($fields);

	        /// Fill ships from array
	        foreach (self::GetRelationships() as $key => $ship){
		        if ($fieldsCount && !in_array($key, $fields)) continue;

		        if(isset($array[$key])){
			        if ($ship instanceof RelationshipManyToOne && $this->IsMagic()){
				        $array[$key] = $array[$key] instanceof BaseModel ? $array[$key] : BaseModel::FromArray($array[$key]);

				        $ship->Save($this, [$array[$key]]);
			        }else{
				        foreach($array[$key] as $k=>$v){
					        $array[$key][$k] = $v instanceof BaseModel ? $v : BaseModel::FromArray($v);
				        }
			        }

			        $this->getRelationshipInstance($key)->Fill($array[$key]);
		        }
	        }

            foreach($this->GetFields() as $field){
                if(isset($array[$field->DBName]) && (!$fieldsCount || in_array($field->Name, $fields))){
                    $field->Set($this, $array[$field->DBName]);
                }
            }

            $ships = self::GetRelationships();

                self::startLoadingRelationships($this);
                /// Load ships that are auto-loaded but aren't filled
                foreach ($ships as $key => $ship){
                    if ($fieldsCount && !in_array($key, $fields)) continue;

                    if (!isset($array[$key]) && $ship->Autoload && self::canAutoLoad($this, $ship)){
                        $this->getRelationshipInstance($key)->Select(true);
                    }
                }
                self::endLoadingRelationships($this);

            $this->Exists = true;
        }else {
            $this->Exists = false;
        }
    }

    /**
     * @var BaseModel[]
     */
    private static $objQueue;
    public static function startLoadingRelationships($obj){
        if (!self::$objQueue){
            self::$objQueue = [];
        }

        self::$objQueue[] = $obj;

        //$indent = str_pad("=",array_search($obj, self::$objQueue)+1, " ", STR_PAD_LEFT);
        //echo $indent . get_class($obj) . " started loading\n";
    }
    public static function endLoadingRelationships($obj){
        $idx = array_search($obj, self::$objQueue);

        if (self::$objQueue && self::$objQueue[0] == $obj){
            self::$objQueue = null;
        }else{
            array_splice(self::$objQueue, $idx, 1);
        }

        //$indent = str_pad("=",$idx+1, " ", STR_PAD_LEFT);
        //echo $indent . get_class($obj) . " ended loading\n";
    }

    /**
     * @param BaseModel $obj
     * @param Relationship $ship
     * @return bool
     */
    public static function canAutoLoad($obj, $ship){
        foreach (self::$objQueue as $queueObj){

            foreach ($queueObj->GetRelationships() as $queueObjShip){
                if (get_class($queueObj) != $ship->Model) continue;
                if (get_class($obj) != $queueObjShip->Model) continue;
                if (!$queueObjShip->Autoload) continue;

                if ($ship instanceof RelationshipOneToMany and $queueObjShip instanceof RelationshipManyToOne){
                    if ($ship->ExternalForeignKey == $queueObjShip->LocalKey){
                        return false;
                    }
                }

                if ($ship instanceof RelationshipManyToOne and $queueObjShip instanceof RelationshipOneToMany){
                    if ($ship->LocalKey == $queueObjShip->ExternalForeignKey){
                        return false;
                    }
                }
            }
        }

        //$idx = array_search($obj, self::$objQueue);
        //$indent = str_pad("",$idx+2, " ", STR_PAD_LEFT);
        //echo $indent . get_class($obj) . " loading ship " . $ship->Model . " \n";

        return true;
    }

    /**
     * @param bool $safeMode
     * @return array
     * @throws Exception
     */
    public function Serialize($safeMode = false){
        $array = array();

	    $fields = $this->GetFields();
	    if (!is_array($fields)){
		    throw new Exception("Couldn't get the fields for model ".get_class($this));
	    }

	    foreach (self::GetRelationships() as $key => $ship){
		    if ($ship instanceof RelationshipManyToOne){
			    $shipInstance = $this->getRelationshipInstance($key);

			    // TODO: Make sure this doesn't cause any infinite loops when paired with autoload
			    if (!$shipInstance->IsLoaded()){
				    continue;
			    }

			    $shipInstance->Select();
			    $ship->UpdateLocalKey($this, $shipInstance->GetCache());

			    /** @var BaseModel[] $data */
			    $data = $shipInstance->GetCache();

			    if (count($data)) {
				    $data = $data[0]->Serialize($safeMode);

				    $array[$key] = $this->IsMagic() ? $data : [$data];
			    }else{
				    $array[$key] = null;
			    }

		    }else{
			    $array[$key] = BaseModel::SerializeArray($this->getRelationshipInstance($key)->GetCache(), $safeMode);
		    }
	    }

        foreach($fields as $field){
            if(!$field->Sensitive || !$safeMode){
                $array[$field->Name] = $field->Get($this);
            }
        }

	    $array['__class'] = get_class($this);

        return $array;
    }

    public function Save($insert=null){

	    foreach (self::GetRelationships() as $key => $ship){
		    if ($ship instanceof RelationshipManyToOne && $ship->SaveParentToo){
			    $this->getRelationshipInstance($key)->Save();
		    }
	    }

        $DA = self::GetDataAccess();

        $result = $DA->Save($this, $insert);

	    if (!is_bool($result) && is_numeric($result) && $result && !$this->Id){
		    if (!$this::$insertWithId && count($this::$Identifier) === 1){
			    $identifier = $this::$Identifier[0];

			    $this->{$identifier} = $result;
		    }
	    }

	    return $result;
    }

    public function SaveAndSetId($insert=null){
        return $this->Save($insert);
    }

    public function Delete(){
        $DA = self::GetDataAccess();

	    $result = $DA->Delete($this);

	    if ($result){
		    foreach (self::GetRelationships() as $key => $ship){
			    if ($ship instanceof RelationshipManyToOne && $ship->DeleteParentToo){
				    $data = $this->getRelationshipInstance($key)->GetCache();

				    if (count($data) && $data[0] instanceof BaseModel){
					    $data[0]->Delete();
				    }
			    }
		    }
	    }

        return $result;
    }

    public function Get($id=null){
        $DA = self::GetDataAccess();

        if ($id && !is_array($id)){
            $id = [$id];
        }
        $DA->GetById($id ? $id : $this, $this);
    }

	public function LoadRelationships($forceQuery=false){
		foreach (self::GetRelationships() as $key => $ship){
			$this->getRelationshipInstance($key)->Select($forceQuery);
		}

		return $this;
	}

	public function SaveRelationships(){
		foreach (self::GetRelationships() as $key => $ship){
			if (!$ship->ReadOnly)
				$this->getRelationshipInstance($key)->Save();
		}
	}


	/**
	 * @param null $filter
	 * @param string $OrderClause
	 * @param array $groupFields
	 * @param array $joins
	 *
	 * @return BaseModel[]
	 * @throws Exception
	 */
	public static function Select($filter=null, $OrderClause='', $groupFields = [], $joins = []){
		$DA = self::GetDataAccess();
		return $DA->ListByFilter($filter, $OrderClause, $groupFields, $joins);
	}
	public static function Count($filter=null, $OrderClause='', $groupFields = [], $joins = []){
		$DA = self::GetDataAccess();
		return $DA->CountByFilter(null, $filter, $OrderClause, $groupFields, $joins);
	}
	public static function DeleteWithFilter($filter=null){
		$DA = self::GetDataAccess();
		return $DA->DeleteWithFilter($filter);
	}
	public static function SelectRelationships($list, $relationships = null){
		foreach (self::GetRelationships() as $key => $ship){
			if (!$relationships || array_search($key, $relationships) !== false){
				$ship->SelectMany($list, $key);
			}
		}
	}

	public static function FromQueryResult($r){
		$class = get_called_class();
		$result = [];
		foreach($r as $data){
			$o = $class::FromArray($data);
			$result[] = $o;
		}
		return $result;
	}

	/**
	 * @param array $a
	 *
	 * @return BaseModel
	 * @throws Exception
	 */
	public static function FromArray($a) {
		$class = get_called_class();

		if ( $class === 'BaseModel' && $a['__class'] ) {
			$class = $a['__class'];
		}

		if ($class === 'BaseModel'){
			throw new Exception("Can't build BaseModel, please extend it. \n".print_r($a, true));
		}

		if (!class_exists($class)){
			throw new Exception("Invalid class.");
		}

		$o = new $class($a);

		return $o;
	}

	/**
	 * @param BaseModel[] $a
	 * @param Bool $safeMode
	 * @return array
	 */
	public static function SerializeArray($a, $safeMode = false){
		$r = [];
		foreach($a as $e){
			if (!($e instanceof BaseModel)){
				print_r($a);
			}
			array_push($r, $e->Serialize($safeMode));
		}

		return $r;
	}

	public function IsMagic(){
		return false;
	}
}
?>