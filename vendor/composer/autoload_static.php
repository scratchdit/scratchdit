<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite744883bcc16176ff0483b5a1c01047c
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInite744883bcc16176ff0483b5a1c01047c::$classMap;

        }, null, ClassLoader::class);
    }
}