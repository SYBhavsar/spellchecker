<?php

use App\Models\Dictionary;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Dictionary::truncate();
});

describe('Dictionary Model', function () {
    it('stores words in lowercase', function () {
        Dictionary::create(['word' => 'Hello']);
        Dictionary::create(['word' => 'WORLD']);

        expect(Dictionary::pluck('word')->toArray())->toBe(['hello', 'world']);
    });

    it('fetches all words from the dictionary', function () {
        Dictionary::insert([
            ['word' => 'hello'],
            ['word' => 'world'],
        ]);

        expect(Dictionary::pluck('word')->toArray())->toBe(['hello', 'world']);
    });

    it('returns an empty array when the dictionary is empty', function () {
        expect(Dictionary::pluck('word')->toArray())->toBe([]);
    });

    it('ensures words are unique', function () {
        Dictionary::create(['word' => 'Laravel']);
        Dictionary::firstOrCreate(['word' => 'Laravel']);

        expect(Dictionary::where('word', 'Laravel')->count())->toBe(1);
    });

    it('allows timestamps by default', function () {
        $word = Dictionary::create(['word' => 'testing']);

        expect($word->created_at)->not->toBeNull()
            ->and($word->updated_at)->not->toBeNull();
    });


    it('stores words in the dictionary', function () {
        $response = $this->postJson('/store-dictionary', [
            'words' => ['hello', 'world']
        ]);
        $response->assertStatus(200)
        ->assertJson(['message' => 'Dictionary updated successfully']);
    });


    it('clears the dictionary', function () {
        // Insert some words into the dictionary first
        Dictionary::updateOrCreate(['word' => 'test']);
        Dictionary::updateOrCreate(['word' => 'example']);

        // Ensure words exist before clearing
        expect(Dictionary::count())->toBeGreaterThanOrEqual(2);

        // Call the DELETE endpoint
        $response = $this->deleteJson('/clear-dictionary');

        // Assertions
        $response->assertStatus(200)
            ->assertJson(['message' => 'Dictionary cleared successfully.']);

        // Ensure the dictionary is empty
        expect(Dictionary::count())->toBe(0);
    });

});
