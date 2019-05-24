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

namespace OpenCensus\Tests\Unit\Trace\Integrations;

use MongoDB\Driver\Command;
use MongoDB\Driver\Query;
use MongoDB\Driver\ReadPreference;
use OpenCensus\Trace\Integrations\Mongo;
use PHPUnit\Framework\TestCase;
use MongoDB\Driver\BulkWrite;

/**
 * @group trace
 */
class MongoTest extends TestCase
{
    public function testHandleExecuteBulkWrite()
    {
        $namespace = 'database.collection';
        $zwrite = new BulkWrite(['option' => 0]);
        $options = ['option' => 1];

        $actual = Mongo::handleExecuteBulkWrite($namespace, $zwrite, $options);
        $expected = [
            'attributes' => [
                'namespace' => $namespace,
                'zwrite' => json_encode($zwrite),
                'options' => $options
            ],
            'kind' => 'CLIENT'
        ];
        $this->assertSame($expected, $actual);
    }

    public function testHandleExecuteReadorWriteCommand()
    {
        $db = 'database';
        $command = new Command([1, 2], [3, 4]);
        $option = ['option' => 1];

        $actual = Mongo::handleExecuteReadOrWriteCommand($db, $command, $option);
        $expected = [
            'attributes' => [
                'db' => $db,
                'command' => json_encode($command),
                'option' => $option
            ],
            'kind' => 'CLIENT'
        ];
        $this->assertSame($expected, $actual);
    }

    public function testHandleExecuteCommand()
    {
        $db = 'database';
        $command = new Command([1, []]);
        $readPreference = new ReadPreference(2, []);

        $actual = Mongo::handleExecuteCommand($db, $command, $readPreference);
        $expected = [
            'attributes' => [
                'db' => $db,
                'command' => json_encode($command),
                'readPreference' => json_encode($readPreference)
            ],
            'kind' => 'CLIENT'
        ];
        $this->assertSame($expected, $actual);
    }

    public function testHandleExecuteQuery()
    {
        $namespace = 'database.collection';
        $query = new Query([1, 2], [3, 4]);
        $readPreference = new ReadPreference(1, []);

        $actual = Mongo::handleExecuteQuery($namespace, $query, $readPreference);
        $expected = [
            'attributes' => [
                'namespace' => $namespace,
                'query' => json_encode($query),
                'readPreference' => json_encode($readPreference)
            ],
            'kind' => 'CLIENT'
        ];
        $this->assertSame($expected, $actual);
    }
}
