<?php


class ComposerAutoloaderInitdigital6ef396a4dc448dde7e07aff6fe5d6eda
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
        if ('Composer\Autoload\BarcodesDigitalClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInitdigital6ef396a4dc448dde7e07aff6fe5d6eda', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\BarcodesDigitalClassLoader();
        spl_autoload_unregister(array('ComposerAutoloaderInitdigital6ef396a4dc448dde7e07aff6fe5d6eda', 'loadClassLoader'));

        $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION') && (!function_exists('zend_loader_file_encoded') || !zend_loader_file_encoded());
        if ($useStaticLoader) {
            require_once __DIR__ . '/autoload_static.php';

            call_user_func(\Composer\Autoload\ComposerStaticInitdigital6ef396a4dc448dde7e07aff6fe5d6eda::getInitializer($loader));
        } else {
            $map = require __DIR__ . '/autoload_namespaces.php';
            foreach ($map as $namespace => $path) {
                $loader->set($namespace, $path);
            }

            $map = require __DIR__ . '/autoload_psr4.php';
            foreach ($map as $namespace => $path) {
                $loader->setPsr4($namespace, $path);
            }

            $classMap = require __DIR__ . '/autoload_classmap.php';
            if ($classMap) {
                $loader->addClassMap($classMap);
            }
        }

        $loader->register(true);

        if ($useStaticLoader) {
            $includeFiles = Composer\Autoload\ComposerStaticInitdigital6ef396a4dc448dde7e07aff6fe5d6eda::$files;
        } else {
            $includeFiles = require __DIR__ . '/autoload_files.php';
        }
        foreach ($includeFiles as $fileIdentifier => $file) {
            composerRequiredigital6ef396a4dc448dde7e07aff6fe5d6eda($fileIdentifier, $file);
        }

        return $loader;
    }
}

function composerRequiredigital6ef396a4dc448dde7e07aff6fe5d6eda($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        require $file;

        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
    }
}
