<?php

/**
 * Copyright 2019 OpenCensus Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenCensus\Trace\Integrations;

use OpenCensus\Trace\Span;

/**
 * This class handles MongoDB queries with mongodb library using the opencensus extension.
 *
 * Example:
 * ```
 * use OpenCensus\Trace\Integrations\MongoDB;
 *
 * MongoDB::load();
 * ```
 */
class MongoDB implements IntegrationInterface
{
    /**
     * Static method to add instrumentation to mongo queries
     */
    public static function load()
    {
        if (!extension_loaded('opencensus')) {
            trigger_error('opencensus extension required to load Elastica integrations.', E_USER_WARNING);
            return;
        }

        opencensus_trace_method('MongoDB\Client', '__construct', [static::class, 'handleClient']);
        opencensus_trace_method('MongoDB\Client', 'selectDatabase', [static::class, 'handleSelectDatabase']);
        opencensus_trace_method('MongoDB\Database', 'command', [static::class, 'handleCommand']);
        opencensus_trace_method('MongoDB\Collection', 'find', [static::class, 'handleFind']);
        opencensus_trace_method('MongoDB\Collection', 'findOne', [static::class, 'handleFind']);
        opencensus_trace_method('MongoDB\Collection', 'updateOne', [static::class, 'handleUpdate']);
        opencensus_trace_method('MongoDB\Collection', 'updateMany', [static::class, 'handleUpdate']);
    }

    /**
     * @param $namespace
     * @param $uri
     * @return array
     */
    public static function handleClient($namespace, $uri)
    {
        return [
            'attributes' => [
                'uri' => $uri,
            ],
            'kind' => Span::KIND_CLIENT,
        ];
    }

    /**
     * @param $namespace
     * @param $database
     * @param $options
     * @return array
     */
    public static function handleSelectDatabase($namespace, $database, $options)
    {
        return [
            'attributes' => [
                'database' => $database,
                'options' => json_encode($options),
            ],
            'kind' => Span::KIND_CLIENT,
        ];
    }

    /**
     * @param $namespace
     * @param $command
     * @param $options
     * @return array
     */
    public static function handleCommand($namespace, $command, $options)
    {
        return [
            'attributes' => [
                'command' => json_encode($command),
                'options' => json_encode($options),
            ],
            'kind' => Span::KIND_CLIENT,
        ];
    }

    /**
     * @param $namespace
     * @param $filter
     *
     * @return array
     */
    public static function handleFind($namespace, $filter)
    {
        return [
            'attributes' => [
                'filter' => json_encode($filter),
            ],
            'kind' => Span::KIND_CLIENT,
        ];
    }

    /**
     * @param $namespace
     * @param $filter
     * @param $update
     * @param $options
     *
     * @return array
     */
    public static function handleUpdate($namespace, $filter, $update, $options)
    {
        return [
            'attributes' => [
                'filter' => json_encode($filter),
                'update' => json_encode($update),
                'options' => json_encode($options),
            ],
            'kind' => Span::KIND_CLIENT,
        ];
    }
}
