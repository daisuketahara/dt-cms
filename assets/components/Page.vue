<template>
    <transition name="fade" enter-active-class="animated fadeInRight" leave-active-class="animated fadeOutDown">
        <div>
            <div class="" v-html="content" v-link></div>
        </div>
    </transition>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "Page",
        data() {
            return {
                title: '',
                content: ''
            }
        },
        mounted() {
            this.getPage();
        },
        methods: {
            getPage: function() {

                let url = '/api/v1/page/' + this.$attrs.id + '/' + this.$attrs.locale + '/';

                axios.get(url)
                .then(response => {
                    var result = JSON.parse(response.data);
                    if (result.success == true) {
                        this.title = result.data.pageTitle;
                        this.content = result.data.content;
                        this.pageCss(result.data.customCss);
                    }
                })
                .catch(e => {
                    console.log(e);
                });
            },
            pageCss: function(css) {

                /* Create style document */
                var style = document.createElement('style');
                style.type = 'text/css';

                if (style.styleSheet)
                    style.styleSheet.cssText = css;
                else
                    style.appendChild(document.createTextNode(css));

                /* Append style to the tag name */
                document.getElementsByTagName("head")[0].appendChild(style);
            }
        },
        created: function () {
        },
        beforeRouteLeave (to, from, next) {
            this.$modal.hide('header-nav');
        }
    }
</script>

<style scoped>

</style>
