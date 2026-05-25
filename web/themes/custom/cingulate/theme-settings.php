<?php

declare(strict_types=1);

/**
 * @file
 * Theme settings form for Cingulate theme.
 */

use Drupal\Core\Form\FormState;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function cingulate_form_system_theme_settings_alter(array &$form, FormState $form_state): void {

  $form['cingulate'] = [
    '#type' => 'details',
    '#title' => t('Cingulate'),
    '#open' => TRUE,
  ];

  $form['cingulate']['example'] = [
    '#type' => 'textfield',
    '#title' => t('Example'),
    '#default_value' => theme_get_setting('example'),
  ];

}
