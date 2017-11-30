<div id="vue">


    <div class="row">
        <div class="col-12">
            <h2>User Groups</h2>
        </div>
        <div class="col-md-4" v-for="g in groups">
            <div class="card">

                <div class="card-body">
                    <h5 class="my-0">{{g.Name}}</h5>
                </div>


                <ul class="list-group list-group-flush">
                    <li class="list-group-item" v-for="p in permissions"
                        :class="g.hasPermission(p.Slug) ? 'text-success' : 'text-warning'">
                        {{p.Name}}

                        <button class="btn btn-sm btn-secondary float-right" v-if="!g.hasPermission(p.Slug)"
                                v-popover.top.hover="'Add Permission'"
                                v-on:click="addPermission(g,p)">
                            <i class="fa fa-plus"></i>
                        </button>

                        <button class="btn btn-sm btn-secondary float-right" v-if="g.hasPermission(p.Slug)"
                                v-popover.top.hover="'Take Permission'"
                                v-on:click="removePermission(g,p)">
                            <i class="fa fa-minus"></i>
                        </button>
                    </li>
                </ul>

                <div class="card-footer text-right">
                    <button class="btn btn-success" v-on:click="save(g)">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>

<script type="application/json"
        id="user-groups-json"><?= json_encode(BaseModel::SerializeArray(UserGroup::Select())) ?></script>
<script type="application/json"
        id="permissions-json"><?= json_encode(BaseModel::SerializeArray(Permission::Select())) ?></script>

<script>
    /** @var UserGroup[] **/
    let groups = InlineJsonToEntityList("user-groups");
    /** @var Permission[] **/
    let permissions = InlineJsonToEntityList("permissions");

    let vm = new Vue({
        el: "#vue",
        data: {
            groups: groups,
            permissions: permissions
        },

        computed: {},

        methods: {
            /**
             * @param {UserGroup} group
             * @param {Permission} p
             */
            addPermission: function (group, p) {
                group.Permissions.push(p.Clone());
            },
            /**
             * @param {UserGroup} group
             * @param {Permission} p
             */
            removePermission: function (group, p) {
                let gP = group.Permissions.filter(x => x.Id === p.Id);

                if (!gP.length) return;

                let idx = group.Permissions.indexOf(gP[0]);

                group.Permissions.splice(idx, 1);
            },

            save: function (group) {
                group.Save();
            }
        }
    });
</script>