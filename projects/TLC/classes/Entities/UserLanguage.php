<?PHP

/**
 * Class Like
 *
 * @property int UserId
 * @property int UserGroupId
 */
class UserLanguage extends BaseModel {
    use MagicEntity;
	public static $databaseTable = 'user_language';
	public static $Identifier = ['UserId', 'LanguageId'];
	public static $insertWithId = true;
}

UserLanguage::SetRelationships([
    'User' => (new RelationshipManyToOne(
        'User',
        'UserId',
        false))->OnDelete('CASCADE'),

    'Language' => (new RelationshipManyToOne(
        'Language',
        'LanguageId',
        false))->OnDelete('CASCADE')
]);

UserLanguage::SetFields([
    (new IntegerField('UserId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),
    (new IntegerField('LanguageId'))
        ->SetDatabaseDescriptor('int', 11)->PrimaryKey(),
]);