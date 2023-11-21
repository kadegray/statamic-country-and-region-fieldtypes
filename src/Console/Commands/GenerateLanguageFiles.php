<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Sokil\IsoCodes\TranslationDriver\SymfonyTranslationDriver;
use Illuminate\Support\Str;

class GenerateLanguageFiles extends Command
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kg:scarf:generate:lang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate language files.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Clean out the lang directory.
        exec('rm -f -r ' . base_path() . "/lang/*");

        // sokil/php-isocodes-db-i18n locales.
        $locales = scandir(base_path() . "/vendor/sokil/php-isocodes-db-i18n/messages/");
        $locales = Arr::where($locales, function (string $value) {
            return !in_array($value, ['.', '..', 'LICENSE']);
        });

        $progressBar = $this->output->createProgressBar(count($locales));
        $progressBar->setFormat('very_verbose');

        $this->line('Processing countries...');
        $progressBar->start();

        foreach ($locales as $locale) {

            if (Str::startsWith($locale, 'iso_')) {
                continue;
            }

            $langDirectory = base_path() . "/lang/{$locale}";
            if (!is_dir($langDirectory)) {
                mkdir($langDirectory);
            }
            $countryPhpFile = fopen("$langDirectory/countries.php", 'c') or die('Unable to open file!');

            $langFile = "<?php\n";
            $langFile .= "\n";
            $langFile .= "// lang/{$locale}/countries.php\n";
            $langFile .= "\n";
            $langFile .= "return [\n";
            fwrite($countryPhpFile, $langFile);

            $driver = new SymfonyTranslationDriver();
            $driver->setLocale($locale);
            $isoCodes = new \Sokil\IsoCodes\IsoCodesFactory(null, $driver);
            foreach ($isoCodes->getCountries() as $country) {
                fwrite($countryPhpFile, "    \"{$country->getAlpha2()}\" => \"{$country->getLocalName()}\",\n");
            }

            fwrite($countryPhpFile, "];\n");
            fclose($countryPhpFile);

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->newLine();
        $this->line('Processing regions...');
        $progressBar->start();

        foreach ($locales as $locale) {

            if (Str::startsWith($locale, 'iso_')) {
                continue;
            }

            $langDirectory = base_path() . "/lang/{$locale}";
            if (!is_dir($langDirectory)) {
                mkdir($langDirectory);
            }
            $regionsPhpFile = fopen("$langDirectory/regions.php", 'c') or die('Unable to open file!');

            $langFile = "<?php\n";
            $langFile .= "\n";
            $langFile .= "// lang/{$locale}/regions.php\n";
            $langFile .= "\n";
            $langFile .= "return [\n";
            fwrite($regionsPhpFile, $langFile);

            $driver = new SymfonyTranslationDriver();
            $driver->setLocale($locale);
            $isoCodes = new \Sokil\IsoCodes\IsoCodesFactory(null, $driver);
            foreach ($isoCodes->getSubdivisions() as $region) {
                fwrite($regionsPhpFile, "    \"{$region->getCode()}\" => \"{$region->getLocalName()}\",\n");
            }

            fwrite($regionsPhpFile, "];\n");
            fclose($regionsPhpFile);

            $progressBar->advance();
        }

        $progressBar->finish();

        return Command::SUCCESS;
    }
}
