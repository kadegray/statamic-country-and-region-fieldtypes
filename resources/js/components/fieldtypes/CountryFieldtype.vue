<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
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

const countries = ref([]);

async function getCountries() {
    if (!countries.value.length) {
        const response = await fetch('/!/statamic-country-and-region-fieldtypes/countries', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': statamicConfig.get('csrfToken'),
            },
        });

        countries.value = await response.json();
    }

    return countries.value;
}

function updateValue(value) {
    update(value);
}

// Lets a linked `region` (or `region_in_country`) fieldtype elsewhere on the same
// form react to this field's value without needing a shared store to read it from.
function broadcastValue() {
    events.$emit('statamic-country-and-region-fieldtypes.country-updated', {
        handle: props.handle,
        value: props.value,
    });
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

    const labelFor = (value) => countries.value.find((country) => country.value === value)?.label ?? value;

    return Array.isArray(props.value)
        ? props.value.map(labelFor).join(', ')
        : labelFor(props.value);
});

function respondToCountryRequest(event) {
    if (event.handle !== props.handle) return;

    broadcastValue();
}

onMounted(async () => {
    await getCountries();
    events.$on('statamic-country-and-region-fieldtypes.request-country', respondToCountryRequest);
    broadcastValue();
    emit('loaded');
});

onUnmounted(() => {
    events.$off('statamic-country-and-region-fieldtypes.request-country', respondToCountryRequest);
});

watch(() => props.value, broadcastValue);

defineExpose({
    ...expose,
    currentValue: () => props.value,
    update,
    getCountries,
});
</script>

<template>
    <div>
        <Combobox
            :model-value="value"
            :options="countries"
            :multiple="Boolean(config.multiple)"
            :clearable="clearable || Boolean(config.clearable)"
            searchable
            :read-only="isReadOnly"
            :disabled="isReadOnly"
            :placeholder="placeholder || config.placeholder || 'Select Country'"
            @update:model-value="updateValue"
        />
        <div v-if="config.max_items" class="text-xs ml-1 mt-1.5" :class="limitIndicatorColor">
            <span v-text="currentLength"></span>/<span v-text="config.max_items"></span>
        </div>
    </div>
</template>
