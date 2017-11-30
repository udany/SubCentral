<?PHP

/**
 * Class Like
 *
 * @property int UserId
 * @property int UserGroupId
 */
class UserGroupUser extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'user_group_user';
	public static $Identifier = ['UserId', 'UserGroupId'];
	public static $insertWithId = true;
}

UserGroupUser::SetRelationships([
    'User' => (new RelationshipManyToOne(
        'User',
        'UserId',
        false))->OnDelete('CASCADE'),

    'UserGroup' => (new RelationshipManyToOne(
        'UserGroup',
        'UserGroupId',
        false))->OnDelete('CASCADE')
]);

UserGroupUser::SetFields([
    (new IntegerField('UserId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),
    (new IntegerField('UserGroupId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),
]);