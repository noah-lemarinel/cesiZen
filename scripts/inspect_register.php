<?php
require __DIR__ . '/../vendor/autoload.php';
// Load .env.test to ensure test env variables are present
use Symfony\Component\Dotenv\Dotenv;
$env = __DIR__ . '/../.env.test';
if (file_exists($env)) {
    (new Dotenv())->overload($env);
}
putenv('APP_ENV=test');
putenv('APP_DEBUG=1');
$kernel = new App\Kernel('test', true);
$kernel->boot();
$client = new Symfony\Bundle\FrameworkBundle\KernelBrowser($kernel);
$response = $client->request('GET', '/register');
$content = $client->getResponse()->getContent();
file_put_contents(__DIR__ . '/register_debug.html', $content);
echo "Wrote register_debug.html (" . strlen($content) . " bytes)\n";
