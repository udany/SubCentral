<?php
/* Filter class for the DNA Framework system
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2016
 */
abstract class BaseFilter {

    /**
     * @var string Order Clause
     */
    public $OrderClause = "";

    public function __construct($order){
        if ($order) $this->OrderClause = $order;
    }

    public $pageNumber = null;
    public $pageSize = 100;

    /**
     * @abstract
     * @return array
     */
    abstract public function Serialize();
}
?>