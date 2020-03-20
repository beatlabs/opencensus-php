<?php

declare(strict_types = 1);

namespace OpenCensus\Trace\Propagator;

use OpenCensus\Trace\SpanContext;

/**
 * JaegerTraceFormatter implements the propagation format
 * as instructed by Jaeger (https://www.jaegertracing.io/docs/1.7/client-libraries/#propagation-format)
 *
 * @package OpenCensus\Trace\Propagator
 */
class JaegerTraceFormatter implements FormatterInterface
{
    /**
     * Header format:
     *  {trace-id}:{span-id}:{parent-span-id}:{flags}
     *
     *  NOTE ABOUT {parent-span-id}: Deprecated, most Jaeger clients ignore
     *  on the receiving side, but still include it on the sending side
     */
    const CONTEXT_HEADER_FORMAT = '/(\w+):(\w+):(\w+):(\d)/';

    /**
     * Generate a SpanContext object from the Trace Context header
     *
     * @param string $header
     *
     * @return SpanContext
     */
    public function deserialize($header) : SpanContext
    {
        if (preg_match(self::CONTEXT_HEADER_FORMAT, $header, $matches)) {
            $traceId = strtolower($matches[1]);

            $spanId = array_key_exists(2, $matches) && !empty($matches[2])
                ? $matches[2]
                : null;

            $isEnabled = array_key_exists(4, $matches) ? $matches[4] == '1' : null;

            $spanContext = new SpanContext(
                $traceId,
                $spanId,
                $isEnabled,
                true
            );

            return $spanContext;
        }

        return new SpanContext();
    }

    /**
     * Convert a SpanContext to header string
     *
     * @param SpanContext $context
     *
     * @return string
     */
    public function serialize(SpanContext $context) : string
    {
        $ret = '' . $context->traceId();

        if ($context->spanId()) {
            // spanId
            $ret .= ':' . $context->spanId();
            // parentSpanId
            $ret .= ':0' ;
        } else {
            // spanId with parentSpanId
            $ret .= ':0:0' ;
        }

        // isEnabled
        $ret .= ':' . (!empty($context->enabled()) ? '1' : '0');

        return $ret;
    }
}
