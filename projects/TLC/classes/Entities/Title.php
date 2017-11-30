<?PHP

/**
 * @property int Id
 * @property string Name
 * @property int ParentId
 *
 * @property Title Parent
 * @property Title[] Children
 * @property TitleReleaseDate[] ReleaseDates
 * @property Media[] Media
 * @property Artifact[] Artifacts
 */
class Title extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'title';

    public function __construct($id = null)
    {
        $this->Image = new DynamicFile(DIR_DYNAMIC.'/title/', function($obj){
            return $obj->Id;
        }, 'TLC', $this);

        parent::__construct($id);
    }

    /** @var  DynamicFile */
    public $Image;
    public function GetImageUrl(){
        return $this->Image->Exists() ? $this->Image->GetUrl() : "";
    }
}

Title::SetRelationships([
    'Parent' => (new RelationshipManyToOne(
        'Title',
        'ParentId',
        false))->OnDelete('CASCADE'),

    'Language' => (new RelationshipManyToOne(
        'Language',
        'LanguageId',
        false))->OnDelete('CASCADE'),

    'Children'=>(new RelationshipOneToMany(
        'Title',
        'Id',
        'ParentId'))->Autoload(false),

    'Artifacts'=>(new RelationshipOneToMany(
        'Artifact',
        'Id',
        'TitleId'))->Autoload(false),

    'ReleaseDates'=>(new RelationshipOneToMany(
        'TitleReleaseDate',
        'Id',
        'TitleId'))->Autoload(true)->setQueryOrder("Date ASC"),

    'Media'=>(new RelationshipOneToMany(
        'Media',
        'Id',
        'TitleId'))->Autoload(true),
]);


Title::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

    (new Field('Name'))
        ->SetDatabaseDescriptor('varchar', 512),

    (new NullableIntegerField('LanguageId'))
        ->SetDatabaseDescriptor('int', 11)->Null(true),

    (new NullableIntegerField('ParentId'))
        ->SetDatabaseDescriptor('int', 11)->Null(true),

    (new ComputedField('Image', 'GetImageUrl'))
        ->InDatabase(false)
]);