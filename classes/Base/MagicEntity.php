<?php

trait MagicEntity {
	/** @return Field[] */
	public abstract function GetFields();

	/** @var Field[] */
	protected $_fieldCache = null;

	/** @var Field[] */
	protected $_attributeCache = [];

	/** @var RelationshipInstance[] */
	protected $_relationshipInstances = [];


	protected function fillRelationshipProperties(){
		foreach (self::GetRelationships() as $key => $ship){
			$this->_relationshipInstances[$key] = new RelationshipInstance($ship, $this);
		}
	}
	public function getRelationshipInstance($key){
		if (isset($this->_relationshipInstances[$key])){
			return $this->_relationshipInstances[$key];
		}
		return null;
	}

	public function __isset($name) {
		if (isset($this->_fieldCache[$name])){
			return true;
		}
		if (isset($this->_relationshipInstances[$name])){
			return true;
		}

		return false;
	}

	public function __get($name) {
		if (!$this->_fieldCache) $this->_buildAttributeCache();

		if (isset($this->_fieldCache[$name])){
			if ($this->_fieldCache[$name] instanceof ComputedField){
				return $this->_fieldCache[$name]->Get($this);
			}else{
				if (isset($this->_attributeCache[$name])){
					return $this->_attributeCache[$name];
				}
			}
		}
		
		if (isset($this->_relationshipInstances[$name])){
			$shipInstance = $this->_relationshipInstances[$name];
			if ($shipInstance->Relationship instanceof RelationshipManyToOne){
				$result = $shipInstance->Select();
				if (count($result)){
					return $result[0];
				}else{
					return null;
				}
			}else{
				return $shipInstance->Select();
			}
		}

		return null;
	}
	public function __set($name, $value) {
		if (!$this->_fieldCache) $this->_buildAttributeCache();

		if (isset($this->_fieldCache[$name])){
			if ($this->_fieldCache[$name] instanceof ComputedField){
				$this->_fieldCache[$name]->Set($this, $value);
			}else{
				$this->_attributeCache[$name] = $value;
			}
		}

		if (isset($this->_relationshipInstances[$name])){
			$shipInstance = $this->_relationshipInstances[$name];
			$shipInstance->Fill($value);
		}
	}
	private function _buildAttributeCache(){
		$fields = $this->GetFields();

		$this->_fieldCache = [];
		foreach($fields as $field){
			$this->_fieldCache[$field->Name] = $field;
		}
	}
	
	public function IsMagic(){
		return true;
	}
}