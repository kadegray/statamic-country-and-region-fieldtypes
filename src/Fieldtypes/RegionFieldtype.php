<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes;

use Illuminate\Support\Facades\App;
use Statamic\Fields\Fieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\FieldtypeFilters\RegionFieldtypeFilter;
use Kadegray\StatamicCountryAndRegionFieldtypes\Traits\FetchLocale;

class RegionFieldtype extends Fieldtype
{
    use FetchLocale;

    /**
     * The blank/default value.
     *
     * @return array
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        return $data;
    }

    /**
     * Process the data before it gets saved.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function process($data)
    {
        return $data;
    }

    public function filter()
    {
        return new RegionFieldtypeFilter($this);
    }

    protected function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                'type' => 'text',
                'default' => 'Select region...',
                'width' => 50,
            ],
            'country' => [
                'display' => 'Country',
                'instructions' => 'Select how you would like to filter Regions by Country.',
                'type' => 'select',
                'default' => 'countries_field',
                'options' => [
                    'countries_field' =>  'Field',
                    'countries_manual' => 'Manual',
                ],
                'width' => 50,
            ],
            'countries_manual' => [
                'display' => 'Manually defined Countries',
                'instructions' => 'Select specific Countries.',
                'type' => 'country',
                'multiple' => true,
                'width' => 50,
                'if' => [
                    'country' => 'countries_manual',
                ]
            ],
            'countries_field' => [
                'display' => 'Country field handle',
                'instructions' => 'This should be the handle of the Country Fieldtype that you would like to use.',
                'type' => 'text',
                'default' => 'country',
                'width' => 50,
                'if' => [
                    'country' => 'countries_field',
                ]
            ],
            'max_items' => [
                'display' => __('Max Items'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'min' => 1,
                'type' => 'integer',
                'width' => 50,
            ],
            'multiple' => [
                'display' => __('Multiple'),
                'instructions' => __('statamic::fieldtypes.select.config.multiple'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'clearable' => [
                'display' => __('Clearable'),
                'instructions' => __('statamic::fieldtypes.select.config.clearable'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'render_invalid_value' => [
                'display' => __('Render Invalid Value'),
                'instructions' => 'In antlers, this will render the raw value if the value is not a principal subdivision code (ISO 3166-2). A use case for this would be if you have imported region data that is not in principal subdivision code format and you want it to render anyway.',
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
        ];
    }

    public function augment($value)
    {
        $regions = [];

        if (is_string($value)) {
            $regions[] = $value;
        } else {
            $regions = $value;
        }

        if (!$regions) {
            return null;
        }

        $renderInvalidValue = $this->config('render_invalid_value');

        $originalLocale = App::currentLocale();
        $scarfLocale = $this->getScarfLocale();
        if ($scarfLocale) {
            App::setLocale($scarfLocale);
        }

        $iso31662Regex = '/^[A-Za-z0-9]{2}-[A-Za-z0-9]{2,3}$/';
        foreach ($regions as &$region) {

            $valid = preg_match($iso31662Regex, $region);
            if ($valid !== 1) {
                if (!$renderInvalidValue) {
                    $region = null;
                }
                continue;
            }

            $langKey = "kadegray_scarf::regions.{$region}";
            $localName = __($langKey);
            if ($localName === $langKey) {
                if (!$renderInvalidValue) {
                    $region = null;
                }
                continue;
            }

            $region = $localName;
        }

        if ($scarfLocale) {
            App::setLocale($originalLocale);
        }

        if (count($regions) > 1) {
            return $regions;
        }

        return $regions[0];
    }
}
