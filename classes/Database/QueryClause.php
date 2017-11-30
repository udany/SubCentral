<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Daniel
 * Date: 15/07/14
 * Time: 06:18
 * To change this template use File | Settings | File Templates.
 */

class QueryClause implements QueryComponent {
    private $clauses;
    private $operator;

    public function __construct($clauses, $operator = "OR"){
        $this->clauses = $clauses;
        $this->operator = $operator;
    }

    public function GetClause(){
        $clauses = [];

        foreach($this->clauses as $val){
            if (is_string($val)){
                $clauses[] = '('.$val.')';
            }else if ($val instanceof QueryComponent){
	            $clauses[] = $val->GetClause();
            }
        }

        $clause = '('.implode(' '.$this->operator.' ', $clauses).')';

        return $clause;
    }

    public function GetParams(){
        $params = [];

        foreach($this->clauses as $val){
            if ($val instanceof QueryComponent){
                $clauseParams = $val->GetParams();
                foreach($clauseParams as $k=>$v){
                    if (isset($params[$k]) && $params[$k]!=$v){
                        throw new Exception("Error while generating clause, conflicting parameters are to be bound to the same alias.\nPS: There's no way to fiz this yet, so, go fix this by making bindField settable on Filter.");
                    }
                }

                $params = array_merge($params, $clauseParams);
            }
        }

        return $params;
    }
}