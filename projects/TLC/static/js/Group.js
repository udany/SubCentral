/**
 * @property {int} Id
 * @property {string} Name
 * @property {string} Description
 *
 * @extends ORM.Entity
 * @constructor
 */
function Group(){
    this.Parent(null, arguments, Group);
}

Group.inherit(Entity);

Group.Attributes = [
    new Entity.Attributes.Integer('Id'),
    new Entity.Attributes.String('Name'),
    new Entity.Attributes.String('Description'),
    new Entity.Attributes.EntityList('Members', false)
];
Entity.ClassMap.Register(Group);

/**
 * @property {int} Id
 * @property {string} Name
 * @property {string} Description
 * @property {Group} Group
 *
 * @extends ORM.Entity
 * @constructor
 */
function GroupMember(){
    this.Parent(null, arguments, GroupMember);
}

GroupMember.inherit(Entity);

GroupMember.Attributes = [
    new Entity.Attributes.Integer('UserId'),
    new Entity.Attributes.Integer('GroupId'),
    new Entity.Attributes.String('Role'),
    new Entity.Attributes.Entity('User', false),
    new Entity.Attributes.Entity('Group', false)
];
Entity.ClassMap.Register(GroupMember);