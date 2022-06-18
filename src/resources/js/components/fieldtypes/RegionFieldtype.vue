<template>
    <div>
        <v-select
            v-model="region"
            :options="regions"
            :placeholder="placeholder || config.display"
            :resetOnOptionsChange="true"
            @input="internalUpdate" />
    </div>
</template>

<script>

import _filter from 'lodash/filter';
import _first from 'lodash/first';
import _get from 'lodash/get';
import _isNil from 'lodash/isNil';

export default {

    mixins: [Fieldtype],

    props: {
        placeholder: {
            type: String,
            default: ""
        },
    },

    data() {

        return {
            regions: [],
            region: null,
            regionCode: null,
            manualCountry: null,
        };
    },

    created() {
        
        if ('country_field' !== _get(this, 'config.country')) {

            return;
        }

        const countryField = _get(this, 'config.country_field');
        if (!countryField) {

            return;
        }

        this.$watch(`$store.state.publish.base.values.${countryField}`, (countryCode) => {

            if (countryCode) {
                // external country has been set, so update regions and set region
                this.externalUpdate(this.regionCode);
            } else {
                // external country input has been unset
                this.externalUpdate(null);
            }
        });
    },

    computed: {
        country() {

            if (this.manualCountry) {
                
                return this.manualCountry;
            }

            if ('country_manual' === this.config.country) {

                return _get(this.config, 'country_manual');
            }

            if ('country_field' === this.config.country) {

                return _get(this, `$store.state.publish.base.values.${this.config.country_field}`);
            }

            return null;
        }
    },

    mounted() {
        this.externalUpdate(this.value);
    },

    watch: {
        region(region) {
            this.internalUpdate(region);
        },
        value(value) {
            if (value != this.regionCode) {
                this.externalUpdate(value);
            }
        },
    },

    methods: {
        async getRegions(manualCountry) {

            if (manualCountry) {
                this.manualCountry = manualCountry;
            }

            if (!this.country) {
                
                return [];
            }

            const path = `statamic-country-and-region-fieldtype/${this.country}/regions`;
            const data = localStorage.getItem(path);
            if (data) {
                const regions = JSON.parse(data);
                this.regions = regions;

                return;
            }

            let regions = await fetch(`/!/${path}`);
            regions = await regions.json();

            localStorage.setItem(path, JSON.stringify(regions));

            this.regions = regions;

            return;
        },
        async internalUpdate(region) {
            this.regionCode = _get(region, 'value');
            this.update(this.regionCode);
        },
        async externalUpdate(regionCode) {
            this.regionCode = regionCode;
            await this.getRegions();
            this.region = await _first(_filter(this.regions, (region) => region.value == regionCode));
        },
    }

};
</script>
