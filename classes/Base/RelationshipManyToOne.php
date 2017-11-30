<?php
class RelationshipManyToOne extends Relationship {

	public $SaveParentToo;
	public $DeleteParentToo;
	public $Magic;

	/**
	 * RelationshipManyToOne constructor.
	 *
	 * @param $model
	 * @param $localKey string Local key where the external object's id will be found
	 * @param $autoLoad bool Always loads this relationship when object is fetched
	 */
	public function __construct($model, $localKey, $autoLoad=false) {
		$this->Model         = $model;
		$this->LocalKey      = $localKey;
		$this->Autoload      = $autoLoad;
		$this->SaveParentToo = false;
		$this->DeleteParentToo = false;
		$this->Magic = $this::$AlwaysMagic ? true : false;
	}

	public function Select($obj) {
		$model = $this->Model;
		$id = $obj->{$this->LocalKey};

		$shipModel = $this->Model;
		$shipModelIdentifier = $shipModel::$Identifier;

		if (count($shipModelIdentifier) > 1) {
			throw new Exception("$shipModel has more than one id. Can't load relationships for a list of objects with multi column identifiers");
		}
		$shipModelIdentifier = $shipModelIdentifier[0];


		$filters = array_merge($this->filters, [
			$shipModelIdentifier=>$id
		]);
		
		/** @var BaseModel $result */
		$result = $model::Select($filters);

		return count($result) ? $result : [];
	}

	public function SelectMany(&$listOfObjects, $shipKey){
		if (count($listOfObjects) == 0) return;

		
		$shipModel = $this->Model;
		$shipModelIdentifier = $shipModel::$Identifier;

		if (count($shipModelIdentifier) > 1) {
			throw new Exception("$shipModel has more than one id. Can't load relationships for a list of objects with multi column identifiers");
		}
		$shipModelIdentifier = $shipModelIdentifier[0];

		/// Will select 0 or 1 objects for each result
		$ids = [];
		$map = [];

		foreach ($listOfObjects as $i => &$obj){
			if ($obj instanceof BaseModel){
				$localKey = $obj->{$this->LocalKey};
				$obj->{$shipKey}->Fill([]);
			}else{
				$localKey = $obj[$this->LocalKey];
				$obj[$shipKey] = $this->Magic ? null : [];
			}

			if ($localKey){
				$ids[] = $localKey;

				if (!isset($map[$localKey])) $map[$localKey] = [];

				$map[$localKey][] = $i;
			}
		}
		unset($obj);

		$ids = array_unique($ids);

		if (count($ids)){
			$filters = array_merge($this->filters, [
				new QueryFilterIn($shipModelIdentifier, $ids)
			]);

			/** @var BaseModel[] $shipResults */
			$shipResults = $shipModel::Select($filters);

			foreach ($shipResults as $shipResult){
				$shipResultId = $shipResult->$shipModelIdentifier;
				foreach ($map[$shipResultId] as $resultId){
					$obj = &$listOfObjects[$resultId];

					if ($obj instanceof BaseModel){
						$obj->{$shipKey}->Fill([$shipResult]);
					}else{
						$obj[$shipKey] = $this->Magic ? $shipResult :  [$shipResult];
					}
				}
			}
		}
	}

	/**
	 * @param BaseModel $obj
	 * @param BaseModel[] $data
	 * @return void
	 */
	public function Save($obj, $data) {
		if ($this->SaveParentToo){
			if (count($data)){
				$data[0]->Save();
			}
		}

		$this->UpdateLocalKey($obj, $data);
	}

	public function UpdateLocalKey($obj, $data){
		$obj->{$this->LocalKey} = count($data) ? $data[0]->Id : null;
	}

	public function SaveParentToo(){
		$this->SaveParentToo = true;

		return $this;
	}

	public function DeleteParentToo(){
		$this->DeleteParentToo = true;

		return $this;
	}

	public static $AlwaysMagic = false;

	public function Magic(){
		$this->Magic = true;

		return $this;
	}
}