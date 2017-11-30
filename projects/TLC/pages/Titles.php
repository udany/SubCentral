<div class="row">
    <div class="col text-center">
        <h2>Titles Available</h2>
    </div>
</div>
<div class="row">
    <div class="col">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <tr v-for="title in titles">
                <td>{{title.Name}}</td>
                <td class="text-right">
                    <a :href="'/Title/?id='+title.Id" class="btn btn-sm btn-info">
                        Details <i class="fa fa-eye"></i>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
        <vue-link page="TitleNew" class="btn btn-sm btn-success float-right" v-if="hasPermission(permissions.TitleCreate)">
            <i class="fa fa-plus"></i> Add New Title
        </vue-link>
    </div>
</div>

<script type="application/json" id="titles-json"><?=json_encode(BaseModel::SerializeArray(Title::Select([new Filter("ParentId", "", "IS NULL", "OR", false)])))?></script>

<script>
    /** @var Title[] **/
    let titles = InlineJsonToEntityList("titles");

    let vm = new Vue({
        el: "#main",
        data: {
            user: Session.User,
            titles: titles,
            permissions: Permission.List,
        },

        computed: {
        },

        methods: {
            hasPermission: function (slug) {
                return this.user ? this.user.hasPermission(slug) : false;
            },
        }
    });
</script>