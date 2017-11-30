<?PHP

/**
 * Class Like
 *
 * @property int UserGroupId
 * @property int PermissionId
 */
class UserGroupPermission extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'user_group_permission';
	public static $Identifier = ['UserGroupId', 'PermissionId'];
	public static $insertWithId = true;
}

UserGroupPermission::SetRelationships([
    'UserGroup' => (new RelationshipManyToOne(
        'UserGroup',
        'UserGroupId',
        false))->OnDelete('CASCADE'),

    'Permission' => (new RelationshipManyToOne(
        'Permission',
        'PermissionId',
        false))->OnDelete('CASCADE'),
]);

UserGroupPermission::SetFields([
    (new IntegerField('UserGroupId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),
    (new IntegerField('PermissionId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),
]);