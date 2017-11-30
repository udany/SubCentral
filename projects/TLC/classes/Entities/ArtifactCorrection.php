<?PHP

/**
 * @property int Id
 * @property int UserId
 * @property int ArtifactId
 * @property int Date
 * @property array MetaData
 * @property string Value
 * @property string Description
 * @property bool Accepted
 *
 * @property Artifact Artifact
 */
class ArtifactCorrection extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'artifact_correction';

	public function __construct($id = null)
    {
        $this->MetaData = [];
        parent::__construct($id);
    }
}

ArtifactCorrection::SetRelationships([
    'User' => (new RelationshipManyToOne(
        'User',
        'UserId',
        true))->OnDelete('CASCADE'),

    'Artifact' => (new RelationshipManyToOne(
        'Artifact',
        'ArtifactId',
        false))->OnDelete('CASCADE'),
]);

ArtifactCorrection::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

    (new IntegerField('UserId'))
        ->SetDatabaseDescriptor('int', 11),

    (new IntegerField('ArtifactId'))
        ->SetDatabaseDescriptor('int', 11),

    (new IntegerField('Date'))
        ->SetDatabaseDescriptor('int', 11),

    (new JsonField('MetaData'))
        ->SetDatabaseDescriptor('text'),

    (new NotNullField('Value', ''))
        ->SetDatabaseDescriptor('text'),

    (new NotNullField('Description', ''))
        ->SetDatabaseDescriptor('text'),

    (new NullableBooleanField('Accepted'))
        ->SetDatabaseDescriptor('tinyint', 1)->Null(true),
]);