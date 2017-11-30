<?php
function Save(){
	$objData = $_POST['object'];
	$objData = json_decode($objData, true);
	$obj = BaseModel::FromArray($objData);
	$obj->SaveAndSetId();
	$obj->SaveRelationships();

	return $obj->Serialize();
}
function Delete(){
	$objData = $_POST['object'];
	$objData = json_decode($objData, true);
	$obj = BaseModel::FromArray($objData);
	$obj->Delete();
	return true;
}
function Select(){
	$c = $_POST['class'];
	$f = json_decode($_POST['filter'], true);

	$r = $c::Select($f);

	BaseModel::SelectRelationships($r);

	return BaseModel::SerializeArray($r);
}