<div id="vue" style="display: none" v-unhide>
    <div class="py-2"></div>
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <div class="card text-white bg-dark">
                <div class="card-header" v-if="!canEdit">
                    <h5 class="card-title mb-0">{{title.Name}}</h5>
                    <p class="text-muted mb-0">{{title.Language.Name}}</p>
                    <p class="text-muted mb-0" v-if="title.Parent">
                        <i class="fa fa-level-up"></i> <a :href="'/Title/?id='+title.Parent.Id">{{title.Parent.Name}}</a>
                    </p>
                </div>
                <div class="card-header" v-if="canEdit">
                    <h5>Edit info:</h5>
                    <div class="row  no-gutters">
                        <div class="col-12">
                            <input class="form-control" v-model="title.Name" placeholder="Name">
                        </div>
                    </div>
                    <div class="row no-gutters">
                        <div class="col-10">
                            <object-select class="form-control" v-model="title.Language" :data="languages"></object-select>
                        </div>
                        <div class="col-2">
                            <button class="btn btn-block btn-primary float-right"
                                    v-on:click="saveTitle">
                                <i class="fa fa-save"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Edit title button -->
                <button class="btn btn-sm btn-info" style="position:absolute; right: 0;"
                        v-if="hasPermission(permissions.TitleEdit)" v-popover.left.hover="'Toggle Edit'"
                        v-on:click="editEnabled=!editEnabled">
                    <i class="fa fa-edit"></i>
                </button>

                <img :src="title.Image" alt="" class="card-img-top">
                <div class="card-body">
                    <!-- Children -->
                    <template v-if="canEdit || title.Children.length">
                        <vue-link page="TitleNew" targe="_blank" :params="{parent: title.Id}"
                                  class="btn btn-sm btn-secondary float-right" v-if="canEdit">
                            <i class="fa fa-plus"></i> Add
                        </vue-link>

                        <h5>Titles:</h5>
                        <ul>
                            <li v-for="title in title.Children">
                                <a :href="'/Title/?id='+title.Id">{{title.Name}}</a>
                            </li>
                        </ul>
                    </template>


                    <!-- Release Dates -->
                    <template v-if="canEdit || title.ReleaseDates.length">
                        <button class="btn btn-sm btn-secondary float-right" v-on:click="showAddRelease"
                                v-if="canEdit">
                            <i class="fa fa-plus"></i> Add
                        </button>
                        <h5>Release Date</h5>
                        <ul>
                            <li v-for="d in title.ReleaseDates">
                                {{d.Date.format('d/m/Y')}} - <i>{{d.getTypeString()}}</i>

                                <a href="#" class="text-danger float-right" v-on:click="removeRelease(d)"
                                   v-if="canEdit">
                                    &times
                                </a>
                            </li>
                        </ul>
                    </template>

                    <!-- Release Date Form -->
                    <pane ref="addReleasePane" :expanded="false" class="row no-gutters mb-2">
                        <div class="col-5">
                            <date-input class="form-control form-control-sm" v-model="releaseDate.Date"></date-input>
                        </div>
                        <div class="col-5">
                            <select class="form-control form-control-sm" v-model="releaseDate.Type">
                                <option :value="j" v-for="(i, j) in releaseTypes._values">{{releaseTypes.Data(j)}}
                                </option>
                            </select>
                        </div>
                        <div class="col">
                            <button class="btn btn-sm btn-block btn-primary float-right" :disabled="!canAddRelease"
                                    v-on:click="addRelease">
                                <i class="fa fa-save"></i>
                            </button>
                        </div>
                    </pane>

                    <!-- Media -->
                    <template v-if="canEdit || title.Media.length">
                        <button class="btn btn-sm btn-secondary float-right"  v-on:click="showAddMedia"
                                v-if="canEdit">
                            <i class="fa fa-plus"></i> Add
                        </button>
                        <h5>Media</h5>
                        <ul class="list-group">
                            <li class="list-group-item" v-for="m in title.Media">
                                <button class="btn btn-sm float-right" :class="selectedMedia == m ? 'btn-success' : 'btn-primary'" v-on:click="selectMedia(m)">
                                    <i class="fa fa-filter"></i>
                                </button>

                                <a :href="m.Link" target="_blank">{{m.Name}}</a> <br>
                                <small><i>{{m.ReleaseDate.format('d/m/Y')}}</i></small>
                            </li>
                        </ul>
                    </template>

                    <!-- Media Form -->
                    <pane ref="addMediaPane" :expanded="false">
                        <div class="row  no-gutters">
                            <div class="col-6">
                                <input class="form-control form-control-sm" v-model="media.Name" placeholder="Name">
                            </div>
                            <div class="col-6">
                                <date-input class="form-control form-control-sm" v-model="media.ReleaseDate" placeholder="Release"></date-input>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-10">
                                <drop-textarea class="form-control form-control-sm" v-model="media.Link" placeholder="Link" rows="1"></drop-textarea>
                            </div>
                            <div class="col-2">
                                <button class="btn btn-sm btn-block btn-primary float-right" :disabled="!canSaveMedia"
                                        v-on:click="saveMedia">
                                    <i class="fa fa-save"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row no-gutters">
                        </div>
                    </pane>

                </div>
                <div class="card-footer" v-if="hasPermission(permissions.ArtifactSubmit) && title.Media.length">
                    <button class="btn btn-sm btn-success btn-block"
                            :class="{disabled: !title.Media.length}"
                            v-popover.top.hover="title.Media.length ? '' : 'No media available'"
                            v-on:click="newArtifact()">
                        <i class="fa fa-send"></i> Submit Translation
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-sm-12">
            <masonry-grid class="row mb-4" selector=".artifact-card" :items="filteredArtifacts">
                <!-- Artifact cards -->
                <template slot-scope="props" slot="item">
                    <div class="col-md-6 col-sm-12 artifact-card" v-if="key === 'item'" v-for="(a, key) in props">
                        <div class="card text-white bg-dark mt-4 mt-md-3">
                            <div class="card-header">
                                <span class="float-right text-right" v-if="a.Rating">
                                    <span style="font-size: 1.2em">
                                        {{a.Rating.pad(0,1)}}
                                        <i class="fa fa-star text-info"></i>
                                    </span>
                                    <span class="text-muted">
                                        <br>{{a.Ratings.length}} rating{{a.Ratings.length > 1 ? 's' : ''}}
                                    </span>
                                </span>

                                <p class="mb-0">
                                    <b style="font-size: 1.2em">{{artifactType.strings[a.Type]}}</b>
                                </p>
                                <p class="mb-0">
                                    <i class="mr-2">{{a.Media.Name}}</i>

                                    <span class="badge badge-primary">
                                        {{a.Language.Acronym}}
                                    </span>
                                    <span class="badge badge-secondary">
                                        {{a.getFormat()}}
                                    </span>
                                </p>
                            </div>
                            <div class="card-body pb-0" style="min-height: 200px; overflow: auto;">
                                <!-- Info -->
                                <p v-if="a.Groups.length" class="mb-0">
                                    Submitted by
                                    <template v-for="(g, idx) in a.Groups">
                                        <template v-if="idx > 0">,</template>
                                        <a :href="'/Group/?id='+g.Id">{{g.Name}}</a>
                                    </template>
                                </p>

                                <p v-if="a.Contributors.length">
                                    Made by
                                    <template v-for="(u, idx) in a.Contributors">
                                        <template v-if="idx > 0">,</template>
                                        <a :href="'/User/?id='+u.Id" >{{u.Name}}</a>
                                    </template>
                                </p>

                                <p v-if="a.Description">{{a.Description}}</p>

                                <div class="text-muted">
                                    <p class="text-sm-center">
                                        Created on {{a.CreationDate.format('d/m/Y H:i:s')}}
                                        <template v-if="a.CreationDate.getTime() != a.ModificationDate.getTime()" >
                                            <br>Modified on {{a.ModificationDate.format('d/m/Y H:i:s')}}
                                        </template>
                                    </p>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row no-gutters">
                                    <div class="col-4 pr-1">
                                        <vue-link class="btn btn-block btn-sm btn-success" controller="Download" :params="{id: a.Id}">
                                            <i class="fa fa-download"></i> Download
                                        </vue-link>
                                    </div>

                                    <div class="col-4 px-1">
                                        <div class="btn-group btn-block">
                                            <button class="btn btn-block btn-sm btn-info" :class="{'dropdown-toggle': user}" data-toggle="dropdown" :disabled="!user">
                                                <i class="fa fa-star"></i> {{user && a.hasRating(user.Id) ? a.hasRating(user.Id).Value : "Rate"}}
                                            </button>
                                            <div class="dropdown-menu p-4" v-if="user">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-info" v-for="i in 5"
                                                            v-on:click="rate(a, i)" :disabled="a.hasRating(user.Id) ? a.hasRating(user.Id).Value === i : false">
                                                        {{i}}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-4 pl-1">
                                        <button class="btn btn-block btn-sm btn-warning" :disabled="!user"
                                                v-on:click="correct(a)">
                                            <i class="fa fa-edit"></i> Correct
                                        </button>
                                    </div>

                                    <div class="col-4 pr-1 mt-2" v-if="canEditArtifact(a)">
                                        <button class="btn btn-block btn-sm btn-primary" :disabled="!user"
                                                v-on:click="editArtifact(a)">
                                            <i class="fa fa-pencil"></i> Edit
                                        </button>
                                    </div>

                                    <div class="col-4 px-1 mt-2" v-if="canEditArtifact(a)">
                                        <button class="btn btn-block btn-sm btn-warning" :disabled="!a.Corrections.length"
                                                v-on:click="seeCorrections(a)">
                                            <i class="fa fa-exclamation-triangle"></i> Corrections
                                        </button>
                                    </div>
                                </div>
                                <!-- Actions -->
                            </div>
                        </div>
                    </div>
                </template>
                <!-- /Artifact cards -->
            </masonry-grid>
        </div>

        <!-- Correction List Modal -->
        <modal title="Artifact corrections" ref="correctionListModal" :actions="{Done: closeCorrectionList}">
            <div slot="body">
                <div class="row mb-4" v-for="c in artifact.Corrections">
                    <div class="col">
                        <div class="card"
                             :class="{'border-primary': c.Accepted===null, 'border-success': c.Accepted, 'border-danger': c.Accepted===false}">
                            <div class="card-body">
                                <p>{{c.Description}}</p>
                                <p class="float-right text-muted">
                                    by {{c.User.Name}} on {{c.Date.format('d/m/Y - H:i:s')}}
                                </p>
                            </div>
                            <div class="card-footer text-right" v-if="c.Accepted === null"
                                 :class="{'border-primary': c.Accepted===null, 'border-success': c.Accepted, 'border-danger': c.Accepted===false}" >
                                <button class="btn btn-success" v-on:click="replyCorrection(c, true)">
                                    <i class="fa fa-check"></i> Accept
                                </button>
                                <button class="btn btn-danger" v-on:click="replyCorrection(c, false)">
                                    <i class="fa fa-close"></i> Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </modal>
        <!-- /Correction List Modal -->

        <!-- Correction Modal -->
        <modal title="Send correction" ref="correctionModal" :actions="{Send: saveCorrection}">
            <div slot="body">
                <div class="row">
                    <!-- Desc -->
                    <div class="col">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" v-model="correction.Description" rows="5"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="alert alert-primary">
                            Be clear and concise, this helps the chances of having it accepted!
                        </div>
                    </div>
                </div>
            </div>
        </modal>
        <!-- /Correction Modal -->

        <!-- Artifact Modal -->
        <modal :title="artifact.Id ? 'Edit Artifact' : 'New Artifact'" ref="artifactModal" :actions="{Send: saveArtifact}" size="lg">
            <div slot="body">
                <div class="row">
                    <!-- Type -->
                    <div class="col">
                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control" v-model="artifact.Type">
                                <option :value="i" v-for="(name, i) in artifactType.strings" v-if="name">{{name}}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Language -->
                    <div class="col">
                        <div class="form-group">
                            <label>Language</label>
                            <object-select class="form-control" v-model="artifact.Language" :data="languages"></object-select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Description -->
                    <div class="col">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" v-model="artifact.Description" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Media -->
                    <div class="col">
                        <div class="form-group">
                            <label>Media</label>
                            <object-select class="form-control" v-model="artifact.Media" :data="title.Media"></object-select>
                        </div>
                    </div>

                    <!-- Format -->
                    <div class="col">
                        <div class="form-group">
                            <label>Format</label>
                            <select class="form-control" v-model="artifact.ContentFormat" :disabled="artifact.Type !== artifactType.Subtitle">
                                <option :value="i" v-for="(name, i) in contentFormat.extensions">{{name}}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Content (Just drop file over the text area)</label>
                            <drop-textarea class="form-control" v-model="artifact.Content" rows="8"></drop-textarea>
                        </div>
                    </div>
                </div>
            </div>
        </modal>
        <!-- /Artifact Modal -->
    </div>
</div>

<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.js"></script>

<script type="application/json" id="title-json"><?= json_encode($title->Serialize()) ?></script>

<script>
    Vue.component('masonry-grid', {
        template: `<div ref="grid">
    <slot name="item" :item="item" v-for="item in items"></slot>
</div>`,
        data: function () {
            return {
                el: null
            }
        },
        props: {
            selector: {type: String, default: ".grid-item"},
            items: {type: Array, default: ()=>[]},
        },
        computed: {
            classList: function(){
                return {
                    fade: this.fade
                }
            }
        },
        watch: {
            "items": function () {
                let that = this;
                this.$nextTick(function () {
                    that.el.masonry('reloadItems').masonry();
                })
            }
        },
        methods: {
        },
        mounted: function () {
            this.el = $(this.$refs.grid);

            this.$nextTick(function () {
                let that = this;
                that.el.masonry({
                    itemSelector: that.selector
                });
            })
        }
    });

    /** @var Title[] **/
    let title = InlineJsonToEntity("title");

    let vm = new Vue({
        el: "#vue",
        data: {
            editEnabled: false,

            user: Session.User,
            permissions: Permission.List,
            title: title,

            selectedMedia: null,

            ///Release date
            releaseDate: new TitleReleaseDate(),
            releaseTypes: TitleReleaseDate.Types,

            ///Media
            media: new Media(),

            //Artifact
            artifact: new Artifact(),
            artifactType: Artifact.Type,
            contentFormat: Artifact.ContentFormat,
            languages: Session.Languages,

            correction: new ArtifactCorrection()
        },

        computed: {
            canAddRelease: function () {
                return this.releaseDate.Date && this.releaseDate.Type
            },
            canSaveMedia: function () {
                let media = this.media;
                return media.ReleaseDate && media.Name && media.Link
            },

            filteredArtifacts: function () {
                let r;
                if (this.selectedMedia){
                    r = this.title.Artifacts.filter(x => x.Media.Id === this.selectedMedia.Id);
                }else{
                    r = this.title.Artifacts;
                }
                return r.sort((a,b) => a.Id - b.Id).sort((a,b) => a.Rating - b.Rating).reverse();
            },
            canEdit: function () {
                return this.hasPermission(this.permissions.TitleEdit) && this.editEnabled;
            },
        },

        methods: {
            hasPermission: function (slug) {
                return this.user ? this.user.hasPermission(slug) : false;
            },

            saveTitle: function () {
                this.title.Save();
            },

            /**
             * @param {Artifact} a
             */
            canEditArtifact: function (a) {
                if (!this.user ) return false;

                return a.Contributors.map(x=>x.Id).indexOf(this.user.Id) >= 0;
            },

            showAddRelease: function () {
                this.$refs.addReleasePane.toggle();
            },
            addRelease: function () {
                if (!this.canAddRelease) return;

                this.releaseDate.Title = this.title.Clone();
                this.releaseDate.Save({
                    callback: function () {
                        vm.title.ReleaseDates.push(vm.releaseDate);
                        vm.releaseDate = new TitleReleaseDate();
                        vm.$refs.addReleasePane.hide();
                    }
                });
            },
            /**
             * @param {TitleReleaseDate} d
             */
            removeRelease: function (d) {
                d.Delete({
                    callback: function () {
                        let idx = vm.title.ReleaseDates.indexOf(d);
                        vm.title.ReleaseDates.splice(idx, 1);
                    }
                });
            },


            showAddMedia: function () {
                this.$refs.addMediaPane.toggle();
            },
            saveMedia: function () {
                if (!this.canSaveMedia) return;

                this.media.Title = this.title.Clone();
                this.media.Save({
                    callback: function () {
                        vm.title.Media.push(vm.media);
                        vm.media = new Media();
                        vm.$refs.addReleasePane.hide();
                    }
                });
            },


            selectMedia: function (m) {
                if (this.selectedMedia === m){
                    this.selectedMedia = null;
                }else{
                    this.selectedMedia = m;
                }
            },


            /**
             *
             * @param {Artifact} artifact
             * @param {number} value
             */
            rate: function (artifact, value) {
                let rating = artifact.hasRating(this.user.Id);
                if (!rating){
                    rating = new ArtifactRating();
                    rating.Artifact = artifact;
                    rating.User = this.user;
                }

                rating.Date = new Date();
                rating.Value = value;

                rating.Save({
                    callback: function () {
                        if (artifact.Ratings.indexOf(rating) === -1){
                            artifact.Ratings.push(rating);
                        }
                        artifact.updateRating();
                    }
                });
            },

            correct: function (a) {
                this.correction = new ArtifactCorrection();
                this.correction.Artifact = a;

                this.$refs.correctionModal.show();
            },
            saveCorrection: function () {
                let that = this;
                let correction = this.correction;
                let artifact = this.correction.Artifact;

                correction.Date = new Date();
                correction.Artifact = artifact.Clone();
                correction.User = this.user.Clone();

                correction.Save({
                    callback: function () {
                        that.$refs.correctionModal.hide();
                        artifact.Corrections.push(correction);
                    }
                });
            },


            newArtifact: function () {
                this.artifact = new Artifact();
                this.$refs.artifactModal.show();
            },
            editArtifact: function (a) {
                this.artifact = a;

                this.$refs.artifactModal.show();
            },
            saveArtifact: function () {
                let title = this.title;
                let artifact = this.artifact;

                let idx = artifact.Contributors.map(x=>x.Id).indexOf(this.user.Id);

                if (idx === -1){
                    artifact.Contributors.push(this.user.Clone());
                }

                artifact.Title = this.title.Clone();
                if (artifact.Type !== this.artifactType.Subtitle){
                    artifact.ContentFormat = Artifact.ContentFormat.Txt;
                }

                artifact.Save({
                    callback: function () {
                        if (title.Artifacts.indexOf(artifact) === -1){
                            title.Artifacts.push(artifact);
                        }
                    }
                });

                this.$refs.artifactModal.hide();
            },

            seeCorrections: function (a) {
                this.artifact = a;
                this.$refs.correctionListModal.show();
            },
            closeCorrectionList: function (a) {
                this.$refs.correctionListModal.hide();
            },
            replyCorrection: function (c, val) {
                c.Accepted = val;
                c.Save();
            }
        }
    });
</script>