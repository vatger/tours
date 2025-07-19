<?php

namespace App\Console\Commands\GrgsDev;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslatedAttributesToJson extends Command
{
    protected $signature = 'grgsdev:translated-to-json';
    protected $description = 'Convert translated attributes from PHP array to JSON';

    public function handle()
    {
        // Caminho para o arquivo appTranslatedAttributes.php
        $translatedFilePath = base_path('documentation/translations/appTranslatedAttributes.php');

        // Verificar se o arquivo de traduções existe
        if (!File::exists($translatedFilePath)) {
            $this->error("Arquivo appTranslatedAttributes.php não encontrado!");
            return Command::FAILURE;
        }

        // Carregar o array de traduções
        $translatedAttributes = include $translatedFilePath;
        // Processar o array para ajustar as chaves
        $formattedAttributes = $this->formatKeys($translatedAttributes);
        // Converter o array para JSON com opções para não escapar caracteres Unicode
        $jsonOutput = json_encode($formattedAttributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Caminho para o arquivo de saída
        $outputFilePath = base_path('documentation/translations/translatedAttributes.json');

        // Escrever o conteúdo JSON no arquivo
        File::put($outputFilePath, $jsonOutput);

        $this->info("Arquivo translatedAttributes.json criado com sucesso em " . base_path('documentation/translations/translatedAttributes.json'));

        return Command::SUCCESS;
    }

    /**
     * Formata as chaves do array removendo sublinhados e capitalizando as palavras com espaços entre elas.
     *
     * @param array $attributes
     * @return array
     */
    private function formatKeys(array $attributes): array
    {
        $formatted = [];

        foreach ($attributes as $lang => $translations) {
            $formatted[$lang] = [];

            foreach ($translations as $key => $value) {
                // Substitui sublinhados por espaços e capitaliza palavras
                $formattedKey = ucwords(str_replace('_', ' ', $key));
                $formattedValue = ucwords(str_replace('_', ' ', $value));

                $formatted[$lang][$formattedKey] = $formattedValue;
            }
        }

        return $formatted;
    }
}
