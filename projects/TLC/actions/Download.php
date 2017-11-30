<?php
/**
 * Created by PhpStorm.
 * User: andra
 * Date: 29/11/2017
 * Time: 02:01
 */

function Download(){
    RequireFields('id');
    $a = new Artifact($_REQUEST['id']);

    if ($a->Exists){
        $ext = ArtifactContentFormat::$ext[$a->ContentFormat];

        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="'.$a->Title->Name.'.'.$ext.'"');

        echo $a->Content;
        die();
    }
}