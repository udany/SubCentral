<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Daniel
 * Date: 15/07/14
 * Time: 06:18
 * To change this template use File | Settings | File Templates.
 */

class QueryFilterIn extends Filter {
    protected $field;
	protected $bindField;
	protected $values;
	protected $mode;
	protected $operator;
	protected $bound;

    public function __construct($field, $values){
        $this->field = $field;
        $this->bindField = $this->SanitizeField($field);

        if (!is_array($values)) $values = [$values];
        $this->values = $values;
    }

    public function GetClause(){
	    $values = implode(', ', $this->values);

        return '('.$this->field.' IN ('.$values.') )';
    }

    public function GetParams(){
        return [];
    }
}