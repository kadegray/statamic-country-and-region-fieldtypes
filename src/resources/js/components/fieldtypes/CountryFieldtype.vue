<template>
    <div>
        <v-select
            v-model="country"
            :options="countries"
            :placeholder="placeholder || config.display"
            @input="changed" />
    </div>
</template>

<script>

import _filter from 'lodash/filter';
import _first from 'lodash/first';
import _get from 'lodash/get';

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
            countries: [],
            country: null,
        };
    },

    watch: {
        value(country, oldCountry) {

            if (country === oldCountry) {
                
                return;
            }

            this.getCountriesAndSetCountry(country);
        },
    },

    mounted() {
        this.getCountriesAndSetCountry(this.value);
    },

    methods: {
        getCountriesAndSetCountry(country) {

            return new Promise((resolve) => {

                if (country) {
                    this.update(country);
                }

                this.getCountries().then(() => {

                    if (!this.value) {

                        return;
                    }
                
                    this.country = _first(_filter(this.countries, (country) => {
                        
                        return country.value == this.value;
                    }));

                    resolve();
                });
            });
        },
        getCountries() {

            return new Promise((resolve) => {

                fetch('/!/statamic-country-and-region-fieldtype/countries')
                    .then(response => response.json())
                    .then((countries) => {
                        this.countries = countries;
                        resolve();
                    });
            });
        },
        changed() {
            
            this.update(_get(this, 'country.value'));
        },
    }

};
</script>
