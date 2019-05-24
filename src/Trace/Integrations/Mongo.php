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
 * use OpenCensus\Trace\Integrations\Mongo;
 *
 * Mongo::load();
 * ```
 */
class Mongo implements IntegrationInterface
{
    /**
     * Static method to add instrumentation to mongo queries
     */
    public static function load()
    {
        if (!extension_loaded('opencensus')) {
            trigger_error('opencensus extension required to load Elastica integrations.', E_USER_WARNING);
        }

        opencensus_trace_method('MongoDB\Driver\Server', 'executeBulkWrite', [static::class, 'handleExecuteBulkWrite']);

        opencensus_trace_method('MongoDB\Driver\Server', 'executeCommand', [static::class, 'handleExecuteCommand']);

        opencensus_trace_method('MongoDB\Driver\Server', 'executeReadCommand', [static::class, 'handleExecuteReadOrWriteCommand']);

        opencensus_trace_method('MongoDB\Driver\Server', 'executeReadWriteCommand', [static::class, 'handleExecuteReadOrWriteCommand']);

        opencensus_trace_method('MongoDB\Driver\Server', 'executeWriteCommand', [static::class, 'handleExecuteReadOrWriteCommand']);

        opencensus_trace_method('MongoDB\Driver\Server', 'executeQuery', [static::class, 'handleExecuteQuery']);
    }

    /**
     * @param $namespace
     * @param $zwrite
     * @param $options
     *
     * @return array
     */
    public static function handleExecuteBulkWrite($namespace, $zwrite, $options = [])
    {
        return [
            'attributes' => [
                'namespace' => $namespace,
                'zwrite' => json_encode($zwrite),
                'options' => $options
            ],
            'kind' => Span::KIND_CLIENT
        ];
    }

    /**
     * @param $db
     * @param $command
     * @param $readPreference
     *
     * @return array
     */
    public static function handleExecuteCommand($db, $command, $readPreference = null)
    {
        return [
            'attributes' => [
                'db' => $db,
                'command' => json_encode($command),
                'readPreference' => is_null($readPreference) ? '' : json_encode($readPreference),
            ],
            'kind' => Span::KIND_CLIENT
        ];
    }

    /**
     * @param $db
     * @param $command
     * @param $option
     *
     * @return array
     */
    public static function handleExecuteReadOrWriteCommand($db, $command, $option = [])
    {
        return [
            'attributes' => [
                'db' => $db,
                'command' => json_encode($command),
                'option' => $option
            ],
            'kind' => Span::KIND_CLIENT
        ];
    }

    /**
     * @param $namespace
     * @param $query
     * @param null $readPreference
     *
     * @return array
     */
    public static function handleExecuteQuery($namespace, $query, $readPreference = null)
    {
        return [
            'attributes' => [
                'namespace' => $namespace,
                'query' => json_encode($query),
                'readPreference' => is_null($readPreference) ? '' : json_encode($readPreference),
            ],
            'kind' => Span::KIND_CLIENT
        ];
    }
}
