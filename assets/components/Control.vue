<template>
    <form id="" method="post"  v-on:submit.prevent="submit">
        <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
            <div v-if="alert.text != '' && alert.type == 'success'" class="alert alert-success" role="alert">
                {{alert.text}}
            </div>
            <div v-else-if="alert.text != '' && alert.type == 'error'" class="alert alert-danger" role="alert">
                {{alert.text}}
            </div>
        </transition>
        <div v-for="element in elements">
            <div v-if="element.type === 'text'" class="form-group">
                <label :for="'form-'+element.id">{{element.label}}</label>
                <input type="text" :id="'form-'+element.id" :name="'form-'+element.id" :data-id="element.id" class="form-control" :value="form_data[element.id]" v-on:keyup="updateFormData">
            </div>
            <div v-else-if="element.type === 'integer'" class="form-group">
                <label :for="'form-'+element.id">{{element.label}}</label>
                <input type="integer" :id="'form-'+element.id" :name="'form-'+element.id" :data-id="element.id" class="form-control" :value="form_data[element.id]" v-on:keyup="updateFormData">
            </div>
            <div v-else-if="element.type === 'date'" class="form-group">
                <label :for="'form-'+element.id">{{element.label}}</label>
                <input type="date" :id="'form-'+element.id" :name="'form-'+element.id" :data-id="element.id" class="form-control" :value="form_data[element.id]" v-on:keyup="updateFormData">
            </div>
            <div v-else-if="element.type === 'textarea'" class="form-group">
                <label :for="'form-'+element.id">{{element.label}}</label>
                <textarea :id="'form-'+element.id" :name="'form-'+element.id" :data-id="element.id" class="form-control" v-on:keyup="filterlist">{{ form_data[element.id] }}</textarea>
            </div>
            <div v-else-if="element.type === 'texteditor'" class="form-group">
                <label :for="'form-'+element.id">{{element.label}}</label>
                <ckeditor :editor="editor" v-model="form_data[element.id]" :config="editorConfig"></ckeditor>
            </div>
            <div v-else-if="element.type === 'checkbox'" class="form-group">
                <div class="checkbox">
                    <label :for="'form-'+element.id">
                        <input type="checkbox" :id="'form-'+element.id" :name="'form-'+element.id" v-model="form_data[element.id]" v-on:keyup="updateFormData">
                        {{element.label}}
                    </label>
                </div>
            </div>
            <div v-else-if="element.type === 'button'" class="form-group">
                <button class="btn btn-sm btn-secondary" :data-id="element.id" :data-api="element.api" :data-url="element.url" v-on:click.prevent="customButton">{{translations[element.label] || element.label}}</button>
            </div>
            <div v-else class="form-group">
                <label :for="'form-'+element.id">{{element.label}}</label>
                <input type="text" :id="'form-'+element.id" :name="'form-'+element.id" :data-id="element.id" class="form-control" :value="form_data[element.id]" v-on:keyup="updateFormData">
            </div>
        </div>
        <div v-if="api.submit" class="form-group">
            <button class="btn btn-primary">{{translations.submit}}</button>
        </div>
    </form>
</template>

<script>
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    export default {
        name: "control",
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('token')
                },
                elements: [],
                form_data: {},
                alert: {},
                api: {},
                editor: ClassicEditor, //ClassicEditor,
                editorData: {}, //'<p>Rich-text editor content.</p>',
                editorConfig: {
                    'min-height': '500px'
                }
            }
        },
        created() {
            this.getElements ();
        },
        computed: {
            translations () {
                return this.$store.state.translations;
            },
        },
        methods: {
            getElements: function() {
                this.$axios.get('/api/v1'+this.$attrs.info, {headers: this.headers})
                    .then(response => {
                        this.elements = response.data.elements;

                        if (response.data.settings != undefined) this.settings = response.data.settings;
                        else this.settings = {};
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            customButton: function(event){

                if (event.target.dataset.api) {
                    this.$axios.get('/api/v1'+event.target.dataset.api, {headers: this.headers})
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
                    window.location.href = event.target.dataset.url;
                }
            },
            setAlert: function(text, type) {
                var self = this;
                this.alert = {text: text, type: type};
                setTimeout(function() { self.alert = {}; }, 5000);
            }
        },
    }
</script>

<style scoped>

</style>
