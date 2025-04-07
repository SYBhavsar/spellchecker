<?php
use App\Models\Dictionary;

it('suggests similar words based on Levenshtein distance', function () {
    // Seed the dictionary
    Dictionary::updateOrCreate(['word' => 'hello']);
    Dictionary::updateOrCreate(['word' => 'help']);

    // Send GET request to suggest words
    $response = $this->getJson('/suggest-words?word=helo');

    // Assertions
    $response->assertStatus(200)
        ->assertJson([
            'suggestions' => ['hello', 'help'] // Words within Levenshtein distance ≤ 2
        ]);
});

it('identifies misspelled words and suggests corrections', function () {
    // Seed dictionary
    Dictionary::truncate();
    Dictionary::create(['word' => 'hello']);
    Dictionary::create(['word' => 'world']);
    Dictionary::create(['word' => 'help']);
    Dictionary::create(['word' => 'hell']);

    $response = $this->getJson('/spell-check-text?text=helo wurld!');

    $response->assertStatus(200)
        ->assertJsonFragment([
            'helo' => ['hello', 'hell', 'help'],
        ])
        ->assertJsonFragment([
            'wurld' => ['world'],
        ]);
});


