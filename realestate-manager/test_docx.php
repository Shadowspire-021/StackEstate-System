<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$receipt = \App\Models\Receipt::first();
if (!$receipt) {
    echo "No receipts.\n";
    exit;
}

$service = new \App\Services\ReceiptService();
try {
    $path = $service->generate($receipt);
    echo "Generated at: $path\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
