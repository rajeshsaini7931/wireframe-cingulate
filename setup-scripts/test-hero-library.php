<?php
/**
 * @file
 * Test hero library loading.
 */

$library = \Drupal::service('library.discovery')->getLibraryByName('cingulate', 'component-hero');
echo "=== Hero Library CSS ===\n";
print_r($library['css']);
