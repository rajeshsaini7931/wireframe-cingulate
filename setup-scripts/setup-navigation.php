<?php

/**
 * @file
 * Setup script: Create Main and Footer navigation menus.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-navigation.php
 */

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\Entity\Menu;

echo "=== Creating Navigation Menus ===\n\n";

// ============================================================================
// STEP 1: Ensure Main and Footer menus exist
// ============================================================================

echo "[1/3] Checking system menus...\n";

// Main navigation menu (should exist in Drupal core)
if (!Menu::load('main')) {
  Menu::create([
    'id' => 'main',
    'label' => 'Main navigation',
    'description' => 'Site main navigation menu',
  ])->save();
  echo "  ✓ Main navigation menu created.\n";
}
else {
  echo "  ⊙ Main navigation menu already exists.\n";
}

// Footer menu (create if not exists)
if (!Menu::load('footer')) {
  Menu::create([
    'id' => 'footer',
    'label' => 'Footer',
    'description' => 'Site footer legal links menu',
  ])->save();
  echo "  ✓ Footer menu created.\n";
}
else {
  echo "  ⊙ Footer menu already exists.\n";
}

echo "\n";

// ============================================================================
// STEP 2: Build Main Navigation Menu Structure
// ============================================================================

echo "[2/3] Building Main navigation menu...\n";

// Delete existing main menu links to rebuild fresh
$main_links = \Drupal::entityTypeManager()
  ->getStorage('menu_link_content')
  ->loadByProperties(['menu_name' => 'main']);

if (!empty($main_links)) {
  echo "  Clearing existing main menu links...\n";
  foreach ($main_links as $link) {
    $link->delete();
  }
}

// ── Parent: ADHD Overview ────────────────────────────────────────────────
echo "  Creating ADHD Overview parent...\n";
$adhd_overview_parent = MenuLinkContent::create([
  'title' => 'ADHD Overview',
  'link' => ['uri' => 'route:<nolink>'],
  'menu_name' => 'main',
  'weight' => 0,
  'expanded' => TRUE,
]);
$adhd_overview_parent->save();
$adhd_overview_uuid = $adhd_overview_parent->uuid();

// Child links for ADHD Overview
$adhd_overview_children = [
  ['title' => 'Definition & classification', 'uri' => 'internal:/adhd-overview'],
  ['title' => 'Epidemiology (children, adolescents, adults)', 'uri' => 'internal:/adhd-overview/epidemiology'],
  ['title' => 'Neurobiology & pathophysiology', 'uri' => 'internal:/adhd-overview/neurobiology'],
  ['title' => 'ADHD across the lifespan', 'uri' => 'internal:/adhd-overview/lifespan'],
  ['title' => 'Burden of disease & unmet needs', 'uri' => 'internal:/adhd-overview/burden-of-disease'],
];

foreach ($adhd_overview_children as $index => $child) {
  MenuLinkContent::create([
    'title' => $child['title'],
    'link' => ['uri' => $child['uri']],
    'menu_name' => 'main',
    'parent' => 'menu_link_content:' . $adhd_overview_uuid,
    'weight' => $index,
  ])->save();
}
echo "    ✓ Added 5 child links.\n";

// ── Parent: Diagnosis & Assessment ───────────────────────────────────────
echo "  Creating Diagnosis & Assessment parent...\n";
$diagnosis_parent = MenuLinkContent::create([
  'title' => 'Diagnosis & Assessment',
  'link' => ['uri' => 'route:<nolink>'],
  'menu_name' => 'main',
  'weight' => 1,
  'expanded' => TRUE,
]);
$diagnosis_parent->save();
$diagnosis_uuid = $diagnosis_parent->uuid();

// Child links for Diagnosis & Assessment
$diagnosis_children = [
  ['title' => 'Screening & diagnostic tools', 'uri' => 'internal:/diagnosis/screening-tools'],
  ['title' => 'Rating scales & assessments', 'uri' => 'internal:/diagnosis/rating-scales'],
  ['title' => 'Differential diagnosis', 'uri' => 'internal:/diagnosis/differential'],
];

foreach ($diagnosis_children as $index => $child) {
  MenuLinkContent::create([
    'title' => $child['title'],
    'link' => ['uri' => $child['uri']],
    'menu_name' => 'main',
    'parent' => 'menu_link_content:' . $diagnosis_uuid,
    'weight' => $index,
  ])->save();
}
echo "    ✓ Added 3 child links.\n";

// ── Parent: Treatment Guidelines ─────────────────────────────────────────
echo "  Creating Treatment Guidelines parent...\n";
$treatment_parent = MenuLinkContent::create([
  'title' => 'Treatment Guidelines',
  'link' => ['uri' => 'route:<nolink>'],
  'menu_name' => 'main',
  'weight' => 2,
  'expanded' => TRUE,
]);
$treatment_parent->save();
$treatment_uuid = $treatment_parent->uuid();

// Child links for Treatment Guidelines
$treatment_children = [
  ['title' => 'Pharmacological treatment', 'uri' => 'internal:/treatment/pharmacological'],
  ['title' => 'Non-pharmacological treatment', 'uri' => 'internal:/treatment/non-pharmacological'],
  ['title' => 'Monitoring & follow-up', 'uri' => 'internal:/treatment/monitoring'],
];

foreach ($treatment_children as $index => $child) {
  MenuLinkContent::create([
    'title' => $child['title'],
    'link' => ['uri' => $child['uri']],
    'menu_name' => 'main',
    'parent' => 'menu_link_content:' . $treatment_uuid,
    'weight' => $index,
  ])->save();
}
echo "    ✓ Added 3 child links.\n";

echo "  ✓ Main navigation menu complete: 3 parents + 11 child links.\n\n";

// ============================================================================
// STEP 3: Build Footer Menu Structure
// ============================================================================

echo "[3/3] Building Footer menu...\n";

// Delete existing footer menu links to rebuild fresh
$footer_links = \Drupal::entityTypeManager()
  ->getStorage('menu_link_content')
  ->loadByProperties(['menu_name' => 'footer']);

if (!empty($footer_links)) {
  echo "  Clearing existing footer menu links...\n";
  foreach ($footer_links as $link) {
    $link->delete();
  }
}

// Footer legal links (no children)
$footer_items = [
  ['title' => 'Legal Notice', 'uri' => 'internal:/legal-notice'],
  ['title' => 'Consumer Health Privacy', 'uri' => 'internal:/consumer-health-privacy'],
  ['title' => 'Privacy Notice', 'uri' => 'internal:/privacy-notice'],
];

foreach ($footer_items as $index => $item) {
  MenuLinkContent::create([
    'title' => $item['title'],
    'link' => ['uri' => $item['uri']],
    'menu_name' => 'footer',
    'weight' => $index,
  ])->save();
}

echo "  ✓ Footer menu complete: 3 links.\n\n";

echo "=== Setup Complete ===\n";
echo "✓ Main navigation: 3 parent items + 11 child items\n";
echo "✓ Footer navigation: 3 links\n";
echo "\nNext steps:\n";
echo "1. Run: drush cr\n";
echo "2. Run setup-header-footer-blocks.php to create block instances\n";
