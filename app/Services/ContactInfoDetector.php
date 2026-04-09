<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;

class ContactInfoDetector
{
    /**
     * Patterns that suggest contact information.
     */
    private const PATTERNS = [
        // Email
        '/\b[\w.+\-]+@[\w\-]+\.[a-z]{2,}\b/i',
        // URL / web
        '/https?:\/\//i',
        '/\bwww\.\S+/i',
        // WhatsApp
        '/\bwa\.me\b/i',
        '/\bwhatsapp\b/i',
        // Argentine phone numbers: sequences of 7+ digits with common separators
        // Covers: 011-4567-8901 | 15 4567 8901 | +54 9 11 1234 5678 | (011) 4567-8901
        '/\(?\+?(?:54\s*)?\)?(?:\d[\s\-\.]?){7,}\d/i',
    ];

    /**
     * Returns true if any contact pattern is found in the given text.
     */
    public function foundInText(string $text): bool
    {
        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Runs OCR on the image file and checks for contact patterns.
     * Returns false if OCR fails (don't block on error).
     */
    public function foundInImage(string $absolutePath): bool
    {
        try {
            $ocr = new TesseractOCR($absolutePath);

            $tesseractPath = env('TESSERACT_PATH');
            if ($tesseractPath) {
                $ocr->executable($tesseractPath);
            }

            // English is sufficient — emails, phones and URLs are language-agnostic
            $ocr->lang('eng');

            $text = $ocr->run();

            return $this->foundInText($text);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
