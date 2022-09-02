# SEO bundle

SEO useful functionality

![lib tests](https://github.com/yapro/seo-bundle/actions/workflows/main.yml/badge.svg)

## Installation

Add as a requirement in your `composer.json` file or run for prod:
```sh
composer require yapro/seo-bundle
```

As dev:
```sh
composer require yapro/seo-bundle dev-main
```

## How to configure bundle

Add to config/services.yaml
```yaml
parameters:
  yapro.seo_bundle.redirect_page: '/redirect/page'
  yapro.seo_bundle.security_key: 'securityKey'
  yapro.seo_bundle.current_http_host: 'site.com'
  yapro.seo_bundle.protocols: ['https', 'http', 'ftp']
```

## How to use bundle

Replace external links with safe seo links:
```php
class MyController extends AbstractController
{
    /**
     * @Route("/article/{id}", methods={"GET"})
     */
    public function article(
        Article $article
        ContentManager $contentManager
    ): Response {
        $textWithSeoLinks = $contentManager->getSafeHtmlWithSeoLinks($article->getText());
        return new Response($textWithSeoLinks);
    }
}
```

Write a redirect for safe seo link to an external link:
```php
/**
 * @Route("/redirect/page", methods={"GET"})
 */
public function seoRedirect(Request $request, LinkManager $linkManager): Response
{
    $redirect = $linkManager->getRedirect($request->getRequestUri());
    if ($redirect->getHttpStatus() === 200) {
        return new Response('<META HTTP-EQUIV="Refresh" CONTENT="0; URL=' . $redirect->getUrl() . '">', 404);
    }
    throw $this->createNotFoundException('Redirect not found');
}
```

Add to robots.txt
```text
User-agent: *
Disallow: /redirect/page
```

## Development

Build:
```sh
docker build -t yapro/seo-bundle:latest -f ./Dockerfile ./
```

Tests:
```sh
wget https://phar.phpunit.de/phpunit-9.5.16.phar -O phpunit.phar && chmod +x ./phpunit.phar
docker run --user=1000:1000 --rm -v $(pwd):/app -w /app yapro/seo-bundle:latest bash -c "
  composer install --optimize-autoloader --no-scripts --no-interaction && 
  ./phpunit.phar tests"
```

Installation dev`s requirements:
```sh
docker run --user=1000:1000 --add-host=host.docker.internal:host-gateway -it --rm -v $(pwd):/app -w /app yapro/seo-bundle:latest bash
composer install -o
```

Debug PHP:
```sh
PHP_IDE_CONFIG="serverName=common" \
XDEBUG_SESSION=common \
XDEBUG_MODE=debug \
XDEBUG_CONFIG="max_nesting_level=200 client_port=9003 client_host=host.docker.internal" \
./phpunit.phar --cache-result-file=/tmp/phpunit.cache -v --stderr --stop-on-incomplete --stop-on-defect \
--stop-on-failure --stop-on-warning --fail-on-warning --stop-on-risky --fail-on-risky tests
```

Cs-Fixer:
```sh
wget https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v3.8.0/php-cs-fixer.phar && chmod +x ./php-cs-fixer.phar
docker run --user=1000:1000 --rm -v $(pwd):/app -w /app yapro/seo-bundle:latest ./php-cs-fixer.phar fix --config=.php-cs-fixer.dist.php -v --using-cache=no --allow-risky=yes
```

Update phpmd rules:
```shell
wget https://github.com/phpmd/phpmd/releases/download/2.12.0/phpmd.phar && chmod +x ./phpmd.phar
docker run --user=1000:1000 --rm -v $(pwd):/app -w /app yapro/seo-bundle:latest ./phpmd.phar . text phpmd.xml --exclude .github/workflows,vendor --strict --generate-baseline
```


