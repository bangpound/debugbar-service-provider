<?php

namespace Bangpound\Provider;

use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use DebugBar\OpenHandler;
use DebugBar\RequestIdGenerator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DebugbarServiceProvider implements ServiceProviderInterface
{
    const COLLECTOR_MATCH = '/^debugbar\.collector\.[a-z0-9_.]+?$/';

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple An Container instance
     */
    public function register(Container $pimple)
    {
        $pimple['debugbar.base_url'] = null;
        $pimple['debugbar.base_path'] = null;

        $pimple['debugbar'] = function ($c) {
            return new DebugBar();
        };

        $pimple['debugbar.collector.phpinfo'] = function ($c) {
            return new PhpInfoCollector();
        };
        $pimple->extend('debugbar', function (DebugBar $debugbar, Container $c) {
            $ids = preg_grep(self::COLLECTOR_MATCH, $c->keys());

            foreach ($ids as $id) {
                $debugbar->addCollector($c[$id]);
            }

            return $debugbar;
        });

        $pimple['debugbar.collector.messages'] = function ($c) {
            return new MessagesCollector();
        };

        $pimple['debugbar.collector.request_data'] = function ($c) {
            return new RequestDataCollector();
        };

        $pimple['debugbar.collector.time_data'] = function ($c) {
            return new TimeDataCollector();
        };

        $pimple['debugbar.collector.memory'] = function ($c) {
            return new MemoryCollector();
        };

        $pimple['debugbar.collector.exceptions'] = function ($c) {
            return new ExceptionsCollector();
        };

        $pimple['debugbar.handler.open'] = function ($c) {
            return new OpenHandler($c['debugbar']);
        };

        $pimple['debugbar.generator.request_id'] = function ($c) {
            return new RequestIdGenerator();
        };
        $pimple->extend('debugbar', function (DebugBar $debugbar, Container $c) {
            $debugbar->setRequestIdGenerator($c['debugbar.generator.request_id']);

            return $debugbar;
        });

        $pimple['debugbar.renderer'] = function ($c) {
            return $c['debugbar']->getJavascriptRenderer($c['debugbar.base_url'], $c['debugbar.base_path']);
        };
    }
}
