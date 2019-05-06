<template>


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
                modal: false,
                alert: {},
                isShow: false
            }
        },
        computed: {
            translations () {
                return this.$store.state.translations;
            }
        },
        created() {

        },
        methods: {

        }
    }
</script>


<style lang="scss" scoped>


</style>
