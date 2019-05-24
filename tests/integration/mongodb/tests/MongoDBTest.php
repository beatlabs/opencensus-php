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

namespace OpenCensus\Tests\Integration\Trace\Exporter;

use MongoDB\Driver\Manager;
use OpenCensus\Trace\Tracer;
use OpenCensus\Trace\Exporter\ExporterInterface;
use PHPUnit\Framework\TestCase;

class MongoDBTest extends TestCase
{

    const DATABASE = 'mock_db';
    const COLLECTION = 'mock_collection';
    const SERVER = 'mongodb://127.0.0.1:27017';

    private $manager;

    private $tracer;

    public function setUp()
    {
        if (!extension_loaded('opencensus')) {
            $this->markTestSkipped('Please enable the opencensus extension.');
        }
        opencensus_trace_clear();
        $exporter = $this->prophesize(ExporterInterface::class);
        $this->tracer = Tracer::start($exporter->reveal(), [
            'skipReporting' => true
        ]);

        $this->manager = new Manager(static::SERVER);
    }

    public function testExecuteBulkWrite()
    {
        $bulk = new MongoDB\Driver\BulkWrite;

        $this->manager->executeBulkWrite(static::DATABASE . '.' . static::COLLECTION, $bulk);
    }

    public function testExecuteCommand()
    {

    }

    public function testExecuteReadCommand()
    {

    }

    public function testExecuteReadWriteCommand()
    {

    }

    public function testExecuteWriteCommand()
    {

    }

    public function testExecuteQuery()
    {

    }

}
