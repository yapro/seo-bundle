<?php

declare(strict_types=1);

namespace YaPro\SeoBundle;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

// https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag
class NoindexResponseListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->getPathInfo() !== $request->getRequestUri()) {
            $event->getResponse()->headers->set('X-Robots-Tag', 'noindex');
        }
    }
}
