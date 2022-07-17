<?php

declare(strict_types=1);

namespace YaPro\SeoBundle;

class RedirectValueObject
{
    private int $httpStatus;
    private string $infoStatus;
    private ?string $url;

    public function __construct(int $httpStatus, string $infoStatus, ?string $url = '')
    {
        $this->httpStatus = $httpStatus;
        $this->infoStatus = $infoStatus;
        $this->url = $url;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getInfoStatus(): string
    {
        return $this->infoStatus;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
