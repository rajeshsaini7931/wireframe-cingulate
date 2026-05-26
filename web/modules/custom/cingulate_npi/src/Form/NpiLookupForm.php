<?php

namespace Drupal\cingulate_npi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the NPI lookup form.
 */
class NpiLookupForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'cingulate_npi_lookup_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#prefix'] = '<div id="npi-lookup-form-wrapper">';
    $form['#suffix'] = '</div>';
    $form['#attributes']['class'][] = 'npi-lookup-form';

    $form['search_fields'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['npi-search-fields']],
    ];

    $form['search_fields']['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
      '#maxlength' => 50,
      '#attributes' => [
        'placeholder' => $this->t('Enter first name'),
        'class' => ['npi-field'],
      ],
    ];

    $form['search_fields']['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      '#maxlength' => 50,
      '#attributes' => [
        'placeholder' => $this->t('Enter last name'),
        'class' => ['npi-field'],
      ],
    ];

    $form['search_fields']['state'] = [
      '#type' => 'select',
      '#title' => $this->t('State'),
      '#options' => $this->getStateOptions(),
      '#empty_option' => $this->t('- Any -'),
      '#attributes' => ['class' => ['npi-field']],
    ];

    $form['search_fields']['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#maxlength' => 50,
      '#attributes' => [
        'placeholder' => $this->t('Enter city (optional)'),
        'class' => ['npi-field'],
      ],
    ];

    $form['search_fields']['zip_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ZIP Code'),
      '#maxlength' => 10,
      '#attributes' => [
        'placeholder' => $this->t('12345 or 12345-6789'),
        'pattern' => '^\d{5}(-\d{4})?$',
        'class' => ['npi-field'],
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['npi-form-actions']],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#attributes' => ['class' => ['npi-search-btn']],
      '#ajax' => [
        'callback' => '::ajaxSearchCallback',
        'wrapper' => 'npi-results-wrapper',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Searching NPI Registry...'),
        ],
      ],
    ];

    $form['results'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'npi-results-wrapper', 'class' => ['npi-results-container']],
    ];

    // If form has been submitted, show placeholder results.
    if ($form_state->isSubmitted() && !$form_state->hasAnyErrors()) {
      $form['results']['#markup'] = $this->buildPlaceholderResults();
    }

    return $form;
  }

  /**
   * AJAX callback for search submission.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The results container render array.
   */
  public function ajaxSearchCallback(array &$form, FormStateInterface $form_state): array {
    if ($form_state->hasAnyErrors()) {
      return $form['results'];
    }

    // For now, return placeholder results.
    // This will be replaced with actual API integration in next phase.
    $form['results']['#markup'] = $this->buildPlaceholderResults();

    return $form['results'];
  }

  /**
   * Builds placeholder results HTML for testing the modal.
   *
   * @return string
   *   HTML markup for placeholder results.
   */
  private function buildPlaceholderResults(): string {
    return '
      <div class="npi-results">
        <p class="npi-results__count">Found 3 providers (showing sample results)</p>
        
        <table class="npi-results__table">
          <thead>
            <tr>
              <th>Provider</th>
              <th>NPI Number</th>
              <th>Location</th>
              <th>Specialty</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr class="npi-result-row">
              <td class="npi-result__name">John Smith, MD</td>
              <td class="npi-result__number">1234567890</td>
              <td class="npi-result__address">Springfield, IL</td>
              <td class="npi-result__taxonomy">Family Medicine</td>
              <td class="npi-result__action">
                <button
                  type="button"
                  class="npi-select-btn"
                  data-npi="1234567890"
                  data-name="John Smith, MD"
                >
                  Select
                </button>
              </td>
            </tr>
            <tr class="npi-result-row">
              <td class="npi-result__name">Jane Doe, DO</td>
              <td class="npi-result__number">9876543210</td>
              <td class="npi-result__address">Chicago, IL</td>
              <td class="npi-result__taxonomy">Internal Medicine</td>
              <td class="npi-result__action">
                <button
                  type="button"
                  class="npi-select-btn"
                  data-npi="9876543210"
                  data-name="Jane Doe, DO"
                >
                  Select
                </button>
              </td>
            </tr>
            <tr class="npi-result-row">
              <td class="npi-result__name">Robert Johnson, MD</td>
              <td class="npi-result__number">5551234567</td>
              <td class="npi-result__address">Peoria, IL</td>
              <td class="npi-result__taxonomy">Pediatrics</td>
              <td class="npi-result__action">
                <button
                  type="button"
                  class="npi-select-btn"
                  data-npi="5551234567"
                  data-name="Robert Johnson, MD"
                >
                  Select
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    ';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Validation is handled by #required attributes.
    // Additional custom validation can be added here if needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Form submission is handled via AJAX callback.
    // This method is required by FormBase but won't be called in AJAX mode.
    $form_state->setRebuild();
  }

  /**
   * Returns an array of US state options.
   *
   * @return array
   *   Associative array of state code => state name.
   */
  private function getStateOptions(): array {
    return [
      'AL' => 'Alabama',
      'AK' => 'Alaska',
      'AZ' => 'Arizona',
      'AR' => 'Arkansas',
      'CA' => 'California',
      'CO' => 'Colorado',
      'CT' => 'Connecticut',
      'DE' => 'Delaware',
      'FL' => 'Florida',
      'GA' => 'Georgia',
      'HI' => 'Hawaii',
      'ID' => 'Idaho',
      'IL' => 'Illinois',
      'IN' => 'Indiana',
      'IA' => 'Iowa',
      'KS' => 'Kansas',
      'KY' => 'Kentucky',
      'LA' => 'Louisiana',
      'ME' => 'Maine',
      'MD' => 'Maryland',
      'MA' => 'Massachusetts',
      'MI' => 'Michigan',
      'MN' => 'Minnesota',
      'MS' => 'Mississippi',
      'MO' => 'Missouri',
      'MT' => 'Montana',
      'NE' => 'Nebraska',
      'NV' => 'Nevada',
      'NH' => 'New Hampshire',
      'NJ' => 'New Jersey',
      'NM' => 'New Mexico',
      'NY' => 'New York',
      'NC' => 'North Carolina',
      'ND' => 'North Dakota',
      'OH' => 'Ohio',
      'OK' => 'Oklahoma',
      'OR' => 'Oregon',
      'PA' => 'Pennsylvania',
      'RI' => 'Rhode Island',
      'SC' => 'South Carolina',
      'SD' => 'South Dakota',
      'TN' => 'Tennessee',
      'TX' => 'Texas',
      'UT' => 'Utah',
      'VT' => 'Vermont',
      'VA' => 'Virginia',
      'WA' => 'Washington',
      'WV' => 'West Virginia',
      'WI' => 'Wisconsin',
      'WY' => 'Wyoming',
      'DC' => 'District of Columbia',
    ];
  }

}
