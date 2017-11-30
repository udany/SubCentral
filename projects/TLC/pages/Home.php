<div class="row" id="vue">
    <div class="col text-center">
        <h2>Welcome</h2>
    </div>
</div>
<script>
    let vm = new Vue({
        el: "#vue",
        data: {
            user: Session.User,
        },

        computed: {
        },

        methods: {
        }
    });
</script>