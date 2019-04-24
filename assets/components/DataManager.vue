<template>
    <transition name="fade" enter-active-class="animated fadeInRight" leave-active-class="animated fadeOutDown">
        <div v-if="mode === 'table'" id="data-manager-table" class="table-responsive">
            <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
                <div v-if="alert.text != '' && alert.type == 'success'" class="alert alert-success" role="alert">
                    {{alert.text}}
                </div>
                <div v-else-if="alert.text != '' && alert.type == 'error'" class="alert alert-danger" role="alert">
                    {{alert.text}}
                </div>
            </transition>
            <table class="data-manager-table table table-hover table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th width="30"><input type="checkbox" v-on:click="selectAllDelete"></th>
                        <th v-for="column in columns" v-if="column.show_list == true" :data-column="column.id">
                            {{column.label}}
                            <a class="table-sort" v-on:click="sortlist" :data-id="column.id" data-dir="asc">
                                <i v-if="column.id === sort.id && sort.dir === 'desc'" class="fa fa-sort-down" aria-hidden="true"></i>
                                <i v-else-if="column.id === sort.id && sort.dir === 'asc'" class="fa fa-sort-up" aria-hidden="true"></i>
                                <i v-else class="fa fa-sort" aria-hidden="true"></i>
                            </a>
                        </th>
                        <th width="160">
                            <button v-if="api.insert" class="btn btn-success btn-sm" v-on:click="add"><i class="fa fa-plus" aria-hidden="true"></i> {{translations.new}}</button>
                            <button v-if="special.insert" class="btn btn-success btn-sm" :data-url="special.insert" v-on:click="customButton"><i class="fa fa-plus" aria-hidden="true" :data-url="special.insert"></i> {{translations.new}}</button>
                        </th>
                    </tr>
                    <tr class="table-filter">
                        <td></td>
                        <td v-for="column in columns" v-if="column.show_list == true">
                            <input type="text" :id="'filter-'+column.id" :name="'filter-'+column.id" placeholder="filter" v-on:keyup="filterlist">
                        </td>
                        <td class="text-right pr-2 pt-1">
                            <button class="btn btn-secondary btn-sm text-white pointer ml-1" v-on:click="resetFilter">{{translations.reset_filter}}</button>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in data" :key="item.id" :id="'row-'+item.id">
                        <td><input type="checkbox" name="select-delete[]" class="select-delete" :value="item.id" v-on:click="markToDelete"></td>
                        <td v-for="column in columns" v-if="column.show_list == true">
                            <span v-if="typeof column.object !== 'undefined'">{{item[column.object][column.id]}}</span>
                            <span v-else>{{item[column.id]}}</span>
                        </td>
                        <td>
                            <button v-if="api.get" class="btn btn-secondary btn-sm text-white pointer ml-1" v-on:click="view" :data-id="item.id"><i class="fa fa-search" aria-hidden="true" :data-id="item.id"></i></button>
                            <button v-if="api.update" class="btn btn-secondary btn-sm text-white pointer ml-1" v-on:click="edit" :data-id="item.id"><i class="fa fa-pencil-alt" aria-hidden="true" :data-id="item.id"></i></button>
                            <button v-if="special.update" class="btn btn-secondary btn-sm text-white pointer ml-1" v-on:click="customButton" :data-url="special.update+item.id+'/'" :data-id="item.id"><i class="fa fa-pencil-alt" aria-hidden="true" v-on:click="customButton" :data-url="special.update+item.id+'/'"></i></button>
                            <button v-if="api.delete" class="btn btn-secondary btn-sm text-white pointer ml-1" v-on:click="drop" :data-id="item.id"><i class="fa fa-trash" aria-hidden="true" :data-id="item.id"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-3">
                        <button class="btn btn-danger btn-sm" v-on:click="dropMultiple">{{translations.delete_selected}}</button>
                    </div>
                    <div class="col-9">
                        <select class="select-limit form-control form-inline" v-on:change="change_amount">
                            <option value="15">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="250">250</option>
                        </select>
                        <nav v-if="pages > 1" aria-label="List navigation">
                            <ul class="pagination justify-content-end">
                                <li v-if="offset > 0" class="page-item">
                                    <a class="page-link" href="#" aria-label="Previous" data-offset="1" v-on:click="gotopage">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <li v-for="n in pages" :class="{ 'page-item': true, 'active' : n == (offset+1)}"><a class="page-link" href="#" :data-offset="n" v-on:click="gotopage">{{n}}</a></li>
                                <li v-if="offset < pages-1">
                                    <a class="page-link" href="#" aria-label="Next" :data-offset="pages" v-on:click="gotopage">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div v-if="buttons.length > 0">
                    <ul class="list-inline">
                        <li v-for="button in buttons" class="list-inline-item">
                            <button class="btn btn-sm btn-secondary" :data-id="button.id" :data-api="button.api" :data-url="button.url" v-on:click="customButton">{{button.label}}</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div v-else-if="mode === 'view'" id="data-manager-view">
            <table class="table table-hover table-striped">
                <tbody>
                    <tr v-for="column in columns">
                        <th>{{column.label}}</th>
                        <td>{{form_data[column.id]}}</td>
                    </tr>
                </tbody>
            </table>
            <div class="row">
                <div class="col">
                    <button class="btn btn-primary" v-on:click.prevent="edit" :data-id="form_id">{{translations.edit}}</button>
                </div>
                <div class="col text-right">
                    <button class="btn btn-primary" v-on:click.prevent="gotoList">{{translations.back}}</button>
                </div>
            </div>
        </div>
        <div v-else-if="mode === 'form'" id="data-manager-form">
            <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
                <div v-if="alert.text != '' && alert.type == 'success'" class="alert alert-success" role="alert">
                    {{alert.text}}
                </div>
                <div v-else-if="alert.text != '' && alert.type == 'error'" class="alert alert-danger" role="alert">
                    {{alert.text}}
                </div>
            </transition>
            <ul v-if="locales.length > 0" class="nav nav-tabs">
                <li v-for="item in locales" class="nav-item">
                    <a :class="{ 'nav-link': true, 'active' : (locale_id == item.id && translate_id === 0) || translate_id == item.id}" href="#" v-on:click.prevent="setTranslate" :data-locale="item.locale" :data-lid="item.id">{{item.name}}</a>
                </li>
            </ul>
            <form method="post" v-on:submit.prevent="update">
                <div v-for="column in columns">
                    <div v-if="column.type === 'text' && (column.editable || (form_id === 0 && column.show_form))" class="form-group">
                        <label :for="'form-'+column.id">{{column.label}}</label>
                        <input type="text" :id="'form-'+column.id" :name="'form-'+column.id" :data-id="column.id" class="form-control" :value="form_data[column.id]" v-on:keyup="updateFormData">
                    </div>
                    <div v-else-if="column.type === 'email' && (column.editable || (form_id === 0 && column.show_form))" class="form-group">
                        <label :for="'form-'+column.id">{{column.label}}</label>
                        <input type="email" :id="'form-'+column.id" :name="'form-'+column.id" :data-id="column.id" class="form-control" :value="form_data[column.id]" v-on:keyup="updateFormData">
                    </div>
                    <div v-else-if="column.type === 'phone' && (column.editable || (form_id === 0 && column.show_form))" class="form-group">
                        <label :for="'form-'+column.id">{{column.label}}</label>
                        <vue-tel-input v-model="form_data[column.id]" class="form-control" :preferredCountries="['nl', 'be', 'gb']"></vue-tel-input>
                    </div>
                    <div v-else-if="column.type === 'integer' && (column.editable || (form_id === 0 && column.show_form))" class="form-group">
                        <label :for="'form-'+column.id">{{column.label}}</label>
                        <input type="integer" :id="'form-'+column.id" :name="'form-'+column.id" :data-id="column.id" class="form-control" :value="form_data[column.id]" v-on:keyup="updateFormData">
                    </div>
                    <div v-else-if="column.type === 'date' && (column.editable || (form_id === 0 && column.show_form))" class="form-group">
                        <label :for="'form-'+column.id">{{column.label}}</label>
                        <input type="date" :id="'form-'+column.id" :name="'form-'+column.id" :data-id="column.id" class="form-control" :value="form_data[column.id]" v-on:keyup="updateFormData">
                    </div>
                    <div v-else-if="column.type === 'textarea' && (column.editable || (form_id === 0 && column.show_form))" class="form-group">
                        <label :for="'form-'+column.id">{{column.label}}</label>
                        <textarea :id="'form-'+column.id" :name="'form-'+column.id" :data-id="column.id" class="form-control" v-on:keyup="filterlist">{{ form_data[column.id] }}</textarea>
                    </div>
                    <div v-else-if="column.type === 'texteditor' && (column.editable || (form_id === 0 && column.show_form))" class="form-group">
                        <label :for="'form-'+column.id">{{column.label}}</label>
                        <ckeditor :editor="editor" v-model="form_data[column.id]" :config="editorConfig"></ckeditor>
                    </div>
                    <div v-else-if="column.type === 'checkbox' && (column.editable || (form_id === 0 && column.show_form))" class="form-group">
                        <div class="checkbox">
                            <label :for="'form-'+column.id">
                                <input type="checkbox" :id="'form-'+column.id" :name="'form-'+column.id" v-model="form_data[column.id]" v-on:keyup="updateFormData">
                                {{column.label}}
                            </label>
                        </div>
                    </div>
                    <div v-else-if="column.type === 'checkboxes' && (column.editable || (form_id === 0 && column.show_form))" class="form-group">
                        <h4>{{column.label}}</h4>
                        <div class="row">
                            <div v-for="(description, index) in column.options" class="col-sm-6 col-md-4 col-lg-3">
                                <div class="checkbox">
                                    <label :for="column.id+'-'+index">
                                        <input type="checkbox" :id="column.id+'-'+index" :name="column.id+'-'+index" :value="index" v-model="form_data[column.id+'-'+index]" v-on:keyup="updateFormData">
                                        {{description}}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="(column.editable || (form_id === 0 && column.show_form))" class="form-group">
                        <label :for="'form-'+column.id">{{column.label}}</label>
                        <input type="text" :id="'form-'+column.id" :name="'form-'+column.id" :data-id="column.id" class="form-control" :value="form_data[column.id]" v-on:keyup="updateFormData">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <button class="btn btn-primary">{{translations.submit}}</button>
                    </div>
                    <div class="col text-right">
                        <button class="btn btn-primary" v-on:click.prevent="gotoList">{{translations.cancel}}</button>
                    </div>
                </div>
            </form>
        </div>
    </transition>
</template>

<script>
    import axios from 'axios';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import VueTelInput from 'vue-tel-input';

    export default {
        components: {
            VueTelInput,
        },
        name: "datamanager",
        data() {
            return {
                mode: 'table',
                locales: [],
                locale: '',
                locale_id: 0,
                default_locale_id: 0,
                translate_id: 0,
                data: {},
                form_id: 0,
                form_status: '',
                form_data: {},
                errors: {},
                columns: {},
                api: {},
                special: {},
                sort: {},
                filter: '',
                offset: 0,
                limit: 10,
                total: 0,
                pages: 0,
                buttons: [],
                alert: {},
                editor: ClassicEditor, //ClassicEditor,
                editorData: {}, //'<p>Rich-text editor content.</p>',
                editorConfig: {
                    'min-height': '500px'
                }
            }
        },
        created() {
            this.columns = columns;
            this.api = api;
            this.translations = translations;
            if (typeof buttons !== 'undefined') this.buttons = buttons;
            if (typeof special !== 'undefined') this.special = special;
            this.getLocales();
        },
        methods: {
            getLocales: function() {
                this.locale = document.body.dataset.locale;
                if (this.api.locales){
                    axios.get('/api/v1'+this.api.locales, { headers: {"Authorization" : "Bearer " + apikey} })
                        .then(response => {
                            this.locales = JSON.parse(response.data)['data'];
                            for (var i = 0; i < this.locales.length; i++) {
                                if (this.locale == this.locales[i]['locale']) this.locale_id = this.locales[i]['id'];
                                if (this.locales[i]['default']) this.default_locale_id = this.locales[i]['id'];
                            }
                            this.list();
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    this.list();
                }
            },
            setTranslate: function(event) {
                if (event.target.dataset.lid != this.default_locale_id) this.translate_id = parseInt(event.target.dataset.lid);
                else this.translate_id = 0;
                this.mode = 'form';
                this.edit();
            },
            list: function() {

                this.columns = columns;

                var headers = {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "Authorization" : "Bearer " + apikey
                }

                let params = {};
                params.offset = this.offset * this.limit;
                params.limit = this.limit;
                params.sort = this.sort.id;
                params.dir = this.sort.dir;
                params.filter = this.filter;
                params.locale = this.locale_id;

                axios.post('/api/v1'+api.list, params, {headers: headers})
                    .then(response => {
                        this.data = JSON.parse(response.data)['data'];
                        this.total = parseInt(JSON.parse(response.data)['total']);
                        this.pages = Math.ceil(this.total/this.limit);
                    })
                    .catch(e => {
                        this.errors.push(e)
                    });
            },
            sortlist: function(event) {

                let el = event.target;
                if (el.tagName == 'I') el = el.parentNode;

                this.sort.id = el.dataset.id;
                this.sort.dir = el.dataset.dir;

                if (el.dataset.dir === 'asc') el.dataset.dir = 'desc';
                else el.dataset.dir = 'asc';

                this.list();
            },
            filterlist: function(event) {

                this.filter = '';
                for (var i = 0; i < this.columns.length; i++) {

                    if (this.columns[i]['show_list']) {
                        let column = this.columns[i]['id'];
                        let value = document.getElementById('filter-' + column).value;

                        if (i !== 0) this.filter += '&';
                        this.filter += column + '=' + encodeURI(value);
                    }
                }

                this.list();
            },
            resetFilter: function(event) {

                this.filter = '';
                for (var i = 0; i < this.columns.length; i++) {
                    let column = this.columns[i]['id'];
                    document.getElementById('filter-' + column).value = '';
                }
                this.list();
            },
            gotopage: function(event) {
                this.offset = event.target.dataset.offset - 1;
                this.list();
            },
            change_amount: function(event) {
                this.offset = 0;
                this.limit = event.target.value;
                this.list();
            },
            view: function(event) {

                this.form_id = parseInt(event.target.dataset.id);
                axios.get('/api/v1'+api.get + event.target.dataset.id + '/', { headers: {"Authorization" : "Bearer " + apikey} })
                    .then(response => {
                        this.form_data = JSON.parse(response.data)['data'];
                        this.mode = 'view';
                    })
                    .catch(e => {
                        this.errors.push(e)
                    });
            },
            add: function(event) {
                this.form_id = 0;
                this.mode = 'form';
            },
            edit: function(event) {

                if (this.api.custom_form) {

                    axios.get('/api/v1'+this.api.custom_form, { headers: {"Authorization" : "Bearer " + apikey} })
                        .then(response => {
                            var result = JSON.parse(response.data);
                            if (result.success) {
                                this.columns = result.fields;
                            } else {
                                this.setAlert(result.message, 'error');
                            }
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                }

                if (this.form_id === 0) this.form_id = parseInt(event.target.dataset.id);
                let url = '/api/v1'+api.get + this.form_id + '/';

                let headers = {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "Authorization" : "Bearer " + apikey
                }

                let params = {};
                if (this.translate_id > 0) params['locale'] = this.translate_id;

                axios.post(url, params, { headers: headers })
                    .then(response => {
                        var result = JSON.parse(response.data);
                        if (result.success) {
                            if (result['data'].constructor === {}.constructor) this.form_data = result['data'];
                            else this.form_data = {};

                            for (var i = 0; i < this.columns.length; i++) {
                                if (this.columns[i]['type'] == 'checkboxes') {

                                    var values = result['data'][this.columns[i]['id']];
                                    for (var k = 0; k < values.length; k++) {
                                        this.form_data[this.columns[i]['id']+'-'+values[k]['id']] = true;
                                    }
                                }
                            }

                            this.mode = 'form';
                        } else {
                            this.setAlert(result.message, 'error');
                        }
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                        this.errors.push(e)
                    });
            },
            updateFormData: function(event) {
                this.form_data[event.target.dataset.id] = event.target.value;
            },
            update: function(event) {

                let headers = {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "Authorization" : "Bearer " + apikey
                }

                let params = {};
                if (this.translate_id > 0) params['locale'] = this.translate_id;
                for (var i = 0; i < this.columns.length; i++) {
                    if (this.columns[i]['editable'] || (this.form_id === 0 && this.columns[i]['show_form'])) {
                        if (this.columns[i]['type'] == 'texteditor') {
                            params[this.columns[i]['id']] = this.form_data[this.columns[i]['id']];
                        } else if (this.columns[i]['type'] == 'checkbox') {
                            if (document.getElementById('form-' + this.columns[i]['id']).checked) params[this.columns[i]['id']] = true;
                            else params[this.columns[i]['id']] = false;
                        } else if (this.columns[i]['type'] == 'phone') {
                            params[this.columns[i]['id']] = this.form_data[this.columns[i]['id']];
                        } else if (this.columns[i]['type'] == 'checkboxes') {
                            for (var index in this.columns[i]['options']) {
                                if (document.getElementById(this.columns[i].id+'-'+index).checked) params[this.columns[i].id+'-'+index] = true;
                                else params[this.columns[i].id+'-'+index] = false;
                            }
                        } else {
                            params[this.columns[i]['id']] = document.getElementById('form-' + this.columns[i]['id']).value;
                            this.form_data[this.columns[i]['id']] = document.getElementById('form-' + this.columns[i]['id']).value;
                        }
                    }
                }

                let url = '/api/v1'+api.insert;
                if (this.form_id > 0) url = '/api/v1'+api.update + this.form_id + '/';

                axios.put(url, params, {headers: headers})
                    .then(response => {
                        var result = JSON.parse(response.data);

                        if (result.success) {
                            this.form_id = parseInt(result['id']);
                            this.mode = 'form';
                            this.setAlert(translations.saved, 'success');
                        } else {
                            this.setAlert(result.message, 'error');
                        }
                    })
                    .catch(e => {
                        this.errors.push(e)
                    });
            },
            drop: function(event) {

                var element = document.getElementById('row-'+event.target.dataset.id);
                element.classList.add("to-delete");

                var self = this;

                this.$dialog
                    .confirm(translations['confirm_delete_text'] + ' ' + translations['want_proceed'])
                    .then(function(dialog) {
                        axios.delete('/api/v1'+api.delete + event.target.dataset.id + '/', { headers: {"Authorization" : "Bearer " + apikey} })
                            .then(response => {
                                var result = JSON.parse(response.data);
                                if (result.success) {
                                    self.list();
                                    self.setAlert(translations.delete_confirmation, 'success');
                                } else {
                                    element.classList.remove("to-delete");
                                    self.setAlert(result.message, 'error');
                                }
                            })
                            .catch(e => {
                                console.log(e);
                                this.errors.push(e);
                            });
                    })
                    .catch(function() {
                        console.log(e);
                        element.classList.remove("to-delete");
                    });
            },
            dropMultiple: function(event) {

                var self = this;

                this.$dialog
                    .confirm(translations['confirm_multiple_delete_text'] + ' ' + translations['want_proceed'])
                    .then(function(dialog) {

                        var headers = {
                            'Content-Type': 'application/json;charset=UTF-8',
                            "Authorization" : "Bearer " + apikey
                        }

                        let chk_arr =  document.getElementsByName("select-delete[]");
                        let chklength = chk_arr.length;
                        let ids = [];
                        for(var k=0;k< chklength;k++) {
                            if (chk_arr[k].checked) ids.push(chk_arr[k].value);
                        }

                        let params = {ids: ids};

                        axios.put('/api/v1'+api.delete, params, {headers: headers})
                            .then(response => {
                                var result = JSON.parse(response.data);

                                if (result.success) {
                                    self.list();
                                    self.setAlert(translations.delete_confirmation, 'success');
                                } else {
                                    self.setAlert(result.message, 'error');
                                }
                            })
                            .catch(e => {
                                console.log(e);
                                this.errors.push(e)
                            });
                    })
                    .catch(function() {
                    });
            },
            selectAllDelete: function(event) {
                let checkboxes = document.getElementsByName('select-delete[]');
                for(var i=0, n=checkboxes.length;i<n;i++) {
                    checkboxes[i].checked = event.target.checked;

                    if (event.target.checked) checkboxes[i].parentNode.parentNode.classList.add("to-delete");
                    else checkboxes[i].parentNode.parentNode.classList.remove("to-delete");
                }
            },
            markToDelete: function(event){

                var element = document.getElementById('row-'+event.target.value);

                if (event.target.checked) element.classList.add("to-delete");
                else element.classList.remove("to-delete");
            },
            gotoList: function(event) {
                this.mode = 'table';
                this.list();
                this.form_data = [];
                this.form_id = 0;
            },
            customButton: function(event){
                if (event.target.dataset.api) {
                    axios.get(event.target.dataset.api, { headers: {"Authorization" : "Bearer " + apikey} })
                        .then(response => {
                            var result = JSON.parse(response.data);
                            if (result.success) {
                                this.setAlert(result.message, 'success');
                                this.list();
                            } else {
                                self.setAlert(result.message, 'error');
                            }
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else if (event.target.dataset.url) {
                    window.location.href = '/' + this.locale + event.target.dataset.url;
                }
            },
            setAlert: function(text, type) {
                var self = this;
                this.alert = {text: text, type: type};
                setTimeout(function() { self.alert = {}; }, 5000);
            }
        }
    }
</script>

<style scoped>

</style>
