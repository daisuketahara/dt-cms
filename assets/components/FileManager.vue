<template>


</template>

<script>
    import axios from 'axios';

    export default {

        name: "filemanagement",
        data() {
            return {
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
