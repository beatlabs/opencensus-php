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
    const CONTEXT_HEADER_FORMAT = '/(\w+):(\d+)?:?(\w+):?(\d)/';

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
                ? $this->decToHex($matches[2])
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
            $ret .= ':' . $this->hexToDec($context->spanId());
            // parentSpanId
            $ret .= ':0' ;
        }

        // isEnabled
        $ret .= ':' . (!empty($context->enabled()) ? '1' : '0');

        $this->deserialize($ret);

        return $ret;
    }

    /**
     * Hexadecimal to Decimal conversion
     *
     * @param $numstring
     *
     * @return float|int|string
     */
    private function hexToDec($numstring)
    {
        $dec = hexdec($numstring);
        if ($this->isBigNum($dec)) {
            return $this->baseConvert($numstring, 16, 10);
        }

        return $dec;
    }

    /**
     * Decimal to Hexadecimal conversion
     *
     * @param $numstring
     *
     * @return string
     */
    private function decToHex($numstring) : string
    {
        $int = (int) $numstring;
        if ($this->isBigNum($int)) {
            return $this->baseConvert($numstring, 10, 16);
        }

        return dechex($int);
    }

    /**
     * @param $number
     *
     * @return bool
     */
    private function isBigNum($number) : bool
    {
        return $number >= PHP_INT_MAX;
    }

    /**
     * Returns a string containing number represented in base toBase.
     *
     * @param $numstring
     * @param $fromBase
     * @param $toBase
     *
     * @return string
     */
    private function baseConvert($numstring, $fromBase, $toBase) : string
    {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
        $newstring = substr($chars, 0, $toBase);

        $length = strlen($numstring);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $number[$i] = strpos($chars, $numstring{$i});
        }

        do {
            $divide = 0;
            $newlen = 0;
            for ($i = 0; $i < $length; $i++) {
                $divide = $divide * $fromBase + $number[$i];
                if ($divide >= $toBase) {
                    $number[$newlen++] = (int) ($divide / $toBase);
                    $divide = $divide % $toBase;
                } elseif ($newlen > 0) {
                    $number[$newlen++] = 0;
                }
            }
            $length = $newlen;
            $result = $newstring{$divide} . $result;
        } while ($newlen != 0);

        return $result;
    }
}
