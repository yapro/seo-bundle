<?php

declare(strict_types=1);

namespace YaPro\SeoBundle\Tests\Unit\BlackBox;

use PHPUnit\Framework\TestCase;
use YaPro\SeoBundle\ContentManager;
use YaPro\SeoBundle\LinkManager;

class ContentManagerTest extends TestCase
{
    private static string $currentHttpHost = 'my.ru';

    public function testGetSafeHtmlWithSafeExternalLinks()
    {
        $externalLink = 'http://external.com/page';
        $redirectUrl = '/redirect?to=' . $externalLink;
        $html = '
             String1  <!--NoReplace--><a href="http://external.com/page">unchangeable link</a><!--/NoReplace--> String2
             String1  <a href="' . $externalLink . '">changeable link with double quotes</a> String2
             String1  <a href=' . $externalLink . '>changeable link without double quotes</a> String2
             String1  <a href="/">world</a> String2
             String1  <script>let my = "var";</script> String2
             String1  <style>.my { display: block }</style> String2
             String1  <textarea>my text</textarea> String2
             String1 <input value="text"> String2
             String1 <img src="/path.png" alt="some text"> String2
             ';
        $expected = '
             String1  <a href="http://external.com/page">unchangeable link</a> String2
             String1  <a href="' . $redirectUrl . '" target=_blank rel=nofollow>changeable link with double quotes</a> String2
             String1  <a href=' . $redirectUrl . ' target=_blank rel=nofollow>changeable link without double quotes</a> String2
             String1  <a href="/">world</a> String2
             String1  <script>let my = "var";</script> String2
             String1  <style>.my { display: block }</style> String2
             String1  <textarea>my text</textarea> String2
             String1 <input value="text"> String2
             String1 <img src="/path.png" alt="some text"> String2
             ';
        $linkManager = self::createMock(LinkManager::class);
        $linkManager->method('getSeoLink')->willReturn($redirectUrl);
        $linkManager->method('getCurrentHttpHost')->willReturn(self::$currentHttpHost);
        $linkManager->method('getProtocols')->willReturn(['http']);
        $contentManager = new ContentManager($linkManager);
        $result = $contentManager->getSafeHtmlWithSeoLinks($html);
        $this->assertSame($expected, $result);
    }
}
