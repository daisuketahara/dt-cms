<template>
    <transition name="fadeIn" enter-active-class="animated fadeIn">
        <div v-if="loaded" class="container-fluid">
            <h1>{{translations.modules || 'Modules'}}</h1>
            <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
                <div v-if="alert.text != '' && alert.type == 'success'" class="alert alert-success" role="alert">
                    {{alert.text}}
                </div>
                <div v-else-if="alert.text != '' && alert.type == 'error'" class="alert alert-danger" role="alert">
                    {{alert.text}}
                </div>
            </transition>
            <div class="row">
                <div v-for="module in modules" class="col-lg-3">
                    <div class="module-card card mb-3">
                        <div class="card-body">
                            <span v-html="module.icon"></span>
                            <h3>{{module.name}}</h3>
                            <p>{{module.description}}</p>
                            <button v-if="module.active" class="btn btn-sm btn-danger" @click="activate" :data-id="module.id">{{translations.deactivate || 'Deactivate'}}</button>
                            <button v-else class="btn btn-sm btn-success" @click="activate" :data-id="module.id">{{translations.activate || 'Activate'}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>

    export default {

        name: "Module",
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('token')
                },
                loaded: false,
                alert: '',
                modules: []
            }
        },
        computed: {
            authenticated () {
                return this.$store.state.authenticated;
            },
            initialised () {
                return this.$store.state.init;
            },
            locales () {
                return this.$store.state.locales;
            },
            locale () {
                return this.$store.state.locale;
            },
            locale_id () {
                return this.$store.state.locale_id;
            },
            translations () {
                return this.$store.state.translations;
            }
        },
        created() {
            this.getModules();
        },
        methods: {
            getModules: function() {
                this.$axios.get('/api/v1/module/', {headers: this.headers})
                    .then(response => {
                        this.modules = JSON.parse(response.data).data;
                        this.loaded = true;
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            activate: function(event) {

                let id = event.target.dataset.id;

                this.$axios.post('/api/v1/module/activate/'+id+'/', {headers: this.headers})
                    .then(response => {
                        let result = JSON.parse(response.data).data;
                        this.getModules();
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            setAlert: function(text, type) {
                var self = this;
                this.alert = {text: text, type: type};
                setTimeout(function() { self.alert = {}; }, 5000);
            }
        }
    }
</script>

<style lang="scss">

    .module-card {

        span {
            float: right;
            i {
                font-size: 50px;
            }
        }

        h3 {
            font-size: 1.2rem;
        }

        p {
            font-size: 0.9rem;
            font-weight: 300;
        }


    }

</style>
