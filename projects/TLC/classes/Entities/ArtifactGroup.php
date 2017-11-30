<?PHP

/**
 * Class Like
 *
 * @property int UserId
 * @property int UserGroupId
 */
class ArtifactGroup extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'artifact_group';
	public static $Identifier = ['ArtifactId', 'GroupId'];
	public static $insertWithId = true;
}

ArtifactGroup::SetRelationships([
    'Artifact' => (new RelationshipManyToOne(
        'Artifact',
        'ArtifactId',
        false))->OnDelete('CASCADE'),

    'Group' => (new RelationshipManyToOne(
        'Group',
        'GroupId',
        false))->OnDelete('CASCADE')
]);

ArtifactGroup::SetFields([
    (new IntegerField('ArtifactId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),
    (new IntegerField('GroupId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),
]);