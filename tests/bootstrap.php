<?php

require __DIR__.'/../vendor/autoload.php';

// FetchLocale::getSokilLocales() reads from base_path().'/vendor/sokil/...'. In real
// usage that's the consuming site's vendor dir (correct), but Orchestra Testbench
// boots a synthetic skeleton app whose base_path() points elsewhere. Symlink the
// real package in so the trait finds it during tests.
$target = __DIR__.'/../vendor/orchestra/testbench-core/laravel/vendor/sokil/php-isocodes-db-i18n';
$source = __DIR__.'/../vendor/sokil/php-isocodes-db-i18n';

if (! is_dir($target) && is_dir($source)) {
    @mkdir(dirname($target), 0755, true);
    @symlink($source, $target);
}
