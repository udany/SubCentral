<?PHP

/**
 * @property int Id
 * @property int Date
 * @property int UserId
 * @property string Content
 * @property int EntityType
 * @property int EntityId
 *
 */
class Comment extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'comment';
}

Comment::SetRelationships([
    'User' => (new RelationshipManyToOne(
        'User',
        'UserId',
        false))->OnDelete('CASCADE'),
]);

Comment::SetFields([
	(new IntegerField('Id'))
	     ->SetDatabaseDescriptor('int', 11)->AutoIncrement(true)->PrimaryKey(),

    (new Field('ReleaseDate'))
        ->SetDatabaseDescriptor('int', 11),

    (new Field('UserId'))
        ->SetDatabaseDescriptor('int', 11),

    (new Field('Content'))
        ->SetDatabaseDescriptor('text'),

    (new Field('EntityType'))
        ->SetDatabaseDescriptor('smallint', 2),

    (new Field('EntityId'))
        ->SetDatabaseDescriptor('int', 11),
]);