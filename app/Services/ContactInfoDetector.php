<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;

class ContactInfoDetector
{
    /**
     * Patterns that suggest contact information in text fields.
     */
    private const PATTERNS = [
        // Email — cualquier @ es suficiente
        '/@/',
        // URL / web
        '/https?:\/\//i',
        '/\bwww[\.\-]\S+/i',
        // WhatsApp
        '/\bwa[\.\-]me\b/i',
        '/\bwhatsapp\b/i',
        // Cualquier número de 7+ dígitos consecutivos
        '/\d{7,}/',
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
     * Runs OCR on the image. Returns true if ANY text is detected
     * (images with text are always flagged for manual review).
     * Returns false only if OCR fails or image has no readable text.
     */
    public function foundInImage(string $absolutePath): bool
    {
        try {
            // Normalize slashes — mixed paths break Tesseract on Windows
            $absolutePath = str_replace('\\', '/', $absolutePath);

            $ocr = new TesseractOCR($absolutePath);

            $tesseractPath = env('TESSERACT_PATH');
            if ($tesseractPath) {
                $ocr->executable($tesseractPath);
            }

            $ocr->lang('eng');
            // PSM 6: treat image as a single uniform block of text (better detection)
            $ocr->psm(6);

            $text = trim($ocr->run());

            return $this->foundInText($text);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
