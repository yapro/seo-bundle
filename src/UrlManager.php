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
        'ё' => 'e', // Чтобы избежать путаницы и удлинения слов (можно заменить на yo, если точность важнее краткости)
        'ж' => 'zh', // стандарт ГОСТ, j — короче, но может путать с «дж»
        'з' => 'z',
        'и' => 'i',
        'й' => 'y', // Это официальная транслитерация в загранпаспортах РФ и ГОСТах: may VS maj VS tajm
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
        'ц' => 'ts', // "с" нельзя, ведь cvet (цвет) может быть прочитано как «свет».
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'sch', // Это «исторически сложившаяся» транслитерация (можно 'щ' => 'sh' но теряется отличие от ш)
        'ъ' => 'i',
        'ы' => 'y', // y - более точное фонетическое соответствие, чем i (еще i может путать с и), однако: silniy VS silnyy
        'ь' => '', // лучше удалять, чем использовать i (медь → med, дочь → doch)
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
        $text = str_replace('+', ' плюс ', $text);
        $text = str_replace('%', ' процентов', $text);
        $text = str_replace('$', ' долларов', $text);
        $text = str_replace('₽', ' рублей', $text);
        $text = str_replace('¥', ' йен', $text);
        $text = mb_strtolower(trim($text));
        $text = strtr($text, $this->translit);
        $text = $this->replaceDashes($text);
        $text = preg_replace('/[^-a-z0-9]/sUi', $defaultSign, $text);
        $text = preg_replace('/[\\' . $defaultSign . ']{2,}/', $defaultSign, $text);

        return trim($text, $defaultSign);
    }

    private function replaceDashes(string $text): string
    {
        $text = str_replace('—', '-', $text);
        $text = str_replace('–', '-', $text);
        $text = str_replace(' - ', '-', $text);
        $text = str_replace('- ', '-', $text);
        $text = str_replace(' -', '-', $text);

        return str_replace('.', '-', $text); // чтобы site.ru превращался в site-ru
    }

    public function transliterateEnglishSlug(string $text, string $defaultSign = '-'): string
    {
        $text = str_replace('%', ' percent', $text);
        $text = str_replace('$', ' dollars', $text);
        $text = str_replace('₽', ' rubles', $text);
        $text = str_replace('¥', ' yen', $text);
        $text = str_replace('+', ' plus', $text);
        $text = str_replace('\'s ', ' ', $text); // children's cards -> children cards
        $text = str_replace('`s ', ' ', $text); // children`s cards -> children cards
        $text = $this->replaceDashes($text);
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
