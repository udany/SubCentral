<?php
class RelationshipManyToMany extends Relationship {
	public $RelationshipModel;

	public function __construct($model, $localKey, $localFK, $externalKey, $externalFK, $shipModel) {
		$this->Model = $model;
		$this->LocalKey = $localKey;
		$this->LocalForeignKey = $localFK;
		$this->ExternalKey = $externalKey;
		$this->ExternalForeignKey = $externalFK;
		$this->RelationshipModel = $shipModel;
	}

	public function SelectMany(&$listOfObjects, $shipKey){
		if (count($listOfObjects) == 0) return;

		$model = $this->Model;

		$rModel = $this->RelationshipModel;

		$that = $this;


		$localIds = array_map(function (&$item) use ($that, $shipKey){
			if ($item instanceof BaseModel) {
				return $item->{$that->LocalKey};
			}else{
				return $item[$that->LocalKey];
			}
		}, $listOfObjects);

		$localIdIndexLookup = array_flip($localIds);

		$filters = array_merge($this->filters, [
			new QueryFilterIn($this->LocalForeignKey, $localIds)
		]);

		$shipResults = $rModel::Select($filters);


		$externalIds = [];
		$externalObjectDestinations = [];
		foreach ($shipResults as $shipResult){
			$externalId = $shipResult->{$this->ExternalForeignKey};

			$externalIds[] = $externalId;

			if (!isset($externalObjectDestinations[$externalId])) $externalObjectDestinations[$externalId] = [];

			$externalObjectDestinations[$externalId][] = $shipResult->{$this->LocalForeignKey};
		}

		$externalIds = array_unique($externalIds);

		$shipArrays = [];


		//// If there's anything to select
		if (count($externalIds)){
			$results = $model::Select([
				new QueryFilterIn($this->ExternalKey, $externalIds)
			]);

			foreach ($results as $result){
				$externalId = $result->{$this->ExternalKey};
				if ( isset( $externalObjectDestinations[$externalId] ) ){
					foreach ($externalObjectDestinations[$externalId] as $localId){
						$index = $localIdIndexLookup[$localId];

						if (!isset($shipArrays[$index])) $shipArrays[$index] = [];
						$shipArrays[$index][] = $result;
					}
				}
			}
		}

		foreach ($localIds as $index => $id){
			$localObj = $listOfObjects[$index];

			$array = [];
			if (isset($shipArrays[$index])){
				$array = $shipArrays[$index];
			}


			if ($localObj instanceof BaseModel) {
				$localObj->{$shipKey}->Fill($array);
			}else{
				$localObj[$shipKey] = $array;
			}
		}
	}

	public function Select( $obj ) {
		$model = $this->Model;
		$modelTable = '`'.$model::$databaseTable.'`';

		$rModel = $this->RelationshipModel;
		$rTable = '`'.$rModel::$databaseTable.'`';

		return $model::Select( [ $rTable.'.'.$this->LocalForeignKey => $obj->{$this->LocalKey} ], $this->Order, [],
			[new QueryJoin($rTable, $rTable.'.'.$this->ExternalForeignKey.' = '.$modelTable.'.'.$this->ExternalKey, 'LEFT')] );
	}
	public function Save( $obj, $data ) {
		$model = $this->Model;
		$rModel = $this->RelationshipModel;

		$rModel::DeleteWithFilter([$this->LocalForeignKey=>$obj->{$this->LocalKey}]);

		foreach ( $data as $item ) {
			$ship = new $rModel;
			$ship->{$this->LocalForeignKey} = $obj->{$this->LocalKey};
			$ship->{$this->ExternalForeignKey} = $item->{$this->ExternalKey};
			$ship->Save();
		}
	}
}