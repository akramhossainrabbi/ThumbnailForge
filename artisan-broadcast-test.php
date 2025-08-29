<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$job = App\Models\ThumbnailJob::first();
if ($job) {
    event(new App\Events\ThumbnailJobUpdated($job));
    echo "event-dispatched\n";
} else {
    echo "no-job-found\n";
}
