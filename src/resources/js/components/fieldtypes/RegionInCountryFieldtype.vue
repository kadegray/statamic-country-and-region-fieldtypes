<template>
    <div>

        <country-fieldtype v-model="country" handle="country" ref="country"
            :placeholder="config.country_placeholder"
            :clearable="config.clearable"
            @loaded="countryFieldTypeLoaded" />

        <region-fieldtype v-model="regions" handle="regions" ref="regions"
            :placeholder="config.region_placeholder"
            :clearable="config.clearable"
            @loaded="regionFieldTypeLoaded"
            class="mt-1" />

    </div>
</template>

<script>
import _split from 'lodash/split';
import _first from 'lodash/first';

export default {

    mixins: [Fieldtype],

    mounted() {
        this.updateValue();
    },

    data() {
        return {
            country: null,
            regions: null,
        };
    },

    watch: {
        async country(country, oldCountry) {
            if (!country) {
                this.update(null);
            }

            const regions = await this.$refs.regions.getRegions(this.country);
            // if (regions.length > 0) {
                this.update(country);
            // }
            
            if (country !== oldCountry) {
                this.$refs.regions.update(null);
            }
        },
        regions(regions) {
            if (regions) {
                this.update(this.regions);
            }
        },
        value() {
            this.updateValue();
        },
    },

    methods: {

        countryFieldTypeLoaded() {
            this.updateValue();
        },

        regionFieldTypeLoaded() {
            this.updateValue();
        },

        async updateValue() {

            let regionCode = this.value;
            if (!regionCode) {
                return;
            }

            let countryCode = regionCode.includes('-')
                ? _first(_split(regionCode, '-'))
                : regionCode;

            await this.$refs.country.setSelected(countryCode);

            const regions = await this.$refs.regions.getRegions(countryCode);
            if (regions.length > 0) {
                this.$refs.regions.update(this.value);
            } else {
                this.$refs.regions.update(null);
            }
        },

    }

};
</script>
