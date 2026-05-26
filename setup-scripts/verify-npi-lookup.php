<?php
/**
 * @file
 * Verify NPI lookup module and assets are properly loaded.
 */

use Drupal\webform\Entity\Webform;

echo "=== NPI Lookup Module Status ===\n\n";

// 1. Check module is enabled
$moduleHandler = \Drupal::service('module_handler');
$enabled = $moduleHandler->moduleExists('cingulate_npi');
echo "1. Module cingulate_npi: " . ($enabled ? "✓ ENABLED" : "✗ DISABLED") . "\n";

// 2. Check webform field configuration
$webform = Webform::load('register_form');
if ($webform) {
  $elements = $webform->getElementsDecoded();
  $hasButton = isset($elements['npi_number']['#description']) && 
               strpos($elements['npi_number']['#description'], 'npi-lookup-button') !== false;
  echo "2. NPI lookup button in webform: " . ($hasButton ? "✓ CONFIGURED" : "✗ MISSING") . "\n";
} else {
  echo "2. Webform register_form: ✗ NOT FOUND\n";
}

// 3. Check if route exists
$routeProvider = \Drupal::service('router.route_provider');
try {
  $route = $routeProvider->getRouteByName('cingulate_npi.lookup_form');
  echo "3. Route /npi-lookup/form: ✓ EXISTS\n";
} catch (\Exception $e) {
  echo "3. Route /npi-lookup/form: ✗ NOT FOUND\n";
}

// 4. Check library definition
$libraryDiscovery = \Drupal::service('library.discovery');
$library = $libraryDiscovery->getLibraryByName('cingulate_npi', 'npi-lookup');
echo "4. Asset library cingulate_npi/npi-lookup: " . ($library ? "✓ DEFINED" : "✗ MISSING") . "\n";

// 5. Check files exist
$jsFile = DRUPAL_ROOT . '/../web/modules/custom/cingulate_npi/js/npi-lookup.js';
$cssFile = DRUPAL_ROOT . '/../web/modules/custom/cingulate_npi/css/npi-lookup.css';
echo "5. JavaScript file: " . (file_exists($jsFile) ? "✓ EXISTS" : "✗ MISSING") . "\n";
echo "6. CSS file: " . (file_exists($cssFile) ? "✓ EXISTS" : "✗ MISSING") . "\n";

echo "\n=== Summary ===\n";
echo "If all items show ✓, the NPI lookup feature is ready.\n";
echo "Visit: https://wireframe-cingulate.ddev.site/register\n";
echo "Look for: 'Need your NPI number? Look it up here' below the NPI field\n";
