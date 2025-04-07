<?php

use App\Models\Dictionary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

/** 🟢 Spell Checking */
test('correctly detects misspelled words', function () {
    Dictionary::insert([['word' => 'hello'], ['word' => 'world']]);

    $response = getJson('/spell-check?text=helo wrld');

    $response->assertStatus(200)
        ->assertJson(['misspelled' => ['helo', 'wrld']]);
});

test('handles correctly spelled words', function () {
    Dictionary::insert([['word' => 'hello'], ['word' => 'world']]);

    $response = getJson('/spell-check?text=hello world');

    $response->assertStatus(200)
        ->assertJson(['misspelled' => []]);
});

test('handles mixed-case words correctly', function () {
    Dictionary::insert([['word' => 'hello'], ['word' => 'world']]);

    $response = getJson('/spell-check?text=Hello WoRld');

    $response->assertStatus(200)
        ->assertJson(['misspelled' => []]);
});

/** 🟠 Dictionary Management */
test('stores words in the dictionary', function () {
    postJson('/store-dictionary', ['words' => ['apple', 'banana', 'grape']])
        ->assertStatus(200);

    expect(Dictionary::pluck('word')->toArray())->toBe(['apple', 'banana', 'grape']);
});



test('clears dictionary correctly', function () {
    Dictionary::insert([['word' => 'testword']]);

    deleteJson('/clear-dictionary')->assertStatus(200);

    expect(Dictionary::count())->toBe(0);
});

/** 🔵 Suggestions Feature */
test('returns suggested words for a misspelling', function () {
    Dictionary::insert([['word' => 'hello'], ['word' => 'world']]);

    $response = getJson('/suggest-words?word=helo');

    $response->assertStatus(200)
        ->assertJsonFragment(['suggestions' => ['hello']]);
});

test('returns multiple suggestions', function () {
    Dictionary::insert([['word' => 'world'], ['word' => 'wild']]);

    $response = getJson('/suggest-words?word=wrld');

    $response->assertStatus(200)
        ->assertJsonFragment(['suggestions' => ['world', 'wild']]);
});

test('handles words with no close matches', function () {
    Dictionary::insert([['word' => 'apple'], ['word' => 'banana']]);

    $response = getJson('/suggest-words?word=xyzabc');

    $response->assertStatus(200)
        ->assertJson(['suggestions' => []]);
});

test('handles empty input', function () {
    Dictionary::insert([['word' => 'hello'], ['word' => 'world']]);

    $response = getJson('/spell-check?text=');

    $response->assertStatus(200)
        ->assertJson(['misspelled' => []]);
});


it('handles punctuation and numbers correctly', function () {
    Dictionary::insert([['word' => 'hello'], ['word' => 'world']]);

    $response = getJson('/spell-check-text?text="hello, 123 world!"');

    $response->assertOk()
        ->assertJson([
            'misspelled_words' => []
        ]);
});

it('handles words with extra spaces correctly', function () {
    Dictionary::insert([['word' => 'hello'], ['word' => 'world']]);

    $response = getJson('/spell-check-text?text="    hello world  "');

    $response->assertOk()
        ->assertJson([
            'misspelled_words' => []
        ]);
});

it('handles uppercase vs lowercase words correctly (should be case insensitive)', function () {
    Dictionary::insert([['word' => 'hello'], ['word' => 'world']]);

    $response = getJson('/spell-check-text?text=HELLO%20world');

    $response->assertOk()
        ->assertJson([
            'misspelled_words' => []
        ]);
});

it('handles mixed input of words and numbers correctly', function () {
    Dictionary::insert([['word' => 'hello'], ['word' => 'world']]);
    $response = getJson('/spell-check?text=Hello is 4 world');

    $response->assertOk()
        ->assertJson([
            'misspelled' => ['is', '4']
        ]);
});



it('prevents SQL injection in dictionary storage', function () {
    $maliciousInput = "'DROP TABLE dictionary;--";

    $response = postJson('/store-dictionary', ['words' => [$maliciousInput]]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Dictionary updated successfully']);

    $this->assertDatabaseHas('dictionary', ['word' => strtolower($maliciousInput)]);
});



it('prevents XSS in word suggestions', function () {
    Dictionary::create(['word' => 'hello']);

    $maliciousInput = "<script>alert('hacked')</script>";

    $response = getJson('/suggest-words?word=' . urlencode($maliciousInput));

    $response->assertStatus(200);

    $data = $response->json();

    expect($data['suggestions'])->toBe([]);
});
