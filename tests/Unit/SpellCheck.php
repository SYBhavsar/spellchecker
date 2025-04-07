<?php
use App\Services\SpellChecker;

it('identifies misspelled words', function () {
    $dictionary = ['hello', 'world', 'php', 'laravel'];
    $spellChecker = new SpellChecker($dictionary);

    // Given a text with some misspelled words
    $text = "Helo wurld! Welcome to PHP.";

    // Expected misspelled words: "Helo" and "wurld"
    $misspelled = $spellChecker->check($text);

    expect($misspelled)->toBe(['helo', 'wurld']);
});

it('returns an empty array when all words are correct', function () {
    $dictionary = ['hello', 'world', 'php', 'laravel'];
    $spellChecker = new SpellChecker($dictionary);

    // Given a text where all words exist in the dictionary
    $text = "Hello world! PHP Laravel.";

    // Expect no misspelled words
    $misspelled = $spellChecker->check($text);

    expect($misspelled)->toBe([]);
});

it('ignores punctuation and only checks words', function () {
    $dictionary = ['test', 'case', 'unit'];
    $spellChecker = new SpellChecker($dictionary);

    $text = "Test, cases! are important... for unit-testing.";

    $misspelled = $spellChecker->check($text);

    expect($misspelled)->toBe(['are', 'for']);
});
