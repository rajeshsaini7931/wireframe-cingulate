<?php
/**
 * @file
 * Test breadcrumb library loading.
 */

use Drupal\Core\Asset\LibraryDiscovery;

$theme_path = \Drupal::service('extension.list.theme')->getPath('cingulate');
echo "Theme path: {$theme_path}\n";

$css_file = DRUPAL_ROOT . "/{$theme_path}/css/components/breadcrumb-nav.css";
echo "CSS file path: {$css_file}\n";
echo "File exists: " . (file_exists($css_file) ? 'YES' : 'NO') . "\n";

if (file_exists($css_file)) {
  echo "File size: " . filesize($css_file) . " bytes\n";
  echo "File readable: " . (is_readable($css_file) ? 'YES' : 'NO') . "\n";
}

/** @var \Drupal\Core\Asset\LibraryDiscovery $library_discovery */
$library_discovery = \Drupal::service('library.discovery');
$library = $library_discovery->getLibraryByName('cingulate', 'component-breadcrumb-nav');

if ($library) {
  echo "\n✓ Library 'cingulate/component-breadcrumb-nav' found\n";
  echo "CSS files in library:\n";
  if (isset($library['css'])) {
    print_r($library['css']);
  } else {
    echo "  No CSS files found in library definition!\n";
  }
} else {
  echo "\n✗ Library 'cingulate/component-breadcrumb-nav' NOT found\n";
}

echo "\nDone.\n";
