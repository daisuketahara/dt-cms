<template>
    <transition name="fadeIn" enter-active-class="animated fadeIn">
        <div v-if="true" class="container">
            <div id="map"></div>
        </div>
    </transition>
</template>

<script>

import tt from '@tomtom-international/web-sdk-maps';
import tt_services from '@tomtom-international/web-sdk-services';

export default {

    name: "TomTomMap",
    data() {
        return {
            loaded: false,
            map: {},
            marker: {},
            markers: [],
            popup: {},
            address: '',
            center: [],
            style: 'tomtom://vector/1/basic-main',
        }
    },
    computed: {
        authenticated() {
            return this.$store.state.authenticated;
        },
        initialised() {
            return this.$store.state.init;
        },
        locales() {
            return this.$store.state.locales;
        },
        locale() {
            return this.$store.state.locale;
        },
        locale_id() {
            return this.$store.state.locale_id;
        },
        translations() {
            return this.$store.state.translations;
        },
        darkmode() {
            return this.$store.state.darkmode;
        }
    },
    mounted() {

        console.log(this.$attrs.longitude);


        if (typeof this.$attrs.longitude != 'undefined' && typeof this.$attrs.latitude != 'undefined') {

            this.center = [
                this.$attrs.longitude,
                this.$attrs.latitude
            ];

        } else if (typeof this.$attrs.address != 'undefined') {

            this.address = this.$attrs.address;

        }

        if (this.darkmode == 1) this.style = 'tomtom://vector/1/basic-night';
        else this.style = 'tomtom://vector/1/basic-main';

        if (this.center.length == 0) this.geocode();
        else this.createMap();

    },
    methods: {
        geocode: function() {
            tt_services.services.geocode({
                key: 'DpmSXVu5ZQJADM9nOdqWL4G5AVXvqngh',
                query: this.address
            })
                .go()
                .then(this.setCenter)
                .catch(this.notFound);
        },
        setCenter: function(result) {

            //console.log(result);

            this.center = [
                result.results[0].position.lng,
                result.results[0].position.lat
            ];

            this.createMap();
        },
        createMap: function() {

            this.map = tt.map({
                key: 'DpmSXVu5ZQJADM9nOdqWL4G5AVXvqngh',
                container: 'map',
                style: this.style,
                center: this.center,
                zoom: 14,

                // Disable interactions.
                //doubleClickZoom: false,
                scrollZoom: false,
                //dragPan: false,
                //boxZoom: false,
                //dragRotate: false,
                //touchZoomRotate: false,
                //pitchWithRotate: false
            });
            this.map.addControl(new tt.FullscreenControl());
            this.map.addControl(new tt.NavigationControl());

            this.marker = new tt.Marker({
                draggable: false
            }).setLngLat(this.center).addTo(this.map);

            this.loaded = true;

            if (typeof this.$attrs.title != 'undefined') {

                var markerHeight = 50, markerRadius = 10, linearOffset = 50;
                var popupOffsets = {
                    'top': [0, 0],
                    'top-left': [0,0],
                    'top-right': [0,0],
                    'bottom': [0, -markerHeight],
                    'bottom-left': [linearOffset, (markerHeight - markerRadius + linearOffset) * -1],
                    'bottom-right': [-linearOffset, (markerHeight - markerRadius + linearOffset) * -1],
                    'left': [markerRadius, (markerHeight - markerRadius) * -1],
                    'right': [-markerRadius, (markerHeight - markerRadius) * -1]
                };
                var popup = new tt.Popup({offset: popupOffsets, className: 'map-popup'})
                    .setLngLat(this.center)
                    .setHTML(this.$attrs.title)
                    .addTo(this.map);
            }
        },
        notFound: function() {

        }

    }
}
</script>

<style lang="scss">
    #map {
        height: 500px;
        width: auto;
        margin-bottom: 50px;
    }

    .map-popup {

        button {
            float: right;
            background: transparent;
            border: 0;
            margin-right: -8px;
            margin-top: -8px;
        }
    }

    .mapboxgl-ctrl-bottom-left,
    .mapboxgl-ctrl-bottom-right,
    .mapboxgl-ctrl-top-left,
    .mapboxgl-ctrl-top-right {

        position: absolute !important;

    }

</style>
