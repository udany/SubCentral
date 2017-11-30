<?PHP

/**
 * @property int Id
 * @property string Name
 * @property string Slug
 */
class Permission extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'permission';
}

Permission::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

    (new Field('Name'))
        ->SetDatabaseDescriptor('varchar', 512),

    (new Field('Slug'))
        ->SetDatabaseDescriptor('varchar', 128),
]);