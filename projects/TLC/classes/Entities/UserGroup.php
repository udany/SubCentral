<?PHP

/**
 * @property int Id
 * @property string Name
 * @property Permission[] Permissions
 */
class UserGroup extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'user_group';
}

UserGroup::SetRelationships([
    'Permissions'=>(new RelationshipManyToMany(
        'Permission',
        'Id',
        'UserGroupId',
        'Id',
        'PermissionId',
        'UserGroupPermission'))->Autoload(true),
]);

UserGroup::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

	(new Field('Name'))
		->SetDatabaseDescriptor('varchar', 512),
]);