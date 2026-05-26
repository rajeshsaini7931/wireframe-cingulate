<?php
/**
 * @file
 * Initialize default configuration for register modal settings.
 */

$config = \Drupal::service('config.factory')->getEditable('cingulate_blocks.register_modal.settings');
$config->set('modal_title', 'Join ADHD ENGAGE');
$config->set('modal_description.value', 'Be part of our community and stay informed.');
$config->set('modal_description.format', 'filtered_html');
$config->set('cta_text', 'Register');
$config->set('cta_url', '/register');
$config->set('cookie_duration', 2592000);
$config->save();

echo "✓ Default register modal configuration saved:\n";
echo "  - Title: Join ADHD ENGAGE\n";
echo "  - Description: Be part of our community and stay informed.\n";
echo "  - CTA: Register → /register\n";
echo "  - Cookie duration: 2592000 seconds (30 days)\n";
echo "\nConfiguration form available at: /admin/config/cingulate/register-modal\n";
