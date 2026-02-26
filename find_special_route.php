<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

$routes = collect(app('router')->getRoutes()->getRoutes());
$matches = $routes->filter(function($r) {
    return str_contains($r->getActionName(), 'SpecialIdRequestController');
});
foreach ($matches as $r) {
    echo $r->uri() . ' => ' . $r->getActionName() . PHP_EOL;
}
