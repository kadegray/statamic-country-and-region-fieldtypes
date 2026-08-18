<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Fieldtype } from '@statamic/cms';
import { Combobox } from '@statamic/cms/ui';
import { config as statamicConfig, events } from '@statamic/cms/api';

const emit = defineEmits([...Fieldtype.emits, 'loaded']);

const props = defineProps({
    ...Fieldtype.props,
    placeholder: {
        type: String,
        default: '',
    },
    clearable: {
        type: Boolean,
        default: false,
    },
});

const { expose, update, isReadOnly, defineReplicatorPreview } = Fieldtype.use(emit, props);

const regions = ref([]);
const countriesOverride = ref(null);
const linkedCountries = ref(null);

const countries = computed(() => {
    if (countriesOverride.value) {
        return countriesOverride.value;
    }

    if (props.config.country === 'countries_manual') {
        return props.config.countries_manual;
    }

    if (props.config.country === 'countries_field') {
        return linkedCountries.value;
    }

    return null;
});

async function externalUpdate(newValue, countryCodes = null) {
    if (!countryCodes) {
        update(newValue);
    } else {
        const values = Array.isArray(newValue) ? newValue : [newValue];

        update(values.filter((value) => countryCodes.includes(value.split('-')?.[0])));
    }

    await getRegions();
}

function handleCountryUpdated(event) {
    if (props.config.country !== 'countries_field') return;
    if (event.handle !== props.config.countries_field) return;

    linkedCountries.value = event.value;

    if (event.value && props.value) {
        externalUpdate(props.value, event.value);
    } else {
        externalUpdate(null);
    }
}

async function getRegions(countriesArgOverride = null) {
    if (countriesArgOverride) {
        countriesOverride.value = countriesArgOverride;
        regions.value = [];
    } else if (!countries.value || countries.value.length === 0) {
        regions.value = [];

        return regions.value;
    }

    const response = await fetch(`/!/statamic-country-and-region-fieldtypes/${countries.value}/regions`, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': statamicConfig.get('csrfToken'),
        },
    });

    regions.value = await response.json();

    return regions.value;
}

function updateValue(value) {
    update(value);
}

const currentLength = computed(() => {
    if (!props.value) return 0;

    return typeof props.value === 'string' ? 1 : props.value.length;
});

const limitReached = computed(() => Boolean(props.config.max_items) && currentLength.value >= props.config.max_items);
const limitExceeded = computed(() => Boolean(props.config.max_items) && currentLength.value > props.config.max_items);
const limitIndicatorColor = computed(() => {
    if (limitExceeded.value) return 'text-red';
    if (limitReached.value) return 'text-green';

    return 'text-grey';
});

defineReplicatorPreview(() => {
    if (!props.value) return null;

    const labelFor = (value) => regions.value.find((region) => region.value === value)?.label ?? value;

    return Array.isArray(props.value)
        ? props.value.map(labelFor).join(', ')
        : labelFor(props.value);
});

onMounted(async () => {
    if (props.config.country === 'countries_field' && props.config.countries_field) {
        events.$on('statamic-country-and-region-fieldtypes.country-updated', handleCountryUpdated);
        events.$emit('statamic-country-and-region-fieldtypes.request-country', {
            handle: props.config.countries_field,
        });
    }

    await getRegions();
    emit('loaded');
});

onUnmounted(() => {
    events.$off('statamic-country-and-region-fieldtypes.country-updated', handleCountryUpdated);
});

defineExpose({
    ...expose,
    currentValue: () => props.value,
    update,
    getRegions,
});
</script>

<template>
    <div>
        <Combobox
            :model-value="value"
            :options="regions"
            :multiple="Boolean(config.multiple)"
            :clearable="clearable || Boolean(config.clearable)"
            searchable
            :read-only="isReadOnly"
            :disabled="isReadOnly"
            :placeholder="placeholder || config.display || 'Select Region'"
            @update:model-value="updateValue"
        />
        <div v-if="config.max_items" class="text-xs ml-1 mt-1.5" :class="limitIndicatorColor">
            <span v-text="currentLength"></span>/<span v-text="config.max_items"></span>
        </div>
    </div>
</template>
