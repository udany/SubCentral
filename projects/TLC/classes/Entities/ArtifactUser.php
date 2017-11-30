<?PHP

/**
 * Class Like
 *
 * @property int UserId
 * @property int UserGroupId
 */
class ArtifactUser extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'artifact_user';
	public static $Identifier = ['ArtifactId', 'UserId'];
	public static $insertWithId = true;
}

ArtifactUser::SetRelationships([
    'Artifact' => (new RelationshipManyToOne(
        'Artifact',
        'ArtifactId',
        false))->OnDelete('CASCADE'),

    'User' => (new RelationshipManyToOne(
        'User',
        'UserId',
        false))->OnDelete('CASCADE')
]);

ArtifactUser::SetFields([
    (new IntegerField('ArtifactId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),
    (new IntegerField('UserId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),
]);