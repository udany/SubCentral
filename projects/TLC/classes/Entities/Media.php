<?PHP

/**
 * @property int Id
 * @property int ReleaseDate
 * @property int Type
 * @property string Name
 * @property string Link
 * @property int TitleId
 * @property Title Title
 */
class Media extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'media';
}

Media::SetRelationships([
    'Title' => (new RelationshipManyToOne(
        'Title',
        'TitleId',
        true))->OnDelete('CASCADE')
]);

Media::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

    (new Field('ReleaseDate'))
        ->SetDatabaseDescriptor('int', 11),

    (new Field('Type'))
        ->SetDatabaseDescriptor('smallint', 2),

    (new Field('Name'))
        ->SetDatabaseDescriptor('varchar', 512),

    (new Field('Link'))
        ->SetDatabaseDescriptor('varchar', 512),

    (new IntegerField('TitleId'))
        ->SetDatabaseDescriptor('int', 11),
]);