/**
 * @property {int} Id
 * @property {string} Name
 * @property {string} Avatar
 * @property {UserGroup[]} UserGroups
 * @property {Language[]} Languages
 * @property {GroupMember[]} GroupMembers
 *
 * @extends ORM.Entity
 * @constructor
 */
function User(){
    this.Parent(null, arguments, User);
}

User.inherit(Entity);

User.Attributes = [
    new Entity.Attributes.Integer('Id'),
    new Entity.Attributes.String('Name'),
    new Entity.Attributes.String('Avatar'),
    new Entity.Attributes.EntityList('UserGroups', false),
    new Entity.Attributes.EntityList('Languages', false),
    new Entity.Attributes.EntityList('GroupMembers', false)
];
Entity.ClassMap.Register(User);

User.prototype.hasPermission = function (slug) {
    return this.UserGroups.map(x => x.Permissions).flatten().filter(x=>x.Slug === slug).length > 0;
};



/**
 * @property {int} Id
 * @property {string} Name
 * @property {Permission[]} Permissions
 *
 * @extends ORM.Entity
 * @constructor
 */
function UserGroup(){
    this.Parent(null, arguments, UserGroup);
}
UserGroup.inherit(Entity);

UserGroup.Attributes = [
    new Entity.Attributes.Integer('Id'),
    new Entity.Attributes.String('Name'),
    new Entity.Attributes.EntityList('Permissions', false)
];
Entity.ClassMap.Register(UserGroup);

UserGroup.prototype.hasPermission = function (slug) {
    return this.Permissions.filter(x=>x.Slug === slug).length > 0;
};



/**
 * @property {int} Id
 * @property {string} Name
 * @property {string} Slug
 *
 * @extends ORM.Entity
 * @constructor
 */
function Permission(){
    this.Parent(null, arguments, Permission);
}
Permission.inherit(Entity);

Permission.Attributes = [
    new Entity.Attributes.Integer('Id'),
    new Entity.Attributes.String('Name'),
    new Entity.Attributes.String('Slug'),
];
Entity.ClassMap.Register(Permission);

Permission.List = {
    AdminPanel: "admin_panel",
    TitleCreate: "create_title",
    TitleEdit: "edit_title",
    ArtifactSubmit: "submit_artifact"
};