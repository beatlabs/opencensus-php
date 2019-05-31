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

use MongoDB\Client;
use OpenCensus\Trace\Integrations\MongoDB;
use OpenCensus\Trace\Tracer;
use OpenCensus\Trace\Exporter\ExporterInterface;
use PHPUnit\Framework\TestCase;

class MongoDBTest extends TestCase
{
    private const DATABASE = 'mock_db';
    private const COLLECTION = 'mock_collection';
    private const SERVER = 'mongodb://127.0.0.1:27017';

    private $tracer;

    public static function setUpBeforeClass()
    {
        MongoDB::load();
    }

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
    }

    private function getSpans()
    {
        $this->tracer->onExit();

        return $this->tracer->tracer()->spans();
    }

    private function getDatabase()
    {
        $client = new Client(self::SERVER);
        return $client->selectDatabase(self::DATABASE);
    }


    public function testHandleClient()
    {
        $client = new Client(self::SERVER);
        $spans = $this->getSpans();
        $this->assertCount(2, $spans);
    }

    public function testHandleDatabase()
    {
        $this->getDatabase();
        $spans = $this->getSpans();
        $this->assertCount(3, $spans);
    }

    public function testExecuteFind()
    {
        $database = $this->getDatabase();
        $database->createCollection(self::COLLECTION);
        $collection = $database->selectCollection(self::COLLECTION);
        $collection->find(['key' => 'value']);

        $spans = $this->getSpans();
        $this->assertCount(4, $spans);
    }

    public function testExecuteUpdate()
    {
        $database = $this->getDatabase();
        $collection = $database->selectCollection(self::COLLECTION);
        $collection->updateOne(['key' => 'value'], ['$set' => ['key' => 'value']], ['upsert' => true]);

        $spans = $this->getSpans();
        $this->assertCount(4, $spans);
    }

    public function testExecuteCommand()
    {
        $database = $this->getDatabase();
        try {
            $database->command($database, ['num' => 10]);
        } catch(\InvalidArgumentException $e) {
        }

        $spans = $this->getSpans();
        $this->assertCount(4, $spans);
    }
}