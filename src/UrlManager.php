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
        'ж' => 'j',
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

    // Возвращает текст в транслитерированном виде, с заменой на символ _ всех символов кроме букв, цифр и тире
    // Функция создана потому, что Transliterator::create('Any-Latin; Latin-ASCII')->transliterate($string); работает не так как хотелось.
    public function transliterate(string $text): string
    {
        // todo знаки нужно заменять на склонения - подбирать в зависиости от числа до знака %/$/etc
        $text = str_replace('%', ' процентов', $text);
        $text = str_replace('$', ' долларов', $text);
        $text = str_replace('₽', ' рублей', $text);
        $text = str_replace('¥', ' йен', $text);
        $text = mb_strtolower($text);
        $text = strtr($text, $this->translit);
        $text = preg_replace('/[^-a-z0-9]/sUi', '_', $text);
        $text = preg_replace('/[\_]{2,}/', '_', $text);

        return trim($text, '_');
    }
}
