<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Yiisoft\Di\Container;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerInterface;
use Yiisoft\Profiler\Target\AbstractTarget;

/**
 * @var array $params
 */
return [
    ProfilerInterface::class => static function (Container $container, LoggerInterface $logger) use ($params) {
        $params = $params['yiisoft/profiler'];
        $targets = $params['targets'];
        foreach ($targets as $name => $target) {
            if (!($target instanceof AbstractTarget)) {
                $target = $container->get($target);
            }
            /** @var AbstractTarget $target */

            //$target = $container->get($target);

            $targets[$name] = $target->include($params['targets.params'][$name]['include'])
                ->exclude($params['targets.params'][$name]['exclude']);
        }
        return new Profiler($logger, $targets);
    },
];
