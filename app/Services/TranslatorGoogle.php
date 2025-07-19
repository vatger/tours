<?php

namespace App\Services;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Sleep;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslatorGoogle
{
    /**
     * Traduzir a string para múltiplos idiomas e, opcionalmente, detectar a língua de origem.
     *
     * @param string $text
     * @param bool $detectSource
     * @return array
     */
    public static function translatexx($text, $detectSource = true)
    {
        $tr = new GoogleTranslate();
        $sourceLanguage = null;
        $locales = config('app.supported_locales', ['pt_BR']);
        $translations = [];
        foreach ($locales as $language) {
            $tr->setTarget($language);
            // $translations[$language] = $tr->preserveParameters('/<[^>]+>/')
            //     ->translate($text);
            $translations[$language] = $tr->translate($text);
            usleep(500000);
        }
        if ($detectSource) {
            $sourceLanguage = $tr->getLastDetectedSource();
        } else {
            $sourceLanguage = null;
        }
        if ($sourceLanguage == 'pt') {
            $sourceLanguage = 'pt_BR';
        }
        $result = [
            'original_text' => $text,
            'translations' => $translations,
            'source_language' => $sourceLanguage,
        ];

        return $result;
    }


    public static function translate($text, $detectSource = true)
    {
        $tr = new GoogleTranslate();
        $locales = config('app.supported_locales', ['pt_BR']);
        $translations = [];
        $placeholders = [];

        // Passo 1: Substituir tags HTML por placeholders codificados em Base64
        $parts = preg_split('/(<[^>]+>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        try {
            foreach ($locales as $language) {
                $tr->setTarget($language);
                $translatedHtml = '';
                foreach ($parts as $part) {
                    if (preg_match('/^<[^>]+>$/', $part)) {
                        // Preservar tags HTML intactas
                        $translatedHtml .= $part;
                    } else {
                        // Passo 2: Capturar espaços antes/depois do texto
                        preg_match('/^(\s*)(.*?)(\s*)$/u', $part, $matches);
                        $leadingSpace = $matches[1];
                        $content = $matches[2];
                        $trailingSpace = $matches[3];

                        // Traduzir apenas o conteúdo (sem espaços)
                        $translatedContent = $tr->translate($content);

                        // Reconstruir com espaços originais
                        $translatedHtml .= $leadingSpace . $translatedContent . $trailingSpace;
                    }
                }

                $translations[$language] = $translatedHtml;
                usleep(500000); // Pausa de 1 segundo

            }
        } catch (\Exception $e) {
            Log::error("Erro ao traduzir: " . $e->getMessage());
            return [
                'error' => true,
                'message' => "Erro ao traduzir: " . $e->getMessage(),
            ];
        }

        if ($detectSource) {
            $sourceLanguage = $tr->getLastDetectedSource();
        } else {
            $sourceLanguage = null;
        }
        if ($sourceLanguage == 'pt') {
            $sourceLanguage = 'pt_BR';
        }
        $result = [
            'original_text' => $text,
            'translations' => $translations,
            'source_language' => $sourceLanguage,
        ];

        return $result;
        if ($sourceLanguage === 'pt') {
            $sourceLanguage = 'pt_BR';
        }

        return [
            'original_text' => str_replace(array_keys($placeholders), array_values($placeholders), $text),
            'translations' => $translations,
            'source_language' => $sourceLanguage ?? null,
        ];
    }

    public static function translateWithHtml($text, $detectSource = true)
    {
        $tr = new GoogleTranslate();
        $locales = config('app.supported_locales', ['pt_BR']);
        $translations = [];
        $placeholders = [];

        // Passo 1: Substituir tags HTML por placeholders codificados em Base64
        $parts = preg_split('/(<[^>]+>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        try {
            foreach ($locales as $language) {
                $tr->setTarget($language);
                $translatedHtml = '';
                foreach ($parts as $part) {
                    if (preg_match('/^<[^>]+>$/', $part)) {
                        // Preservar tags HTML intactas
                        $translatedHtml .= $part;
                    } else {
                        // Passo 2: Capturar espaços antes/depois do texto
                        preg_match('/^(\s*)(.*?)(\s*)$/u', $part, $matches);
                        $leadingSpace = $matches[1];
                        $content = $matches[2];
                        $trailingSpace = $matches[3];

                        // Traduzir apenas o conteúdo (sem espaços)
                        $translatedContent = $tr->translate($content);

                        // Reconstruir com espaços originais
                        $translatedHtml .= $leadingSpace . $translatedContent . $trailingSpace;
                    }
                }

                $translations[$language] = $translatedHtml;
                usleep(500000); // Pausa de 1 segundo
            }
        } catch (\Exception $e) {
            Log::error("Erro ao traduzir: " . $e->getMessage());
            return [
                'error' => true,
                'message' => "Erro ao traduzir: " . $e->getMessage(),
            ];
        }

        if ($detectSource) {
            $sourceLanguage = $tr->getLastDetectedSource();
        } else {
            $sourceLanguage = null;
        }
        if ($sourceLanguage == 'pt') {
            $sourceLanguage = 'pt_BR';
        }
        $result = [
            'original_text' => $text,
            'translations' => $translations,
            'source_language' => $sourceLanguage,
        ];

        return $result;
        if ($sourceLanguage === 'pt') {
            $sourceLanguage = 'pt_BR';
        }

        return [
            'original_text' => str_replace(array_keys($placeholders), array_values($placeholders), $text),
            'translations' => $translations,
            'source_language' => $sourceLanguage ?? null,
        ];
    }

    /**
     * Traduzir a string para o idioma escolhido, opcionalmente, detectar a língua de origem.
     *
     * @param string $text
     * @param string $target [pt_BR]
     * @param bool $detectSource
     * @return array
     */
    public static function translateToTarget($text, $target = null, $detectSource = true)
    {
        if (!$target) {
            $target = 'pt_BR';
        }
        $tr = new GoogleTranslate();
        $sourceLanguage = null;
        $translation = '';
        $tr->setTarget($target);
        $translation = $tr->translate($text);
        if ($detectSource) {
            $sourceLanguage = $tr->getLastDetectedSource();
        } else {
            $sourceLanguage = null;
        }
        if ($sourceLanguage == 'pt') {
            $sourceLanguage = 'pt_BR';
        }
        $result = [
            'original_text' => $text,
            'translation' => $translation,
            'source_language' => $sourceLanguage,
            'target_language' => $target,
        ];

        return $result;
    }
}
