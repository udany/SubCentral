<?php
require_once("setup.php");

$json = true;
$jsonPretty = false;
Controller::CallFromRequest();

//if($_GET["action"] != "") {
//    $temp = explode('.', $_GET["action"]);
//    if (count($temp) == 2){
//        $fileString = $temp[0];
//        $action = $temp[1];
//    }else{
//        $fileString = $_GET["action"];
//        $action = $_GET["action"];
//    }
//
//    //LogMessage("File: ".$fileString." | Action: ".$action);
//    if (isset($_GET["returnTo"])){
//        $returnTo = $_GET["returnTo"];
//    }
//    if (isset($_POST["returnTo"])){
//        $returnTo = $_POST["returnTo"];
//    }
//
//    $result = Controller::Call($fileString, $action);
//
//    if (isset($returnTo)){
//        $result['redirect'] = $returnTo;
//    }
//
//    if ($json){
//        print_r(json_encode($result, $jsonPretty ? JSON_PRETTY_PRINT : 0));
//        die();
//    }else{
//        if(isset($returnTo)) {
//            Redirect($returnTo);
//        }
//    }
//}
?>