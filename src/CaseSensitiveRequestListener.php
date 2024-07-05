<?php

declare(strict_types=1);

namespace YaPro\SeoBundle;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

// если кто-то зайдет на страницу в которой будут символы в верхнем реигстре, его перенаправит на страницу с символами в нижнем регистре
class CaseSensitiveRequestListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();
        $requestUri = $request->getRequestUri();
        $lowercasePath = mb_strtolower($request->getRequestUri());
        if ($requestUri === $lowercasePath) {
            return;
        }
        $this->logger->notice('redirectToLowercase', [
            'requestUri' => $requestUri,
            'lowercasePath' => $lowercasePath,
        ]);
        $event->setResponse(new RedirectResponse($request->getUriForPath($lowercasePath), 301));
    }
}
