<?php

namespace Drupal\cingulate_blocks\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Register Modal settings.
 */
class RegisterModalSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'cingulate_blocks_register_modal_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['cingulate_blocks.register_modal.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('cingulate_blocks.register_modal.settings');

    $form['modal_title'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Modal Title'),
      '#description'   => $this->t('The main heading displayed at the top of the modal.'),
      '#default_value' => $config->get('modal_title') ?: 'Join ADHD ENGAGE',
      '#required'      => TRUE,
      '#maxlength'     => 255,
    ];

    $form['modal_description'] = [
      '#type'          => 'text_format',
      '#title'         => $this->t('Modal Description'),
      '#description'   => $this->t('The subtitle or description text displayed below the title.'),
      '#default_value' => $config->get('modal_description.value') ?: 'Be part of our community and stay informed.',
      '#format'        => $config->get('modal_description.format') ?: 'filtered_html',
      '#required'      => FALSE,
    ];

    $form['cta_text'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('CTA Button Text'),
      '#description'   => $this->t('The text displayed on the call-to-action button.'),
      '#default_value' => $config->get('cta_text') ?: 'Register',
      '#required'      => TRUE,
      '#maxlength'     => 100,
    ];

    $form['cta_url'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('CTA Button URL'),
      '#description'   => $this->t('The URL where users will be redirected when clicking the CTA button. Use internal path like <code>/register</code> or full URL.'),
      '#default_value' => $config->get('cta_url') ?: '/register',
      '#required'      => TRUE,
      '#maxlength'     => 255,
    ];

    $form['cookie_duration'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Cookie Duration (seconds)'),
      '#description'   => $this->t('How long to suppress the modal after user closes it. Examples: <strong>86400</strong> = 1 day, <strong>604800</strong> = 1 week, <strong>2592000</strong> = 30 days, <strong>31536000</strong> = 1 year.'),
      '#default_value' => $config->get('cookie_duration') ?: 2592000,
      '#required'      => TRUE,
      '#min'           => 60,
      '#max'           => 31536000,
      '#step'          => 1,
      '#field_suffix'  => $this->t('seconds'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('cingulate_blocks.register_modal.settings')
      ->set('modal_title', $form_state->getValue('modal_title'))
      ->set('modal_description.value', $form_state->getValue('modal_description')['value'])
      ->set('modal_description.format', $form_state->getValue('modal_description')['format'])
      ->set('cta_text', $form_state->getValue('cta_text'))
      ->set('cta_url', $form_state->getValue('cta_url'))
      ->set('cookie_duration', $form_state->getValue('cookie_duration'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
