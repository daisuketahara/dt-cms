<template>
    <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
        <div v-if="modal" id="im-editor-modal">
            <div class="im-modal-backdrop">
                <div class="im-modal-body p-4">
                    <div class="im-modal-close" v-on:click="closeModal"><i class="fas fa-times"></i></div>
                    {{modal_content}}
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
    export default {
        name: 'Modal',
        data() {
            return {
                translations: this.$store.state.translations,
                modal_content: '',
                modal:false
            };
        },
        methods: {
            launchModal: function() {
                document.getElementById('admin-c').style.zIndex = '14';
                this.modal = true;
            },
            closeModal: function() {
                this.modal = false;
                this.modal_content = '';
                document.getElementById('admin-content').style.zIndex = '11';
            },
        }
    };
</script>

<style lang="scss" scoped>
    #im-editor-modal{
        display: block;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        z-index: 30000 !important;

        .im-modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background-color: rgba(0,0,0,0.7);

            .im-modal-body {
                width: 80vw;
                height: 90vh;
                margin: 0 auto;
                margin-top: 5vh;
                background-color: rgba(255,255,255,0.8);
            }

            .im-modal-close {
                float: right;
                color: black;
            }
        }
    }
</style>
