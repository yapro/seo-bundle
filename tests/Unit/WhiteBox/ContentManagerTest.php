<?php

declare(strict_types=1);

namespace YaPro\SeoBundle\Tests\Unit\WhiteBox;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use YaPro\Helper\LiberatorTrait;
use YaPro\SeoBundle\ContentManager;
use YaPro\SeoBundle\LinkManager;

class ContentManagerTest extends TestCase
{
    use LiberatorTrait;

    private static string $currentHttpHost = 'my.ru';
    private static string $redirectUrl = '/redirect/?to=';

    public function providerGetHtmlWithSafeExternalLinks(): iterable
    {
        yield [
            'html' => 'my <a href="https://external.ru/">site link</a> text',
            'expected' => 'my <a href="' . self::$redirectUrl . 'https://external.ru/" target=_blank rel=nofollow>site link</a> text',
        ];
    }

    /**
     * @dataProvider providerGetHtmlWithSafeExternalLinks
     *
     * @param string $html
     * @param string $expected
     *
     * @return void
     */
    public function testGetHtmlWithSafeExternalLinks(string $html, string $expected): void
    {
        $linkManager = self::createMock(LinkManager::class);
        $linkManager->method('getCurrentHttpHost')->willReturn(self::$currentHttpHost);
        $linkManager->method('getProtocols')->willReturn(['https']);
        $linkManager->method('getSeoLink')->will(
            $this->returnCallback(function ($argument) {
                return self::$redirectUrl . $argument;
            })
        );
        /** @var ContentManager|MockObject $contentManager */
        $contentManager = self::getMockBuilder(ContentManager::class)
            ->setConstructorArgs([$linkManager])
            ->setMethodsExcept(['getHtmlWithSeoLinks'])
            ->getMock();
        $result = $contentManager->getHtmlWithSeoLinks($html);
        $this->assertSame($expected, $result);
    }

    public function testGetSafeHtmlWithSafeExternalLinks()
    {
        $linkManager = self::createMock(LinkManager::class);
        /** @var ContentManager|MockObject $contentManager */
        $contentManager = self::getMockBuilder(ContentManager::class)
            ->setConstructorArgs([$linkManager, self::$currentHttpHost, ['http']])
            ->setMethodsExcept(['getSafeHtmlWithSeoLinks'])
            ->getMock();
        $html = 'Hello <a href="http://ya.ru">world</a> <img src="/path.png">';
        $contentManager->method('getSafeHtml')->willReturn('Hello <a href="http://ya.ru">world</a> [placeholder]');
        $contentManager->method('getHtmlWithSeoLinks')->willReturn('Hello <a href="/outer?to=http://ya.ru">world</a> [placeholder]');
        $this->setClassPropertyValue($contentManager, 'before', ['1' => '<img src="/path.png">']);
        $this->setClassPropertyValue($contentManager, 'after', ['1' => '[placeholder]']);
        $result = $contentManager->getSafeHtmlWithSeoLinks($html);
        $this->assertSame('Hello <a href="/outer?to=http://ya.ru">world</a> <img src="/path.png">', $result);
    }
}
