<?PHP

/**
 * @property int Id
 * @property string Name
 * @property string Acronym
 */
class Language extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'language';
}

Language::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

    (new Field('Name'))
        ->SetDatabaseDescriptor('varchar', 512),

    (new Field('Acronym'))
        ->SetDatabaseDescriptor('varchar', 128),
]);