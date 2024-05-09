<?php
declare(strict_types=1);

namespace YaPro\SeoBundle\Tests\Unit\WhiteBox;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use YaPro\SeoBundle\LastModifiedResponseListener;
use YaPro\SeoBundle\LinkManager;

class LastModifiedResponseListenerTest extends TestCase
{
    public function testFormat()
    {
        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified#hour
        $expected = 'Wed, 21 Oct 2015 07:28:00 GMT';
        $carbon = CarbonImmutable::createFromFormat(CarbonInterface::RFC7231_FORMAT, $expected);
        $result = LastModifiedResponseListener::format($carbon);
        $this->assertSame($expected, $result);
    }
}
