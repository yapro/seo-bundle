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
        $request = $event->getRequest();
        $lowercasePath = mb_strtolower($request->getRequestUri());
        if ($request->getRequestUri() === $lowercasePath) {
            return;
        }
        $event->setResponse(new RedirectResponse($request->getUriForPath($lowercasePath), 301));
    }
}
