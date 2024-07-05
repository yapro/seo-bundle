<?php

declare(strict_types=1);

namespace YaPro\SeoBundle;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

// если кто-то зайдет на страницу в которой будут символы в верхнем реигстре, его перенаправит на страницу с символами в нижнем регистре
class CaseSensitiveRequestListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        if (!isset($_SERVER['REQUEST_URI'])) {
            return;
        }
        $url = parse_url($_SERVER['REQUEST_URI']);
        $requestUri = $url['path'] ?? '';
        $lowercasePath = mb_strtolower($requestUri);
        if ($requestUri === $lowercasePath) {
            return;
        }
        if (isset($url['query'])) {
            $lowercasePath .= '?' . $url['query'];
        }
        if (isset($url['fragment'])) {
            $lowercasePath .= '#' . $url['fragment'];
        }
        $event->setResponse(new RedirectResponse($event->getRequest()->getUriForPath($lowercasePath), 301));
    }
}
