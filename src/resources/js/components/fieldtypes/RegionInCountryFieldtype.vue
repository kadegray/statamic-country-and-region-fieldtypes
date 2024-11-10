<template>
    <div>

        <country-fieldtype v-model="country" handle="country" ref="country"
            :placeholder="config.country_placeholder"
            :clearable="config.clearable"
            @loaded="countryFieldTypeLoaded" />

        <region-fieldtype v-model="region" handle="region" ref="region"
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
            region: null,
        };
    },

    watch: {
        async country(country, oldCountry) {
            if (!country) {
                this.update(null);
                return;
            }

            // if current value is not a region, set country as value
            if (
                !this.value
                || (this.value && !this.value.includes('-'))
            ) {
                if (!this.config.region_is_required) {
                    this.update(country);
                }
            }

            await this.$refs.region.getRegions(this.country);
            
            if (country !== oldCountry) {
                this.$refs.region.update(null);
            }
        },
        region(region) {
            if (region) {
                this.update(region);
            } else if (!this.config.region_is_required) {
                let countryCode = this.value.includes('-')
                    ? _first(_split(this.value, '-'))
                    : this.value;
                this.update(countryCode);
            } else if (this.config.region_is_required) {
                this.update(null);
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

            let theValue = this.value;
            if (!theValue) {
                this.$refs.region.update(null);
                return;
            }

            let countryCode = theValue.includes('-')
                ? _first(_split(theValue, '-'))
                : theValue;

            if (this.$refs.country.value !== countryCode) {
                await this.$refs.country.update(countryCode);
                const regions = await this.$refs.region.getRegions(countryCode);
                this.$refs.region.update(regions.length > 0 ? this.value : null);
            }
        },

    }

};
</script>
