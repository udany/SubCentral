<?PHP
/**
 * @property int Id
 * @property string Name
 * @property string Email
 * @property int CreationDate
 * @property string FacebookId
 * @property UserGroup[] UserGroups
 * @property Language[] Languages
 * @property GroupMember[] GroupMembers
 */
class User extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'user';

	function __construct($id=null) {
		$this->CreationDate = time();

        $this->Avatar = new DynamicFile(DIR_DYNAMIC.'/avatar/', function($obj){
            return $obj->Id;
        }, 'BSI', $this);

		parent::__construct($id);
	}
	
	/** @var  DynamicFile */
	public $Avatar;

	//// Auth ////
	public function CookieHash($time){
	    return hash('sha256', $this->CreationDate . $this->Id . $time);
	}
	public function SetCookies() {
		$time = time();

		$status = true;
		$status = $status && setcookie('uid', $this->Id, $time+60*60*24*30, '/');
		$status = $status && setcookie('time', $time, $time+60*60*24*30, '/');
		$status = $status && setcookie('hash', $this->CookieHash($time), $time+60*60*24*30, '/');
	}
	public static function UnsetCookies() {
		setcookie('uid', 0, 0, '/');
		setcookie('time', 0, 0, '/');
		setcookie('hash', 0, 0, '/');
	}

    public function LoginAs(){
        $_SESSION['user'] = $this->Id;
    }
	public static function Logout(){
		unset($_SESSION['user']);
		$_SESSION['logout'] = 1;
	}

	public static function LoggedUser(){
		if (isset($_SESSION['logout'])){
			self::UnsetCookies();

			unset($_SESSION['user']);
			unset($_SESSION['logout']);

			return null;
		}

		if (!isset($_SESSION['user']) && isset($_COOKIE['uid'])){
			$user = new User($_COOKIE['uid']);

			if ($_COOKIE['hash'] == $user->CookieHash($_COOKIE['time'])){
				$user->LoginAs();
				return $user;
			}
		}
		if (isset($_SESSION['user'])) {
			$user = new User($_SESSION['user']);
			if ($user->Exists){
				if (!isset($_COOKIE['uid']) || !$_COOKIE['uid']){
					$user->SetCookies();
				}

				return $user;
			}else{
				self::Logout();
			}
		}
		return null;
	}
	public static function LoggedUserOrDie(){
		$user = self::LoggedUser();
		if ($user){
			return $user;
		}else{
			die();
		}
	}
	public static function LoggedUserOrHome(){
		$user = self::LoggedUser();
		if ($user){
			return $user;
		}else{
			Redirect(GetProjectUrl());
			die();
		}
	}

    //// Avatar ////
	public function GetImageFromFacebook(){
		$fb = FacebookHandler::getInstance();
		$r = $fb->api('/'.$this->FacebookId.'/picture/?width=720&redirect=false', 'GET');

		if (isset($r['url'])){
			file_put_contents($this->Avatar->GetPath(), file_get_contents($r['url']));
		}
	}
    public function GetAvatarUrl(){
	    return $this->Avatar->Exists() ? $this->Avatar->GetUrl() : "";
    }

    public function HasPermission($slug){
        $mdArray = array_map(function ($el){ return $el->Permissions; }, $this->UserGroups);

        $flatArray = array();
        array_walk_recursive($mdArray, function($a) use (&$flatArray) { $flatArray[] = $a; });

        return count(array_filter($flatArray, function ($p) use ($slug){ return $p->Slug == $slug; })) > 0;
    }
}

User::SetRelationships([
    'UserGroups'=>(new RelationshipManyToMany(
        'UserGroup',
        'Id',
        'UserId',
        'Id',
        'UserGroupId',
        'UserGroupUser'))->Autoload(true),

    'Languages'=>(new RelationshipManyToMany(
        'Language',
        'Id',
        'UserId',
        'Id',
        'LanguageId',
        'UserLanguage'))->Autoload(true),

    'GroupMembers'=>(new RelationshipOneToMany(
        'GroupMember',
        'Id',
        'UserId'))->Autoload(true),
]);

User::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

	(new IntegerField('CreationDate'))
		->SetDatabaseDescriptor('int', 11),

	(new NotNullField('Name',''))
		->SetDatabaseDescriptor('varchar', 256),

	(new NotNullField('Email',''))
		->SetDatabaseDescriptor('varchar', 512)->Sensitive(true),

	(new Field('FacebookId'))
		->SetDatabaseDescriptor('varchar', 128),

    (new ComputedField('Avatar', 'GetAvatarUrl'))
        ->InDatabase(false)
]);