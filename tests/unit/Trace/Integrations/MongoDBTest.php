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

use OpenCensus\Trace\Integrations\MongoDB;
use PHPUnit\Framework\TestCase;
/**
 * @group trace
 */
class MongoDBTest extends TestCase
{

    public function testHandleClient()
    {
        $uri = 'mongodb://localhost::27017';

        $actual = MongoDB::handleClient('mongodb', $uri);
        $expected = [
            'attributes' => [
                'uri' => $uri,
            ],
            'kind' => 'CLIENT',
        ];
        $this->assertSame($expected, $actual);
    }

    public function testHandleSelectDatabase()
    {
        $database = 'test_db';
        $options = [
            'key' => 'value',
        ];

        $actual = MongoDB::handleSelectDatabase('mongodb', $database, $options);
        $expected = [
            'attributes' => [
                'database' => $database,
                'options' => json_encode($options),
            ],
            'kind' => 'CLIENT',
        ];
        $this->assertSame($expected, $actual);
    }

    public function testHandleCommand()
    {
        $command = [ 'key' => 'value'];
        $options = ['key' => 'value'];

        $actual = MongoDB::handleCommand('mongodb', $command, $options);
        $expected = [
            'attributes' => [
                'command' => json_encode($command),
                'options' => json_encode($options),
            ],
            'kind' => 'CLIENT',
        ];
        $this->assertSame($expected, $actual);
    }

    public function testHandleFind()
    {
        $filter = [ 'key' => 'value'];

        $actual = MongoDB::handleFind('mongodb', $filter);
        $expected = [
            'attributes' => [
                'filter' => json_encode($filter),
            ],
            'kind' => 'CLIENT',
        ];
        $this->assertSame($expected, $actual);
    }

    public function testHandleUpdate()
    {
        $filter = [ 'key' => 'value'];
        $options = [];
        $update = [];

        $actual = MongoDB::handleUpdate('mongodb', $filter, $update, $options);
        $expected = [
            'attributes' => [
                'filter' => json_encode($filter),
                'update' => '[]',
                'options' => '[]',
            ],
            'kind' => 'CLIENT',
        ];
        $this->assertSame($expected, $actual);
    }
}