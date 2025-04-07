<?php

namespace App\Services;

class SpellChecker
{
    protected array $dictionary;

    public function __construct(array $dictionary)
    {
        $this->dictionary = array_map('strtolower', $dictionary);
    }

    public function check(string $text): array
    {
        $words = preg_split('/\s+/', strtolower(trim($text)));
        $misspelled = [];

        foreach ($words as $word) {
            $cleanWord = preg_replace('/[^a-zA-Z]/', '', $word);
            if (!in_array($cleanWord, $this->dictionary) && $cleanWord !== '') {
                $misspelled[] = $cleanWord;
            }
        }

        return $misspelled;
    }


}
