<?php

declare(strict_types=1);

namespace YaPro\SeoBundle\Tests\Unit\WhiteBox;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function str_replace;
use YaPro\SeoBundle\LinkManager;

class LinkManagerTest extends TestCase
{
    public function testGetLink()
    {
        $redirectAddress = 'redirectAddress';
        $privateKey = 'privateKey';
        $linkManager = new LinkManager($redirectAddress, $privateKey, 'site.com');
        $url = 'url';
        $result = $linkManager->getSeoLink($url);
        $expected = $redirectAddress . '?key=' . sha1($privateKey . $url) . '&to=' . $url;
        $this->assertSame($expected, $result);
    }

    public function providerGetRedirect(): iterable
    {
        $linkManager = new LinkManager(null, null, 'test.ru');

        yield [
            'requestUrl' => $linkManager->getSeoLink('scheme:// '),
            'expectedStatus' => LinkManager::STATUS_SCHEME_AND_HOST_NOT_SPECIFIED,
        ];
        yield [
            'requestUrl' => 'string',
            'expectedStatus' => LinkManager::STATUS_I_DON_T_HAVE_GET_VARIABLE_NAMED_KEY,
        ];
        yield [
            'requestUrl' => $linkManager->getRedirectPage() . '?key=string',
            'expectedStatus' => LinkManager::STATUS_I_DON_T_HAVE_GET_VARIABLE_NAMED_TO,
        ];
        yield [
            'requestUrl' => $linkManager->getRedirectPage() . '?key=&to=',
            'expectedStatus' => LinkManager::STATUS_I_DON_T_HAVE_GET_VARIABLE_NAMED_TO,
        ];
        yield [
            'requestUrl' => $linkManager->getRedirectPage() . '?key=1&to=',
            'expectedStatus' => LinkManager::STATUS_I_DON_T_HAVE_GET_VARIABLE_NAMED_TO,
        ];
        yield [
            'requestUrl' => $linkManager->getRedirectPage() . '?key=&to=1',
            'expectedStatus' => LinkManager::STATUS_I_DON_T_HAVE_GET_VARIABLE_NAMED_TO,
        ];
        yield [
            'requestUrl' => $linkManager->getRedirectPage() . '?key=1&to=2',
            'expectedStatus' => LinkManager::STATUS_WRONG_ACCESS_KEY,
        ];
        yield [
            'requestUrl' => $linkManager->getSeoLink(':'),
            'expectedStatus' => LinkManager::STATUS_PROBLEMS_WITH_PARSE_URL,
        ];
        yield [
            'requestUrl' => $linkManager->getSeoLink('scheme://'),
            'expectedStatus' => LinkManager::STATUS_PROBLEMS_WITH_PARSE_URL,
        ];
        yield [
            'requestUrl' => $linkManager->getSeoLink('scheme:// '),
            'expectedStatus' => LinkManager::STATUS_SCHEME_AND_HOST_NOT_SPECIFIED,
        ];
        yield [
            'requestUrl' => $linkManager->getSeoLink('/'),
            'expectedStatus' => LinkManager::STATUS_SCHEME_AND_HOST_NOT_SPECIFIED,
        ];
        yield [
            'requestUrl' => $linkManager->getSeoLink('/page'),
            'expectedStatus' => LinkManager::STATUS_SCHEME_AND_HOST_NOT_SPECIFIED,
        ];
        yield [
            'requestUrl' => $linkManager->getSeoLink('tcp://host'),
            'expectedStatus' => LinkManager::STATUS_NOT_ALLOWED_SCHEME,
        ];
        yield [
            'requestUrl' => $linkManager->getSeoLink('tcp://host'),
            'expectedStatus' => LinkManager::STATUS_NOT_ALLOWED_SCHEME,
        ];
        yield [
            'requestUrl' => $linkManager->getSeoLink('http://test.ru'),
            'expectedStatus' => LinkManager::STATUS_REDIRECT_ON_CURRENT_HTTP_HOST,
        ];
        yield [
            'requestUrl' => $linkManager->getSeoLink('http://external.ru/page'),
            'expectedStatus' => LinkManager::STATUS_REDIRECT_IS_ALLOWED,
        ];
        yield [
            'requestUrl' => $linkManager->getSeoLink('http://external.ru/page?a=1&amp;s=2'),
            'expectedStatus' => LinkManager::STATUS_REDIRECT_IS_ALLOWED,
        ];
        yield [
            // такую замену делает браузер:
            'requestUrl' => str_replace('&amp;', '&', $linkManager->getSeoLink('http://external.ru/page?a=1&amp;s=2')),
            'expectedStatus' => LinkManager::STATUS_REDIRECT_IS_ALLOWED,
        ];
    }

    /**
     * @dataProvider providerGetRedirect
     *
     * @param string $requestUrl
     * @param string $expectedStatus
     *
     * @return void
     */
    public function testGetRedirect(string $requestUrl, string $expectedStatus)
    {
        /** @var LinkManager|MockObject $linkManager */
        $linkManager = self::getMockBuilder(LinkManager::class)
            ->setConstructorArgs([null, null, 'test.ru'])
            ->setMethodsExcept(['getRedirect', 'getKey'])
            ->getMock();
        $result = $linkManager->getRedirect($requestUrl);
        $this->assertSame($expectedStatus, $result->getInfoStatus());
    }
}
