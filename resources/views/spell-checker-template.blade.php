<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spell Checker</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.3/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-200 flex items-center justify-center min-h-screen p-4">

<div x-data="spellChecker()" class="max-w-lg w-full bg-gray-800 shadow-lg p-6 rounded-lg">
    <!-- Title -->
    <h1 class="text-2xl font-semibold mb-4 text-center"> Spell Checker</h1>

    <!-- Text Input -->
    <div class="relative">
        <textarea x-model="text" @input.debounce.500="checkRealTimeSpelling"
                  placeholder="Enter text here..."
                  class="w-full h-28 p-3 border border-gray-600 bg-gray-700 text-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
    </div>

    <!-- Real-Time Suggestions (Always Visible) -->
    <div class="w-full bg-gray-700 border border-gray-600 mt-2 rounded-md shadow-lg p-3 min-h-[50px]">
        <h3 class="text-sm font-semibold text-yellow-400"> Real-Time Suggestions:</h3>
        <ul>
            <template x-for="(suggestions, word) in realTimeSuggestions" :key="word">
                <li class="text-sm mt-1">
                    <span class="font-medium text-yellow-400" x-text="word"></span> →
                    <span x-text="suggestions.length ? suggestions.join(', ') : 'No suggestions'"></span>
                </li>
            </template>
        </ul>
        <p x-show="Object.keys(realTimeSuggestions).length === 0" class="text-gray-400 text-sm">
            No suggestions yet.
        </p>
    </div>

    <!-- Action Buttons (Now below real-time suggestions) -->
    <div class="grid grid-cols-2  gap-2 mt-4 flex">

        <button @click="checkSpellingWithSuggestions"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Check with Suggestions</button>
        <button @click="clearDictionary"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">Clear Dictionary</button>
    </div>

    <!-- Misspelled Words -->
    <div class="mt-6">
        <h2 class="text-lg font-semibold mb-2">Misspelled Words:</h2>
        <div class="bg-gray-700 p-3 rounded-md border border-gray-600 min-h-[50px]">
            <ul>
                <template x-for="(suggestions, word) in misspelled" :key="word">
                    <li class="mb-2">
                        <span class="font-medium text-yellow-400" x-text="word"></span> →
                        <span x-text="suggestions.length ? suggestions.join(', ') : 'No suggestions'"></span>
                    </li>
                </template>
            </ul>
            <p x-show="Object.keys(misspelled).length === 0" class="text-gray-400 text-sm">
                No misspelled words.
            </p>
        </div>
    </div>

    <!-- Add Words to Dictionary -->
    <div class="mt-6">
        <h2 class="text-lg font-semibold mb-2"> Add Words to Dictionary:</h2>
        <div class="flex gap-2">
            <input type="text" x-model="newWord"
                   class="w-full border border-gray-600 bg-gray-700 text-gray-300 p-2 rounded-md focus:ring-2 focus:ring-green-500"
                   placeholder="Enter words (comma or space-separated)">
            <button @click="addWord"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">Add</button>
        </div>
    </div>
</div>

<script>
    function spellChecker() {
        return {
            text: '',
            misspelled: {},
            realTimeSuggestions: {},
            newWord: '',


            checkSpellingWithSuggestions() {
                fetch(`/spell-check-text?text=${encodeURIComponent(this.text)}`)
                    .then(res => res.json())
                    .then(data => {
                        // Append new results without removing old ones
                        for (const word in data.misspelled) {
                                this.misspelled[word] = data.misspelled[word]; // Add new word suggestions
                        }
                    })
                    .catch(error => console.error('Error:', error));
            },

            checkRealTimeSpelling() {
                if (!this.text.trim()) {
                    this.realTimeSuggestions = {};
                    return;
                }

                fetch(`/spell-check-text?text=${encodeURIComponent(this.text)}`)
                    .then(res => res.json())
                    .then(data => {
                        this.realTimeSuggestions = data.misspelled;
                    })
                    .catch(error => console.error('Error:', error));
            },

            addWord() {
                if (!this.newWord.trim()) return;

                let words = this.newWord.includes(',')
                    ? this.newWord.split(',').map(word => word.trim())
                    : this.newWord.split(/\s+/).filter(word => word.trim() !== '');

                fetch('/store-dictionary', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ words })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.message === "Dictionary updated successfully") {
                            alert(data.message);
                            this.newWord = '';
                        }
                    })
                    .catch(error => console.error('Error:', error));
            },

            clearDictionary() {
                fetch('/clear-dictionary', { method: 'DELETE' })
                    .then(res => res.json())
                    .then(data => {
                        if (data.message === "Dictionary cleared successfully.") {
                            alert(data.message);
                            this.misspelled = {};
                            this.realTimeSuggestions = {};
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    }
</script>

</body>
</html>
