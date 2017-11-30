<?php
/* BaseDataAccess class for the DNA Framework system
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2016
 */
class BaseDataAccessJson {
    protected $ModelClass = 'BaseModel';
	protected $Table;
	protected $Directory;
	protected $GlobalDb = false;

    /**
     * @var PDOStatement[]
     */
    protected $Statements = [];

    public function __construct() {
	    if ($this->GlobalDb){
		    $this->Directory = "db/";
	    }else{
		    $this->Directory = GetDynamicDirectory()."db/";
	    }

	    if (!file_exists($this->Directory)) mkdir($this->Directory);
	    $this->Directory .= $this->Table."/";
	    if (!file_exists($this->Directory)) mkdir($this->Directory);
    }

	protected function GetFile($id){
		if ($id instanceof BaseModel) $id = $id->Id;
		return $this->Directory.$id.".json";
	}

	protected function GetNewId(){
		$aiFile = $this->Directory."auto_increment";
		$id = 1;
		if (file_exists($aiFile)){
			$id = intval(file_get_contents($aiFile));
		}
		FileSystem::Write($aiFile, $id+1);

		return $id;
	}

    /**
     * @param array|BaseModel $idObj
     * @param $obj BaseModel
     * @throws Exception
     * @return BaseModel
     */
    public function GetById($idObj, &$obj=null){
        if (!($obj instanceof BaseModel)) $obj = new $this->ModelClass;

        if( !( $obj instanceof BaseModel ) )
        {
            throw new Exception( "Unable to use GetById method while using a model that doesn't extend BaseModel." );
        }

	    if (is_array($idObj)) $idObj = $idObj[0];
		$file = $this->GetFile($idObj);

        if (file_exists($file)){
	        $result = json_decode(file_get_contents($file), true);
	        if (is_array($result)){
		        $obj->FillFromArray($result);
	        }
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
	    $files = glob($this->Directory."*.json");
	    $result = [];
	    foreach($files as $file){
		    $data = json_decode(file_get_contents($file), true);
		    if (is_array($data)){
			    $obj = new $this->ModelClass;
			    $obj->FillFromArray($data);
			    array_push($result, $obj);
		    }
	    }

	    return $result;
    }

    /**
     * @param BaseModel $object
     * @return int | bool
     * @throws Exception
     */
    public function Save($object){
        if( !( $object instanceof BaseModel ) )
        {
            throw new Exception( "Unable to use Save method while using a model that doesn't extend BaseModel." );
        }
	    if (!$object->Id){
		    $object->Id = $this->GetNewId();
	    }

        $data = $object->Serialize();
	    $file = $this->GetFile($object);
	    FileSystem::Write($file, json_encode($data, JSON_PRETTY_PRINT));

	    return $object->Id;
    }

    /**
     * @param []|BaseModel $obj
     * @return bool
     * @throws Exception
     */
    public function Delete($obj){
	    $file = $this->GetFile($obj);
	    if (file_exists($file)) unlink($file);

        return true;
    }
}
?>