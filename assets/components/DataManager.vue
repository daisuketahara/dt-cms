<template>
    <v-container v-if="tablemode" fluid>
        <div v-if="mode === 'list'"class="table-responsive">
            <div class="mb-3">
                <v-btn color="secondary" small @click="setTableMode" data-tablemode="0">
                    <i class="fal fa-th-list"></i>
                    {{translations.list_view || 'List view'}}
                </v-btn>
                <v-btn v-if="api.insert" color="success" small @click="add"><i class="fa fa-plus" aria-hidden="true"></i> {{translations.new || 'New'}}</v-btn>
                <v-btn v-if="settings.insert" color="success" small :data-url="settings.insert" @click="customButton"><i class="fa fa-plus" aria-hidden="true" :data-url="settings.insert"></i> {{translations.new || 'New'}}</v-btn>
            </div>
            <table class="data-manager-table table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th width="30"><input type="checkbox" @click="selectAllDelete"></th>
                        <th v-for="column in columns" v-if="column.list == true" :data-column="column.id">
                            {{translations[column.label] || column.label}}
                            <a class="table-sort" @click="sortlist" :data-id="column.id" :data-alias="column.alias" data-dir="asc">
                                <i v-if="column.id === sort.id && sort.dir === 'desc'" class="fa fa-sort-down" aria-hidden="true"></i>
                                <i v-else-if="column.id === sort.id && sort.dir === 'asc'" class="fa fa-sort-up" aria-hidden="true"></i>
                                <i v-else class="fa fa-sort" aria-hidden="true"></i>
                            </a>
                        </th>
                        <th width="160">
                        </th>
                    </tr>
                    <tr class="table-filter-row">
                        <td></td>
                        <td v-for="column in columns" v-if="column.list == true">
                            <select v-if="column.type === 'select'" :id="'filter-'+column.id" :name="'filter-'+column.id" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-on:change="filterlist">
                                <option value="">{{translations.search_for || 'search for..'}}</option>
                                <option v-for="(optionvalue, optionkey) in column.options" :value="optionkey">{{optionvalue}}</option>
                            </select>
                            <select v-else-if="column.type === 'switch'" :id="'filter-'+column.id" :name="'filter-'+column.id" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-on:change="filterlist">
                                <option value="">{{translations.search_for || 'search for..'}}</option>
                                <option v-for="(optionvalue, optionkey) in column.options" :value="optionkey">{{optionvalue}}</option>
                            </select>
                            <input v-else type="text" :id="'filter-'+column.id" :name="'filter-'+column.id" placeholder="filter" v-on:keyup="filterlist">
                        </td>
                        <td class="text-right pr-2 pt-1">
                            <v-btn color="error" x-small @click="resetFilter">{{translations.reset_filter || 'Reset filter'}}</v-btn>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in data" :key="item.id" :id="'row-'+item.id">
                        <td><input type="checkbox" name="select-delete[]" class="select-delete" :value="item.id" @click="markToDelete"></td>
                        <td v-for="column in columns" v-if="column.list == true">
                            <span v-if="typeof column.object !== 'undefined' && typeof column.object2 !== 'undefined'">{{item[column.object][column.object2][column.id]}}</span>
                            <span v-if="typeof column.object !== 'undefined'">{{item[column.object][column.id]}}</span>
                            <span v-else-if="column.type=='switch'">
                                <i v-if="item[column.id] == 1" class="fas fa-check"></i>
                                <i v-else class="fas fa-times"></i>
                            </span>
                            <span v-else-if="column.type=='select'">
                                {{translations[column.options[item[column.id]]] || column.options[item[column.id]]}}
                            </span>
                            <span v-else-if="column.type=='date'">{{formatDate(item[column.id])}}</span>
                            <span v-else>{{item[column.id]}}</span>
                        </td>
                        <td>
                            <button v-if="api.get" class="btn btn-secondary btn-sm text-white pointer ml-1" @click="view" :data-id="item.id"><i class="fa fa-search" aria-hidden="true" :data-id="item.id"></i></button>
                            <button v-if="api.update" class="btn btn-secondary btn-sm text-white pointer ml-1" @click="edit" :data-id="item.id"><i class="fa fa-pencil-alt" aria-hidden="true" :data-id="item.id"></i></button>
                            <button v-if="settings.update" class="btn btn-secondary btn-sm text-white pointer ml-1" @click="customButton" :data-url="settings.update+item.id+'/'" :data-id="item.id"><i class="fa fa-pencil-alt" aria-hidden="true" :data-url="settings.update+item.id+'/'"></i></button>
                            <button v-if="api.delete" class="btn btn-danger btn-sm text-white pointer ml-1" @click="drop" :data-id="item.id"><i class="fa fa-trash" aria-hidden="true" :data-id="item.id"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="row">
                <div class="col-3">
                    <v-btn color="error" small @click="dropMultiple">{{translations.delete_selected || 'Delete selected'}}</v-btn>
                </div>
                <div class="col-3">
                    <v-select class="d-inline" width="40" :items="[10,20,50,250]" v-model="limit" :dark="darkmode" solo x-small dense></v-select>
                </div>
                <div class="col-6 text-right">
                    <v-pagination
                        v-if="total > limit"
                        v-model="offset"
                        :length="pages"
                        :page="offset"
                        total-visible="7"
                        :dark="darkmode"
                        prev-icon="fal fa-angle-left"
                        next-icon="fal fa-angle-right"
                        small
                        dense
                        inline
                    ></v-pagination>
                </div>
            </div>
            <div v-if="buttons.length > 0">
                <ul class="list-inline">
                    <li v-for="button in buttons" class="list-inline-item">
                        <button class="btn btn-sm btn-secondary" :data-id="button.id" :data-api="button.api" :data-url="button.url" @click="customButton">{{button.label}}</button>
                    </li>
                </ul>
            </div>
        </div>
        <div v-else-if="mode === 'view'" id="data-manager-view">
            <table class="table table-hover table-striped">
                <tbody>
                    <tr v-for="column in columns">
                        <th>{{translations[column.label] || column.label}}</th>
                        <td v-if="column.type=='switch'">
                            <i v-if="form_data[column.id] == 1" class="fas fa-check"></i>
                            <i v-else class="fas fa-times"></i>
                        </td>
                        <td v-else-if="column.type=='select'">
                            {{translations[column.options[form_data[column.id]]] || column.options[form_data[column.id]]}}
                        </td>
                        <td v-else>{{form_data[column.id]}}</td>
                    </tr>
                </tbody>
            </table>
            <div class="row">
                <div class="col">
                    <button v-if="api.update" class="btn btn-primary" v-on:click.prevent="edit" :data-id="form_id">{{translations.edit || 'Edit'}}</button>
                </div>
                <div class="col text-right">
                    <button class="btn btn-primary" v-on:click.prevent="gotoList">{{translations.back_to_list || 'Back to list'}}</button>
                </div>
            </div>
        </div>
        <div v-else-if="mode === 'form'" id="data-manager-form">
            <ul v-if="settings.translate == true" class="nav nav-tabs">
                <li v-for="item in locales" class="nav-item">
                    <a :class="{ 'nav-link': true, 'active' : (locale_id == item.id && translate_id === 0) || translate_id == item.id}" href="#" v-on:click.prevent="setTranslate" :data-locale="item.locale" :data-lid="item.id">{{item.name}}</a>
                </li>
            </ul>
            <form method="post" v-on:submit.prevent="update">
                <div v-for="column in columns">
                    <div v-if="column.type === 'checkbox' && (column.editable || (form_id === 0 && column.form))" class="form-group">
                        <div class="checkbox">
                            <label :for="'form-'+column.id">
                                <input type="checkbox" v-model="form_data[column.id]">
                                <span v-html="translations[column.label] || column.label"></span>
                            </label>
                        </div>
                    </div>
                    <div v-else-if="column.type === 'checkboxes' && (column.editable || (form_id === 0 && column.form))" class="form-group">
                        <h4>
                            {{translations[column.label] || column.label}}
                            <button class="btn btn-sm btn-link" v-on:click.prevent="toggleCheckboxes" data-status="0">{{translations.select_all || 'Select all'}}</button>
                        </h4>
                        <div class="row">
                            <div v-for="(description, index) in column.options" class="col-sm-6 col-md-4 col-lg-3">
                                <div class="checkbox">
                                    <label :for="column.id+'-'+index">
                                        <input type="checkbox" :id="column.id+'-'+index" :name="column.id+'-'+index" :value="index" v-model="form_data[column.id+'-'+index]">
                                        {{description}}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="column.editable || (form_id === 0 && column.form)" class="form-group">
                        <label :for="'form-'+column.id">{{translations[column.label] || column.label}}</label>
                        <input v-if="column.type === 'text'" type="text" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-model="form_data[column.id]" v-on:blur="validateField" v-on:keyup="validateField" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}">
                        <input v-else-if="column.type === 'email'" type="email" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-model="form_data[column.id]" v-on:blur="validateField" v-on:keyup="validateField" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}">
                        <vue-tel-input v-else-if="column.type === 'phone'" v-model="form_data[column.id]" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-bind="phoneProps" :preferredCountries="['nl', 'be', 'gb']"></vue-tel-input>
                        <input v-else-if="column.type === 'integer'" type="integer" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-model="form_data[column.id]" v-on:blur="validateField" v-on:keyup="validateField" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}">
                        <datepicker v-else-if="column.type === 'date'" format="yyyy-MM-dd" placeholder="Select Date":id="'form-'+column.id" :name="'form-'+column.id" :data-id="column.id" v-bind:input-class="{ 'form-control': true, 'form-required': column.required == true}" v-model="form_data[column.id]" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}"></datepicker>
                        <textarea v-else-if="column.type === 'textarea'" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-on:blur="validateField" v-on:keyup="validateField" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}">{{ form_data[column.id] }}</textarea>
                        <select v-else-if="column.type === 'select'" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-model="form_data[column.id]" v-on:blur="validateField" v-on:keyup="validateField" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}">
                            <option value=""></option>
                            <option v-for="(optionvalue, optionkey) in column.options" :value="optionkey">{{translations[optionvalue] || optionvalue}}</option>
                        </select>
                        <ckeditor v-else-if="column.type === 'texteditor'" :editor="editor" v-model="form_data[column.id]" :config="editorConfig"></ckeditor>
                        <select v-else-if="column.type === 'switch'" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-model="form_data[column.id]" v-on:blur="validateField" v-on:keyup="validateField" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}">
                            <option value=""></option>
                            <option v-for="(optionvalue, optionkey) in column.options" v-if="column.id != 'AgreeTerms' || optionkey == '0' || (optionkey == '1' && form_data[column.id] == '1')" :value="optionkey">{{translations[optionvalue] || optionvalue}}</option>
                        </select>
                         <model-select v-else-if="column.type === 'selectfilter'" :options="select_options[column.id]" :data-id="column.id" class="form-control form-selectfilter" v-model="form_data[column.id]" @select="validateSelectFilter" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}"></model-select>
                        <input v-else-if="column.type === 'password'" type="password" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-model="password" v-on:blur="validateField" v-on:keyup="validateField" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}">
                        <input v-else type="text" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-model="form_data[column.id]" v-on:blur="validateField" v-on:keyup="validateField" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <button class="btn btn-primary" v-on:click.prevent="gotoList">{{translations.back_to_list || 'back to list'}}</button>
                    </div>
                    <div class="col text-right">
                        <button v-if="form_id != 0 && typeof settings.updateSubmitLabel != typeof undefined" class="btn btn-primary">{{translations[settings.updateSubmitLabel] || settings.updateSubmitLabel}}</button>
                        <button v-else-if="typeof settings.insertSubmitLabel != typeof undefined" class="btn btn-primary">{{translations[settings.insertSubmitLabel] || settings.insertSubmitLabel}}</button>
                        <button v-else class="btn btn-primary">{{translations.submit || 'Submit'}}</button>
                    </div>
                </div>
            </form>
        </div>
    </v-container>
    <div v-else class="row ml-0 mr-3">
        <div class="data-list-container col-6 col-lg-5 px-0">
            <transition name="fade" enter-active-class="animated fadeIn">
                <v-pagination
                    v-if="total > limit"
                    v-model="offset"
                    :length="pages"
                    :page="offset"
                    total-visible="7"
                    :dark="darkmode"
                    prev-icon="fal fa-angle-left"
                    next-icon="fal fa-angle-right"
                    small
                    dense
                    inline
                ></v-pagination>
            </transition>
            <transition-group name="fade" enter-active-class="animated flipInX">
                <div v-for="item in data" :key="item.id" :id="'data-card-'+item.id" class="data-card d-flex">
                    <ul class="data-card-functions">
                        <li v-if="api.get">
                            <button class="btn btn-secondary" @click="view" :data-id="item.id"><i class="fad fa-eye" :data-id="item.id"></i></button>
                        </li>
                        <li v-if="api.update">
                            <button class="btn btn-dark" @click="edit" :data-id="item.id"><i class="fad fa-pencil-alt" :data-id="item.id"></i></button>
                        </li>
                        <li v-if="settings.update">
                            <button class="btn btn-dark" @click="customButton" :data-url="settings.update+item.id+'/'" :data-id="item.id"><i class="fad fa-pencil-alt" aria-hidden="true" :data-url="settings.update+item.id+'/'"></i></button>
                        </li>
                        <li v-if="settings.component">
                            <button class="btn btn-dark" @click="setComponent" :data-component="settings.component" :data-id="item.id"><i class="fad fa-pencil-alt"></i></button>
                        </li>
                        <li v-if="api.delete">
                            <button class="btn btn-danger" @click="drop" :data-id="item.id"><i class="fad fa-trash-alt" :data-id="item.id"></i></button>
                        </li>
                    </ul>
                    <div class="data-card-content flex-grow-1">
                        <div v-for="(column, key, index) in columns" v-if="column.list == true">
                            <div v-if="index == 1">
                                <h3 v-if="typeof column.object !== 'undefined' && typeof column.object2 !== 'undefined'">{{item[column.object][column.object2][column.id]}}</h3>
                                <h3 v-else-if="typeof column.object !== 'undefined'">{{item[column.object][column.id]}}</h3>
                                <h3 v-else-if="column.type=='switch'">
                                    <i v-if="item[column.id] == 1" class="fas fa-check"></i>
                                    <i v-else class="fas fa-times"></i>
                                </h3>
                                <h3 v-else-if="column.type=='select'">
                                    {{translations[column.options[item[column.id]]] || column.options[item[column.id]]}}
                                </h3>
                                <h3 v-else-if="column.type=='date'">{{formatDate(item[column.id])}}</h3>
                                <h3 v-else>{{item[column.id]}}</h3>
                            </div>
                            <div v-else-if="index > 1">
                                <span class="data-card-label">{{translations[column.label] || column.label}}: </span>
                                <span class="data-card-value" v-if="typeof column.object !== 'undefined' && typeof column.object2 !== 'undefined'">{{item[column.object][column.object2][column.id]}}</span>
                                <span class="data-card-value" v-else-if="typeof column.object !== 'undefined'">{{item[column.object][column.id]}}</span>
                                <span class="data-card-value" v-else-if="column.type=='switch'">
                                    <i v-if="item[column.id] == 1" class="fas fa-check"></i>
                                    <i v-else class="fas fa-times"></i>
                                </span>
                                <span class="data-card-value" v-else-if="column.type=='select'">
                                    {{translations[column.options[item[column.id]]] || column.options[item[column.id]]}}
                                </span>
                                <span class="data-card-value" v-else-if="column.type=='date'">{{formatDate(item[column.id])}}</span>
                                <span class="data-card-value" v-else>{{item[column.id]}}</span>
                            </div>
                        </div>
                        <input type="checkbox" name="select-delete[]" class="select-delete" :value="item.id" @click="markToDelete">
                        <div class="card-id">ID: {{item.id}}</div>
                    </div>
                </div>
            </transition-group>
        </div>
        <div class="data-form-container col-6 col-lg-7">
            <transition-group name="fade-right" enter-active-class="animated fadeIn">
                <div v-if="mode === 'list'" id="data-manager-view" key="list">
                    <div class="data-functions-container text-center">
                        <h1 v-if="typeof settings.title !== 'undefined'">{{translations[settings.title] || settings.title}}</h1>
                        <div v-else class="title-replace-spacer"></div>
                        <div class="my-3">
                            <v-btn v-if="api.insert" block color="success" x-large @click="add">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                                {{translations.add_new_item || 'Add a new item'}}
                            </v-btn>
                        </div>
                        <div class="my-3">
                            <v-btn v-if="settings.insert" block color="success" x-large :data-url="settings.insert" @click="customButton">
                                <i class="fa fa-plus" aria-hidden="true" :data-url="settings.insert"></i>
                                {{translations.add_new_item || 'Add a new item'}}
                            </v-btn>
                        </div>
                        <div class="my-3">
                            <v-btn v-if="settings.component" block color="success" x-large @click="setComponent" :data-component="settings.component">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                                {{translations.add_new_item || 'Add a new item'}}
                            </v-btn>
                        </div>
                        <div class="my-3">
                            <v-btn block color="primary" x-large @click="search">
                                <i class="fad fa-trash-alt"></i>
                                {{translations.search || 'Search'}}
                            </v-btn>
                        </div>
                        <div class="my-3">
                            <v-btn block color="error" x-large @click="dropMultiple">
                                <i class="fad fa-trash-alt"></i>
                                {{translations.delete_selected || 'Delete selected'}}
                            </v-btn>
                        </div>
                        <div class="my-3">
                            <v-btn block color="secondary" x-large @click="setTableMode" data-tablemode="1">
                                <i class="fal fa-table"></i>
                                {{translations.table_view || 'Table view'}}
                            </v-btn>
                        </div>
                        <div v-for="button in buttons" class="my-3" :key="button.label">
                            <v-btn block color="secondary" x-large :data-id="button.id" :data-api="button.api" :data-url="button.url" @click="customButton">
                                <i class="fal fa-cogs"></i>
                                {{translations[button.label] || button.label}}
                            </v-btn>
                        </div>
                    </div>
                </div>
                <div v-if="mode === 'search'" id="data-manager-view" key="search">
                    <v-btn class="mb-3" outlined x-small fab :dark="darkmode" v-on="on" @click="gotoList">
                        <v-icon x-small>fal fa-arrow-left</v-icon>
                    </v-btn>
                    <h1>{{translations.search || 'Search'}}</h1>
                    <table class="table-filter table table-striped">
                        <tbody>
                            <tr v-for="column in columns" v-if="column.list == true">
                                <td>
                                    <a class="table-sort" @click="sortlist" :data-id="column.id" :data-alias="column.alias" data-dir="asc">
                                        <i v-if="column.id === sort.id && sort.dir === 'desc'" class="fa fa-sort-down" aria-hidden="true"></i>
                                        <i v-else-if="column.id === sort.id && sort.dir === 'asc'" class="fa fa-sort-up" aria-hidden="true"></i>
                                        <i v-else class="fa fa-sort" aria-hidden="true"></i>
                                        {{translations[column.label] || column.label}}
                                    </a>
                                </td>
                                <td>
                                    <select v-if="column.type === 'select'" :id="'filter-'+column.id" :name="'filter-'+column.id" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-on:change="filterlist">
                                        <option value="">{{translations.search_for || 'search for..'}}</option>
                                        <option v-for="(optionvalue, optionkey) in column.options" :value="optionkey">{{optionvalue}}</option>
                                    </select>
                                    <select v-else-if="column.type === 'switch'" :id="'filter-'+column.id" :name="'filter-'+column.id" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-on:change="filterlist">
                                        <option value="">{{translations.search_for || 'search for..'}}</option>
                                        <option v-for="(optionvalue, optionkey) in column.options" :value="optionkey">{{optionvalue}}</option>
                                    </select>
                                    <input v-else type="text" :id="'filter-'+column.id" class="form-control" :name="'filter-'+column.id" v-on:keyup="filterlist">
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">
                                    <button class="btn btn-danger btn-sm" @click="dropMultiple">
                                        <i class="fad fa-trash-alt"></i>
                                        {{translations.delete_selected || 'Delete selected'}}
                                    </button>
                                    <button class="btn btn-secondary btn-sm text-white pointer ml-1" @click="resetFilter">{{translations.reset_filter || 'Reset filter'}}</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div v-if="mode === 'view'" id="data-manager-view" key="view">
                    <v-btn class="mb-3" outlined x-small fab :dark="darkmode" v-on="on" @click="gotoList">
                        <v-icon x-small>fal fa-arrow-left</v-icon>
                    </v-btn>
                    <table class="table table-striped">
                        <tbody>
                            <tr v-for="column in columns">
                                <th>{{translations[column.label] || column.label}}</th>
                                <td v-if="column.type=='switch'">
                                    <i v-if="form_data[column.id] == 1" class="fas fa-check"></i>
                                    <i v-else class="fas fa-times"></i>
                                </td>
                                <td v-else-if="column.type=='select'">
                                    {{translations[column.options[form_data[column.id]]] || column.options[form_data[column.id]]}}
                                </td>
                                <td v-else>{{form_data[column.id]}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col">
                        </div>
                        <div class="col text-right">
                            <button v-if="api.update" class="btn btn-primary" v-on:click.prevent="edit" :data-id="form_id">{{translations.edit || 'Edit'}}</button>
                        </div>
                    </div>
                </div>
            </transition-group>
            <transition name="fade-right" enter-active-class="animated fadeIn">
                <div v-if="mode === 'form'" id="data-manager-form">
                    <v-btn class="mb-3" outlined x-small fab :dark="darkmode" v-on="on" @click="gotoList">
                        <v-icon x-small>fal fa-arrow-left</v-icon>
                    </v-btn>
                    <ul v-if="settings.translate == true" class="nav nav-tabs">
                        <li v-for="item in locales" class="nav-item">
                            <a :class="{ 'nav-link': true, 'active' : (locale_id == item.id && translate_id === 0) || translate_id == item.id}" href="#" v-on:click.prevent="setTranslate" :data-locale="item.locale" :data-lid="item.id">{{item.name}}</a>
                        </li>
                    </ul>
                    <v-form>
                        <div v-for="column in columns">
                            <v-checkbox
                                v-if="column.type === 'checkbox' && (column.editable || (form_id === 0 && column.form))"
                                v-model="form_data[column.id]"
                                :label="translations[column.label] || column.label"
                                :dark="darkmode"
                            ></v-checkbox>
                            <v-switch
                                v-else-if="column.type === 'switch' && (column.editable || (form_id === 0 && column.form))"
                                v-model="form_data[column.id]"
                                :label="translations[column.label] || column.label"
                                :dark="darkmode"
                            ></v-switch>
                            <div v-else-if="column.type === 'checkboxes' && (column.editable || (form_id === 0 && column.form))" class="form-group">
                                <h4>
                                    {{translations[column.label] || column.label}}
                                    <button class="btn btn-sm btn-link" v-on:click.prevent="toggleCheckboxes" data-status="0">{{translations.select_all || 'Select all'}}</button>
                                </h4>
                                <div class="row">
                                    <div v-for="(description, index) in column.options" class="col-sm-6 col-md-4 col-lg-3">
                                        <div class="checkbox">
                                            <label :for="column.id+'-'+index">
                                                <input type="checkbox" :id="column.id+'-'+index" :name="column.id+'-'+index" :value="index" v-model="form_data[column.id+'-'+index]">
                                                {{description}}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="column.editable || (form_id === 0 && column.form)">
                                <v-text-field v-if="column.type === 'text'" v-model="form_data[column.id]" :label="translations[column.label] || column.label" :rules="validation[column.id]" :hint="column.tooltip" :dark="darkmode"></v-text-field>
                                <input v-else-if="column.type === 'email'" type="email" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-model="form_data[column.id]" v-on:blur="validateField" v-on:keyup="validateField" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}">
                                <vue-tel-input v-else-if="column.type === 'phone'" v-model="form_data[column.id]" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-bind="phoneProps" :preferredCountries="['nl', 'be', 'gb']"></vue-tel-input>
                                <input v-else-if="column.type === 'integer'" type="integer" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-model="form_data[column.id]" v-on:blur="validateField" v-on:keyup="validateField" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}">


                                <datepicker v-else-if="column.type === 'date'" format="yyyy-MM-dd" placeholder="Select Date":id="'form-'+column.id" :name="'form-'+column.id" :data-id="column.id" v-bind:input-class="{ 'form-control': true, 'form-required': column.required == true}" v-model="form_data[column.id]" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}"></datepicker>

                                <v-menu
                                    v-else-if="column.type === 'date'"
                                    v-model="menu2"
                                    :close-on-content-click="false"
                                    :nudge-right="40"
                                    transition="scale-transition"
                                    offset-y
                                    min-width="290px"
                                >
                                    <template v-slot:activator="{ on }">
                                        <v-text-field
                                            v-model="date"
                                            label="Picker without buttons"
                                            prepend-icon="event"
                                            readonly
                                            v-on="on"
                                        ></v-text-field>
                                    </template>
                                    <v-date-picker v-model="date" @input="menu2 = false"></v-date-picker>
                                </v-menu>

                                <v-textarea v-else-if="column.type === 'textarea'" v-model="form_data[column.id]" :label="translations[column.label] || column.label"  :rules="validation[column.id]" :hint="column.tooltip" :dark="darkmode">{{ form_data[column.id] }}</v-textarea>
                                <v-select v-else-if="column.type === 'select'" v-model="form_data[column.id]" :items="column.options" :label="translations[column.label] || column.label" :rules="validation[column.id]" :hint="column.tooltip" :dark="darkmode"></v-select>
                                <ckeditor v-else-if="column.type === 'texteditor'" :editor="editor" v-model="form_data[column.id]" :config="editorConfig"></ckeditor>
                                <select v-else-if="column.type === 'switch'" :data-id="column.id" v-bind:class="{ 'form-control': true, 'form-required': column.required == true}" v-model="form_data[column.id]" v-on:blur="validateField" v-on:keyup="validateField" :data-required="column.required" v-tooltip.bottom-start="{ content: column.tooltip || 'Field required', show: tooltip == 'form-'+column.id, trigger: 'manual'}">
                                    <option value=""></option>
                                    <option v-for="(optionvalue, optionkey) in column.options" v-if="column.id != 'AgreeTerms' || optionkey == '0' || (optionkey == '1' && form_data[column.id] == '1')" :value="optionkey">{{translations[optionvalue] || optionvalue}}</option>
                                </select>
                                <v-text-field
                                    v-else-if="column.type === 'password'"
                                    v-model="password"
                                    :append-icon="passwordShow ? 'fal fa-eye' : 'fal fa-eye-slash'"
                                    :type="passwordShow ? 'text' : 'password'"
                                    :rules="validation[column.id]"
                                    :label="translations[column.label] || column.label"
                                    :hint="column.tooltip"
                                    :dark="darkmode"
                                    @click:append="passwordShow = !passwordShow"
                                ></v-text-field>
                                <v-text-field v-else v-model="form_data[column.id]" :label="translations[column.label] || column.label" :rules="validation[column.id]" :hint="column.tooltip" :dark="darkmode"></v-text-field>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                            </div>
                            <div class="col text-right">
                                <v-btn v-if="form_id != 0 && typeof settings.updateSubmitLabel != typeof undefined" color="primary" :dark="darkmode">{{translations[settings.updateSubmitLabel] || settings.updateSubmitLabel}}</v-btn>
                                <v-btn v-else-if="typeof settings.insertSubmitLabel != typeof undefined" color="primary" :dark="darkmode">{{translations[settings.insertSubmitLabel] || settings.insertSubmitLabel}}</v-btn>
                                <v-btn v-else color="primary" :dark="darkmode">{{translations.submit || 'Submit'}}</v-btn>
                            </div>
                        </div>
                    </v-form>
                </div>
            </transition>
            <transition name="fade-right" enter-active-class="animated fadeIn">
                <div v-if="mode === 'component'">
                    <v-btn class="mb-3" outlined x-small fab :dark="darkmode" v-on="on" @click="gotoList">
                        <v-icon x-small>fal fa-arrow-left</v-icon>
                    </v-btn>
                    <component v-bind:is="component" v-bind="{id: form_id}"></component>
                </div>
            </transition>
        </div>
    </div>
</template>

<script>
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import { ModelSelect } from 'vue-search-select';

    export default {
        components: {
            ModelSelect
        },
        name: "DataManager",
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('token')
                },
                loaded: false,
                tablemode: false,
                mode: '',
                default_locale_id: 0,
                translate_id: 0,
                data: {},
                form_id: 0,
                form_status: '',
                form_data: {},
                errors: {},
                columns: [],
                api: {},
                settings: {},
                sort: {},
                filter: '',
                offset: 1,
                limit: 7,
                total: 0,
                pages: 0,
                buttons: [],
                editor: ClassicEditor, //ClassicEditor,
                editorData: {}, //'<p>Rich-text editor content.</p>',
                editorConfig: {
                    'min-height': '500px'
                },
                tooltip: '',
                phoneProps: {
                    defaultCountry: "NL",
                    preferredCountries: ['NL', 'BE', 'LU'],
                    //mode: 'International'
                    inputOptions: {
                        showDialCode: true
                    }
                },
                select_options: {},
                component: '',
                passwordShow: false,
                validation: {},
                rules: {
                    required: value => !!value || 'Required.',
                    min: v => v.length >= 8 || 'Min 8 characters',
                    emailMatch: () => ('The email and password you entered don\'t match'),
                },
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
            darkmode () {
                return this.$store.state.darkmode;
            },
            translations () {
                return this.$store.state.translations;
            }
        },
        created() {
            this.getEntityInfo();
            this.loadSelectOptions();

            if (this.$cookies.isKey('tablemode')) {
                this.tablemode = true;
                this.limit = 10;
            }
        },
        watch: {
            offset: function(val, oldVal) {
                if (this.loaded) this.list();
            },
            limit: function(val, oldVal) {
                if (this.loaded) this.list();
            }
        },
        methods: {
            getEntityInfo: function() {

                this.$axios.get('/api/v1'+this.$attrs.info, {headers: this.headers})
                    .then(response => {

                        this.api = response.data.api;
                        this.columns = response.data.fields;

                        if (response.data.buttons != undefined) this.buttons = response.data.buttons;
                        else this.buttons = {};

                        if (response.data.settings != undefined) this.settings = response.data.settings;
                        else this.settings = {};

                        this.list();
                        this.setRules();
                        this.loaded = true;
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            setTranslate: function(event) {
                if (event.target.dataset.lid != this.default_locale_id) this.translate_id = parseInt(event.target.dataset.lid);
                else this.translate_id = 0;
                this.mode = 'form';
                this.edit();
            },
            list: function() {

                let params = {};
                params.offset = (this.offset-1) * this.limit;
                params.limit = this.limit;
                params.sort = this.sort.id;
                params.dir = this.sort.dir;
                params.filter = this.filter;
                params.locale = this.$store.state.locale_id;

                this.$axios.post('/api/v1'+this.api.list, params, {headers: this.headers})
                    .then(response => {
                        this.data = JSON.parse(response.data)['data'];
                        this.total = parseInt(JSON.parse(response.data)['total']);
                        this.pages = Math.ceil(this.total/this.limit);
                        this.mode = 'list';
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
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
                for (var i in this.columns) {

                    if (this.columns[i]['list']) {
                        let column = this.columns[i]['id'];
                        let value = window.document.getElementById('filter-' + column).value;

                        let alias = '';
                        if (this.columns[i].alias != undefined) alias = this.columns[i].alias + '.';

                        if (i !== 0) this.filter += '&';
                        this.filter += alias + column + '=' + encodeURI(value);
                    }
                }

                this.list();
            },
            resetFilter: function(event) {

                this.filter = '';
                for (var i in this.columns) {
                    let column = this.columns[i]['id'];
                    let el = document.getElementById('filter-' + column);
                    if (el != null) el.value = '';
                }
                this.list();
            },
            search: function() {
                this.mode = 'search';
            },
            view: function(event) {

                this.form_id = parseInt(event.target.dataset.id);
                this.$axios.get('/api/v1'+this.api.get + event.target.dataset.id + '/', {headers: this.headers})
                    .then(response => {
                        this.form_data = JSON.parse(response.data)['data'];
                        this.mode = 'view';
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            add: function(event) {
                this.form_id = 0;
                this.mode = 'form';
            },
            edit: function(event) {

                this.mode = 'list';

                if (this.api.custom_form) {

                    this.$axios.get('/api/v1'+this.api.custom_form, {headers: this.headers})
                        .then(response => {
                            var result = JSON.parse(response.data);
                            if (result.success) {
                                this.columns = result.fields;
                            } else {
                                this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                            }
                        })
                        .catch(e => {
                            this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                        });
                }

                this.form_id = parseInt(event.target.dataset.id);
                let url = '/api/v1'+this.api.get + this.form_id + '/';

                let params = {};
                if (this.translate_id > 0) params['locale'] = this.translate_id;

                this.$axios.post(url, params, {headers: this.headers})
                    .then(response => {
                        var result = JSON.parse(response.data);
                        if (result.success) {
                            if (result['data'].constructor === {}.constructor) this.form_data = result['data'];
                            else this.form_data = {};

                            for (var i in this.columns) {
                                if (this.columns[i]['type'] == 'checkboxes') {

                                    var values = result['data'][this.columns[i]['id']];
                                    for (var k = 0; k < values.length; k++) {
                                        this.form_data[this.columns[i]['id']+'-'+values[k]['id']] = true;
                                    }
                                }
                            }

                            this.mode = 'form';
                        } else {
                            this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                        }
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            updateFormData: function(event) {
                this.form_data[event.target.dataset.id] = event.target.value;
            },
            update: function(event) {

                let params = {};
                if (this.translate_id > 0) params['locale'] = this.translate_id;
                for (var i in this.columns) {
                    if (this.columns[i]['editable'] || (this.form_id === 0 && this.columns[i]['form'])) {
                        if (this.columns[i]['type'] == 'texteditor') {
                            params[this.columns[i]['id']] = this.form_data[this.columns[i]['id']];
                        } else if (this.columns[i]['type'] == 'checkbox') {
                            if (document.getElementById('form-' + this.columns[i]['id']).checked) params[this.columns[i]['id']] = true;
                            else params[this.columns[i]['id']] = false;
                        } else if (this.columns[i]['type'] == 'selectfilter') {
                            params[this.columns[i]['id']] = this.form_data[this.columns[i]['id']];
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

                let url = '/api/v1'+this.api.insert;
                if (this.form_id > 0) url = '/api/v1'+this.api.update + this.form_id + '/';

                this.$axios.put(url, params, {headers: this.headers})
                    .then(response => {
                        var result = JSON.parse(response.data);

                        if (result.success) {
                            this.form_id = parseInt(result['id']);
                            this.mode = 'form';
                            this.$store.commit('setAlert', {type: 'success', message: translations.saved || "Saved", autohide: true});
                        } else {
                            this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                        }
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            drop: function(event) {

                var element = document.getElementById('data-card-'+event.target.dataset.id);
                element.classList.add("to-delete");

                var self = this;

                this.$modal.show('dialog', {
                    title: 'Alert!',
                    text: this.translations.confirm_delete_text + ' ' + this.translations.want_proceed,
                    buttons: [{
                        title: this.translations.cancel,
                        handler: () => {
                            element.classList.remove("to-delete");
                            this.$modal.hide('dialog');
                        }
                    },
                    {
                        title: this.translations.confirm,
                        handler: () => {

                            this.$modal.hide('dialog');
                            this.$axios.delete('/api/v1'+this.api.delete + event.target.dataset.id + '/', {headers: this.headers})
                                .then(response => {
                                    var result = JSON.parse(response.data);
                                    if (result.success) {
                                        self.list();
                                        this.$store.commit('setAlert', {type: 'success', message: translations.delete_confirmation || 'Deleted', autohide: true});
                                    } else {
                                        element.classList.remove("to-delete");
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
            dropMultiple: function(event) {

                var self = this;

                this.$modal.show('dialog', {
                    title: 'Alert!',
                    text: this.translations.confirm_multiple_delete_text + ' ' + this.translations.want_proceed,
                    buttons: [{
                        title: this.translations.cancel,
                        handler: () => {
                            this.$modal.hide('dialog');
                        }
                    },
                    {
                        title: this.translations.confirm,
                        handler: () => {

                            let chk_arr =  document.getElementsByName("select-delete[]");
                            let chklength = chk_arr.length;
                            let ids = [];
                            for(var k=0;k< chklength;k++) {
                                if (chk_arr[k].checked) ids.push(chk_arr[k].value);
                            }

                            let params = {ids: ids};

                            this.$axios.put('/api/v1'+this.api.delete, params, {headers: this.headers})
                                .then(response => {
                                    var result = JSON.parse(response.data);

                                    if (result.success) {
                                        self.list();
                                        this.$store.commit('setAlert', {type: 'success', message: translations.delete_confirmation || 'Deleted', autohide: true});
                                    } else {
                                        this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                                    }
                                    this.$modal.hide('dialog');
                                })
                                .catch(e => {
                                    this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                                    this.$modal.hide('dialog');
                                });
                        }
                    }]
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

                var element = document.getElementById('data-card-'+event.target.value);

                if (event.target.checked) element.classList.add("to-delete");
                else element.classList.remove("to-delete");
            },
            gotoList: function(event) {
                this.mode = 'list';
                this.list();
                this.form_data = [];
                this.form_id = 0;
            },
            toggleCheckboxes: function(event) {

                var status = event.target.dataset.status;
                var checkboxes = event.target.parentNode.parentNode.querySelectorAll('input[type="checkbox"]');

                if (status == 0) {
                    event.target.dataset.status = 1;
                    for(var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = true;
                    }
                } else {
                    event.target.dataset.status = 0;
                    for(var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = false;
                    }
                }
            },
            loadSelectOptions: function() {

                for (var i in this.columns) {

                    var column = this.columns[i];

                    if (column.type == 'selectfilter') {

                        this.getSelectOptions(column);

                    }
                }
            },
            getSelectOptions: function(column) {

                var options = [];
                this.select_options[column.id] = options;

                this.$axios.get('/api/v1' + column.options_source, { headers: this.headers })
                    .then(response => {

                        var data = JSON.parse(response.data.data);

                        for (var n = 0; n < data.length; n++) {

                            var value = {
                                value: data[n][column.option_key],
                                text: data[n][column.option_value],
                            };
                            options.push(value);
                        }
                        this.select_options[column.id] = options;
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: 'Error, cannot load options', autohide: true});
                    });
            },
            customButton: function(event){
                if (event.target.dataset.api) {
                    this.$axios.get('/api/v1' + event.target.dataset.api, {headers: this.headers})
                        .then(response => {
                            var result = JSON.parse(response.data);
                            if (result.success) {
                                this.$store.commit('setAlert', {type: 'success', message: translations[result.message] || result.message, autohide: true});
                                this.list();
                            } else {
                                this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                            }
                        })
                        .catch(e => {
                            this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                        });
                } else if (event.target.dataset.url) {






                    this.$router.push({ path: '/' + this.$store.state.locale + '/admin' + event.target.dataset.url })
                }
            },
            setComponent: function(event) {

                this.form_id = event.target.dataset.id;
                this.component = event.target.dataset.component;
                this.mode  = 'component';
            },
            setTableMode: function(event) {

                var tablemode = event.target.dataset.tablemode;

                if (tablemode == 1){
                    this.tablemode = true;
                    this.$cookies.set('tablemode', 1);
                    this.limit = 10;
                    this.list();
                } else {
                    this.tablemode = false;
                    this.$cookies.remove('tablemode');
                    this.limit = 7;
                    this.list();
                }
            },

            tableView: function() {




            },
            tableEdit: function() {




            },
            tableCloseDialog: function() {
                this.table.dialog = false;
                setTimeout(() => {
                    this.editedItem = Object.assign({}, this.defaultItem)
                    this.editedIndex = -1
                }, 300)
            },

            validateField: function(e) {

                var value = e.target.value;

                if (value != '') {
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                } else {
                    e.target.classList.remove('is-valid');
                    e.target.classList.add('is-invalid');
                }
                this.showTooltip();
            },
            validateEmail: function(e) {

                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                var value = e.target.value;

                if (value != '' && re.test(String(value).toLowerCase())) {
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                } else {
                    e.target.classList.remove('is-valid');
                    e.target.classList.add('is-invalid');
                }
                this.showTooltip();
            },
            validatePhone: function() {

                if (this.phone == '' || typeof this.phone == typeof undefined) {
                    document.getElementById('phone').classList.remove('is-valid');
                    document.getElementById('phone').classList.add('is-invalid');
                } else {
                    document.getElementById('phone').classList.remove('is-invalid');
                    document.getElementById('phone').classList.add('is-valid');
                }
                this.showTooltip();
            },
            validateSelectFilter: function(e) {

                if (this.form_data['SysModel'] != '') {
                    document.getElementById('form-SysModel').parent.classList.remove('is-invalid');
                    document.getElementById('form-SysModel').parent.classList.add('is-valid');
                } else {
                    document.getElementById('form-SysModel').parent.classList.remove('is-valid');
                    document.getElementById('form-SysModel').parent.classList.add('is-invalid');
                }
                this.showTooltip();
            },
            validateCheckbox: function(e) {

                if (e.target.checked) {
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                    e.target.parentNode.classList.remove('red');
                } else {
                    e.target.classList.remove('is-valid');
                    e.target.classList.add('is-invalid');
                    e.target.parentNode.classList.add('red');
                }
                this.showTooltip();
            },
            showTooltip: function() {

                this.tooltip = '';
                var fields = document.getElementsByClassName('form-required');
                for(var i = 0; i < fields.length; i++) {

                    if (fields[i].classList.contains('is-invalid')) {
                        this.tooltip = fields[i].getAttribute('id');
                        return;
                    }
                }
            },
            setRules: function() {

                for (var i in this.columns) {

                    if (this.columns[i]['required']) {

                        this.validation[this.columns[i]['id']] = [this.rules.required];

                    }
                }

            }
        }
    }
</script>

<style lang="scss" scoped>

h1 {
    text-transform: uppercase;
    font-weight: 300;
    font-size: 2.8rem;
    margin-top: 14vh;
    margin-bottom: 5vh;
    text-align: center;
}

.title-replace-spacer {
    margin-top: 14vh;
    margin-bottom: 5vh;
}

body.darkmode h1,
body.darkmode h2,
body.darkmode h3,
body.darkmode h4 {
    color: white;
}

.data-list-container {
    height: 100%;
    background-color: rgba(40,40,40,0.05);
    padding-top: 0;
}

.data-list-container .v-pagination {
    margin-bottom: 0;
    list-style: none;
    padding-left: 0 !important;

    .btn {
        display: inline-block;
        width: 100%;
        padding: 8px 10px;
        margin-right: 1px;
        border-radius: 0;
        font-weight: 300;
   }
}

body.darkmode .data-card-pagination {

    .btn {
        background-color: rgba(40,40,40,0.9);
    }
    .active .btn,
    .btn:hover {
        background-color: rgba(100,100,100,0.5);
    }
}

.data-form-container {
    height: 100%;
    padding-top: 10px;
}

.data-functions-container {
    width: 100%;
    max-width: 300px;
    margin: 0 auto;

    i {
        margin-right: 10px;
    }
}

body.darkmode .data-list-container {
    background-color: rgba(40,40,40,0.2);
}

.table-filter {
    width: 100%;
    max-width: 500px;
    margin: 0 auto;

    a {
        cursor: pointer;
    }
}

body.darkmode .table-filter a:hover {
    color: white !important;
}


.data-card {
    position: relative;
    -webkit-transition: background-color 0.1s ease-in-out;
    -moz-transition: background-color 0.1s ease-in-out;
    -o-transition: background-color 0.1s ease-in-out;
    transition: background-color 0.1s ease-in-out;

    ul {
        width: 0;
        list-style: none;
        margin-bottom: 0;
        padding-left: 0;
        overflow: hidden;
        -webkit-transition: width 0.25s ease-in-out;
        -moz-transition: width 0.25s ease-in-out;
        -o-transition: width 0.25s ease-in-out;
        transition: width 0.25s ease-in-out;

        li {
            button {
                width: 40px;
                min-height: 40px;
                border: none;
                border-radius: 0;
                font-size: 18px;
                text-align: center;
            }
        }
    }

    .data-card-content {
        padding: .8rem;
        color: #333333;

        h3 {
            font-size: 1.2rem;
            margin-bottom: 0.1rem;
        }

        .data-card-label {
            font-size: 0.8rem;
        }

        .data-card-value {
            font-size: 0.8rem;
            overflow-wrap: break-word;
        }

        .card-id {
            position: absolute;
            bottom: 10px;
            right: 10px;
            font-size: 0.7rem;
        }
    }

    .select-delete {
        position: absolute;
        top: 10px;
        right: 10px;
    }
}

body.darkmode .data-card .data-card-content {
    color: #ffffff;
}


.data-card:nth-child(odd) {
    background-color: rgba(0,0,0,0.15);
}
.data-card:nth-child(even) {
    background-color: rgba(0,0,0,0.05);
}
body.darkmode .data-card:nth-child(even) {
    background-color: rgba(10,10,10,0.4);
}


.data-card:hover {

    background-color: rgba(92,201,245,0.2);

    ul {
        width: 50px;
    }
}

body.darkmode .data-card:hover {
    background-color: rgba(40,40,40,0.95);
}

body .data-card.to-delete:nth-child(odd) {
    background-color: rgba(255,0,0,0.5);
}
body .data-card.to-delete:nth-child(even) {
    background-color: rgba(255,0,0,0.6);
}
body .data-card.to-delete:hover {
    background-color: rgba(255,0,0,0.8);
}
body.darkmode .data-card.to-delete:nth-child(odd) {
    background-color: rgba(255,0,0,0.6);
}
body.darkmode .data-card.to-delete:nth-child(even) {
    background-color: rgba(200,0,0,0.6);
}
body.darkmode .data-card.to-delete:hover {
    background-color: rgba(255,0,0,0.8);
}

.table {
    width: 100%;

    border-spacing: 0;
    border-collapse: collapse;
    font-size: 0.9rem;
    font-weight: 300;
    overflow: hidden;
    border-radius: 4px;

    tr:nth-child(odd) {
        background-color: rgba(0,0,0,0.15);
    }
    tr:nth-child(even) {
        background-color: rgba(0,0,0,0.05);
    }

    th, td {
        padding: 12px 8px;

    }

    td:last

    .table-sort {
        color: black;
    }
}

body.darkmode .table {

    background-color: rgba(40,40,40,0.5);
    color: white;

    th, td {
        border-top: none;
    }

    .table-sort {
        color: white;
    }
}

.table-filter-row td {
    padding: 0;
    background: white;
}
.table-filter-row td input,
.table-filter-row td select {
    padding: 8px;
    width: 100%;
    border: 0;
}

body.darkmode .table .thead-dark th {
    background-color: rgba(10,10,10,0.7);
}

body.darkmode .table-filter-row td {
    background-color: rgba(40,40,40,0.7);
}
body.darkmode .table-filter-row td input,
body.darkmode .table-filter-row td select {
    background-color: transparent;
}

body.darkmode .table-filter-row td input::placeholder,
body.darkmode .table-filter-row td select::placeholder {
    color: #ccc;
}

body.darkmode .table-filter-row td input:-ms-input-placeholder,
body.darkmode .table-filter-row td select:-ms-input-placeholder {
    color: #ccc;
}

body.darkmode .table-filter-row td input::-ms-input-placeholder,
body.darkmode .table-filter-row td select::-ms-input-placeholder {
    color: #ccc;
}

.data-manager-table tbody tr td:last-child {
    text-align: right;
}
</style>
