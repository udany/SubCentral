<?PHP

/**
 * @property int Id
 * @property string Name
 * @property string Description
 * @property User[] Members
 */
class Group extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'group';
}

Group::SetRelationships([
    'Members'=>(new RelationshipOneToMany(
        'GroupMember',
        'Id',
        'GroupId'))->Autoload(true),
]);

Group::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

    (new Field('Name'))
        ->SetDatabaseDescriptor('varchar', 512),

    (new Field('Description'))
        ->SetDatabaseDescriptor('text'),
]);