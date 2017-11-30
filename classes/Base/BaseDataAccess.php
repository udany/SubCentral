<?php
/* BaseDataAccess class for the DNA Framework system
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2016
 */
class BaseDataAccess {
    /**
     * @var Database
     */
    protected static $db;

    protected $ModelClass = 'BaseModel';
    protected $Identifier = [];
    protected $Table;
    protected $InsertWithId = false;
	protected $UpdateOnDuplicate = false;

    protected $Queries = [
        'Select' => 'SELECT DISTINCT @Fields FROM `@Table` @Join WHERE @WhereClause',
        'Insert' => 'INSERT INTO `@Table` (@Fields) VALUES (@Values)',
        'InsertOrUpdate' => 'INSERT INTO `@Table` (@Fields) VALUES (@Values) ON DUPLICATE KEY UPDATE @UpdateValues',
        'Update' => 'UPDATE `@Table` SET @Values WHERE @WhereClause',
        'Delete' => 'DELETE FROM `@Table` WHERE @WhereClause',
    ];

    /**
     * @var PDOStatement[]
     */
    protected $Statements = [];

    public function __construct($model = null, $table = null, $InsertWithId = null, $updateOnDuplicate = null){
        if ($model) $this->ModelClass = $model;
        if ($table) $this->Table = $table;
	    if ($InsertWithId) $this->InsertWithId = $InsertWithId;
	    if ($updateOnDuplicate) $this->UpdateOnDuplicate = $updateOnDuplicate;


        self::$db = Database::getInstance();

        /// Get identifier from model
        $cl = $this->ModelClass;
        $prop = "Identifier";
        $this->Identifier = $cl::$$prop;

	    if (!is_array($this->Identifier)){
		    throw new Exception(get_called_class().' identifier should be an array of strings.');
	    }

        /// Prepare statements
        $identifierClause = $this->GetIdentifierClause("`{0}`=:{1}", ' AND ');

        $obj = new $this->ModelClass;

        $insertFields = $this->GetFields($obj, "`{0}`", ', ', $this->InsertWithId);
        $insertValues = $this->GetFields($obj, ':{1}', ', ', $this->InsertWithId);
        $updateValues = $this->GetFields($obj, '`{0}` = :{1}');

        $this->Statements['GetById'] = $this->PrepareQuery($this->Queries['Select'], ['Fields' => "*", 'WhereClause' => $identifierClause]);
	    if ($this->UpdateOnDuplicate){
		    $this->Statements['Insert'] = $this->PrepareQuery($this->Queries['InsertOrUpdate'], ['Fields' => $insertFields, 'Values' => $insertValues, 'UpdateValues'=>$updateValues]);
	    }else{
		    $this->Statements['Insert'] = $this->PrepareQuery($this->Queries['Insert'], ['Fields' => $insertFields, 'Values' => $insertValues]);
	    }
        $this->Statements['Update'] = $this->PrepareQuery($this->Queries['Update'], ['WhereClause' => $identifierClause, 'Values' => $updateValues]);
        $this->Statements['Delete'] = $this->PrepareQuery($this->Queries['Delete'], ['WhereClause' => $identifierClause]);
    }

    protected function BuildQuery($query, $values){
        $values['Table'] = $this->Table;
        if (!isset($values['Join'])) $values['Join'] = '';

        $query = DNAParser::getInstance()->Format($query, $values, "@", "");

        return $query;
    }

    protected function PrepareQuery($query, $values){
        $q = $this->BuildQuery($query, $values);
        $statement = self::$db->Prepare($q);

        return $statement;
    }

    /**
     * @param $obj BaseModel
     * @param $format string
     * @param $glue string
     * @param $includeIdentifier bool
     * @return string
     */
    protected function GetFields($obj, $format, $glue = ', ', $includeIdentifier=false){
        $p = DNAParser::getInstance();
        $elements = [];

        foreach($obj->GetFields() as $field){
	        if (!$field->InDatabase()) continue;

            if ($includeIdentifier || array_search($field->Name, $this->Identifier)===false){
                array_push($elements, $p->Format($format, [$field->DBName,$field->Name]));
            }
        }

        return implode($glue, $elements);
    }

    /**
     * @param string $format
     * @param string $glue
     * @return string
     */
    protected function GetIdentifierClause($format="`{0}`=:{1}", $glue = ', '){
        $p = DNAParser::getInstance();
        $elements = [];
	    $obj = new $this->ModelClass;

        foreach($this->Identifier as $field){
	        $fieldObj = $obj->GetField($field);

            array_push($elements, $p->Format($format, [$fieldObj ? $fieldObj->DBName : $field, $field]));
        }

        return implode($glue, $elements);
    }

    protected function AddLimitClause($query, $offset, $quantity){
        $limit = $this->BuildQuery("\nLIMIT @arg0, @arg1", array($offset, $quantity));
        if ($limit){
            $query = $query . $limit;
        }
        return $query;
    }

    protected function GetIdentifierParams($obj){
        $params = [];

        if (is_array($obj)){
            foreach ($this->Identifier as $k => $v){
                $params[$v] = $obj[$k];
            }
        }else if ($obj instanceof BaseModel){
            $arr = $obj->Serialize();

            foreach ($this->Identifier as $v){
                $params[$v] = $arr[$v];
            }
        }

        return $params;
    }

    /**
     * @param array|BaseModel $idObj
     * @param $obj BaseModel
     * @throws Exception
     * @return BaseModel
     */
    public function GetById($idObj, &$obj=null){
        if (!$this->Table) return null;

	if (!($obj instanceof BaseModel)) $obj = new $this->ModelClass;

        if( !( $obj instanceof BaseModel ) )
        {
            throw new Exception( "Unable to use GetById method while using a model that doesn't extend BaseModel." );
        }

        $params = $this->GetIdentifierParams($idObj);

        $params = QueryBuilder::GetParamsToBind($this->Statements['GetById'],$params);

        $result = self::$db->Run($this->Statements['GetById'], $params, true);

        if (count($result)){
            $obj->FillFromArray($result[0]);
        }

        return $obj;
    }


    /**
     * @param array | BaseFilter $filter
     * @param string $orderClause
     * @param string[] $groupFields
     * @param string[] $joins
     * @return BaseModel[]
     * @throws Exception
     */
    public function ListByFilter($filter = null, $orderClause = '', $groupFields = [], $joins = []){
	    if (!$filter) $filter = [];

        /** @var $obj BaseModel */
        $obj = new $this->ModelClass;

        if( !( $obj instanceof BaseModel ) )
        {
            throw new Exception( "Unable to use ListByFilter method while using a model that doesn't extend BaseModel." );
        }

        $query = QueryBuilder::BuildSelect($this->Queries['Select'], $this->Table, $obj->GetFields(), $filter, $orderClause, $groupFields, $joins);

        return $this->ListByQuery($query, $filter);
    }

	/**
	 * @param string $query
	 * @param null|array $filter
	 *
	 * @return array
	 * @throws Exception
	 */
    public function ListByQuery($query, $filter=null){
	    if ($filter){
		    $params = QueryBuilder::GetParamsToBind($query,$filter);
	    }else{
		    $params = [];
	    }

        $objs = [];
        $results = self::$db->Run($query, $params, true);


	    $model = $this->ModelClass;
	    $modelIdentifier = $model::$Identifier;

	    /** @var Relationship[] $ships */
	    $ships = $model::GetRelationships();

        $controlObj = new $this->ModelClass;
	    BaseModel::startLoadingRelationships($controlObj);
	    foreach ($ships as $shipKey => $ship){
		    if ($ship->Autoload && BaseModel::canAutoLoad($controlObj, $ship)){
			    $ship->SelectMany($results, $shipKey);
		    }
	    }
        BaseModel::endLoadingRelationships($controlObj);

        foreach($results as $a){
            $obj = forward_static_call(
                [$this->ModelClass, 'FromArray'], $a);
            array_push($objs, $obj);
        }

        return $objs;
    }

	/**
	 * @param null Filter[] $filter
	 * @param string $orderClause
	 * @param array $groupFields
	 * @param array $joins
	 *
	 * @return int
	 * @throws Exception
	 */
	public function CountByFilter($countColumn = null, $filter = null, $orderClause = '', $groupFields = [], $joins = []){
		if (!$filter) $filter = [];

		/** @var $obj BaseModel */
		$obj = new $this->ModelClass;

		if( !( $obj instanceof BaseModel ) )
		{
			throw new Exception( "Unable to use CountByFilter method while using a model that doesn't extend BaseModel." );
		}

		if (!$countColumn) $countColumn = $this->Identifier[0];

		$query = QueryBuilder::BuildSelect($this->Queries['Select'], $this->Table, [new RawField($countColumn, 'Total', 'COUNT')], $filter, $orderClause, $groupFields, $joins);

		$params = QueryBuilder::GetParamsToBind($query,$filter);

		$results = self::$db->Run($query, $params, true);

		return intval($results[0]['Total']);
	}


    /**
     * @param BaseModel $object
     * @param bool $insert
     * @return int | bool
     * @throws Exception
     */
    public function Save($object, $insert = null){
        if( !( $object instanceof BaseModel ) )
        {
            throw new Exception( "Unable to use Save method while using a model that doesn't extend BaseModel." );
        }

        $params = $object->Serialize();

        if (($object->Id && !$insert) || ($insert === false)){

            $params = QueryBuilder::GetParamsToBind($this->Statements['Update'],$params);

	        $fields = $this->GetFields($object, '`{0}` = :{1}');

	        if (!$fields){
		        return true;
	        }

            $result = self::$db->Run($this->Statements['Update'], $params, false);

            return $result ? true : false;
        }else{

            $params = QueryBuilder::GetParamsToBind($this->Statements['Insert'],$params);

            self::$db->Run($this->Statements['Insert'], $params, false);

            return self::$db->getLastId();
        }
    }

	/**
	 * @param []|BaseModel $obj
	 * @return bool
	 * @throws Exception
	 */
	public function Delete($obj){

		$params = $this->GetIdentifierParams($obj);

		$params = QueryBuilder::GetParamsToBind($this->Statements['Delete'],$params);

		$result = self::$db->Run($this->Statements['Delete'], $params, false);

		return $result;
	}

	/**
	 * @param []|BaseModel $obj
	 * @return bool
	 * @throws Exception
	 */
	public function DeleteWithFilter($filter){
		if (!$filter) $filter = [];
		
		$query = QueryBuilder::BuildDelete($this->Queries['Delete'], $this->Table, $filter);

		$params = QueryBuilder::GetParamsToBind($query,$filter);

		$result = self::$db->Run($query, $params, true);

		return $result;
	}



	public function CreateTable($dropIfExists = false){
	    $exists = $this->TableExists();

		if ($exists && $dropIfExists){
			$this->DropTable();
		}else if ($exists){
		    return $this;
        }

		$query = "CREATE TABLE `".$this->Table."` (\n";

		$lines = [];
		$pks = [];
		
		/** @var Field[] $fields */
		$model = $this->ModelClass;
		$fields = $model::GetFields();
		
		foreach($fields as $field){
			if ($field->DBDescriptor){
				$desc = $field->DBDescriptor;
				$line = "    ";

				$line .= "`".$desc->Column."` ".
				         $desc->getTypeString().
				         ($desc->Null ? " NULL" : " NOT NULL").
				         ($desc->getDefaultValue() ? " DEFAULT ".$desc->getDefaultValue() : "").
				         ($desc->AutoIncrement ? " AUTO_INCREMENT" : "");

				$lines[] = $line;

				if ($desc->PrimaryKey){
					$pks[] = '`'.$desc->Column.'`';
				}
			}
		}

		$lines[] = "    PRIMARY KEY (".implode(",",$pks).")";

		//foreach($this->Indexes as $idx){
		//	$lines[] = "    INDEX (".'`'.$idx.'`'.")";
		//}

		$query .= implode(",\n", $lines)."\n";
		$query .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

		self::$db->Run($query);

		return $this;
	}

	public function DropTable(){
		$query = "DROP TABLE IF EXISTS `".$this->Table."`";
		self::$db->Run($query);
	}

	protected function GetAlterTableQuery($lines){
		$query = "ALTER TABLE `".$this->Table."`\n";
		$query .= implode(",\n", $lines).";";

		return $query;
	}

	public function CreateConstraints(){
		/** @var Relationship[] $relationships */
		$model = $this->ModelClass;
		$relationships = $model::GetRelationships();

		if (!count($relationships)) return $this;

		$lines = [];
		foreach ($relationships as $relationship){
			if ($relationship instanceof RelationshipManyToOne){
				$otherModel = $relationship->Model;
				$otherTable = $otherModel::$databaseTable;

				$name = $model."_".$relationship->Model.'_'.$relationship->LocalKey;
				
				$localKey = $relationship->LocalKey;
				$onDelete = $relationship->OnDelete;
				$onUpdate = $relationship->OnUpdate;

				$lines[] = "    ADD CONSTRAINT `$name` FOREIGN KEY (`$localKey`) REFERENCES `$otherTable`(`Id`) ON DELETE $onDelete ON UPDATE $onUpdate";
			}
		}

		$query = $this->GetAlterTableQuery($lines);
		self::$db->Run($query);

		return $this;
	}
	
	public function DropConstraints(){
		/** @var Relationship[] $relationships */
		$model = $this->ModelClass;
		$relationships = $model::GetRelationships();

		foreach ($relationships as $relationship){
			$lines = [];

			if ($relationship instanceof RelationshipManyToOne){
				$name = $model."_".$relationship->Model.'_'.$relationship->LocalKey;

				$lines = ["    DROP FOREIGN KEY `$name`"];
			}

			if (count($lines)){
				try {
					$query = $this->GetAlterTableQuery($lines);
					self::$db->Run($query);
				}catch (Exception $e){

				}
			}
		}

		return $this;
	}
	
	public function ConstraintChecks($state){
		self::$db->Run("SET foreign_key_checks = $state;");

		return $this;
	}


	public function TableExists(){
        $db = Database::$DefaultDatabase;

		return count(self::$db->Run('SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'.$this->Table.'" AND 	TABLE_SCHEMA = "'.$db.'"', null, true));
	}

	public function UpdateTable(){
		$db = Database::$DefaultDatabase;
		$table = $this->Table;

		$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE `TABLE_SCHEMA` = '$db' AND TABLE_NAME = '$table'";

		$columns = self::$db->Run($query, null, true);


		$model = $this->ModelClass;
		/** @var Field[] $fields */
		$fields = $model::GetFields();


		$matchedColumns = [];
		$lines = [];

		foreach($fields as $field){
			if ($field->DBDescriptor){
				$desc = $field->DBDescriptor;

				$column = array_filter($columns, function ($col) use ($desc){
					return $col['COLUMN_NAME'] == $desc->Column;
				});


				if (count($column)){
					$idx = array_keys($column)[0];

					$matchedColumns[$idx] = true;

					$column = $column[$idx];
					$isDifferent = false;

					if ($column['COLUMN_TYPE'] != $desc->getTypeString()){
						$isDifferent = true;
					}

					if (($column['IS_NULLABLE']=="YES") != ($desc->Null ? true : false)){
						$isDifferent = true;
					}

					if (!$isDifferent){
						continue;
					}

					$line = "    MODIFY ";
				}else{
					$line = "    ADD COLUMN ";
				}

				$line .= "`".$desc->Column."` ".
				         $desc->getTypeString().
				         ($desc->Null ? " NULL" : " NOT NULL").
				         ($desc->getDefaultValue() ? " DEFAULT ".$desc->getDefaultValue() : "").
				         ($desc->AutoIncrement ? " AUTO_INCREMENT" : "");

				$lines[] = $line;
			}
		}

		foreach ($columns as  $idx=>$column){
			if (!$matchedColumns[$idx]){
				$lines[] = "    DROP COLUMN ".$column['COLUMN_NAME'];
			}
		}

		self::$db->Run($this->GetAlterTableQuery($lines));

		return $this;
	}
}
?>