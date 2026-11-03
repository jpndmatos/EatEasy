<?php
// Browser-only showcase: copies public assets and fictional fixtures, never server data.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$output = $root . '/dist';
foreach (['', '/css', '/assets'] as $directory) {
    if (!is_dir($output . $directory)) mkdir($output . $directory, 0755, true);
}
foreach (['index.html', 'app.js'] as $file) copy($root . '/demo/' . $file, $output . '/' . $file);
foreach (['style.css', 'dashboard.css'] as $file) copy($root . '/css/' . $file, $output . '/css/' . $file);
foreach (glob($root . '/assets/*.webp') as $file) copy($file, $output . '/assets/' . basename($file));
copy($root . '/assets/icon.svg', $output . '/assets/icon.svg');
copy($root . '/database/demo-restaurants.json', $output . '/restaurants.json');
echo "Built independent browser demo in dist/.\n";
