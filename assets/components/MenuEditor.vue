<template>
    <div>
        <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
            <div v-if="alert.text != '' && alert.type == 'success'" class="alert alert-success" role="alert">
                {{alert.text}}
            </div>
            <div v-else-if="alert.text != '' && alert.type == 'error'" class="alert alert-danger" role="alert">
                {{alert.text}}
            </div>
        </transition>
        <div v-if="menu_id > 0">
            <button class="btn btn-sm btn-secondary mb-3" v-on:click="gotoList">{{translations.go_back}}</button>
            <div class="row">
                <div class="col-md-6">
                    <ul class="menu-editor-list list-group mb-3">
                        <li v-for="(item, index) in menu" class="list-group-item" v-on:click="setActive" :data-id="index">
                            <span v-html="item.icon"></span>
                            {{translations[item.label] || item.label}}
                            <span  v-if="item.submenu != undefined && item.submenu.length > 0">
                                <button v-if="collapse[index]" class="btn btn-sm btn-link" v-on:click="setCollapse" :data-id="index">{{translations.collapse}}</button>
                                <button v-else class="btn btn-sm btn-link" v-on:click="setCollapse" :data-id="index">{{translations.expand}}</button>
                            </span>
                            <span v-if="item_id == 0 && !item_create">
                                <button class="btn btn-sm btn-danger float-right ml-2" v-on:click="removeItem" :data-id="index"><i class="far fa-trash-alt" :data-id="index"></i></button>
                                <button class="btn btn-sm btn-secondary float-right ml-2" v-on:click="editItem" :data-id="index"><i class="fas fa-pencil-alt" :data-id="index"></i></button>
                                <button v-if="index > 0" class="btn btn-sm btn-secondary float-right ml-1" v-on:click="moveItem" :data-id="index" data-dir="up"><i class="fas fa-angle-up" :data-id="index" data-dir="up"></i></button>
                                <button v-if="index < menu.length - 1" class="btn btn-sm btn-secondary float-right ml-1" v-on:click="moveItem" :data-id="index" data-dir="down"><i class="fas fa-angle-down" :data-id="index" data-dir="down"></i></button>
                            </span>
                            <transition name="slide">
                                <ul v-if="item.submenu != undefined && item.submenu.length > 0 && collapse[index] == true">
                                    <li v-for="(subitem, subindex) in item.submenu" class="list-group-item" :data-id="subindex">
                                        <span v-if="subitem.icon" v-html="subitem.icon"></span>
                                        {{translations[subitem.label] || subitem.label}}
                                        <span v-if="item_id == 0 && !item_create">
                                            <button class="btn btn-sm btn-danger float-right ml-2" v-on:click="removeItem" :data-id="subindex"><i class="far fa-trash-alt" :data-id="subindex"></i></button>
                                            <button class="btn btn-sm btn-secondary float-right ml-2" v-on:click="editItem" :data-id="subindex" :data-sub="index"><i class="fas fa-pencil-alt" :data-id="subindex" :data-sub="index"></i></button>
                                            <button v-if="subindex > 0" class="btn btn-sm btn-secondary float-right ml-1" v-on:click="moveItem" :data-id="subindex" data-dir="up" :data-sub="index"><i class="fas fa-angle-up" :data-id="subindex" data-dir="up" :data-sub="index"></i></button>
                                            <button v-if="subindex < item.submenu.length - 1" class="btn btn-sm btn-secondary float-right ml-1" v-on:click="moveItem" :data-id="subindex" data-dir="down" :data-sub="index"><i class="fas fa-angle-down" :data-id="subindex" data-dir="down" :data-sub="index"></i></button>
                                        </span>
                                    </li>
                                </ul>
                            </transition>
                        </li>
                    </ul>
                    <div class="row">
                        <div class="col">
                            <button class="btn btn-sm btn-primary" v-on:click="addItem">{{translations.save}}</button>
                        </div>
                        <div class="col text-right">
                            <button class="btn btn-sm btn-secondary" v-on:click="addItem">{{translations.add_menu_item}}</button>
                        </div>
                    </div>

                </div>
                <div v-if="item_id > 0 || item_create" class="col-md-6">
                    <form v-on:submit.prevent="updateMenu">
                        <div v-if="item_create" class="form-group">
                            <label for="item-parent">{{translations.parent}}</label>
                            <select id="item-parent" name="item-parent" class="form-control" v-model="item.parent_id">
                                <option v-for="(item, index) in menu" :value="index">{{translations[item.label] || item.label}}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="item-label">{{translations.text}}</label>
                            <input type="text" id="item-label" name="item-label" class="form-control" v-model="item.label">
                            <span v-if="item.label != undefined && item.label != '' && translations[item.label] == undefined" class="badge badge-warning">{{translations.translation_unknown}}</span>
                        </div>
                        <div class="form-group">
                            <label for="item-text">{{translations.icon}}</label>
                            <input type="text" id="item-icon" name="item-icon" class="form-control" v-model="item.icon">
                        </div>
                        <div class="form-group">
                            <label for="item-page">Page</label>
                            <div class="row">
                                <div class="col-8 pr-0">
                                    <input type="text" id="item-page" name="item-page" class="form-control" v-model="item.page">
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-secondary w-100" v-on:click.prevent="showPages">{{translations.select_page}}</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="item-permission">Permission</label>
                            <div class="row">
                                <div class="col-8 pr-0">
                                    <input type="text" id="item-permission" name="item-permission" class="form-control" v-model="item.permission">
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-secondary w-100" v-on:click.prevent="showRoutes">{{translations.select_route}}</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="item-custom-route">{{translations.custom_route}}</label>
                            <input type="text" id="item-custom-route" name="item-custom-route" class="form-control" v-model="item.route">
                        </div>
                        <div class="checkbox">
                            <label for="'item-active">
                                <input type="checkbox" id="item-active" name="item-active" v-model="item.active">
                                {{translations.active}}
                            </label>
                        </div>
                        <button class="btn btn-sm btn-secondary">{{translations.ok}}</button>
                    </form>
                </div>
            </div>
        </div>
        <div v-else>
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-group">
                        <li v-for="menu in menus" class="list-group-item">
                            {{menu.name}}
                            <button class="btn btn-sm btn-secondary float-right" v-on:click="editMenu" :data-id="menu.id">{{translations.edit}}</button>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <form class="form-inline">
                        <div class="form-group mb-2">
                            <label for="menu-name">{{translations.new_menu}}</label>
                            <input type="text" class="form-control mx-2" id="menu-name" name="menu-name" placeholder="">
                        </div>
                        <button type="submit" class="btn btn-secondary mb-2">{{translations.create}}</button>
                    </form>
                </div>
            </div>
        </div>

        <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
            <div v-if="modal" id="im-editor-modal">
                <div class="im-modal-backdrop">
                    <div class="im-modal-body p-4">
                        <div class="im-modal-close" v-on:click="closeModal"><i class="fas fa-times"></i></div>



                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {

        name: "menueditor",
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "Authorization" : "Bearer " + this.$store.state.apikey
                },
                menus: [],
                menu_id: 0,
                menu: [],
                item: {},
                item_index: 0,
                item_id: 0,
                item_create: false,
                modal: false,
                alert: {},
                collapse: [],
                isShow: false
            }
        },
        created() {
            this.getMenus();
        },
        computed: {
            translations () {
                return this.$store.state.translations;
            }
        },
        methods: {
            getMenus: function() {

                axios.get('/api/v1/navigation/list/', {headers: this.headers})
                    .then(response => {
                        this.menus = JSON.parse(response.data)['data'];
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            getMenu: function() {
                this.collapse = [];
                axios.get('/api/v1/navigation/get/'+this.menu_id+'/', {headers: this.headers})
                    .then(response => {
                        this.menu = JSON.parse(response.data)['data'];
                        for (var i = 0; i < this.menu.length; i++) {
                            this.collapse[i] = false;
                        }
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            createMenu: function() {

            },
            editMenu: function() {
                this.menu_id = parseInt(event.target.dataset.id);
                this.getMenu();
            },
            addItem: function(event) {
                this.item_create = true;
            },
            editItem: function(event) {
                var index = parseInt(event.target.dataset.id);
                var sub = event.target.dataset.sub;

                var target = this.menu;
                if (sub) target = target[sub].submenu;

                this.item = target[index];
                this.item_index = index;
                this.item_id = target[index]['id'];
            },
            updateMenu: function(event) {

                if (this.item_create) {
                    if (this.item.parent_id > 0) {
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
                this.menu.splice(parseInt(event.target.dataset.id), 1);
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
            launchModal: function() {
                document.getElementById('admin-content').style.zIndex = '14';
                this.modal = true;
            },
            closeModal: function() {
                this.modal = false;
                document.getElementById('admin-content').style.zIndex = '11';
            },
            setAlert: function(text, type) {
                var self = this;
                this.alert = {text: text, type: type};
                setTimeout(function() { self.alert = {}; }, 5000);
            }

        }
    }
</script>

<style lang="scss" scoped>
.menu-editor-list {

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
