<?php
class RelationshipOneToMany extends Relationship {

    public $queryOrder = "";

	/**
	 * RelationshipOneToMany constructor.
	 *
	 * @param $model
	 * @param $localKey string Key within the object whose value will be present in external objects
	 * @param $externalFK string Key where the identifying value will be found within the external objects
	 * @param $readOnly bool If true won't automatically save the relationship on save
	 */
	public function __construct($model, $localKey, $externalFK, $readOnly=false) {
		$this->Model = $model;
		$this->LocalKey = $localKey;
		$this->ExternalForeignKey = $externalFK;
		$this->ReadOnly = $readOnly;
	}

	public function setQueryOrder($value){
	    $this->queryOrder = $value;

	    return $this;
    }

	public function Select($obj) {
		$model = $this->Model;
		$id = $obj->{$this->LocalKey};

		$filters = array_merge($this->filters, [
			$this->ExternalForeignKey => $id
		]);

		return $model::Select($filters, $this->queryOrder);
	}
	public function SelectMany(&$listOfObjects, $shipKey) {
		if (count($listOfObjects) == 0) return;
		
		$shipModel           = $this->Model;

		/// Will select 0, 1 or Many objects for each result
		$ids = [];
		$map = [];

		foreach ($listOfObjects as $i => &$obj){
			if ($obj instanceof BaseModel){
				$localKey = $obj->{$this->LocalKey};
				$obj->getRelationshipInstance($shipKey)->Fill([]);
			}else{
				$localKey = $obj[$this->LocalKey];
				$obj[$shipKey] = [];
			}

			if ($localKey){
				$ids[] = $localKey;
				$map[$localKey] = $i;
			}
		}
		unset($obj);

		$ids = array_unique($ids);

		if (count($ids)) {
			$filters = array_merge($this->filters, [
				new QueryFilterIn($this->ExternalForeignKey, $ids)
			]);

			/** @var BaseModel[] $shipResults */
			$shipResults = $shipModel::Select($filters, $this->queryOrder);

			$shipResultsSorted = [];

			foreach ($shipResults as $shipResult) {
				$shipResultExternalFK = $shipResult->{$this->ExternalForeignKey};

				if (!isset($shipResultsSorted[$shipResultExternalFK])) {
					$shipResultsSorted[$shipResultExternalFK] = [];
				}

				$shipResultsSorted[$shipResultExternalFK][] = $shipResult;
			}

			foreach ($shipResultsSorted as $shipResultExternalFK => $shipResultArray) {
				$obj = &$listOfObjects[$map[$shipResultExternalFK]];

				if ($obj instanceof BaseModel){
					$obj->getRelationshipInstance($shipKey)->Fill($shipResultArray);
				}else{
					$obj[$shipKey] = $shipResultArray;
				}
			}
		}
	}

	/**
	 * @param BaseModel $obj
	 * @param BaseModel[] $data
	 *
	 * @return mixed|void
	 * @throws Exception
	 */
	public function Save($obj, $data) {
		$model = $this->Model;
		$identifier = $model::$Identifier;

		$that = $this;


		/** @var BaseModel[] $currentItems */
		$currentItems = $this->Select($obj);


		$newIds = array_map(function ($o) use ($identifier,$that){ return json_encode($that->GetIdArray($o, $identifier)); }, $data);

		$oldIds = array_map(function ($o) use ($identifier,$that){ return json_encode($that->GetIdArray($o, $identifier)); }, $currentItems);

		foreach ($currentItems as $currentItem){
			$idJson = json_encode($that->GetIdArray($currentItem, $identifier));

			if (array_search($idJson, $newIds)===false){
				$currentItem->Delete();
			}
		}

		foreach ( $data as $item ) {
			$exists = $model::GetDataAccess()->GetById($item);

			$exists = ($exists && $exists->Exists) ? true : false;

			$item->{$this->ExternalForeignKey} = $obj->{$this->LocalKey};
			$item->Save(!$exists);
		}
	}

	public function GetIdArray($obj, $identifiers){
		$r = [];
		foreach ($identifiers as $identifier){
			$r[] = $obj->$identifier;
		}

		return $r;
	}
}