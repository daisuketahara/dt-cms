<template>
    <transition name="fade" enter-active-class="animated fadeIn">
        <div v-if="loaded">
            <nav class="navbar mt-2">
                <div class="form-inline">
                    <select id="file-group2" name="file-group" class="form-control form-control-sm mr-2">
                        <option value="">- {{translations.filter_by_group || 'Filter by group'}} -</option>
                        <option v-for="filegroup in filegroups" :value="filegroup.id">{{filegroup.name}}</option>
                    </select>
                    <button class="btn btn-sm btn-secondary mr-2" type="submit">{{translations.filter || 'Filter'}}</button>
                    <button v-if="!upload" class="btn btn-sm btn-success" @click="uploadFile">
                        <i class="fas fa-upload"></i>
                        {{translations.upload_file || 'Upload a file'}}
                    </button>
                    <button v-if="upload" class="btn btn-sm btn-danger" @click="cancelUpload">{{translations.cancel_upload || 'Cancel upload'}}</button>
                </div>
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
                    </div>
                </div>
            </div>
            <div class="row mt-4 p-3">
                <div v-for="(file, index) in files" class="col-md-2">
                    <div class="card mb-4 file-item pointer" :data-id="file.id">
                        <div :style="'background-image: url(/' + file.filePath + file.fileName + ')'" class="file-img" @click="viewFile($event)" :data-index="index"></div>
                        <div class="card-body pt-2 pb-2">
                            <h5 class="card-title text-center font-xs mb-0">{{file.name}}</h5>
                            <button v-if="select === true" class="btn btn-sm btn-secondary" v-on:click.prevent="selectFile" :data-file="'/' + file.filePath + file.fileName">Select</button>
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

    export default {
        name: "filemanager",
        props: {
            select: Boolean,
        },
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                },
                filegroups: [],
                files: [],
                file: {},
                upload: false,
                upload_files:'',
                loaded: false
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

                this.$axios.get('/api/v1/filegroup/list/', {headers: this.headers})
                    .then(response => {
                        this.filegroups = JSON.parse(response.data)['data'];
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            getFiles: function() {

                this.$axios.get('/api/v1/file/list/', {headers: this.headers})
                    .then(response => {
                        this.files = JSON.parse(response.data)['data'];
                        this.loaded = true;
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            viewFile: function(event) {
                this.file = this.files[event.currentTarget.dataset.index];
                this.$modal.show('view-file');
            },
            selectFile: function(event) {
                this.$emit('choosen', event.target.dataset.file)
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
                this.$axios.post( '/api/v1/file/upload/', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                    }
                }).then(response => {
                    this.upload = false;
                    this.getFiles();
                })
                .catch(e => {
                    this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                });
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
