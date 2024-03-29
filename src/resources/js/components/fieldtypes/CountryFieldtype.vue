<template>
    <div>
        <v-select
            ref="input"
            class="flex-1"
            :clearable="clearable || !!config.clearable"
            :options="countries"
            :placeholder="placeholder || config.placeholder || __('Select Country')"
            :multiple="multiple || config.multiple"
            :reset-on-options-change="resetOnOptionsChange"
            :close-on-select="true"
            :value="selected"
            :create-option="(label, value) => ({ value, label })"
            @input="updateValue"
            @search:focus="$emit('focus')"
            @search:blur="$emit('blur')">

                <template #selected-option-container v-if="config.multiple"><i class="hidden"></i></template>
                <template #search="{ events, attributes }" v-if="config.multiple">
                    <input
                        :placeholder="config.placeholder"
                        class="vs__search"
                        type="search"
                        v-on="events"
                        v-bind="attributes"
                    >
                </template>
                <template #no-options>
                    <div class="text-sm text-grey-70 text-left py-1 px-2" v-text="__('No options to choose from.')" />
                </template>
                <template #footer="{ deselect }" v-if="config.multiple && selected && selected.length">
                    <div class="vs__selected-options-outside flex flex-wrap">
                        <span v-for="selectedOption in selected" :key="selectedOption.value" class="vs__selected mt-1">
                            {{ selectedOption.label }}
                            <button v-if="!readOnly" @click="deselect(selectedOption)" type="button" :aria-label="__('Deselect option')" class="vs__deselect">
                                <span>×</span>
                            </button>
                            <button v-else type="button" class="vs__deselect">
                                <span class="opacity-50">×</span>
                            </button>
                        </span>
                    </div>
                </template>

        </v-select>
        <div class="text-xs ml-1 mt-1.5" :class="limitIndicatorColor" v-if="config.max_items">
            <span v-text="currentLength"></span>/<span v-text="config.max_items"></span>
        </div>
    </div>
</template>

<script>
import HasInputOptions from './../../../../../vendor/statamic/cms/resources/js/components/fieldtypes/HasInputOptions.js';
import _get from 'lodash/get';
import _filter from 'lodash/filter';

export default {

    mixins: [Fieldtype, HasInputOptions],

    props: {
        placeholder: {
            type: String,
            default: ""
        },
        clearable: {
            type: Boolean,
            default: false
        },
    },

    data() {

        return {
            countries: [],
            selected: null,
        };
    },

    async mounted() {
        await this.getCountries();
        this.updateSelected();
        this.$emit('loaded');
    },

    watch: {
        value: function(newValue, oldValue) {
            this.updateSelected();
        }
    },

    computed: {

        options() {
            return this.normalizeInputOptions(this.config.options);
        },

        replicatorPreview() {
            switch(typeof this.selected) {
                case 'array':
                    return this.selected.map(option => _get(option, 'label')).join(', ');
                case 'string':
                    return this.selected.label;
            }
        },

        resetOnOptionsChange() {
            // Reset logic should only happen when the config value is true.
            // Nothing should be reset when it's false or undefined.
            if (this.config.reset_on_options_change !== true) return false;

            // Reset the value if the value doesn't exist in the new set of options.
            return (options, old, val) => {
                let opts = options.map(o => o.value);
                return !val.some(v => opts.includes(v.value));
            };
        },

        limitReached() {
            if (! this.config.max_items) return false;

            return this.currentLength >= this.config.max_items;
        },

        limitExceeded() {
            if (! this.config.max_items) return false;

            return this.currentLength > this.config.max_items;
        },

        currentLength() {
            if (this.value) {
                return (typeof this.value == 'string') ? 1 : this.value.length;
            }

            return 0;
        },

        limitIndicatorColor() {
            if (this.limitExceeded) {
                return 'text-red';
            } else if (this.limitReached) {
                return 'text-green';
            }

            return 'text-grey';
        }
    },

    methods: {

        async getCountries() {

            if (!this.countries || this.countries.length === 0) {
                const response = await fetch('/!/statamic-country-and-region-fieldtypes/countries', {
                    method: "POST",
                    headers: {
                        "X-CSRF-Token": Statamic.$config.get("csrfToken"),
                    },
                });
                this.countries = await response.json();
            }

            return this.countries;
        },

        setSelected(countries) {
            this.selected = _.find(this.countries, country => countries.includes(country.value));
        },

        updateSelected() {

            let selected;

            switch(typeof this.value) {
                case 'string':
                case 'number':
                    selected = _.findWhere(this.countries, { value: this.value });
                    break;
                case 'array':
                case 'object':
                    if (!this.value) {
                        selected = null;
                        break;
                    }
                    selected = this.value
                        .map(value => {
                            let test = _.findWhere(this.countries, {value});
                            return test;
                        })
                        .filter(value => value !== undefined);
                    break;
            }

            this.selected = selected;
        },

        updateValue(value) {
            if (this.config.multiple) {
                this.update(value.map(v => v.value));
            } else {
                if (value) {
                    this.update(value.value)
                } else {
                    this.update(null);
                }
            }
        },

        focus() {
            this.$refs.input.focus();
        },
    }

};
</script>
