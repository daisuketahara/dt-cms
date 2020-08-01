<template>
    <v-container fluid>
        <transition-group name="fade" enter-active-class="animated fadeIn">
            <div v-if="loaded && menu_id > 0" v-bind:key="menu_id">
                <v-btn class="mb-3" outlined x-small fab :dark="darkmode" @click="gotoList">
                    <v-icon x-small>fal fa-arrow-left</v-icon>
                </v-btn>
                <div class="row">
                    <div class="col-md-6">















                        <ul class="menu-editor-list list-group mb-3">
                            <li v-for="(item, index) in menu" class="list-group-item" @click="setActive" :data-id="index">
                                <span v-html="item.icon"></span>
                                {{translations[item.label] || item.label}}
                                <span  v-if="item.submenu != undefined && item.submenu.length > 0">
                                    <button v-if="collapse[index]" class="btn btn-sm btn-link" @click="setCollapse" :data-id="index">{{translations.collapse || 'Collapse'}}</button>
                                    <button v-else class="btn btn-sm btn-link" @click="setCollapse" :data-id="index">{{translations.expand || 'Expand'}}</button>
                                </span>
                                <span>
                                    <button :disabled="disable_buttons" class="btn btn-sm btn-danger float-right ml-2" @click="removeItem" :data-id="index"><i class="far fa-trash-alt" :data-id="index"></i></button>
                                    <button :disabled="disable_buttons" class="btn btn-sm btn-secondary float-right ml-2" @click="editItem" :data-id="index"><i class="fas fa-pencil-alt" :data-id="index"></i></button>
                                    <button :disabled="disable_buttons" v-if="index > 0" class="btn btn-sm btn-secondary float-right ml-1" @click="moveItem" :data-id="index" data-dir="up"><i class="fas fa-angle-up" :data-id="index" data-dir="up"></i></button>
                                    <button :disabled="disable_buttons" v-if="index < menu.length - 1" class="btn btn-sm btn-secondary float-right ml-1" @click="moveItem" :data-id="index" data-dir="down"><i class="fas fa-angle-down" :data-id="index" data-dir="down"></i></button>
                                </span>
                                <transition name="slide">
                                    <ul v-if="item.submenu != undefined && item.submenu.length > 0 && collapse[index] == true">
                                        <li v-for="(subitem, subindex) in item.submenu" class="list-group-item" :data-parent-id="index" :data-id="subindex">
                                            <span v-if="subitem.icon" v-html="subitem.icon"></span>
                                            {{translations[subitem.label] || subitem.label}}
                                            <span>
                                                <button :disabled="disable_buttons" class="btn btn-sm btn-danger float-right ml-2" @click="removeItem" :data-parent="index" :data-id="subindex"><i class="far fa-trash-alt" :data-parent="index" :data-id="subindex"></i></button>
                                                <button :disabled="disable_buttons" class="btn btn-sm btn-secondary float-right ml-2" @click="editItem" :data-id="subindex" :data-sub="index"><i class="fas fa-pencil-alt" :data-id="subindex" :data-sub="index"></i></button>
                                                <button :disabled="disable_buttons" v-if="subindex > 0" class="btn btn-sm btn-secondary float-right ml-1" @click="moveItem" :data-id="subindex" data-dir="up" :data-sub="index"><i class="fas fa-angle-up" :data-id="subindex" data-dir="up" :data-sub="index"></i></button>
                                                <button :disabled="disable_buttons" v-if="subindex < item.submenu.length - 1" class="btn btn-sm btn-secondary float-right ml-1" @click="moveItem" :data-id="subindex" data-dir="down" :data-sub="index"><i class="fas fa-angle-down" :data-id="subindex" data-dir="down" :data-sub="index"></i></button>
                                            </span>
                                        </li>
                                    </ul>
                                </transition>
                            </li>
                        </ul>
                        <div class="row">
                            <div class="col">
                                <button class="btn btn-sm btn-primary" @click="saveMenu">{{translations.save || 'Save'}}</button>
                            </div>
                            <div class="col text-right">
                                <button class="btn btn-sm btn-secondary" @click="addItem">{{translations.add_menu_item || 'Add menu item'}}</button>
                            </div>
                        </div>

                    </div>
                    <div v-if="item_id > 0 || item_create" class="col-md-6">
                        <form v-on:submit.prevent="updateMenu">
                            <div v-if="item_create" class="form-group">
                                <label for="item-parent">{{translations.parent || 'Parent'}}</label>
                                <select id="item-parent" name="item-parent" class="form-control" v-model="item.parent_id">
                                    <option value=""></option>
                                    <option v-for="(item, index) in menu" :value="index">{{translations[item.label] || item.label}}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="item-label">{{translations.text || 'Text'}}</label>
                                <input type="text" id="item-label" name="item-label" class="form-control" v-model="item.label">
                                <span v-if="item.label != undefined && item.label != '' && translations[item.label] == undefined" class="badge badge-warning">{{translations.translation_unknown || 'Translation does not exist'}}</span>
                            </div>
                            <div class="form-group">
                                <label for="item-text">{{translations.icon || 'Icon'}}</label>
                                <input type="text" id="item-icon" name="item-icon" class="form-control" v-model="item.icon">
                            </div>
                            <div class="form-group">
                                <label for="item-permission">{{translations.route || 'Route'}}</label>
                                <div class="row">
                                    <div class="col-8 pr-0">
                                        <input type="text" id="item-route" name="item-route" class="form-control" v-model="item.route">
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-secondary w-100" v-on:click.prevent="showRoutes">{{translations.select_route || 'Select route'}}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="checkbox">
                                <label for="'item-active">
                                    <input type="checkbox" id="item-active" name="item-active" v-model="item.active">
                                    {{translations.active || 'Active'}}
                                </label>
                            </div>
                            <button class="btn btn-sm btn-secondary">{{translations.ok || 'OK'}}</button>
                        </form>
                    </div>
                </div>
            </div>
            <div v-else-if="loaded" v-bind:key="menu_id">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group">
                            <li v-for="menu in menus" class="list-group-item">
                                {{menu.name}}
                                <button class="btn btn-sm btn-danger float-right ml-2" @click="removeMenu" :data-id="menu.id">
                                    <i class="far fa-trash-alt" :data-id="menu.id"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary float-right" @click="editMenu" :data-id="menu.id">
                                    {{translations.edit || 'Edit'}}
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <form class="form-inline" v-on:submit.prevent="createMenu">
                            <div class="form-group mb-2">
                                <label for="menu-name">{{translations.new_menu || 'Create new menu'}}</label>
                                <input type="text" class="form-control mx-2" id="menu-name" name="menu-name" v-model="new_menu_name" placeholder="">
                            </div>
                            <button type="submit" class="btn btn-secondary mb-2">{{translations.create || 'Create'}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </transition-group>
        <modal name="route-modal" width="80%" height="90%">
            <div class="p-4">
                <h3 v-if="available_pages.length > 0">{{translations.pages || 'Pages'}}</h3>
                <div v-if="available_pages.length > 0" class="row">
                    <div v-for="item in available_pages" class="col-sm-6 col-md-4 col-lg-3 mb-2">
                        <button class="btn btn-secondary w-100" @click="setRoute" :data-id="item.id" :data-route="item.route">
                            {{translations[item.label] || item.label}}<br>
                            <span class="font-xs">{{item.route}}</span>
                        </button>
                    </div>
                </div>
                <h3 v-if="available_app.length > 0">{{translations.modules || 'Modules'}}</h3>
                <div v-if="available_app.length > 0" class="row">
                    <div v-for="item in available_app" class="col-sm-6 col-md-4 col-lg-3 mb-2">
                        <button class="btn btn-secondary w-100" @click="setRoute" :data-id="item.id" :data-route="item.route">
                            {{translations[item.label] || item.label}}<br>
                            <span class="font-xs">{{item.route}}</span>
                        </button>
                    </div>
                </div>
                <h3 v-if="available_admin.length > 0">{{translations.admin || 'Admin'}}</h3>
                <div v-if="available_admin.length > 0" class="row">
                    <div v-for="item in available_admin" class="col-sm-6 col-md-4 col-lg-3 mb-2">
                        <button class="btn btn-secondary w-100" @click="setRoute" :data-id="item.id" :data-route="item.route">
                            {{translations[item.label] || item.label}}<br>
                            <span class="font-xs">{{item.route}}</span>
                        </button>
                    </div>
                </div>
            </div>
        </modal>
    </v-container>
</template>

<script>

    export default {

        name: "menueditor",
        data() {
            return {
                loaded: false,
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                },
                menus: [],
                menu_id: 0,
                menu: [],
                new_menu_name: '',
                item: {},
                item_index: 0,
                item_id: 0,
                item_create: false,
                modal: false,
                collapse: [],
                isShow: false,
                disable_buttons: false,
                available_pages: [],
                available_app: [],
                available_admin: [],
            }
        },
        created() {
            this.getMenus();
            this.getRoutes();
        },
        computed: {
            translations () {
                return this.$store.state.translations;
            },
            darkmode () {
                return this.$store.state.darkmode;
            }
        },
        methods: {
            getMenus: function() {

                this.$axios.get('/api/v1/navigation/list/', {headers: this.headers})
                    .then(response => {
                        this.menus = JSON.parse(response.data)['data'];
                        this.loaded = true;
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                        this.loaded = true;
                    });
            },
            getMenu: function() {
                this.collapse = [];
                this.$axios.get('/api/v1/navigation/get-to-edit/'+this.menu_id+'/', {headers: this.headers})
                    .then(response => {
                        this.menu = JSON.parse(response.data)['data'];
                        for (var i = 0; i < this.menu.length; i++) {
                            this.collapse[i] = false;
                        }
                        this.disable_buttons = false;
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            createMenu: function() {

                let params = {};
                params['name'] = this.new_menu_name;

                let url = '/api/v1/navigation/create/';

                this.$axios.put(url, params, {headers: this.headers})
                    .then(response => {
                        var result = JSON.parse(response.data);

                        if (result.success) {
                            this.$store.commit('setAlert', {type: 'success', message: translations.saved || "Saved", autohide: true});
                            this.new_menu_name = '';
                            this.getMenus();
                        } else {
                            this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                        }
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            removeMenu: function(event) {

                var self = this;

                this.$modal.show('dialog', {
                    title: 'Alert!',
                    text: this.translations.confirm_delete_menu + ' ' + this.translations.want_proceed,
                    buttons: [{
                        title: this.translations.cancel,
                        handler: () => {
                            this.$modal.hide('dialog');
                        }
                    },
                    {
                        title: this.translations.confirm,
                        handler: () => {

                            this.$modal.hide('dialog');
                            this.$axios.delete('/api/v1/navigation/delete/' + event.target.dataset.id + '/', {headers: this.headers})
                                .then(response => {
                                    var result = JSON.parse(response.data);
                                    if (result.success) {
                                        self.getMenus();
                                        this.$store.commit('setAlert', {type: 'success', message: translations.delete_confirmation || 'Deleted', autohide: true});
                                    } else {
                                        this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                                    }
                                })
                                .catch(e => {
                                    this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                                    this.$modal.hide('dialog');
                                });
                        }
                    }]
                });

            },
            getRoutes: function() {

                this.$axios.get('/api/v1/navigation/routes/', {headers: this.headers})
                    .then(response => {
                        var result = JSON.parse(response.data);
                        this.available_pages = result.pages;
                        this.available_app = result.app;
                        this.available_admin = result.admin;
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            editMenu: function() {
                this.menu_id = parseInt(event.target.dataset.id);
                this.disable_buttons = true;
                this.getMenu();
            },
            addItem: function(event) {
                this.item_create = true;
                this.disable_buttons = true;
            },
            editItem: function(event) {
                var index = parseInt(event.target.dataset.id);
                var sub = event.target.dataset.sub;

                var target = this.menu;
                if (sub) target = target[sub].submenu;

                this.item = target[index];
                this.item_index = index;
                this.item_id = target[index]['id'];
                this.item_create = false;
                this.disable_buttons = true;
            },
            updateMenu: function(event) {

                if (this.item_create) {
                    if (this.item.parent_id != undefined && this.menu[this.item.parent_id]['submenu'] != '') {
                        if (this.menu[this.item.parent_id]['submenu'] != undefined) this.menu[this.item.parent_id]['submenu'].push(this.item);
                        else this.menu[this.item.parent_id]['submenu'] = [this.item];
                    } else {
                        this.menu.push(this.item);
                    }
                }

                this.item = {};
                this.item_index = 0;
                this.item_id = 0;
                this.item_create = false;
                this.disable_buttons = false;
            },
            saveMenu: function() {

                let url = '/api/v1/navigation/update/'+ this.menu_id + '/';

                this.$axios.put(url, this.menu, {headers: this.headers})
                    .then(response => {
                        var result = JSON.parse(response.data);

                        if (result.success) {
                            this.$store.commit('setAlert', {type: 'success', message: translations.saved || "Saved", autohide: true});
                            this.$parent.$parent.getRoutes();
                        } else {
                            this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                        }
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            moveItem: function(event) {
                var index = parseInt(event.target.dataset.id);
                var dir = event.target.dataset.dir;
                var sub = event.target.dataset.sub;

                if (dir == 'up') var replace_index = index-1;
                else var replace_index = index+1;

                var target = this.menu;
                if (sub) target = target[sub].submenu;

                var row = target[index];
                var replace_row = target[replace_index];

                while (index < 0) {
                    index += target.length;
                }
                while (replace_index < 0) {
                    replace_index += target.length;
                }
                if (replace_index >= target.length) {
                    var k = replace_index - target.length + 1;
                    while (k--) {
                        target.push(undefined);
                    }
                }
                target.splice(replace_index, 0, target.splice(index, 1)[0]);

            },
            removeItem: function(event) {
                var id = event.target.dataset.id;
                var parent = event.target.dataset.parent;

                if (parent != undefined) this.menu[parent]['submenu'].splice(id, 1);
                else this.menu.splice(id, 1);
            },
            setCollapse: function(event) {
                var id = parseInt(event.target.dataset.id);
                if (this.collapse[id]) this.collapse[id] = false;
                else this.collapse[id] = true;

                // hotfix for menu collapse
                var tmp = this.menu;
                this.menu = [];
                this.menu = tmp;
            },
            setActive: function(event) {



            },
            gotoList: function(event) {
                this.menu = [];
                this.collapse = [];
                this.menu_id = 0;
            },
            showRoutes: function() {
                this.$modal.show('route-modal');
            },
            setRoute: function(event) {
                this.item.permission_id = parseInt(event.target.dataset.id);
                this.item.route = event.target.dataset.route;
                this.$modal.hide('route-modal');
            }
        }
    }
</script>

<style lang="scss" scoped>
.menu-editor-list {

    overflow: hidden;

    li:hover {
        background-color: #efefef;
    }

    ul {
        position: relative;
        bottom: -0.75rem;
        margin-left: -1.25rem;
        margin-right: -1.25rem;
        padding-left: 0;
        border-radius: 0;

        li {
            padding-left: 3rem;
            padding-right: 8rem;
            border: 0;
            border-top: 1px solid rgba(0, 0, 0, 0.125);
        }

        li:hover {
            background-color: #ddd;
        }

        li:first-child {
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }

        li:last-child {
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }
    }
}

.slide-enter-active {
   -moz-transition-duration: 0.3s;
   -webkit-transition-duration: 0.3s;
   -o-transition-duration: 0.3s;
   transition-duration: 0.3s;
   -moz-transition-timing-function: ease-in;
   -webkit-transition-timing-function: ease-in;
   -o-transition-timing-function: ease-in;
   transition-timing-function: ease-in;
}

.slide-leave-active {
   -moz-transition-duration: 0.3s;
   -webkit-transition-duration: 0.3s;
   -o-transition-duration: 0.3s;
   transition-duration: 0.3s;
   -moz-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
   -webkit-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
   -o-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
   transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
}

.slide-enter-to, .slide-leave {
   max-height: 100px;
   overflow: hidden;
}

.slide-enter, .slide-leave-to {
   overflow: hidden;
   max-height: 0;
}

</style>
