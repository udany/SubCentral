<?PHP

/**
 * Class Like
 *
 * @property int UserId
 * @property int UserGroupId
 * @property string Role
 */
class GroupMember extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'group_member';
	public static $Identifier = ['UserId', 'GroupId'];
	public static $insertWithId = true;
}

GroupMember::SetRelationships([
    'User' => (new RelationshipManyToOne(
        'User',
        'UserId',
        true))->OnDelete('CASCADE'),

    'Group' => (new RelationshipManyToOne(
        'Group',
        'GroupId',
        true))->OnDelete('CASCADE')
]);

GroupMember::SetFields([
    (new IntegerField('UserId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),

    (new IntegerField('GroupId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),

    (new NotNullField('Role',''))
        ->SetDatabaseDescriptor('varchar', 128),
]);