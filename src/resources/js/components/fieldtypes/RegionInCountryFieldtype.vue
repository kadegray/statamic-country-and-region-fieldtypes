<template>
    <div>
        <country-fieldtype v-model="country" handle="country" ref="country"
            placeholder="Country" />
        <region-fieldtype v-model="region" handle="region" ref="region"
            :placeholder="config.display"
            class="mt-1" />
    </div>
</template>

<script>
import _split from 'lodash/split';
import _first from 'lodash/first';

export default {

    mixins: [Fieldtype],

    mounted() {
        this.initializeFromValue();
    },

    data() {

        return {
            country: null,
            region: null,
        };
    },

    watch: {
        country(country) {
            this.$refs.region.getRegions(this.country);
        },
        region(region) {
            if (region) {
                this.update(this.region);
            }
        },
        value() {
            this.initializeFromValue();
        },
    },

    methods: {
        initializeFromValue() {
            const countryCode = _first(_split(this.value, '-'));

            this.$refs.country.getCountriesAndSetCountry(countryCode)
                .then(() => {
                    this.$refs.region.getRegions(this.country).then(() => {
                        this.$refs.region.update(this.value);
                    });
                });
        },
    }

};
</script>
