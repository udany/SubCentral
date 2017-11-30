<?php
class QueryBuilder{
    protected static function FormatQuery($query, $values){
        if (!isset($values['Join'])) $values['Join'] = '';

        $query = DNAParser::getInstance()->Format($query, $values, "@", "");

        return $query;
    }

    protected static function AddOrderClause($query, $order){
        $order = str_ireplace('ORDER BY', '', $order);
        $order = trim($order);
        if ($order){
            $query = '('. $query .') ORDER BY '.$order;
        }
        return $query;
    }

    public static function GetParamsToBind($query, $values){
        if ($query instanceof PDOStatement){
            $query = $query->queryString;
        }

	    $paramRegex = '/:([A-Z|a-z]*\w)/';

	    $queryParams = [];
	    preg_match_all($paramRegex, $query, $queryParams);

	    $queryParams = $queryParams[1] ?: [];

        $params = [];


        foreach($values as $k => $v){

            if ($v instanceof QueryComponent){
                $clauseParams = $v->GetParams();

                foreach($clauseParams as $k1 => $v1){
                    if (array_search($k1, $queryParams) !== false){
                        $params[":".$k1] = $v1;
                    }
                }
            }else{
                if (array_search($k, $queryParams) !== false){
                    $params[":".$k] = $v;
                }
            }
        }

        return $params;
    }


	public static function BuildWhereClause(&$filter){
		$where = [];
		foreach ($filter as $k => &$v){
			if (!($v instanceof QueryComponent)){
				$v = new Filter($k, $v);
			}
			array_push($where, $v->GetClause());
		}
		$whereClause = implode(' AND ', $where);

		if (!count($where)){
			$whereClause = "1 = 1";
		}

		return $whereClause;
	}

	public static function BuildSelect($query, $table, $fieldList = ['*'], &$filter = null, $orderClause = '', $groupFields = [], $joins = []){
		///// WHERE
		$whereClause = self::BuildWhereClause($filter);

		///// GROUP
		$fields = [];
		if (count($groupFields)){
			foreach($fieldList as $field){
				if ($field instanceof Field){
					if (!$field->InDatabase()) continue;

					$inGroup = array_search($field->DBName, $groupFields);
					if ($inGroup!==false){
						array_push($fields, '`'.$table.'`.`'.$field->DBName.'`');
					} else if ($field->GroupFunction){
						array_push($fields, $field->GroupFunction."(`".$table."`.`".$field->DBName."`) as `".$field->DBName."`");
					}
				}else if ($field instanceof RawField){
					array_push($fields, $field->GetFormatted($table));
				}else{
					array_push($fields, '`'.$table.'`.`'.$field.'`');
				}
			}

			$groupClause = ' GROUP BY '.implode(', ', $groupFields);
		}else{
			foreach($fieldList as $field){
				if ($field instanceof Field){
					if (!$field->InDatabase()) continue;
					
					array_push($fields, '`'.$table.'`.`'.$field->DBName.'`');
				}else if ($field instanceof RawField){
					array_push($fields, $field->GetFormatted($table));
				}else{
					array_push($fields, '`'.$table.'`.`'.$field.'`');
				}
			}
			$groupClause = '';
		}

		///// JOIN
		$joinStatement = [];
		if (count($joins)){
			foreach ($joins as $join){
				if (!($join instanceof QueryJoin)){
					$join = new QueryJoin($join['Table'], $join['On'], $join['Type']);
				}
				$joinStatement[] = $join->GetClause();
			}
		}


		$joinStatement = implode(' ', $joinStatement);
		$fields = implode(', ', $fields);


		$query = QueryBuilder::FormatQuery($query, ['Fields' => $fields, 'Join' => $joinStatement, 'WhereClause' => $whereClause]);
		$query = QueryBuilder::FormatQuery($query, ['Table' => $table]);
		$query .= $groupClause;
		if ($orderClause) $query = self::AddOrderClause($query, $orderClause);

		return Database::getInstance()->Prepare($query);
	}


	public static function BuildDelete($query, $table, &$filter = null){
		///// WHERE
		$whereClause = self::BuildWhereClause($filter);

		$query = QueryBuilder::FormatQuery($query, ['WhereClause' => $whereClause]);
		$query = QueryBuilder::FormatQuery($query, ['Table' => $table]);

		return Database::getInstance()->Prepare($query);
	}
}