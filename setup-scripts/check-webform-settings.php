<?php
/**
 * @file
 * Check register_form webform submission and confirmation settings.
 */

$webform = \Drupal::entityTypeManager()->getStorage('webform')->load('register_form');
if ($webform) {
  echo "Register Form Settings:\n";
  echo str_repeat('=', 70) . PHP_EOL;
  
  $settings = $webform->getSettings();
  
  echo "\nSubmission Settings:\n";
  echo "  ajax: " . ($settings['ajax'] ? 'TRUE' : 'FALSE') . "\n";
  echo "  ajax_progress_type: " . ($settings['ajax_progress_type'] ?? 'N/A') . "\n";
  echo "  ajax_effect: " . ($settings['ajax_effect'] ?? 'N/A') . "\n";
  
  echo "\nConfirmation Settings:\n";
  echo "  confirmation_type: " . ($settings['confirmation_type'] ?? 'N/A') . "\n";
  echo "  confirmation_title: " . ($settings['confirmation_title'] ?? 'N/A') . "\n";
  echo "  confirmation_message: " . ($settings['confirmation_message'] ?? 'N/A') . "\n";
  echo "  confirmation_url: " . ($settings['confirmation_url'] ?? 'N/A') . "\n";
  
  echo "\nForm Settings:\n";
  echo "  form_submit_once: " . ($settings['form_submit_once'] ?? 'FALSE') . "\n";
  echo "  form_novalidate: " . ($settings['form_novalidate'] ?? 'FALSE') . "\n";
  echo "  form_disable_back: " . ($settings['form_disable_back'] ?? 'FALSE') . "\n";
  echo "  form_submit_label: " . ($settings['form_submit_label'] ?? 'Submit') . "\n";
  
  echo "\nPage Settings:\n";
  echo "  page: " . ($settings['page'] ?? 'FALSE') . "\n";
  echo "  page_submit_path: " . ($settings['page_submit_path'] ?? 'N/A') . "\n";
  
  echo "\nOther Settings:\n";
  echo "  submission_label: " . ($settings['submission_label'] ?? 'N/A') . "\n";
  echo "  submission_log: " . ($settings['submission_log'] ?? 'FALSE') . "\n";
}
