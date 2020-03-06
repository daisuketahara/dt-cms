<template>
    <form id="" method="post">
        <div v-for="column in columns">
            <div v-if="column.type === 'text'" class="form-group">
                <label>{{column.label}}</label>
                <input type="text" id="" name="" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <button class="btn btn-primary">Submit</button>
            </div>
            <div class="col text-right">
                <button class="btn btn-primary" @click="gotoList">Go back</button>
            </div>
        </div>
    </form>
</template>

<script>

    import 'form';

    export default {
        name: "form",
        props: {
            columns: Object
        },
        data() {
            return {}
        },
        mounted: function () {
            $('.select2').select2();
        },
        methods: {},
        created: function () {
        }
    }
</script>

<style scoped>

</style>
