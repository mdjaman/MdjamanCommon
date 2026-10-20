<?php

// MDJAMAN_COMMON_AUTOLOAD points at another project's vendor/autoload.php when this package is tested from inside an
// application that already depends on it (the package's own vendor/ is not needed then).
$loader = require getenv('MDJAMAN_COMMON_AUTOLOAD') ?: __DIR__ . '/../vendor/autoload.php';

// The tests must exercise THIS source tree, never an installed copy of the package. An optimized Composer autoloader
// answers from its class map before it looks at any PSR-4 path, so a prepended path is not enough: map every class of
// this tree explicitly (later class map entries win).
$base = realpath(__DIR__ . '/../src/MdjamanCommon');
$map = [];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
foreach ($files as $file) {
    if ($file->getExtension() === 'php') {
        $relative = substr($file->getPathname(), strlen($base) + 1, -4);
        $map['MdjamanCommon\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relative)] = $file->getPathname();
    }
}
$loader->addClassMap($map);
