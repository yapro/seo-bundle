<?php

declare(strict_types=1);

namespace YaPro\SeoBundle;

class LinkManager
{
    public const STATUS_REDIRECT_IS_ALLOWED = 'Redirect is allowed';
    public const STATUS_I_DON_T_HAVE_GET_VARIABLE_NAMED_KEY = 'I don`t have get-variable named key';
    public const STATUS_I_DON_T_HAVE_GET_VARIABLE_NAMED_TO = 'I don`t have get-variable named to';
    public const STATUS_WRONG_ACCESS_KEY = 'Wrong access key';
    public const STATUS_PROBLEMS_WITH_PARSE_URL = 'Problems with parse url';
    public const STATUS_SCHEME_AND_HOST_NOT_SPECIFIED = 'Scheme and host not specified';
    public const STATUS_NOT_ALLOWED_SCHEME = 'Not allowed scheme';
    public const STATUS_REDIRECT_ON_CURRENT_HTTP_HOST = 'Redirect on current http host';
    private string $redirectPage;
    private string $securityKey;
    private string $currentHttpHost;
    private array $protocols;
    private string $anchorSymbol = '-anchorCharacterPlaceholder-';

    public function __construct(
        ?string $redirectPage = null,
        ?string $securityKey = null,
        ?string $currentHttpHost = null,
        ?array $protocols = null
    ) {
        $this->redirectPage = $redirectPage ?? '/redirect/page';
        $this->securityKey = $securityKey ?? 'insecurity';
        $this->currentHttpHost = $currentHttpHost ?? $_SERVER['HTTP_HOST'] ?? '';
        if (empty($this->currentHttpHost)) {
            throw new \UnexpectedValueException('HTTP host must be specified to prevent looping');
        }
        $this->protocols = $protocols ?? ['https', 'http', 'ftp'];
    }

    public function getRedirect(?string $requestUrl = ''): RedirectValueObject
    {
        if (empty(trim($requestUrl))) {
            $requestUrl = $_SERVER['REQUEST_URI'];
            if (empty(trim($requestUrl))) {
                throw new \UnexpectedValueException('Request url must be specified');
            }
        }
        $payload = explode($this->redirectPage . '?key=', $requestUrl);
        if (empty($payload['1'])) {
            return new RedirectValueObject(404, self::STATUS_I_DON_T_HAVE_GET_VARIABLE_NAMED_KEY);
        }
        $keyAndUrl = explode('&to=', $payload['1']);
        if (empty($keyAndUrl['0']) || empty($keyAndUrl['1'])) {
            return new RedirectValueObject(404, self::STATUS_I_DON_T_HAVE_GET_VARIABLE_NAMED_TO);
        }
        $key = $keyAndUrl['0'];
        $url = str_replace($this->anchorSymbol, '#', $keyAndUrl['1']);
        // замену &amp; на & делает браузер в адресной строке, поэтому допускается и такой вариант:
        if ($key !== $this->getKey($url) && $key !== $this->getKey(str_replace('&', '&amp;', $url))) {
            return new RedirectValueObject(404, self::STATUS_WRONG_ACCESS_KEY);
        }
        $components = parse_url($url);
        if (false === $components) {
            return new RedirectValueObject(404, self::STATUS_PROBLEMS_WITH_PARSE_URL);
        }
        $protocol = isset($components['scheme']) ? trim($components['scheme']) : '';
        $host = isset($components['host']) ? trim($components['host']) : '';
        if (empty($protocol) || empty($host)) {
            return new RedirectValueObject(404, self::STATUS_SCHEME_AND_HOST_NOT_SPECIFIED);
        }
        if (false === in_array($protocol, $this->protocols, true)) {
            return new RedirectValueObject(404, self::STATUS_NOT_ALLOWED_SCHEME);
        }
        // нельзя редиректить самому себе:
        if (
            !empty($this->currentHttpHost) &&
            str_replace('www.', '', $host) === str_replace('www.', '', $this->currentHttpHost)
        ) {
            return new RedirectValueObject(404, self::STATUS_REDIRECT_ON_CURRENT_HTTP_HOST);
        }

        return new RedirectValueObject(200, self::STATUS_REDIRECT_IS_ALLOWED, $url);
    }

    public function getSeoLink(string $url): string
    {
        return $this->redirectPage . '?key=' . $this->getKey($url) . '&to=' . str_replace('#', $this->anchorSymbol, $url);
    }

    public function getRedirectPage(): string
    {
        return $this->redirectPage;
    }

    public function getCurrentHttpHost(): string
    {
        return $this->currentHttpHost;
    }

    public function getProtocols(): array
    {
        return $this->protocols;
    }

    public function getKey(string $url): string
    {
        return sha1($this->securityKey . $url);
    }

    public function redirectionBasedOn(string $requestUrl = ''): void
    {
        $redirect = $this->getRedirect($requestUrl);
        if ($redirect->getHttpStatus() === 200) {
            header('location: ' . $redirect->getUrl());
        } else {
            http_response_code(404);
        }
    }
}
