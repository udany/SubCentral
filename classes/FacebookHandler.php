<?PHP
/* Facebook Handler top simplify access to it's API
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2015
 */

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\GraphUser;

class FacebookHandler extends Singleton {
    /**
     * @static
     * @return FacebookHandler
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    public function AddPermission($p){
        if (is_array($p)){
            $this->permissions = array_merge($p, $this->permissions);
        }else{
            array_push($this->permissions, $p);
        }
    }

    public $permissions = ['email'];
    /** @var FacebookSession */
    private $session = null;
    private $tokenSessionKey = 'facebookAccessToken';

    protected function __construct() {
        $this->dumpfile = DIR_DYNAMIC . '/' . date("Y_m_d") . "_facebook.txt";

        FacebookSession::setDefaultApplication(FACEBOOK_APP_ID, FACEBOOK_SECRET);

        if (isset($_SESSION[$this->tokenSessionKey])){
            $this->SaveToken($_SESSION[$this->tokenSessionKey]);
        }
    }

    public function VerifyToken(){
        // Get User ID
        $userId = 0;
        if($this->session){
            try {
                $r = (new FacebookRequest(
                    $this->session, 'GET', '/me'
                ))->execute()->getResponse();

                $userId = $r->id;
            } catch(Exception $e) {
                $userId = 0;
            }
        }

        return $userId ? true : false;
    }

    public function Login($additionalPermissions=[]){
        $helper = new FacebookRedirectLoginHelper(GetProjectUrl().FACEBOOK_LOGIN_REDIRECT);
        $loginUrl = $helper->getLoginUrl(array_merge($this->permissions, $additionalPermissions));
        Redirect($loginUrl);
        die();
    }

    public function ProcessFacebookLoginRedirect(){
        $helper = new FacebookRedirectLoginHelper(GetProjectUrl().FACEBOOK_LOGIN_REDIRECT);

        try {
            $session = $helper->getSessionFromRedirect();
        } catch(FacebookRequestException $ex) {
            Redirect(GetProjectUrl());
        } catch(\Exception $ex) {
            Redirect(GetProjectUrl());
        }

        $this->session = $session;
        $this->SaveToken($session->getToken());
    }

    public function SaveToken($token){
        $_SESSION[$this->tokenSessionKey] = $token;

        if (!$this->session){
            $this->session = new FacebookSession($token);
        }
    }

    /**
     * Low level function to access the API
     * @param $path string path
     * @param null $a Method (GET/POST/DELETE)
     * @param null $b Parameters (Assoc array)
     * @return mixed
     */
    public function api($path, $a = null, $b = null){
        if (!$this->VerifyToken()) return [];

        $r = (new FacebookRequest(
            $this->session, $a, $path, $b
        ))->execute();

		$graphObject = $r->getGraphObject();
		return (array)$graphObject->asArray();
        return get_object_vars($r->getResponse());
    }

    public function apiPaged($path, $a = null, $b = null){
        if (!$this->VerifyToken()) return [];

        $r = (new FacebookRequest(
            $this->session, $a, $path, $b
        ))->execute();

        $data = get_object_vars($r->getResponse());
        if (isset($data['data'])) $data = $data['data'];

        while($r->getRequestForNextPage()){
            $r = $r->getRequestForNextPage()->execute();

            $rData = get_object_vars($r->getResponse());

            if (isset($rData['data'])) array_merge($data, $rData['data']);
        }

        foreach($data as $k => $v){
            $data[$k] = get_object_vars($v);
        }

        return $data;
    }

    public function GetFacebookUser(){
        if (!$this->VerifyToken()) return [];

        $user_profile = $this->api('/me','GET');
        return $user_profile;
    }

    public function GetFacebookUserFriends(){
        if (!$this->VerifyToken()) return [];

        $user_profile = $this->apiPaged('/me/friends','GET');
        return $user_profile;
    }

    public function GetFacebookUserGroups(){
        if (!$this->VerifyToken()) return [];

        $user_profile = $this->api('/me/groups','GET');
        return $user_profile;
    }
}