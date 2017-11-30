<?PHP

/**
 * @property int Id
 * @property int Date
 * @property int Type
 * @property int TitleId
 * @property Title Title
 */
class TitleReleaseDate extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'title_release_date';
}

TitleReleaseDate::SetRelationships([
    'Title' => (new RelationshipManyToOne(
        'Title',
        'TitleId',
        true))->OnDelete('CASCADE')
]);

TitleReleaseDate::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

    (new Field('Date'))
        ->SetDatabaseDescriptor('int', 11),

    (new Field('Type'))
        ->SetDatabaseDescriptor('smallint', 11),

    (new IntegerField('TitleId'))
        ->SetDatabaseDescriptor('int', 11),
]);