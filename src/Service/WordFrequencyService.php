<?php

namespace App\Service;

class WordFrequencyService
{
    public function getMostFrequentWords(string $text, array $banned, int $limit = 3): array
    {
        // Convert text to lowercase and split into words
        $words = str_word_count(strtolower($text), 1);
        
        // Filter out banned words and count occurrences
        $wordCounts = array_count_values(array_diff($words, array_map('strtolower', $banned)));
        
        // Sort by frequency (descending) and then alphabetically
        arsort($wordCounts);
        
        // Return the top $limit words
        return array_slice(array_keys($wordCounts), 0, $limit);
    }
}