/**
 * @property {int} Id
 * @property {string} Name
 * @property {string} Acronym
 *
 * @extends ORM.Entity
 * @constructor
 */
function Language(){
    this.Parent(null, arguments, Language);
}

Language.inherit(Entity);

Language.Attributes = [
    new Entity.Attributes.Integer('Id'),
    new Entity.Attributes.String('Name'),
    new Entity.Attributes.String('Acronym')
];
Entity.ClassMap.Register(Language);