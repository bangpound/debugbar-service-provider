<?php

namespace Bangpound\Provider;

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
     * @param \Pimple\Container $pimple An Container instance
     */
    public function register(\Pimple\Container $pimple)
    {
        $pimple['debugbar.base_url'] = null;
        $pimple['debugbar.base_path'] = null;

        $pimple['debugbar'] = function ($c) {
            return new \DebugBar\DebugBar();
        };

        $pimple['debugbar.collector.phpinfo'] = function ($c) {
            return new \DebugBar\DataCollector\PhpInfoCollector();
        };
        $pimple->extend('debugbar', function (\DebugBar\DebugBar $debugbar, \Pimple\Container $c) {
            $ids = preg_grep(self::COLLECTOR_MATCH, $c->keys());

            foreach ($ids as $id) {
                $debugbar->addCollector($c[$id]);
            }

            return $debugbar;
        });

        $pimple['debugbar.collector.messages'] = function ($c) {
            return new \DebugBar\DataCollector\MessagesCollector();
        };

        $pimple['debugbar.collector.request_data'] = function ($c) {
            return new \DebugBar\DataCollector\RequestDataCollector();
        };

        $pimple['debugbar.collector.time_data'] = function ($c) {
            return new \DebugBar\DataCollector\TimeDataCollector();
        };

        $pimple['debugbar.collector.memory'] = function ($c) {
            return new \DebugBar\DataCollector\MemoryCollector();
        };

        $pimple['debugbar.collector.exceptions'] = function ($c) {
            return new \DebugBar\DataCollector\ExceptionsCollector();
        };

        $pimple['debugbar.handler.open'] = function ($c) {
            return new \DebugBar\OpenHandler($c['debugbar']);
        };

        $pimple['debugbar.renderer'] = function ($c) {
            return $c['debugbar']->getJavascriptRenderer($c['debugbar.base_url'], $c['debugbar.base_path']);
        };
    }
}
