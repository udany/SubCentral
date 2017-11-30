<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 03/06/2015
 * Time: 22:30
 */

interface QueryComponent {

    /**
     * @return string
     */
    public function GetClause();

    /**
     * @return array
     */
    public function GetParams();
}