<?php
function Login(){
    global $returnTo;
    if ($returnTo) $_SESSION['fbReturnTo'] = $returnTo;
    FacebookHandler::getInstance()->Login();
}
function LoginRedirect(){
    global $json;
    $json = false;

    FacebookHandler::getInstance()->ProcessFacebookLoginRedirect();

    if (isset($_SESSION['fbReturnTo'])){
        Redirect($_SESSION['fbReturnTo']);
    }else{
        Redirect("");
    }
}
function GetUser(){
    $user = FacebookHandler::getInstance()->GetFacebookUser();
    return $user;
}
function GetMe(){
    $user = FacebookHandler::getInstance()->api('/102062153777292655', 'GET');
    return $user;
}