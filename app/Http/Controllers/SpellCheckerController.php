<?php

namespace App\Http\Controllers;

use App\Models\Dictionary;
use Illuminate\Http\Request;

class SpellCheckerController extends Controller
{
    // Store dictionary words
    public function storeDictionary(Request $request)
    {
        $request->validate([
            'words' => 'required|array',
            'words.*' => 'string'
        ]);

        foreach ($request->words as $word) {
            Dictionary::updateOrCreate(['word' => strtolower($word)]);
        }

        return response()->json(['message' => 'Dictionary updated successfully']);
    }

    // Check for misspelled words
    public function check(Request $request)
    {
        $request->validate([
            'text' => 'required|string'
        ]);

        $dictionaryWords = Dictionary::pluck('word')->toArray();
        $textWords = explode(' ', strtolower($request->text));
        $misspelled = array_diff($textWords, $dictionaryWords);

        return response()->json(['misspelled' => array_values($misspelled)]);
    }

    // Suggest words based on similarity
    public function suggestWords(Request $request)
    {
        $word = $request->query('word');

        if (!$word) {
            return response()->json(['error' => 'No word provided'], 400);
        }

        $dictionary = Dictionary::pluck('word')->toArray();
        $suggestions = $this->suggestWordsFor($word, $dictionary);

        return response()->json([
            'word' => $word,
            'suggestions' => $suggestions,
        ]);
    }

    // Check an entire text for misspelled words and suggest corrections
    public function checkText(Request $request)
    {
        $text = $request->input('text');

        if (!$text) {
            return response()->json(['error' => 'No text provided'], 400);
        }

        // Tokenize the text (remove punctuation and split into words)
        $words = preg_split('/\s+|[^a-zA-Z]/', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        // Get dictionary words
        $dictionary = Dictionary::pluck('word')->toArray();
        $misspelledWords = [];

        foreach ($words as $word) {
            if (!in_array($word, $dictionary)) {
                $misspelledWords[$word] = $this->suggestWordsFor($word, $dictionary);
            }
        }

        return response()->json(['misspelled' => $misspelledWords]);
    }

    // Private function to suggest similar words
    private function suggestWordsFor($word, $dictionary)
    {
        $suggestions = [];

        foreach ($dictionary as $dictWord) {
            $distance = levenshtein($word, $dictWord);
            if ($distance <= 2) {
                $suggestions[$dictWord] = $distance;
            }
        }

        asort($suggestions);
        return array_keys($suggestions);
    }

    // Clear all dictionary words
    public function clearDictionary()
    {
        Dictionary::truncate(); // Deletes all entries from the dictionary table

        return response()->json(['message' => 'Dictionary cleared successfully.']);
    }
}
