<?php

namespace OpenCensus\Tests\Unit\Trace\Propagator;

use OpenCensus\Trace\SpanContext;
use OpenCensus\Trace\Propagator\JaegerTraceFormatter;
use PHPUnit\Framework\TestCase;

/**
 * @group trace
 */
class JaegerTraceFormatterTest extends TestCase
{
    /**
     * @dataProvider traceHeaders
     */
    public function testParseContext($traceId, $spanId, $parentSpanId, $enabled, $header)
    {
        $formatter = new JaegerTraceFormatter();
        $context = $formatter->deserialize($header);
        $this->assertEquals($traceId, $context->traceId());
        $this->assertEquals($spanId, $context->spanId());
        $this->assertEquals($enabled, $context->enabled());
        $this->assertTrue($context->fromHeader());
    }

    /**
     * @dataProvider traceHeaders
     */
    public function testToString($traceId, $spanId, $parentSpanId, $enabled, $expected)
    {
        $formatter = new JaegerTraceFormatter();
        $context = new SpanContext($traceId, $spanId, $enabled);
        $this->assertEquals($expected, $formatter->serialize($context));
    }

    public function traceHeaders()
    {
        return [
            ['109ae00000000000109ae', '47bdd', '', false, '109ae00000000000109ae:293853:0:0'],
            ['109ae00000000000109ae', '47bdd', '', true,  '109ae00000000000109ae:293853:0:1'],
            ['109ae00000000000109ae', '47bdd', '', null,  '109ae00000000000109ae:293853:0:0'],
            ['109ae00000000000109ae', null, '', false,  '109ae00000000000109ae:0:0:0'],
            ['109ae00000000000109ae', null, '', true,  '109ae00000000000109ae:0:0:1'],
            ['109ae00000000000109ae', null, '', null,   '109ae00000000000109ae:0:0:0'],
            ['109ae00000000000109ae', '47bdd', '', null,   '109ae00000000000109ae:293853:0:0'],
        ];
    }
}
