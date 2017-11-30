/**
 * @property {int} Id
 * @property {int} Type
 * @property {Date} CreationDate
 * @property {Date} ModificationDate
 *
 * @property {string} Description
 * @property {string} Content
 * @property {number} ContentFormat
 * @property {number} Rating
 *
 * @property {Title} Title
 * @property {Media} Media
 * @property {Language} Language
 * @property {Group[]} Groups
 * @property {User[]} Contributors
 * @property {ArtifactRating[]} Ratings
 * @property {ArtifactCorrection[]} Corrections
 *
 * @extends ORM.Entity
 * @constructor
 */
function Artifact(){
    this.Parent(null, arguments, Artifact);
}
Artifact.inherit(Entity);

Artifact.Attributes = [
    new Entity.Attributes.Integer('Id'),
    new Entity.Attributes.Integer('Type'),
    new Entity.Attributes.UnixTimestamp('CreationDate'),
    new Entity.Attributes.UnixTimestamp('ModificationDate'),

    new Entity.Attributes.String('Description'),
    new Entity.Attributes.String('Content'),
    new Entity.Attributes.Integer('ContentFormat'),
    new Entity.Attributes.Float('Rating'),

    new Entity.Attributes.Integer('TitleId'),
    new Entity.Attributes.Integer('MediaId'),
    new Entity.Attributes.Integer('LanguageId'),

    new Entity.Attributes.Entity('Title', false),
    new Entity.Attributes.Entity('Media', false),
    new Entity.Attributes.Entity('Language', false),
    new Entity.Attributes.EntityList('Groups', false),
    new Entity.Attributes.EntityList('Contributors', false),
    new Entity.Attributes.EntityList('Ratings', false),
    new Entity.Attributes.EntityList('Corrections', false),
];
Entity.ClassMap.Register(Artifact);

Artifact.prototype.getFormat = function () {
    return Artifact.ContentFormat.extensions[this.ContentFormat];
};

Artifact.prototype.hasRating = function (userId) {
    let r = this.Ratings.filter(x => x.User.Id === userId);
    return r.length ? r[0] : null;
};

Artifact.prototype.updateRating = function (userId) {
    if (!this.Ratings.length) return;

    let r = this.Ratings.map(x=> x.Value).reduce((v, x) => v + x);
    return this.Rating = r/this.Ratings.length;
};

Artifact.Type = {
    Subtitle: 1,
    Translation: 2,

    strings: [
        '', 'Subtitle', 'Translation'
    ]
};

Artifact.ContentFormat = {
    Txt: 1,
    Srt: 2,
    Ass: 3,

    extensions: [
        '', 'txt', 'srt', 'ass'
    ]
};


/**
 * @property {int} Id
 * @property {Date} Date
 * @property {string} Comment
 * @property {number} Value
 *
 * @property {int} UserId
 * @property {int} ArtifactId
 *
 * @property {User} User
 * @property {Artifact} Artifact
 *
 * @extends ORM.Entity
 * @constructor
 */
function ArtifactRating(){
    this.Parent(null, arguments, ArtifactRating);
}
ArtifactRating.inherit(Entity);

ArtifactRating.Attributes = [
    new Entity.Attributes.Integer('Id'),
    new Entity.Attributes.Integer('UserId'),
    new Entity.Attributes.Integer('ArtifactId'),
    new Entity.Attributes.UnixTimestamp('Date'),

    new Entity.Attributes.String('Comment'),
    new Entity.Attributes.Integer('Value'),

    new Entity.Attributes.Entity('User', false),
    new Entity.Attributes.Entity('Artifact', false)
];
Entity.ClassMap.Register(ArtifactRating);


/**
 * @property {int} Id
 * @property {Date} Date
 * @property {Object} MetaData
 * @property {string} Description
 * @property {Boolean} Accepted
 *
 * @property {int} UserId
 * @property {int} ArtifactId
 *
 * @property {User} User
 * @property {Artifact} Artifact
 *
 * @extends ORM.Entity
 * @constructor
 */
function ArtifactCorrection(){
    this.MetaData = {};

    this.Parent(null, arguments, ArtifactCorrection);
}
ArtifactCorrection.inherit(Entity);

ArtifactCorrection.Attributes = [
    new Entity.Attributes.Integer('Id'),
    new Entity.Attributes.UnixTimestamp('Date'),
    new Entity.Attributes.Json('MetaData'),
    new Entity.Attributes.String('Description'),
    new Entity.Attributes.Boolean('Accepted', true),

    new Entity.Attributes.Integer('UserId'),
    new Entity.Attributes.Integer('ArtifactId'),

    new Entity.Attributes.Entity('User', false),
    new Entity.Attributes.Entity('Artifact', false)
];
Entity.ClassMap.Register(ArtifactCorrection);