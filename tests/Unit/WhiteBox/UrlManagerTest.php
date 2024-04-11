<?php

declare(strict_types=1);

namespace YaPro\SeoBundle\Tests\Unit\WhiteBox;

use PHPUnit\Framework\TestCase;
use YaPro\SeoBundle\UrlManager;

class UrlManagerTest extends TestCase
{
    public function testTransliterate(): void
    {
        $object = new UrlManager();
        $actual = $object->transliterate(' Привет Мир $! ');
        $this->assertSame('privet_mir', $actual);
    }
}
