<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/config', isDev: false)
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // config/events-web.php references an event class from yiisoft/yii-http purely as an
    // optional integration hook (array key); it's not a real dependency of this package.
    ->ignoreUnknownClasses(['Yiisoft\Yii\Http\Event\AfterEmit'])
    ->ignoreErrorsOnPackages(['yiisoft/aliases'], [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('psr/container', __DIR__ . '/config/di.php', [ErrorType::SHADOW_DEPENDENCY]);
