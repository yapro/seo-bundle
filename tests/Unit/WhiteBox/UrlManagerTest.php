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
        $this->assertSame('privet-mir-dollarov', $actual);

        $actual = $object->transliterate('Привет super-Мир.');
        $this->assertSame('privet-super-mir', $actual);
    }

    public function testPrepareEnglishSlug(): void
    {
        $object = new UrlManager();

        $actual = $object->transliterateEnglishSlug(' Children\'s $! ');
        $this->assertSame('children-dollars', $actual);

        $actual = $object->transliterateEnglishSlug(' Children`s $! ');
        $this->assertSame('children-dollars', $actual);

        $actual = $object->transliterateEnglishSlug(' foo — bar – baz ');
        $this->assertSame('foo-bar-baz', $actual);
    }

    public function testPrepareEnglishPath(): void
    {
        $object = new UrlManager();

        $actual = $object->transliterateEnglishPath('The/ Children\'s $! ');
        $this->assertSame('/the/children-dollars', $actual);

        $actual = $object->transliterateEnglishPath('/the/ Children`s $! ');
        $this->assertSame('/the/children-dollars', $actual);

        $actual = $object->transliterateEnglishPath(' / THE / Foo / bar – baz ');
        $this->assertSame('/the/foo/bar-baz', $actual);
    }
}
