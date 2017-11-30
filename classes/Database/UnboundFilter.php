<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Daniel
 * Date: 15/07/14
 * Time: 06:18
 * To change this template use File | Settings | File Templates.
 */

class UnboundFilter extends Filter {
    public function __construct($field, $values, $operator = "=", $mode = "OR"){
	    parent::__construct($field, $values, $operator, $mode, false);
    }
}