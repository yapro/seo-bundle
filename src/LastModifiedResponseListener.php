<?php

declare(strict_types=1);

namespace YaPro\SeoBundle;

use Carbon\CarbonImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

// Логирует ошибку о том, что на странице не установлена дата модификации https://last-modified.com/ru/last-modified-header
class LastModifiedResponseListener
{
    public const LAST_MODIFIED_HEADER_NAME = 'Last-Modified';

    private LoggerInterface $logger;
    private array $disablePaths;

    public function __construct(LoggerInterface $logger, array $disablePaths = [])
    {
        $this->logger = $logger;
        $this->disablePaths = array_merge($disablePaths, ['symfony_profiler' => '/_wdt/']);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $requestPath = $event->getRequest()->getPathInfo();
        foreach ($this->disablePaths as $disablePath) {
            if (mb_stripos($requestPath, $disablePath) === 0) {
                return;
            }
        }
        if (!$event->getResponse()->headers->has(self::LAST_MODIFIED_HEADER_NAME)) {
            $this->logger->notice('The page has no Last-Modified header', ['page' => $requestPath]);
        }
        // todo если символы в верхнем регистре, переводить в нижний и делать редирект:
    }

    public static function format(CarbonImmutable $date): string
    {
        return $date->toRfc7231String(); // Wed, 21 Oct 2015 07:28:00 GMT
    }
}
