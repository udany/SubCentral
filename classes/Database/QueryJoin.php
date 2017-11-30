<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Daniel
 * Date: 15/07/14
 * Time: 06:18
 * To change this template use File | Settings | File Templates.
 */

class QueryJoin {
    private $Table;
    private $Type;
    private $Alias;
    private $On;

    public function __construct($Table, $On, $Type = "LEFT", $Alias=null, $QueryAsTable=false){
        if (!$QueryAsTable){
	        $Table = str_ireplace('`', '', $Table);
        }
	    $this->Table = $QueryAsTable ? '('.$Table.')' : "`".$Table."`";
        $this->Type = $Type;
        $this->Alias = $Alias;
        $this->On = $On;
    }

    public function GetClause(){
        return $this->Type." JOIN ".$this->Table.($this->Alias ? " as `".$this->Alias.'`' : '' )." ON ".$this->On;
    }
}