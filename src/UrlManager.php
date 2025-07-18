<?php

declare(strict_types=1);

namespace YaPro\SeoBundle;

class UrlManager
{
    private $translit = [
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'yo',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'c',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'sh',
        'ъ' => 'i',
        'ы' => 'i',
        'ь' => 'i',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',
        'є' => 'e',
        'і' => 'i',
        'ї' => 'yi',
    ];

    // Возвращает текст в транслитерированном виде, с заменой на символ "-" всех символов кроме букв, цифр и тире
    // Функция создана потому, что Transliterator::create('Any-Latin; Latin-ASCII')->transliterate($string); работает не так как хотелось.
    // Символ "-" выбран согласно рекомендации https://developers.google.com/search/docs/crawling-indexing/url-structure?hl=ru
    public function transliterate(string $text, string $defaultSign = '-'): string
    {
        // todo знаки нужно заменять на склонения - подбирать в зависиости от числа до знака %/$/etc
        $text = str_replace('%', ' процентов', $text);
        $text = str_replace('$', ' долларов', $text);
        $text = str_replace('₽', ' рублей', $text);
        $text = str_replace('¥', ' йен', $text);
        $text = mb_strtolower($text);
        $text = strtr($text, $this->translit);
        $text = preg_replace('/[^-a-z0-9]/sUi', $defaultSign, $text);
        $text = preg_replace('/[\\' . $defaultSign . ']{2,}/', $defaultSign, $text);

        return trim($text, $defaultSign);
    }

    public function transliterateEnglishSlug(string $text, string $defaultSign = '-'): string
    {
        $text = str_replace('%', ' percent', $text);
        $text = str_replace('$', ' dollars', $text);
        $text = str_replace('₽', ' rubles', $text);
        $text = str_replace('¥', ' yen', $text);
        $text = str_replace('+', ' plus', $text);
        $text = str_replace('—', '-', $text);
        $text = str_replace('–', '-', $text);
        $text = str_replace(' - ', '-', $text);
        $text = str_replace('- ', '-', $text);
        $text = str_replace(' -', '-', $text);
        $text = str_replace('\'s ', ' ', $text); // children's cards -> children cards
        $text = str_replace('`s ', ' ', $text); // children`s cards -> children cards
        $text = mb_strtolower($text);
        $text = preg_replace('/[^-a-z0-9]/sUi', $defaultSign, $text);
        $text = preg_replace('/[\\' . $defaultSign . ']{2,}/', $defaultSign, $text);

        return trim($text, $defaultSign);
    }

    public function transliterateEnglishPath(string $path): string
    {
        $result = [];
        foreach (explode('/', $path) as $slug) {
            $slug = $this->transliterateEnglishSlug($slug);
            if (empty($slug)) {
                continue;
            }
            $result[] = $slug;
        }

        return '/' . implode('/', $result);
    }
}
