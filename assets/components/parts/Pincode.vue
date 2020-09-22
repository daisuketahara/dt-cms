<template>
    <div v-bind:class="{ 'pincode': true, 'dark': isDark }" class="pincode">
        <label>{{ label }}</label>
        <input v-for="n in length" type="text" v-model="code[n]"  :placeholder="placeholder" @keyup="test" maxlength="1">
    </div>
</template>

<script>

    export default {

        name: "Pincode ",
        props: ['pincode'],
        data() {
            return {
                numbers: true,
                length: 6,
                placeholder: '',
                code: [],
                string: '',
                label: 'Enter pincode',
                align: 'left',
                isDark: false,
            }
        },
        computed: {
        },
        created() {

            if (typeof this.$attrs.length != typeof undefined) this.length = parseInt(this.$attrs.length);
            if (typeof this.$attrs.placeholder != typeof undefined) this.placeholder = this.$attrs.placeholder;
            if (typeof this.$attrs.label != typeof undefined) this.label = this.$attrs.label;
            if (typeof this.$attrs.dark != typeof undefined && this.$attrs.dark != false) this.isDark = true;

        },
        methods: {
            test: function(event) {
                var i = 1;
                this.string = '';
                while (i <= this.length) {
                    if (typeof this.code[i] != typeof undefined) this.string += this.code[i];
                    i++;
                }
                this.$emit('input', this.string);
                if (event.target.nextElementSibling != null) event.target.nextElementSibling.focus();
            }
        }
    }
</script>

<style lang="scss" scoped>
    .pincode {

        label {
            display: block;
            width: 100%;
            margin-bottom: 3px;
        }

        input {
            border: 1px solid #9e9e9e;
            border-radius: 6px;
            text-align: center;
            width: 50px;
            height: 50px;
            font-size: 31px;
            margin: 0 4px 0 0;
            line-height: 77px;
            padding: 10px 0 4px;
        }
    }

    .pincode.dark {

        label {
            color: white;
        }

        input {
            border: 1px solid white;
            color: white;
            background-color: black;
        }
    }
</style>
