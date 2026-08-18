<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { Fieldtype } from '@statamic/cms';
import CountryFieldtype from './CountryFieldtype.vue';
import RegionFieldtype from './RegionFieldtype.vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update } = Fieldtype.use(emit, props);

const country = ref(null);
const region = ref(null);
const countryRef = ref(null);
const regionRef = ref(null);

async function updateValue() {
    const value = props.value;

    if (!value) {
        regionRef.value?.update(null);
        return;
    }

    const countryCode = value.includes('-') ? value.split('-')[0] : value;

    if (countryRef.value?.currentValue() !== countryCode) {
        await countryRef.value.update(countryCode);
        await nextTick();
        const regions = await regionRef.value.getRegions(countryCode);
        regionRef.value.update(regions.length > 0 ? value : null);
    }
}

function countryFieldTypeLoaded() {
    updateValue();
}

function regionFieldTypeLoaded() {
    updateValue();
}

watch(country, async (newCountry, oldCountry) => {
    if (!newCountry) {
        update(null);
        return;
    }

    // If the current value isn't already a region, set the country as the value.
    if (!props.value || !props.value.includes('-')) {
        if (!props.config.region_is_required) {
            update(newCountry);
        }
    }

    await regionRef.value.getRegions(newCountry);

    if (newCountry !== oldCountry) {
        regionRef.value.update(null);
    }
});

watch(region, (newRegion) => {
    if (newRegion) {
        update(newRegion);
    } else if (!props.config.region_is_required) {
        const countryCode = props.value && props.value.includes('-')
            ? props.value.split('-')[0]
            : props.value;

        update(countryCode);
    } else {
        update(null);
    }
});

watch(() => props.value, updateValue);

onMounted(() => {
    updateValue();
});

defineExpose({ ...expose });
</script>

<template>
    <div>
        <CountryFieldtype
            ref="countryRef"
            handle="country"
            :value="country"
            @update:value="country = $event"
            :placeholder="config.country_placeholder"
            :clearable="config.clearable"
            @loaded="countryFieldTypeLoaded"
        />

        <RegionFieldtype
            ref="regionRef"
            handle="region"
            :value="region"
            @update:value="region = $event"
            :placeholder="config.region_placeholder"
            :clearable="config.clearable"
            @loaded="regionFieldTypeLoaded"
            class="mt-1"
        />
    </div>
</template>
