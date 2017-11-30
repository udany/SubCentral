<?PHP View::Config($page); ?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title><?PHP View::Title($page); ?>SUB Central</title>

        <meta name="viewport" content="width=device-width, user-scalable=yes">

        <!--Incluir o jQuery-->
        <script src="/static/libs/jquery/jquery-3.2.1.js"></script>

        <!--Incluir o Bootstrap-->
        <script src="/static/libs/bootstrap/js/popper.js"></script>
        <script src="/static/libs/bootstrap/js/bootstrap.js"></script>
        <link rel="stylesheet" href="/static/css/bootstrap.cyborg.min.css">

        <!--Incluir o Font Awesome-->
        <link rel="stylesheet" href="/static/libs/font-awesome/css/font-awesome.css">

        <!--Incluir o Vue-->
        <script src="/static/libs/vue/vue.js"></script>

        <script type="text/javascript" src="/static/js/libs/toastr/toastr.min.js"></script>
        <link rel="stylesheet" type="text/css" href="/static/js/libs/toastr/toastr.min.css"/>

        <?= ScriptDependency::Current()->IncludeScript('orm/Entity') ?>
        <?= ScriptDependency::Current()->IncludeScript('Loading') ?>

        <?= ScriptDependency::Current()->IncludeScript('Bootsvue') ?>

        <?= ScriptDependency::Current()->IncludeScript('Language') ?>
        <?= ScriptDependency::Current()->IncludeScript('User') ?>
        <?= ScriptDependency::Current()->IncludeScript('Group') ?>
        <?= ScriptDependency::Current()->IncludeScript('Title') ?>
        <?= ScriptDependency::Current()->IncludeScript('Artifact') ?>

        <?= ScriptDependency::Current()->IncludeScript('Components/autocomplete') ?>
        <?= ScriptDependency::Current()->IncludeScript('libs/bootstrap-datetimepicker.min') ?>


        <link rel="stylesheet" type="text/css" href="/static/css/main.css"/>

        <!-- User -->
        <script type="application/json"
                id="user-json"><?= json_encode($currentUser ? $currentUser->Serialize() : null) ?></script>
        <!-- Languages -->
        <script type="application/json"
                id="languages-json"><?= json_encode(BaseModel::SerializeArray(Language::Select())) ?></script>

        <script type="text/javascript">
            Session.Id = '<?=session_id(); ?>';
            Session.Timestamp = '<?php $time = time(); echo $time; ?>';
            Session.Hash = '<?=SessionHash(session_id(), $time); ?>';
            Session.Logged = <?=$_SESSION['logged'] ? 1 : 0; ?>;
            Session.URL = '<?=GetProjectUrl(); ?>';
            Session.SiteURL = '<?=GetProjectUrl(); ?>';
            Session.actionPrefix = 'do';
            Session.controllerMethodPattern = "{0}.{1}";

            Session.User = InlineJsonToEntity('user');
            Session.Languages = InlineJsonToEntityList('languages');

            Entity.Options.Save.controller = "Entity";
            Entity.Options.Save.method = "Save";

            Entity.Options.Delete.controller = "Entity";
            Entity.Options.Delete.method = "Delete";

            Entity.Options.Select.controller = "Entity";
            Entity.Options.Select.method = "Select";

            toastr.options = {
                "closeButton": false,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "showDuration": "300",
                "hideDuration": "300",
                "timeOut": "1000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "swing",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            function toast(type, msg, title, timeout) {
                timeout = timeout !== null ? {timeOut: timeout} : null;
                return toastr[type](msg, title, timeout);
            }
        </script>

    </head>
    <body class="no-select scrollable">
        <?= VueParser::Inline('Pane', GetProjectDirectory('_shared') . 'static/vue/') ?>

        <nav class="navbar navbar-expand-md navbar-expand-lg navbar-dark bg-dark" id="main-nav">
            <div class="container">
                <a class="navbar-brand" href="/">SUB Central</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Titles">Titles</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Groups">Groups</a>
                        </li>
                        <li class="nav-item" v-if="hasPermission(permissions.AdminPanel)">
                            <a class="nav-link" href="/Admin">Admin</a>
                        </li>
                    </ul>

                    <section id="logged">
                        <template v-if="user">
                            <span>
                                {{user.Name}} &middot;
                                <vue-link controller="Auth" method="Logout" :params="{'origin-url': url}">Logout</vue-link>
                            </span>
                            <div class="avatar avatar-sm pull-right">
                                <img :src="user.Avatar"/>
                            </div>
                        </template>
                        <template v-else>
                            <vue-link class="btn btn-sm btn-primary" controller="Auth" method="FacebookLogin" :params="{'origin-url': url}">Facebook Login</vue-link>
                        </template>
                    </section>
                </div>
            </div>
        </nav>
        <script>
            let navVm = new Vue({
                el: "#main-nav",
                data: {
                    user: Session.User,
                    permissions: Permission.List,
                    location: window.location
                },

                computed: {
                    url: function () {
                        let url = this.location.href;
                        let path = this.location.pathname;
                        if (path === "/") return "";

                        let idx = url.indexOf(path);

                        return url.substr(idx);
                    }
                },

                methods: {
                    hasPermission: function (slug) {
                        return this.user ? this.user.hasPermission(slug) : false;
                    }
                }
            });
        </script>

        <div class="container main" style="position: relative;">
            <section id="main">
                <?PHP View::Load($page); ?>
            </section>
        </div>
    </body>
</html>