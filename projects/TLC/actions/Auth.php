<?php

function FacebookLogin(){
	if (isset($_GET['origin-url']) && $_GET['origin-url'] != '/'){
		$_SESSION['loginRedirectUrl'] = substr($_GET['origin-url'], 1);
	}

	$_SESSION['fbReturnTo'] = GetActionUrl('Auth', 'LoginRedirect');

	if (isset($_GET['successPage'])){
		$_SESSION['fbSuccessPage'] = $_GET['successPage'];
	}

	$fb = FacebookHandler::getInstance();
	$fb->AddPermission(['public_profile','email']);
	$fb->Login();
}

function LoginRedirect(){
	$fb = FacebookHandler::getInstance();

	$fbUser = $fb->api('/me', 'GET', ['fields'=>'id,name,email,age_range,gender']);
	$user = null;

	if (isset($fbUser['id'])){
	    /** @var BsiUser[] $user */
		$user = User::Select(['FacebookId'=>$fbUser['id']]);

		if (count($user)){
			$user = $user[0];

            $user->GetImageFromFacebook();
		}else{
			$user = new User();

			$user->Name = $fbUser['name'];
			$user->Email = $fbUser['email'];
			$user->FacebookId = $fbUser['id'];

			$user->SaveAndSetId();

			if (!$user->Id){
				throw new Exception("Failed to register user");
			}

			$user->GetImageFromFacebook();
		}
	}else{
		throw new Exception("Facebook login failed");
	}

	$user->LoginAs();

	if (isset($_SESSION['loginRedirectUrl'])){
		Redirect($_SESSION['loginRedirectUrl']);
		unset($_SESSION['loginRedirectUrl']);
	}else{
		Redirect(GetProjectUrl(isset($_SESSION['fbSuccessPage']) ? $_SESSION['fbSuccessPage'] : ''));
	}
}

function Logout(){
    User::Logout();

	if (isset($_GET['origin-url']) && $_GET['origin-url'] != '/'){
		$url = substr($_GET['origin-url'], 1);

		Redirect(GetProjectUrl().$url);
	}else{
		Redirect(GetProjectUrl());
	}
}