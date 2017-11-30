<div id="vue">
    <div class="row">
        <div class="col-12">
            <h2>New Title</h2>


        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="form-group">
                <label>Name</label>
                <input class="form-control" v-model="title.Name">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="form-group">
                <label>Language</label>
                <object-select class="form-control" v-model="title.Language" :data="languages"></object-select>
            </div>
        </div>
    </div>

    <div class="row" v-if="title.Parent">
        <div class="col">
            <div class="form-group">
                <label>Parent</label>
                <input class="form-control" disabled v-model="title.Parent.Name" />
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col text-right">
            <button class="btn btn-success float-right"
                    v-on:click="saveTitle">
                <i class="fa fa-save"></i> Save
            </button>
        </div>
    </div>

</div>

<script type="application/json"
        id="parent-json"><?= json_encode($parent) ?></script>

<script>
    /** @var UserGroup[] **/
    let parent = InlineJsonToEntity("parent");

    let vm = new Vue({
        el: "#vue",
        data: {
            title: new Title({Parent: parent, Language: Session.Languages[0]}),
            user: Session.User,
            languages: Session.Languages
        },

        computed: {},

        methods: {
            saveTitle: function () {
                let title = this.title;
                title.Save({
                    callback: function () {
                        if (title.Id){
                            window.location = Session.GetPageUrl('Title', {id: title.Id})
                        }
                    }
                });
            }
        }
    });
</script>