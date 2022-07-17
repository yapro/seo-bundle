<?php

declare(strict_types=1);

namespace YaPro\SeoBundle;

class ContentManager
{
    private LinkManager $linkManager;
    private int $partNumber = 0;
    private array $before = [];
    private array $after = [];
    private string $uniq;

    public function __construct(
        LinkManager $linkManager
    ) {
        $this->linkManager = $linkManager;
        $this->uniq = sha1(uniqid());
    }

    public function getSafeHtmlWithSeoLinks(string $html)
    {
        $html = $this->getSafeHtml($html);
        $html = $this->getHtmlWithSeoLinks($html);

        return str_replace($this->after, $this->before, $html); // возвращаем данные в которых нельзя производить замены
    }

    public function getHtmlWithSeoLinks(string $html): string
    {
        $host = preg_quote($this->linkManager->getCurrentHttpHost(), '/');

        return $this->replace($html, '/<a(.+)href=("|\'|)(' . implode('|', $this->linkManager->getProtocols()) . '):\/\/(?!' . $host . '|www\.' . $host . ')(.*)("|\'|\s|)>/sUi', function ($matches) {
            return '<a' . $matches['1'] . 'href=' . $matches['2'] . $this->linkManager->getSeoLink($matches['3'] . '://' . $matches['4']) . $matches['5'] . ' target=_blank rel=nofollow>';
        });
    }

    private function replace(string $html, string $search, callable $callback): string
    {
        $result = preg_replace_callback($search, $callback, $html);
        if (null === $result) {
            trigger_error('Error in preg_replace_callback');

            return $html;
        }

        return $result;
    }

    /**
     * сохраняем данные в которых нельзя производить замены
     */
    public function getSafeHtml(string $text): string
    {
        $body = preg_split('/<body/i', $text);
        $doc = $body['1'] ?? $body['0'];

        // метод замены строк содержащих символ " пока не разработан!

        $doc = $this->replace($doc, '/<script(.+)<\/script>/sUi', function ($matches) {
            return $this->putInMemory('<script' . $matches['1'] . '</script>');
        });
        $doc = $this->replace($doc, '/<style(.+)<\/style>/sUi', function ($matches) {
            return $this->putInMemory('<style' . $matches['1'] . '</style>');
        });
        $doc = $this->replace($doc, '/<textarea(.+)<\/textarea>/sUi', function ($matches) {
            return $this->putInMemory('<textarea' . $matches['1'] . '</textarea>');
        });
        $doc = $this->replace($doc, '/<input(.+)>/sUi', function ($matches) {
            return $this->putInMemory('<input' . $matches['1'] . '>');
        });
        $doc = $this->replace($doc, '/<img(.+)>/sUi', function ($matches) {
            return $this->putInMemory('<img' . $matches['1'] . '>');
        });
        $doc = $this->replace($doc, '/<!--NoReplace-->(.+)<!--\/NoReplace-->/sUi', function ($matches) {
            return $this->putInMemory($matches['1']);
        });
        if (isset($body['1'])) {
            return $body['0'] . '<body' . $doc;
        } else {
            return $doc;
        }
    }

    /**
     * сохраняет заданную строку в массив $this->before,
     * добавляет инкремент строки (для ее восстановления) в массив $this->after,
     * и возвращает инкремент для замены данной строки
     *
     * @param string $html
     *
     * @return string
     */
    public function putInMemory(string $html): string
    {
        // в связи с регуляркой данные поступают в эскепированном виде, поэтому мы их правильно расслэшиваем (оставляя слэши там где нужно)
        $htmlReadyForStripSlashes = str_replace("\'", 'Save' . $this->uniq . 'escapes', $html);
        $htmlWithoutSlashes = stripslashes($htmlReadyForStripSlashes);
        $htmlWithRealSlashes = str_replace('Save' . $this->uniq . 'escapes', "\'", $htmlWithoutSlashes);
        ++$this->partNumber;
        $this->before[$this->partNumber] = $htmlWithRealSlashes;

        return $this->after[$this->partNumber] = '[' . $this->uniq . $this->partNumber . ']';
    }
}
