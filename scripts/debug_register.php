<?php

require __DIR__.'/../vendor/autoload.php';
putenv('APP_ENV=test');
putenv('APP_DEBUG=1');
$kernel = new App\Kernel('test', true);
$kernel->boot();
$request = Symfony\Component\HttpFoundation\Request::create('/register');
$response = $kernel->handle($request);
file_put_contents(__DIR__.'/register_response.html', $response->getContent());
echo 'Wrote '.__DIR__.'/register_response.html'."\n";
