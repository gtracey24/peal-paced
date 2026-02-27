<?php

/**
 * ============================================================
 * STATIC SITE BUILD SCRIPT
 * ============================================================
 * Assembles HTML from templates + JSON content
 * Outputs production-ready files to /dist
 *
 * Run:
 *   php build/build.php
 * ============================================================
 */

/* ------------------------------------------------------------
 * 1. Resolve project root
 * ------------------------------------------------------------ */
$root = dirname(__DIR__);

/* ------------------------------------------------------------
 * 2. Load site configuration (content + design)
 * ------------------------------------------------------------ */
$configPath = $root . '/content/site.config.json';

if (!file_exists($configPath)) {
  exit("Config file missing: site.config.json\n");
}

$config = json_decode(file_get_contents($configPath), true);

/* ------------------------------------------------------------
 * 3. Load base templates
 * ------------------------------------------------------------ */
$pageWrapper = file_get_contents($root . '/templates/page.html');
$pageContent = file_get_contents($root . '/templates/pages/home.html');

/* ============================================================
 * PHASE 1: SECTION INJECTION (structure)
 * ============================================================ */

/* HERO */
$pageContent = str_replace(
  '{{SECTION_HERO}}',
  file_get_contents(
    $root . "/templates/sections/hero/hero-{$config['design']['heroVariant']}.html"
  ),
  $pageContent
);

/* ABOUT */
$pageContent = str_replace(
  '{{SECTION_ABOUT}}',
  file_get_contents(
    $root . "/templates/sections/about/{$config['about']['aboutVariant']}.html"
  ),
  $pageContent
);

/* SERVICES */
$pageContent = str_replace(
  '{{SECTION_SERVICES}}',
  file_get_contents($root . '/templates/sections/services.html'),
  $pageContent
);

/* PROCESS */
$pageContent = str_replace(
  '{{SECTION_PROCESS}}',
  file_get_contents($root . '/templates/sections/process.html'),
  $pageContent
);

/* GALLERY */
$pageContent = str_replace(
  '{{SECTION_GALLERY}}',
  file_get_contents($root . '/templates/sections/gallery.html'),
  $pageContent
);

/* CTA */
$pageContent = str_replace(
  '{{SECTION_CTA}}',
  file_get_contents($root . '/templates/sections/cta.html'),
  $pageContent
);

/* TESTIMONIALS */
$pageContent = str_replace(
  '{{SECTION_TESTIMONIALS}}',
  file_get_contents(
    $root . "/templates/sections/testimonials/{$config['testimonials']['variant']}.html"
  ),
  $pageContent
);

/* FORM */
$pageContent = str_replace(
  '{{SECTION_FORM}}',
  file_get_contents(
    $root . "/templates/sections/form/{$config['form']['variant']}.html"
  ),
  $pageContent
);

/* FAQ */
$pageContent = str_replace(
  '{{SECTION_FAQ}}',
  file_get_contents($root . '/templates/sections/faq.html'),
  $pageContent
);

/* ============================================================
 * PHASE 2: COMPONENT INJECTION (repeatable blocks)
 * ============================================================ */

/* SERVICES ITEMS */
$servicesHtml = '';
$serviceTemplate = file_get_contents(
  $root . "/templates/components/services/{$config['design']['serviceStyle']}.html"
);

if (!empty($config['services'])) {
  foreach ($config['services'] as $service) {
    $servicesHtml .= str_replace(
      ['{{SERVICE_TITLE}}', '{{SERVICE_DESCRIPTION}}', '{{SERVICE_ICON}}'],
      [$service['title'], $service['description'], $service['icon']],
      $serviceTemplate
    );
  }
}

$pageContent = str_replace(
  '{{SERVICES_ITEMS}}',
  $servicesHtml,
  $pageContent
);

/* PROCESS ITEMS */
$processHtml = '';
$processTemplate = file_get_contents(
  $root . "/templates/components/processes/{$config['design']['processStyle']}.html"
);

if (!empty($config['process'])) {
  foreach ($config['process'] as $step) {
    $processHtml .= str_replace(
      ['{{PROCESS_TITLE}}', '{{PROCESS_DESCRIPTION}}'],
      [$step['title'], $step['description']],
      $processTemplate
    );
  }
}

$pageContent = str_replace(
  '{{PROCESS_STEPS}}',
  $processHtml,
  $pageContent
);

/* GALLERY ITEMS */
$galleryHtml = '';
$galleryItemTemplate = file_get_contents(
  $root . "/templates/components/gallery/{$config['design']['galleryStyle']}.html"
);
if (!empty($config['gallery'])) {
  foreach ($config['gallery'] as $item) {
    $galleryHtml .= str_replace(
      ['{{GALLERY_IMAGE}}', '{{GALLERY_CAPTION}}'],
      [$item['image'], $item['caption']],
      $galleryItemTemplate
    );
  }
}

$pageContent = str_replace(
  '{{GALLERY_ITEMS}}',
  $galleryHtml,
  $pageContent
);

/* TESTIMONIAL ITEMS */
$testimonialHtml = '';
$testimonialTemplate = file_get_contents(
  $root . "/templates/components/testimonials/{$config['testimonials']['itemStyle']}.html"
);

if (!empty($config['testimonials']['items'])) {
  foreach ($config['testimonials']['items'] as $t) {
    $testimonialHtml .= str_replace(
      [
        '{{TESTIMONIAL_TEXT}}',
        '{{TESTIMONIAL_AUTHOR}}',
        '{{TESTIMONIAL_ROLE}}',
        '{{TESTIMONIAL_AVATAR}}'
      ],
      [
        $t['text'],
        $t['author'],
        $t['role'],
        $t['avatar'] ?? ''
      ],
      $testimonialTemplate
    );
  }
}

$pageContent = str_replace(
  '{{TESTIMONIAL_ITEMS}}',
  $testimonialHtml,
  $pageContent
);

/* FAQ ITEMS */
$faqHtml = '';
$faqItemTemplate = file_get_contents(
  $root . "/templates/components/faq/faq-item.html"
);
if (!empty($config['faq'])) {
  foreach ($config['faq'] as $item) {
    $faqHtml .= str_replace(
      ['{{FAQ_QUESTION}}', '{{FAQ_ANSWER}}'],
      [$item['question'], $item['answer']],
      $faqItemTemplate
    );
  }
}
$pageContent = str_replace(
  '{{FAQ_ITEMS}}',
  $faqHtml,
  $pageContent
);

/* ============================================================
 * PHASE 3: WRAP PAGE CONTENT WITH LAYOUT
 * ============================================================ */

$html = str_replace(
  '{{PAGE_CONTENT}}',
  $pageContent,
  $pageWrapper
);

/* ============================================================
 * PHASE 4: GLOBAL PARTIALS
 * ============================================================ */

/* HEAD */
$html = str_replace(
  '{{PARTIAL_HEAD}}',
  file_get_contents($root . '/templates/partials/head.html'),
  $html
);

/* NAV */
$navItemsHtml = '';
$navItemTemplate = file_get_contents(
  $root . '/templates/components/nav-item.html'
);

if (!empty($config['navigation'])) {
  foreach ($config['navigation'] as $item) {
    $navItemsHtml .= str_replace(
      ['{{NAV_LABEL}}', '{{NAV_URL}}'],
      [$item['label'], $item['url']],
      $navItemTemplate
    );
  }
}

$navHtml = file_get_contents($root . '/templates/partials/nav.html');
$navHtml = str_replace('{{NAV_ITEMS}}', $navItemsHtml, $navHtml);

$html = str_replace('{{PARTIAL_NAV}}', $navHtml, $html);

/* FOOTER */

/* ------------------------------------------------------------
 * Build footer content
 * ------------------------------------------------------------ */
$footerHtml = file_get_contents($root . '/templates/partials/footer.html');

/* Footer text */
$footerHtml = str_replace(
  '{{FOOTER_TEXT}}',
  $config['footer']['text'] ?? '',
  $footerHtml
);

/* Footer links */
$footerLinksHtml = '';
$footerLinkTemplate = file_get_contents(
  $root . '/templates/components/footer-link.html'
);

if (!empty($config['footer']['links'])) {
  foreach ($config['footer']['links'] as $link) {
    $footerLinksHtml .= str_replace(
      ['{{FOOTER_LINK_LABEL}}', '{{FOOTER_LINK_URL}}'],
      [$link['label'], $link['url']],
      $footerLinkTemplate
    );
  }
}

$footerHtml = str_replace(
  '{{FOOTER_LINKS}}',
  $footerLinksHtml,
  $footerHtml
);

/* Inject footer */
$html = str_replace('{{PARTIAL_FOOTER}}', $footerHtml, $html);


/* ============================================================
 * PHASE 5: CONTENT TOKEN REPLACEMENT
 * ============================================================ */

$replacements = [
  // Site
  '{{SITE_NAME}}' => $config['site']['name'] ?? '',
  '{{SITE_LANGUAGE}}'     => $config['site']['language'] ?? 'en',

  // SEO
  '{{SEO_TITLE}}'         => $config['seo']['title'] ?? '',
  '{{SEO_DESCRIPTION}}'   => $config['seo']['description'] ?? '',
  '{{SEO_CANONICAL}}'     => $config['seo']['canonical'] ?? '',
  '{{SEO_OG_IMAGE}}'      => $config['seo']['ogImage'] ?? '',

  // Design
  '{{STYLESHEET}}'        => $config['design']['stylesheet'] ?? 'main.css',
  '{{THEME_CLASS}}' => 'theme-' . ($config['design']['theme'] ?? 'modern'),

  // Hero
  '{{HERO_VARIANT}}'     => $config['design']['heroVariant'] ?? '',
  '{{HERO_HEADLINE}}'     => $config['hero']['headline'] ?? '',
  '{{HERO_SUBHEADLINE}}'  => $config['hero']['subheadline'] ?? '',
  '{{HERO_IMAGE}}'        => $config['hero']['image'] ?? '',
  '{{HERO_CTA_LABEL}}'    => $config['hero']['cta']['label'] ?? '',
  '{{HERO_CTA_URL}}'      => $config['hero']['cta']['url'] ?? '',

  // ABOUT
  '{{ABOUT_VARIANT}}'    => $config['about']['aboutVariant'] ?? '',
  '{{ABOUT_HEADLINE}}'    => $config['about']['headline'] ?? '', 
  '{{ABOUT_DESCRIPTION}}'  => $config['about']['description'] ?? '',
  '{{ABOUT_IMAGE}}'        => $config['about']['image'] ?? '',

// TESTIMONIALS
'{{TESTIMONIALS_VARIANT}}' => $config['testimonials']['variant'] ?? '',
'{{TESTIMONIALS_HEADLINE}}' => $config['testimonials']['headline'] ?? '',

// FORM
'{{FORM_HEADLINE}}' => $config['form']['headline'] ?? '',
'{{FORM_ACTION}}' => $config['form']['action'] ?? '',
'{{FORM_BUTTON_LABEL}}' => $config['form']['buttonLabel'] ?? 'Submit',

// CTA
  '{{CTA_HEADLINE}}'      => $config['cta']['headline'] ?? '',
  '{{CTA_BUTTON_LABEL}}'  => $config['cta']['button']['label'] ?? '',
  '{{CTA_BUTTON_URL}}'    => $config['cta']['button']['url'] ?? '',
];
$html = str_replace(
  array_keys($replacements),
  array_values($replacements),
  $html
);

/* ============================================================
 * PHASE 6: OUTPUT
 * ============================================================ */

$distPath = $root . '/dist';

if (!is_dir($distPath)) {
  mkdir($distPath, 0755, true);
}

file_put_contents($distPath . '/index.html', $html);

/* ------------------------------------------------------------
 * Copy static assets
 * ------------------------------------------------------------ */
function copyDir(string $src, string $dst): void
{
  if (!is_dir($src)) return;

  if (!is_dir($dst)) {
    mkdir($dst, 0755, true);
  }

  foreach (scandir($src) as $file) {
    if ($file === '.' || $file === '..') continue;

    $srcPath = "$src/$file";
    $dstPath = "$dst/$file";

    is_dir($srcPath)
      ? copyDir($srcPath, $dstPath)
      : copy($srcPath, $dstPath);
  }
}

/* ------------------------------------------------------------
 * Copy ALL CSS (bootstrap, main, components, presets)
 * ------------------------------------------------------------ */
copyDir($root . '/css', $distPath . '/css');

/* ------------------------------------------------------------
 * Ensure selected preset exists
 * ------------------------------------------------------------ */
$designCss = $config['design']['stylesheet'] ?? null;

if ($designCss) {
    $presetPath = $root . '/css/presets/' . $designCss;

    if (!file_exists($presetPath)) {
        echo "❌ ERROR: Selected preset not found: $presetPath\n";
    } else {
        echo "✔ Using preset: $designCss\n";
    }
}


/* ------------------------------------------------------------
 * Done
 * ------------------------------------------------------------ */
echo "Build complete: dist/index.html generated\n";
