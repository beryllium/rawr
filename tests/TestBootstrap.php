<?php

require __DIR__ . '/../vendor/autoload.php';

define('EXIV2_BIN',    trim(`which exiv2`));
define('EXIFTOOL_BIN', trim(`which exiftool`));
define('SANDBOX_DIR',  __DIR__ . '/sandbox/' . getmypid());
define('ASSETS_DIR',  __DIR__ . '/assets');

if (EXIV2_BIN === null || EXIFTOOL_BIN === null) {
    throw new ErrorException('exiv2 and exiftool must be in your path in order to test Rawr.');
}

if (!is_dir(SANDBOX_DIR)) {
    mkdir(SANDBOX_DIR);
    touch(SANDBOX_DIR . '/test.dir');
}

register_shutdown_function(function () {
    if (!is_dir(SANDBOX_DIR) || SANDBOX_DIR !== __DIR__ . '/sandbox/' . getmypid()) {
        return;
    }

    $di = new RecursiveDirectoryIterator(SANDBOX_DIR, FilesystemIterator::SKIP_DOTS);
    $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($ri as $file) {
        if ($file->isDir()) {
            rmdir($file);
        } else {
            unlink($file);
        }
    }

    rmdir(SANDBOX_DIR);
    return true;
});