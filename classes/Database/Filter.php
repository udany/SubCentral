<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Daniel
 * Date: 15/07/14
 * Time: 06:18
 * To change this template use File | Settings | File Templates.
 */

class Filter implements QueryComponent {
    protected $field;
	protected $bindField;
	protected $values;
	protected $mode;
	protected $operator;
	protected $bound;

    public function __construct($field, $values, $operator = "=", $mode = "OR", $bound=true, $bindField=null){
        $this->field = $field;
        $this->bindField = $bindField ? $this->SanitizeField($bindField) : $this->SanitizeField($field);

	    if (is_array($values) && isset($values['values'])){
		    if (!is_array($values['values'])) $values['values'] = [$values['values']];

		    $this->values = $values['values'];

		    $this->operator = $values['operator'] ? $values['operator'] : '=';
		    $this->mode = $values['mode'] ? $values['mode'] : 'OR';
		    $this->bound = $values['bound'] ? $values['bound'] : true;
	    }else{
		    if (!is_array($values)) $values = [$values];

		    $this->values = $values;

		    $this->operator = $operator;
		    $this->mode = $mode;
		    $this->bound = $bound;
	    }
    }

    public function GetClause(){
        $clauses = [];

        foreach($this->values as $k=>$val){
            $key = $this->bindField.$k;
            if (is_array($val)){
                $operator = $val[1];
                $val = $val[0];
            }else{
                $operator = $this->operator;
            }

	        $escapedField = '`'.implode('`.`', explode('.', str_ireplace('`', '',$this->field))).'`';

            if ($this->bound){
                $clauses[] = $escapedField . " " . $operator . " :" . $key;
            }else{
                $clauses[] = $escapedField . " " . $operator . " " . $val;
            }
        }

        $clause = '('.implode(' '.$this->mode.' ', $clauses).')';

        return $clause;
    }

    public function GetParams(){
        $params = [];

        if ($this->bound){
            foreach($this->values as $k=>$val){
                $key = $this->bindField.$k;

                if (is_array($val)){
                    $val = $val[0];
                }
                $params[$key] = $val;
            }
        }

        return $params;
    }
    protected function SanitizeField($field){
        return preg_replace('/[^0-9A-Za-z]/', '', $field);
    }
}