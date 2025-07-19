<?php

namespace App\Console\Commands\GrgsDev;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateAttributes extends Command
{
    protected $signature = 'grgsdev:translate-attributes';
    protected $description = 'Translate attributes from appAttributes.php into multiple languages and generate appTranslatedAttributes.php';

    public function handle()
    {
        // Caminho para o arquivo de atributos de entrada
        $inputFilePath = base_path('documentation/translations/appAttributes.php');

        // Verificar se o arquivo existe
        if (!File::exists($inputFilePath)) {
            $this->error("Arquivo appAttributes.php não encontrado!");
            return Command::FAILURE;
        }

        // Carregar o array de atributos
        $attributes = include $inputFilePath;

        // Linguagens para tradução
        $locales = ['en', 'it', 'es', 'fr', 'de', 'pt_BR'];

        // Inicializar tradutor (Stichoza/GoogleTranslate)
        $translator = new GoogleTranslate();

        // Caminho para o arquivo de saída
        $outputFilePath = base_path('documentation/translations/appTranslatedAttributes.php');

        // Verificar se o arquivo de saída já existe e carregar as traduções existentes
        if (File::exists($outputFilePath)) {
            $translatedAttributes = include $outputFilePath;
        } else {
            $translatedAttributes = [];
        }
        // Conjuntos de chaves de atributos para verificar se uma tradução é obsoleta
        $attributeKeys = array_flip($attributes);
        // Loop por cada idioma e traduzir novos atributos

        foreach ($locales as $locale) {
            if (!isset($translatedAttributes[$locale])) {
                $translatedAttributes[$locale] = [];
            }

            // Ajustar o tradutor para o idioma alvo
            $translator->setTarget($locale);

            foreach ($attributes as $attribute) {
                // Manter a chave original se já estiver traduzida
                if (array_key_exists($attribute, $translatedAttributes[$locale])) {
                    continue; // Ignora se já existe
                }

                // Substituir underscores por espaços antes de traduzir
                $attributeToTranslate = str_replace('_', ' ', $attribute);

                // Manter as chaves com '.' ou '.*' intactas
                if (strpos($attribute, '.') !== false) {
                    $parts = explode('.', $attribute);
                    $attributeToTranslate = str_replace('_', ' ', $parts[0]);
                    $suffix = '.' . $parts[1];
                } else {
                    $suffix = '';
                }

                // Traduzir o atributo
                try {
                    $translation = $translator->translate($attributeToTranslate);
                } catch (\Exception $e) {
                    $this->error("Erro ao traduzir atributo '$attribute' para '$locale'. Erro: " . $e->getMessage());
                    $translation = $attributeToTranslate;
                }

                // Adicionar a tradução ao array, mantendo a chave original
                $translatedAttributes[$locale][$attribute] = $translation . $suffix;
            }
            // Remover chaves obsoletas
            $translatedAttributes[$locale] = array_intersect_key($translatedAttributes[$locale], $attributeKeys);

            // Ordenar as traduções em ordem alfabética pela chave
            ksort($translatedAttributes[$locale]);
        }

        // Gerar o array formatado para o arquivo de saída com aspas duplas para strings
        $output = "<?php\n\nreturn [\n";
        foreach ($translatedAttributes as $locale => $translations) {
            $output .= "    \"$locale\" => [\n";
            foreach ($translations as $key => $translation) {
                $output .= "        \"$key\" => \"$translation\",\n";
            }
            $output .= "    ],\n";
        }
        $output .= "];\n";

        // Criar o diretório se não existir
        if (!File::isDirectory(base_path('documentation/translations'))) {
            File::makeDirectory(base_path('documentation/translations'), 0755, true);
        }

        // Escrever o arquivo com as traduções
        File::put($outputFilePath, $output);

        $this->info("Traduções geradas com sucesso em app/documentation/translations/appTranslatedAttributes.php");

        return Command::SUCCESS;
    }
}
