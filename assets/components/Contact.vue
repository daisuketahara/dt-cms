<template>
    <transition name="fade" enter-active-class="animated fadeInRight" leave-active-class="animated fadeOutDown">
        <form v-on:submit.prevent="post">
            <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
                <div v-if="alert.text != '' && alert.type == 'success'" class="alert alert-success" role="alert">
                    {{alert.text}}
                </div>
                <div v-else-if="alert.text != '' && alert.type == 'error'" class="alert alert-danger" role="alert">
                    {{alert.text}}
                </div>
            </transition>
            <div v-if="send" class="im-contact-confirm">
                <i class="fal fa-paper-plane"></i>
                <h3>{{translations.message_send || 'Your message has been sent'}}</h3>
            </div>
            <div v-else class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label>{{translations.email || 'Email'}}<span class="red">*</span></label>
                        <input type="email" class="form-control" v-model="email" required>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label>{{translations.phone || 'Phone'}}</label>
                        <input type="text" class="form-control" v-model="phone">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label>{{translations.message || 'Message'}}<span class="red">*</span></label>
                        <textarea class="form-control" v-model="message" rows="6" required></textarea>
                    </div>
                </div>
                <div class="col-12">
                <button type="submit" class="btn btn-primary">{{translations.send || 'Send'}}</button>
                </div>
            </div>
        </form>
    </transition>
</template>

<script>
    import axios from 'axios';
    import VueTelInput from 'vue-tel-input';

    export default {
        components: {
            VueTelInput,
        },
        name: "Contact",
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                },
                translations: {},
                salutation: '',
                name: '',
                email: '',
                phone: '',
                message: '',
                alert: {},
                send: false,
            }
        },
        created: function () {

        },
        computed: {
        },
        methods: {
            post: function() {

                let params = {};
                params.name = this.name;
                params.email = this.email;
                params.phone = this.phone;
                params.message = this.message;
                params.locale = document.body.dataset.locale;

                axios.post('/api/v1/contact/post/', params, {headers: this.headers})
                    .then(response => {
                        var result = JSON.parse(response.data);
                        if (result.success) {
                            this.send = true;
                        }
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
        },
    }
</script>

<style lang="scss" scoped>
    .red { color: red; }
    .im-contact-confirm {

        background-color: #fefefe;
        border-radius: 0,2rem;
        padding: 6rem 15px;
        text-align: center;
        -webkit-box-shadow: 0px 2px 27px 0px rgba(153,153,153,1);
        -moz-box-shadow: 0px 2px 27px 0px rgba(153,153,153,1);
        box-shadow: 0px 2px 27px 0px rgba(153,153,153,1);

        i.fa-paper-plane {
            font-size: 4rem;
            margin-bottom: 2rem;
        }
    }
</style>
