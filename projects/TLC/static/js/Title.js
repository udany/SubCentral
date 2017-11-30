/**
 * @property {int} Id
 * @property {string} Name
 * @property {string} Image
 *
 * @property {Title} Parent
 * @property {Language} Language
 * @property {Title[]} Children
 * @property {TitleReleaseDate[]} ReleaseDates
 * @property {Media[]} Media
 * @property {Artifact[]} Artifacts
 *
 * @extends ORM.Entity
 * @constructor
 */
function Title(){
    this.Parent(null, arguments, Title);
}

Title.inherit(Entity);

Title.Attributes = [
    new Entity.Attributes.Integer('Id'),
    new Entity.Attributes.String('Name'),
    new Entity.Attributes.String('Image'),
    new Entity.Attributes.Integer('ParentId', true),
    new Entity.Attributes.Integer('LanguageId'),

    new Entity.Attributes.Entity('Parent', false),
    new Entity.Attributes.Entity('Language', false),
    new Entity.Attributes.EntityList('Children', false),
    new Entity.Attributes.EntityList('ReleaseDates', false),
    new Entity.Attributes.EntityList('Media', false),
    new Entity.Attributes.EntityList('Artifacts', false),
];
Entity.ClassMap.Register(Title);

/**
 * @property {int} Id
 * @property {Date} Date
 * @property {int} Type
 * @property {Title} Title
 *
 * @extends ORM.Entity
 * @constructor
 */
function TitleReleaseDate(){
    this.Parent(null, arguments, TitleReleaseDate);
}

TitleReleaseDate.inherit(Entity);

TitleReleaseDate.Attributes = [
    new Entity.Attributes.Integer('Id'),
    new Entity.Attributes.UnixTimestamp('Date'),
    new Entity.Attributes.Integer('Type'),

    new Entity.Attributes.Entity('Title', false)
];
Entity.ClassMap.Register(TitleReleaseDate);

TitleReleaseDate.prototype.getTypeString = function(){
    return TitleReleaseDate.Types.Data(this.Type);
};

TitleReleaseDate.Types = new Enum([
    "",
    "Theatrical",
    "Television",
    "HomeVideo",
    "OnDemand",
    "PublicWeb"
],[
    "",
    "Theatrical",
    "Television",
    "Home Video",
    "On Demand",
    "Web"
]);

/**
 * @property {int} Id
 * @property {Date} ReleaseDate
 * @property {int} Type
 * @property {string} Name
 * @property {string} Link
 * @property {Title} Title
 *
 * @extends ORM.Entity
 * @constructor
 */
function Media(){
    this.Parent(null, arguments, Media);
}

Media.inherit(Entity);

Media.Attributes = [
    new Entity.Attributes.Integer('Id'),
    new Entity.Attributes.UnixTimestamp('ReleaseDate'),
    new Entity.Attributes.Integer('Type'),
    new Entity.Attributes.String('Name'),
    new Entity.Attributes.String('Link'),

    new Entity.Attributes.Entity('Title', false)
];
Entity.ClassMap.Register(Media);