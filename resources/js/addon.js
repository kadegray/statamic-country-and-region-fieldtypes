
import CountryFieldtype from './components/fieldtypes/CountryFieldtype.vue';
import RegionFieldtype from './components/fieldtypes/RegionFieldtype.vue';
import RegionInCountryFieldtype from './components/fieldtypes/RegionInCountryFieldtype.vue';

Statamic.booting(() => {
    Statamic.$components.register('country-fieldtype', CountryFieldtype);
    Statamic.$components.register('region-fieldtype', RegionFieldtype);
    Statamic.$components.register('region_in_country-fieldtype', RegionInCountryFieldtype);
});
