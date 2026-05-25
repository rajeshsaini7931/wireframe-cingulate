<?php

/**
 * @file
 * Setup script for Register Webform.
 *
 * Creates:
 * - register_form webform with 6 fields
 * - Inline AJAX submission
 * - Client-side validation ready
 * - NO reCAPTCHA (deferred to later)
 * - NO NPI lookup (deferred to later)
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-register-webform.php
 */

use Drupal\webform\Entity\Webform;

// Check if webform already exists
if (Webform::load('register_form')) {
  echo "Webform 'register_form' already exists — skipping.\n";
  return;
}

// Create webform with YAML elements definition
$webform = Webform::create([
  'id'     => 'register_form',
  'title'  => 'Register Form',
  'status' => 'open',
]);

// Configure webform settings
$webform->setSettings([
  'ajax'               => TRUE,
  'ajax_scroll_top'    => 'form',
  'page'               => FALSE,
  'page_submit_path'   => '',
  'page_confirm_path'  => '',
  'form_submit_once'   => FALSE,
  'form_exception_message' => '',
  'form_open_message'  => '',
  'form_close_message' => '',
  'form_previous_submissions' => TRUE,
  'form_confidential'  => FALSE,
  'form_confidential_message' => '',
  'form_remote_addr'   => TRUE,
  'form_convert_anonymous' => FALSE,
  'form_prepopulate'   => FALSE,
  'form_prepopulate_source_entity' => FALSE,
  'form_novalidate'    => FALSE,
  'form_unsaved'       => FALSE,
  'form_disable_back'  => FALSE,
  'form_submit_back'   => FALSE,
  'form_autofocus'     => FALSE,
  'form_details_toggle' => FALSE,
  'form_login'         => FALSE,
  'submission_label'   => '',
  'submission_log'     => FALSE,
  'submission_user_columns' => [],
  'wizard_progress_bar' => TRUE,
  'wizard_progress_pages' => FALSE,
  'wizard_progress_percentage' => FALSE,
  'wizard_start_label' => '',
  'wizard_preview_link' => FALSE,
  'wizard_confirmation' => TRUE,
  'wizard_confirmation_label' => '',
  'wizard_track' => '',
  'preview'            => 0,
  'preview_label'      => '',
  'preview_title'      => '',
  'preview_message'    => '',
  'preview_attributes' => [],
  'preview_excluded_elements' => [],
  'preview_exclude_empty' => TRUE,
  'draft'              => 'none',
  'draft_multiple'     => FALSE,
  'draft_auto_save'    => FALSE,
  'draft_saved_message' => '',
  'draft_loaded_message' => '',
  'confirmation_type'  => 'inline',
  'confirmation_title' => '',
  'confirmation_message' => '<p>Thank you for registering! You will receive the latest updates, research, and resources on ADHD.</p>',
  'confirmation_url'   => '',
  'confirmation_attributes' => [],
  'confirmation_back'  => TRUE,
  'confirmation_back_label' => '',
  'confirmation_back_attributes' => [],
  'limit_total'        => NULL,
  'limit_total_message' => '',
  'limit_user'         => NULL,
  'limit_user_message' => '',
  'entity_limit_total' => NULL,
  'entity_limit_user'  => NULL,
  'results_disabled'   => FALSE,
  'results_disabled_ignore' => FALSE,
  'token_update'       => FALSE,
]);

// Define form elements using YAML
$elements_yaml = <<<'YAML'
first_name:
  '#type': textfield
  '#title': 'First name'
  '#required': true
  '#placeholder': 'First Name'
  '#autocomplete': given-name
  '#required_error': 'Please enter your first name.'

last_name:
  '#type': textfield
  '#title': 'Last name'
  '#required': true
  '#placeholder': 'Last Name'
  '#autocomplete': family-name
  '#required_error': 'Please enter your last name.'

mobile_phone:
  '#type': tel
  '#title': 'Phone Number'
  '#required': true
  '#placeholder': '+1 (000) 000-0000'
  '#pattern': '^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$'
  '#pattern_error': 'Please enter a valid phone number.'
  '#required_error': 'Please enter your phone number.'

email:
  '#type': email
  '#title': 'Email address'
  '#required': true
  '#placeholder': 'example@email.com'
  '#required_error': 'Please enter your email address.'
  '#pattern': '^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$'
  '#pattern_error': 'Please enter a valid email address.'

npi_number:
  '#type': textfield
  '#title': 'NPI Number'
  '#required': true
  '#placeholder': '1234567890'
  '#maxlength': 10
  '#pattern': '[0-9]{10}'
  '#pattern_error': 'NPI must be exactly 10 digits.'
  '#required_error': 'Please enter your NPI number.'
  '#description': '(NPI lookup will be added in future update)'
  '#description_display': before

zip_code:
  '#type': textfield
  '#title': 'ZIP code'
  '#required': true
  '#placeholder': '10001'
  '#pattern': '[0-9]{5}(-[0-9]{4})?'
  '#pattern_error': 'Please enter a valid ZIP code.'
  '#required_error': 'Please enter your ZIP code.'

legal_agreement:
  '#type': webform_markup
  '#markup': '<p class="reg-form__legal-para">By clicking "Submit" below, you agree to be contacted by phone or email regarding your request.</p>'

actions:
  '#type': webform_actions
  '#title': 'Submit button'
  '#submit__label': 'Sign me up!'
YAML;

// Parse YAML to array
$elements = \Drupal\Core\Serialization\Yaml::decode($elements_yaml);
$webform->setElements($elements);
$webform->save();

echo "✓ Created webform: register_form\n";
echo "✓ Configured with 6 fields + legal text + submit button\n";
echo "✓ AJAX submission enabled\n";
echo "✓ Inline confirmation message configured\n";
echo "\n── Register Webform Setup Complete ──\n";
echo "View at: /admin/structure/webform/manage/register_form\n";
echo "Run: ddev exec drush cr && ddev exec drush cex -y\n";
