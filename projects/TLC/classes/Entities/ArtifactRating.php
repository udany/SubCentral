<?PHP

/**
 * @property int Id
 * @property int UserId
 * @property int ArtifactId
 * @property int Date
 * @property string Comment
 * @property int Value
 *
 * @property User User
 * @property Artifact Artifact
 */
class ArtifactRating extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'artifact_rating';
}

ArtifactRating::SetRelationships([
    'User' => (new RelationshipManyToOne(
        'User',
        'UserId',
        true))->OnDelete('CASCADE'),

    'Artifact' => (new RelationshipManyToOne(
        'Artifact',
        'ArtifactId',
        false))->OnDelete('CASCADE'),
]);

ArtifactRating::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

    (new IntegerField('UserId'))
        ->SetDatabaseDescriptor('int', 11),

    (new IntegerField('ArtifactId'))
        ->SetDatabaseDescriptor('int', 11),

    (new IntegerField('Date'))
        ->SetDatabaseDescriptor('int', 11),

    (new NotNullField('Comment', ''))
        ->SetDatabaseDescriptor('text'),

    (new IntegerField('Value'))
        ->SetDatabaseDescriptor('smallint', 2),
]);