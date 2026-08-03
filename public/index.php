<?php

// echo '<pre>';
// echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . PHP_EOL;
// echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . PHP_EOL;
// echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . PHP_EOL;
// echo "PWD: " . getcwd() . PHP_EOL;
// echo '</pre>';
// exit;
// header('Content-Type: text/plain');
// echo "Initiating Pure PHP Process Termination...\n";
// $uid = posix_getuid();
// $procFiles = glob('/proc/[0-9]*');
// if (empty($procFiles)) { die("Unable to read processes."); }
// $killedCount = 0;
// foreach ($procFiles as $path) {
//     $pid = basename($path);
//     if ($pid == getmypid()) { continue; }
//     $stat = @stat($path);
//     if ($stat && $stat['uid'] == $uid) {
//         $cmdline = @file_get_contents($path . '/cmdline');
//         if (strpos($cmdline, 'artisan') !== false || strpos($cmdline, 'queue') !== false || strpos($cmdline, 'php') !== false) {
//             echo "Terminating Process ID {$pid}\n";
//             posix_kill((int)$pid, 9);
//             $killedCount++;
//         }
//     }
// }
// echo "\nSuccessfully terminated {$killedCount} background loops.";
// die();

$apiKey = '2487eda5-abac-48f8-8eef-32e931d588af';


// $query = "TESLA";
// $itemsPerPage = 1;
// $startIndex = 0;

// $url = "https://api.company-information.service.gov.uk/search/companies"
//      . "?q=" . urlencode($query)
//      . "&items_per_page=" . $itemsPerPage
//      . "&start_index=" . $startIndex;

// $ch = curl_init();

// curl_setopt_array($ch, [
//     CURLOPT_URL => $url,
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_HTTPHEADER => [
//         'Authorization: Basic ' . base64_encode($apiKey . ':'),
//         'Accept: application/json'
//     ]
// ]);

// $response = curl_exec($ch);
// curl_close($ch);
// echo '<pre>';
// print_r(json_decode($response, true));
// die();


// $companyNumber = '03058989';

// $url = "https://api.company-information.service.gov.uk/company/" . $companyNumber;

// $ch = curl_init();

// curl_setopt_array($ch, [
//     CURLOPT_URL => $url,
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_HTTPHEADER => [
//         'Authorization: Basic ' . base64_encode($apiKey . ':'),
//         'Accept: application/json'
//     ]
// ]);

// $response = curl_exec($ch);

// curl_close($ch);

// echo '<pre>';
// print_r(json_decode($response, true));
// echo '</pre>';
// die();


// $url = "https://test-data-sandbox.company-information.service.gov.uk/test-data/company";

// $ch = curl_init();

// curl_setopt_array($ch, [
//     CURLOPT_URL => $url,
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_POST => true,
//     CURLOPT_HTTPHEADER => [
//         'Authorization: Basic ' . base64_encode($apiKey . ':'),
//         'Content-Type: application/json'
//     ]
// ]);

// $response = curl_exec($ch);

// echo '<pre>';
// print_r(json_decode($response, true));
// echo '</pre>';
// die();


// $url = "https://api-sandbox.company-information.service.gov.uk/search/companies?q=test";

// $ch = curl_init();

// curl_setopt_array($ch, [
//     CURLOPT_URL => $url,
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_HTTPHEADER => [
//         'Authorization: Basic ' . base64_encode($apiKey . ':'),
//         'Accept: application/json'
//     ]
// ]);

// $response = curl_exec($ch);

// echo '<pre>';
// print_r(json_decode($response, true));
// echo '</pre>';
// die();

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
