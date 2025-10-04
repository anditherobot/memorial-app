<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$markdown = file_get_contents(__DIR__.'/../docs/DESIGN_SYSTEM.md');
$html = Illuminate\Support\Str::markdown($markdown);

file_put_contents(__DIR__.'/../docs/components.html', $html);

echo "Successfully generated docs/components.html\n";

