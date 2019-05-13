<template>
    <transition name="fade" enter-active-class="animated fadeInRight" leave-active-class="animated fadeOutDown">
        <div>
            <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
                <div v-if="alert.text != '' && alert.type == 'success'" class="alert alert-success" role="alert">
                    {{alert.text}}
                </div>
                <div v-else-if="alert.text != '' && alert.type == 'error'" class="alert alert-danger" role="alert">
                    {{alert.text}}
                </div>
            </transition>
            <nav class="navbar navbar-dark bg-dark">
                <div class="form-inline">
                    <select id="file-group2" name="file-group" class="form-control mr-2">
                        <option value="">- {{translations.filter_by_group || 'Filter by group'}} -</option>
                        <option v-for="filegroup in filegroups" :value="filegroup.id">{{filegroup.name}}</option>
                    </select>
                    <button class="btn btn-secondary" type="submit">{{translations.filter || 'Filter'}}</button>
                </div>
                <button v-if="!upload" class="btn btn-success float-right" v-on:click="uploadFile">
                    <i class="fas fa-upload"></i>
                    {{translations.upload_file || 'Upload a file'}}
                </button>
                <button v-if="upload" class="btn btn-danger float-right" v-on:click="cancelUpload">{{translations.cancel_upload || 'Cancel upload'}}</button>
            </nav>
            <div v-if="upload" class="container-fluid">
                <div class="row mt-4">
                    <form enctype="multipart/form-data" v-on:submit.prevent="submitFiles">
                        <input type="hidden" id="file-group" name="file-group">
                        <input type="hidden" id="hide" name="0">
                        <input type="file" id="files" ref="files" multiple="multiple" v-on:change="handleFilesUpload">
                        <button class="btn btn-secondary" type="submit">{{translations.test || 'Test'}}</button>
                    </form>
                    <div id="file-upload-drop" class="mt-4">
                        <h2>{{translations.drop_file || 'Drop a file in this box'}}</h2>
                        <div>{{translations.or || 'or'}}</div>
                        <button id="btn-file-select" class="btn btn-lg btn-secondary mt-3">{{translations.select_file || 'Select a file'}}</button>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div v-for="(file, index) in files" class="col-md-2" v-on:click="viewFile($event)" :data-index="index">
                    <div class="card mb-4 file-item pointer" :data-id="file.id">
                        <div :style="'background-image: url(/' + file.filePath + file.fileName + ')'" class="file-img"></div>
                        <div class="card-body pt-2 pb-2">
                            <h5 class="card-title text-center font-xs mb-0">{{file.name}}</h5>
                        </div>
                    </div>
                </div>
            </div>
            <modal name="view-file" width="80%" height="90%">
                <div class="container-fluid p-3">
                    <div class="row">
                        <div class="col-sm-9 text-center">
                            <img class="img-fluid" :src="'/' + file['filePath'] + file['fileName']" :alt="file['name']">
                        </div>
                        <div class="col-sm-3">
                            <h3>{{file['name']}}</h3>
                        </div>
                    </div>
                </div>
            </modal>
        </div>
    </transition>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "filemanagement",
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "Authorization" : "Bearer " + this.$store.state.apikey
                },
                filegroups: [],
                files: [],
                file: {},
                upload: false,
                upload_files:'',
                alert: {}
            }
        },
        computed: {
            translations () {
                return this.$store.state.translations;
            }
        },
        created() {
            this.getFileGroups();
            this.getFiles();
        },
        methods: {
            getFileGroups: function() {

                axios.get('/api/v1/filegroup/list/', {headers: this.headers})
                    .then(response => {
                        this.filegroups = JSON.parse(response.data)['data'];
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            getFiles: function() {

                axios.get('/api/v1/file/list/', {headers: this.headers})
                    .then(response => {
                        this.files = JSON.parse(response.data)['data'];
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            viewFile: function(event) {
                this.file = this.files[event.currentTarget.dataset.index];
                this.$modal.show('view-file');
            },
            uploadFile: function(event) {
                this.upload = true;
            },
            cancelUpload: function(event) {
                this.upload = false;
            },
            handleFilesUpload(){
                this.upload_files = this.$refs.files.files;
            },
            submitFiles(){

                let formData = new FormData();
                for( var i = 0; i < this.upload_files.length; i++ ){
                    let file = this.upload_files[i];
                    formData.append('files[' + i + ']', file);
                }
                axios.post( '/api/v1/file/upload/', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        "Authorization" : "Bearer " + this.$store.state.apikey
                    }
                }).then(response => {
                    this.upload = false;
                    this.getFiles();
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


<style lang="scss" scoped>

    #file-upload-drop {
        width: 100%;
        height: 400px;
        border: 3px dashed #999;
        text-align: center;
        padding-top: 120px;
    }
    #btn-file-select {

    }

    .file-img {
        width: 100%;
        height: 120px;
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
    }


</style>
