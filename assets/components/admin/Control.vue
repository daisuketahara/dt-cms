<template>
    <v-form>
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
                <richtext v-model="form_data[element.id]" :value="form_data[element.id]"></richtext>
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
                <v-btn color="primary" :data-id="element.id" :data-api="element.api" :data-url="element.url" @click="customButton">{{translations[element.label] || element.label}}</v-btn>
            </div>
            <div v-else class="form-group">
                <label :for="'form-'+element.id">{{element.label}}</label>
                <input type="text" :id="'form-'+element.id" :name="'form-'+element.id" :data-id="element.id" class="form-control" :value="form_data[element.id]" v-on:keyup="updateFormData">
            </div>
        </div>
        <div v-if="api.submit" class="form-group">
            <v-btn color="primary" @click="submit">{{translations.submit}}</v-btn>
        </div>
    </v-form>
</template>

<script>

    export default {
        name: "control",
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                },
                elements: [],
                form_data: {},
                api: {},
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
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            customButton: function(event){

                if (event.target.dataset.api) {
                    this.$axios.get('/api/v1'+event.target.dataset.api, {headers: this.headers})
                        .then(response => {
                            var result = response.data;
                            if (result.success) {
                                this.$store.commit('setAlert', {type: 'success', message: translations[result.message] || result.message, autohide: true});
                            } else {
                                this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                            }
                        })
                        .catch(e => {
                            this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                        });
                } else if (event.target.dataset.url) {
                    window.location.href = event.target.dataset.url;
                }
            }
        },
    }
</script>

<style scoped>

</style>
