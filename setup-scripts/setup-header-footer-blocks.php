<?php

/**
 * @file
 * Setup script: Create site header and footer block_content instances.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-header-footer-blocks.php
 */

use Drupal\block_content\Entity\BlockContent;
use Drupal\block\Entity\Block;

echo "=== Creating Site Header and Footer Block Instances ===\n\n";

// ============================================================================
// STEP 1: Create Site Header Block Content Instance
// ============================================================================

echo "[1/3] Creating site_header block content instance...\n";

// Check if header block already exists
$existing_header = \Drupal::entityTypeManager()
  ->getStorage('block_content')
  ->loadByProperties([
    'type' => 'site_header',
    'info' => 'Site Header',
  ]);

if (!empty($existing_header)) {
  $header_block_content = reset($existing_header);
  echo "  ⊙ Site Header block content already exists (ID: {$header_block_content->id()}).\n";
}
else {
  $header_block_content = BlockContent::create([
    'type' => 'site_header',
    'info' => 'Site Header',
    'langcode' => 'en',
    'field_hcp_notice' => 'This site is intended for US HCPs',
    'field_site_name' => 'ADHD ENGAGE',
    'field_header_menu' => 'main',
    'field_register_cta_link' => [
      'uri' => 'internal:/register',
      'title' => 'Register',
    ],
  ]);
  $header_block_content->save();
  echo "  ✓ Site Header block content created (ID: {$header_block_content->id()}).\n";
}

// ============================================================================
// STEP 2: Create Site Footer Block Content Instance
// ============================================================================

echo "[2/3] Creating site_footer block content instance...\n";

// Check if footer block already exists
$existing_footer = \Drupal::entityTypeManager()
  ->getStorage('block_content')
  ->loadByProperties([
    'type' => 'site_footer',
    'info' => 'Site Footer',
  ]);

if (!empty($existing_footer)) {
  $footer_block_content = reset($existing_footer);
  echo "  ⊙ Site Footer block content already exists (ID: {$footer_block_content->id()}).\n";
}
else {
  $footer_block_content = BlockContent::create([
    'type' => 'site_footer',
    'info' => 'Site Footer',
    'langcode' => 'en',
    'field_footer_menu' => 'footer',
    'field_copyright_text' => [
      'value' => '©2026 Cingulate | XXXX-XXXXXXXXXXXXXX March 2026 | Produced in USA.',
      'format' => 'basic_html',
    ],
  ]);
  $footer_block_content->save();
  echo "  ✓ Site Footer block content created (ID: {$footer_block_content->id()}).\n";
}

// ============================================================================
// STEP 3: Place Blocks in Regions
// ============================================================================

echo "[3/3] Placing blocks in theme regions...\n";

$theme = 'cingulate';

// Place Site Header in 'header' region
$header_block_id = 'siteheaderblock';
if (!Block::load($header_block_id)) {
  Block::create([
    'id' => $header_block_id,
    'theme' => $theme,
    'region' => 'header',
    'weight' => -10,
    'plugin' => 'block_content:' . $header_block_content->uuid(),
    'settings' => [
      'label' => 'Site Header',
      'provider' => 'block_content',
      'label_display' => '0',
    ],
    'visibility' => [],
  ])->save();
  echo "  ✓ Site Header placed in 'header' region (weight: -10).\n";
}
else {
  echo "  ⊙ Site Header block placement already exists.\n";
}

// Place Site Footer in 'footer' region
$footer_block_id = 'sitefooterblock';
if (!Block::load($footer_block_id)) {
  Block::create([
    'id' => $footer_block_id,
    'theme' => $theme,
    'region' => 'footer',
    'weight' => 10,
    'plugin' => 'block_content:' . $footer_block_content->uuid(),
    'settings' => [
      'label' => 'Site Footer',
      'provider' => 'block_content',
      'label_display' => '0',
    ],
    'visibility' => [],
  ])->save();
  echo "  ✓ Site Footer placed in 'footer' region (weight: 10).\n";
}
else {
  echo "  ⊙ Site Footer block placement already exists.\n";
}

echo "\n=== Setup Complete ===\n";
echo "✓ Site Header block created and placed in 'header' region.\n";
echo "✓ Site Footer block created and placed in 'footer' region.\n";
echo "\nNext steps:\n";
echo "1. Run: drush cr\n";
echo "2. Run: drush cex -y (export configuration)\n";
echo "3. Visit the site to verify header/footer rendering\n";
echo "4. Edit blocks at: /admin/content/block\n";
