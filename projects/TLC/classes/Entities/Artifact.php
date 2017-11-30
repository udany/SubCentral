<?PHP

/**
 * @property int Id
 * @property int Type
 * @property int CreationDate
 * @property int ModificationDate
 *
 * @property string Description
 * @property string Content
 * @property int ContentFormat
 * @property double Rating
 *
 * @property int TitleId
 * @property int MediaId
 * @property int LanguageId
 *
 * @property Title Title
 * @property Media Media
 * @property Language Language
 * @property Group[] Groups
 * @property User[] Contributors
 * @property ArtifactRating[] Ratings
 * @property ArtifactCorrection[] Corrections
 */
class Artifact extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'artifact';


	public function __construct($id = null)
    {
        $this->CreationDate = time();
        parent::__construct($id);
    }


    public function getRating(){
	    if (!count($this->Ratings)) return 0;

	    $sum = array_reduce($this->Ratings, function ($value, $rating){ return $value+$rating->Value; }, 0);

	    return $sum/count($this->Ratings);
    }

    public function Save($insert = null)
    {
        $this->ModificationDate = time();

        return parent::Save($insert);
    }
}

Artifact::SetRelationships([
    'Title' => (new RelationshipManyToOne(
        'Title',
        'TitleId',
        true))->OnDelete('CASCADE'),

    'Media' => (new RelationshipManyToOne(
        'Media',
        'MediaId',
        true))->OnDelete('CASCADE'),

    'Language' => (new RelationshipManyToOne(
        'Language',
        'LanguageId',
        true))->OnDelete('CASCADE'),

    'Groups'=>(new RelationshipManyToMany(
        'Group',
        'Id',
        'ArtifactId',
        'Id',
        'GroupId',
        'ArtifactGroup'))->Autoload(true),

    'Contributors'=>(new RelationshipManyToMany(
        'User',
        'Id',
        'ArtifactId',
        'Id',
        'UserId',
        'ArtifactUser'))->Autoload(true),

    'Ratings'=>(new RelationshipOneToMany(
        'ArtifactRating',
        'Id',
        'ArtifactId'))->Autoload(true),

    'Corrections'=>(new RelationshipOneToMany(
        'ArtifactCorrection',
        'Id',
        'ArtifactId'))->Autoload(true),
]);

Artifact::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

    (new Field('Type'))
        ->SetDatabaseDescriptor('smallint', 2),

    (new Field('CreationDate'))
        ->SetDatabaseDescriptor('int', 11),

    (new Field('ModificationDate'))
        ->SetDatabaseDescriptor('int', 11),

    (new Field('Description'))
        ->SetDatabaseDescriptor('text'),

    (new Field('Content'))
        ->SetDatabaseDescriptor('text'),

    (new Field('ContentFormat'))
        ->SetDatabaseDescriptor('smallint', 2),

    (new ComputedField('Rating', 'getRating'))
        ->SetDatabaseDescriptor('double'),

    (new IntegerField('TitleId'))
        ->SetDatabaseDescriptor('int', 11),

    (new IntegerField('MediaId'))
        ->SetDatabaseDescriptor('int', 11),

    (new IntegerField('LanguageId'))
        ->SetDatabaseDescriptor('int', 11),
]);