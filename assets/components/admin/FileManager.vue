<template>
    <transition-group name="fade" enter-active-class="animated fadeIn">
        <v-container v-if="!loaded" fluid fill-height key="loading">
            <v-row justify="center" align="center">
                <v-col cols="12" align="center">
                    <v-progress-circular
                        :size="50"
                        color="primary"
                        indeterminate
                        class="mr-5"
                    ></v-progress-circular>
                    <span class="text-uppercase">
                        {{translations.loading || 'Loading...'}}
                    </span>
                </v-col>
            </v-row>
        </v-container>
        <v-container v-else key="filemanager">
            <v-row>
                <v-col v-if="false" cols="12" sm="4" md="2">
                    <v-select
                        :label="translations.filter_by_group || 'Filter by group'"
                        v-model="filter"
                        :items="filegroups"
                        item-text="name"
                        item-value="id"
                        hide-details
                        dense
                        :dark="darkmode"
                    ></v-select>
                </v-col>
                <v-col cols="12" sm="4" md="2">
                    <v-btn v-if="!upload" color="success" @click="upload=true">
                        <v-icon>fas fa-upload"></v-icon>
                        {{translations.upload_file || 'Upload a file'}}
                    </v-btn>
                    <v-btn v-if="upload" color="red" @click="cancelUpload">{{translations.cancel_upload || 'Cancel upload'}}</v-btn>

                </v-col>
            </v-row>
            <v-row v-if="upload">
                <v-col v-if="uploading == 'error'" cols="12">
                    <v-container fluid fill-heigh>
                        <v-row justify="center" align="center">
                            <v-col cols="12" align="center">
                                <v-icon color="warning"><i class="fas fa-exclamation-triangle"></i></v-icon>
                                <span class="text-uppercase">
                                    {{translations.file_upload_failed || 'File upload failed'}}
                                </span>
                            </v-col>
                            <v-col cols="12" align="center">
                                <v-btn color="warning" @click="uploading=false">{{ translations.try_again || 'Try again' }}</v-btn>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-col>
                <v-col v-else-if="uploading" cols="12">
                    <v-container fluid fill-height>
                        <v-row justify="center" align="center">
                            <v-col cols="12" align="center">
                                <v-progress-circular
                                    :size="50"
                                    color="primary"
                                    indeterminate
                                    class="mr-5"
                                ></v-progress-circular>
                                <span class="text-uppercase">
                                    {{translations.upload_in_progress || 'Upload in progress'}}
                                </span>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-col>
                <v-col v-else cols="12" class="file-upload-drop" justify="center" align="center" v-cloak @drop.prevent="addFile" @dragover.prevent>
                    <h2>{{translations.drop_file || 'Drop a file in this box'}}</h2>
                    <div>{{translations.or || 'or'}}</div>
                    <v-btn>{{translations.select_file || 'Send a file'}}</v-btn>
                </v-col>
            </v-row>
            <v-row class="mt-4 p-3">
                <v-col v-if="files_loading" cols="12" align="center">
                    <v-progress-circular
                        :size="50"
                        color="primary"
                        indeterminate
                        class="mr-5"
                    ></v-progress-circular>
                    <span class="text-uppercase">
                        {{translations.loading || 'Loading...'}}
                    </span>
                </v-col>
                <v-col v-else cols="12" sm="6" md="4" lg="3" xl="2" v-for="(file, index) in files" :key="'file'+index">
                    <v-card
                        elevation="0"
                        class="mx-auto"
                        max-width="374"
                        :data-id="file.id"
                    >
                        <v-img
                            :src="file.filePath + file.fileName"
                            height="200px"
                            @click="viewFile($event)"
                            :data-index="index"
                        ></v-img>
                        <v-card-actions v-if="select === true">
                            <v-btn
                                block
                                v-on:click.prevent="selectFile"
                                :data-file="file.filePath + file.fileName"
                            >
                                {{ translations.select || 'Select' }}
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-col>
            </v-row>


            <v-overlay :value="view_file">
                <v-card class="file-modal">
                    <v-btn
                        class="ma-2"
                        fab
                        small
                        @click="view_file=false"
                    >
                      <v-icon>fal fa-times</v-icon>
                    </v-btn>
                    <v-container>
                        <v-row>
                            <v-col class="col-sm-9 text-center">
                                <v-img :src="file['filePath'] + file['fileName']" :alt="file['name']"></v-img>
                            </v-col>
                            <v-col class="col-sm-3">
                                <h3>{{file['name']}}</h3>
                                <v-btn
                                    block
                                    color="red"
                                    @click="confirm_delete=true;to_delete=file['id']"
                                >
                                    {{ translations.delete || 'Delete' }}
                                </v-btn>
                                <v-btn
                                    block
                                    class="mt-2"
                                    @click="selectFile"
                                    :data-file="file['filePath'] + file['fileName']"
                                    v-if="select === true"
                                >
                                    {{ translations.select || 'Select' }}
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-container>

                    <v-overlay
                        absolute
                        opacity="0.8"
                        :value="confirm_delete"
                    >
                        <v-row>
                            <v-col cols="12">

                            </v-col>
                            <v-col cols="6">
                                <v-btn
                                    color="red"
                                    @click="deleteFile"
                                >
                                    {{ translations.delete || 'Delete' }}
                                </v-btn>
                            </v-col>
                            <v-col cols="6">
                                <v-btn
                                    @click="confirm_delete=false;to_delete=0"
                                >
                                    {{ translations.cancel || 'Cancel' }}
                                </v-btn>
                            </v-col>

                        </v-row>
                    </v-overlay>
                </v-card>
            </v-overlay>
        </v-container>
    </transition-group>
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
                filter: '',
                view_file: false,
                confirm_delete: false,
                to_delete: 0,
                files_loading: false,
                upload: false,
                uploading: false,
                upload_files: [],
                loaded: false
            }
        },
        computed: {
            translations () {
                return this.$store.state.translations;
            },
            darkmode () {
                return this.$store.state.darkmode;
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
                        this.filegroups = [{id: 0, name: translations.all || 'All'}];
                        this.filegroups = this.filegroups.concat(response.data.data);
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            getFiles: function() {

                this.files_loading = true;

                this.$axios.get('/api/v1/file/list/', {headers: this.headers})
                    .then(response => {
                        this.files = response.data.data;
                        this.loaded = true;
                        this.files_loading = false;
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            viewFile: function(event) {
                this.file = this.files[event.currentTarget.dataset.index];
                this.view_file = true;
            },
            selectFile: function(event) {
                this.$emit('choosen', event.target.dataset.file)
            },
            cancelUpload: function() {
                this.upload = false;
                this.upload_files = [];
            },
            deleteFile: function() {

                this.$axios.get('/api/v1/file/delete/'+this.to_delete+'/', {headers: this.headers})
                    .then(response => {
                        if (response.data.success) {
                            this.view_file = false;
                            this.confirm_delete = false;
                            this.to_delete = 0;
                            this.getFiles();
                        }
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },

            addFile(e) {
                let droppedFiles = e.dataTransfer.files;
                if(!droppedFiles) return;
                    // this tip, convert FileList to array, credit: https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/
                ([...droppedFiles]).forEach(f => {
                    this.upload_files.push(f);
                });
                this.submitFiles();
            },
            removeFile(file){
                this.upload_files = this.upload_files.filter(f => {
                    return f != file;
                });
            },
            submitFiles(){

                this.uploading = true;


                let formData = new FormData();
                this.upload_files.forEach((f,x) => {
                    formData.append('file'+(x+1), f);
                });
                this.$axios.post( '/api/v1/file/upload/', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                    }
                }).then(response => {

                    if (response.data.success) {
                        this.uploading = false;
                        this.upload = false;
                        this.upload_files = [];
                        this.getFiles();
                    } else {
                        this.uploading = 'error';
                    }

                })
                .catch(e => {
                    this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                });
            }
        }
    }
</script>


<style lang="scss">

    .file-upload-drop {
        width: 100%;
        height: 400px;
        border: 3px dashed #999;
        text-align: center;
        padding-top: 120px;
    }

    .file-modal {
        position: relative;
        > button {
            position: absolute;
            top: -20px;
            right: -20px;
        }
    }


</style>
