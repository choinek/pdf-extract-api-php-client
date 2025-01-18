<?php

namespace Choinek\PdfExtractApiClient\Tests\Utility;

use FuzzyWuzzy\Fuzz;

class SimilarityValidator
{
    public function __construct(
        private readonly Fuzz $fuzz,
        private readonly int $minScore = 70,
    ) {
    }

    /**
     * @param string[] $phrases the list of phrases to search within
     * @param string[] $terms   the list of terms to search for
     *
     * @return array<string, array{match: ?string, similarity: int}> $results
     */
    public function findBestMatches(array $phrases, array $terms): array
    {
        $results = [];

        foreach ($terms as $needle) {
            $needle = $this->cleanString(strtolower($needle));
            $bestMatch = null;
            $highestScore = 0;

            foreach ($phrases as $sentence) {
                $sentence = strtolower($sentence);
                $similarity = $this->fuzz->ratio($needle, $sentence);

                if ($similarity > $highestScore) {
                    $highestScore = $similarity;
                    $bestMatch = $sentence;
                }
            }

            $results[$needle] = [
                'match' => $bestMatch,
                'similarity' => $highestScore,
            ];
        }

        return $results;
    }

    /**
     * Split a long string into individual phrases, words, and combinations (two and three words).
     *
     * @return string[]
     */
    public function splitString(string $input): array
    {
        $lines = explode("\n", trim($input));
        $words = [];
        $twoWords = [];
        $threeWords = [];

        foreach ($lines as $line) {
            $lineWords = preg_split('/\s+/', trim($line)) ?: [];
            $words = array_merge($words, $lineWords);

            if (count($lineWords)) {
                for ($i = 0; $i < count($lineWords) - 1; ++$i) {
                    $twoWords[] = $lineWords[$i].' '.$lineWords[$i + 1];
                    if ($i < count($lineWords) - 2) {
                        $threeWords[] = $lineWords[$i].' '.$lineWords[$i + 1].' '.$lineWords[$i + 2];
                    }
                }
            }
        }

        return array_merge($lines, $words, $twoWords, $threeWords);
    }

    /**
     * Clean and filter a given string: Remove unwanted characters.
     *
     * @param string $input the raw input string
     *
     * @return string filtered and cleaned string
     */
    public function cleanString(string $input): string
    {
        return preg_replace('/[^a-zA-Z0-9 \n]/', '', $input) ?: '';
    }

    /**
     * Find best matches for a search phrase within a long string.
     *
     * @param string   $haystack the long input string to search within
     * @param string[] $terms    search terms to find within the haystack
     *
     * @return array<string, array{match: string|null, similarity: int}>
     */
    public function findMatchesInString(string $haystack, array $terms): array
    {
        $clearedHaystack = $this->cleanString($haystack);
        $allPhrases = $this->splitString($clearedHaystack);
        $allPhrases = array_filter(array_unique($allPhrases));

        return $this->findBestMatches($allPhrases, $terms);
    }

    /**
     * @param string[] $terms
     */
    public function validateMultipleTerms(string $haystack, array $terms): bool
    {
        $results = $this->findMatchesInString($haystack, $terms);
        foreach ($results as $result) {
            if ($result['similarity'] < $this->minScore) {
                throw new \InvalidArgumentException(sprintf('The term "%s" has earned %d score and did not meet the required minimum similarity score of %d.', $result['match'], $result['similarity'], $this->minScore));
            }
        }

        return true;
    }
}
