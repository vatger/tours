<?php

namespace App\Console\Commands\GrgsDev;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AppendAttributes extends Command
{
    protected $signature = 'grgsdev:append-attributes';
    protected $description = 'Append translated attributes to the attributes key in validation.php for each locale';

    public function handle()
    {
        // Caminho do arquivo de traduções gerado pelo comando TranslateAttributes
        $inputFilePath = base_path('documentation/translations/appTranslatedAttributes.php');

        // Verificar se o arquivo existe
        if (!File::exists($inputFilePath)) {
            $this->error("O arquivo appTranslatedAttributes.php não foi encontrado.");
            return Command::FAILURE;
        }

        // Incluir o arquivo de traduções
        $translatedAttributes = include $inputFilePath;

        // Verifica se o array de traduções é válido
        if (!is_array($translatedAttributes)) {
            $this->error("O arquivo appTranslatedAttributes.php não contém um array válido.");
            return Command::FAILURE;
        }

        // Loop através de cada idioma (locale) e suas traduções
        foreach ($translatedAttributes as $locale => $attributes) {
            // Caminho do arquivo validation.php para o idioma atual
            $validationFilePath = base_path("lang/{$locale}/validation.php");

            // Verificar se o arquivo validation.php existe
            if (!File::exists($validationFilePath)) {
                $this->error("O arquivo validation.php para o idioma '{$locale}' não foi encontrado.");
                continue;
            }

            // Incluir o conteúdo de validation.php
            $validationArray = include $validationFilePath;

            // Verificar se a chave 'attributes' existe, se não, pula o idioma
            if (!isset($validationArray['attributes'])) {
                $this->error("A chave 'attributes' não foi encontrada no arquivo validation.php para o idioma '{$locale}'.");
                continue;
            }

            // Pegar os atributos existentes em 'attributes'
            $existingAttributes = $validationArray['attributes'];

            // Adicionar as chaves novas (que não existem em attributes) ao array existente
            foreach ($attributes as $key => $value) {
                if (!array_key_exists($key, $existingAttributes)) {
                    $existingAttributes[$key] = $value;
                }
            }

            // Ordenar o array 'attributes' em ordem alfabética
            ksort($existingAttributes);

            // Atualizar apenas a chave 'attributes' no array de validation.php
            $validationArray['attributes'] = $existingAttributes;

            // Lê o conteúdo original do arquivo validation.php para não modificar sua estrutura
            $originalContent = File::get($validationFilePath);

            // Atualiza somente a chave 'attributes' no arquivo
            $updatedAttributesExport = var_export($existingAttributes, true);
            $updatedContent = preg_replace(
                '/\'attributes\'\s*=>\s*\[[^\]]*\]/s',
                "'attributes' => {$updatedAttributesExport}",
                $originalContent
            );

            // Escreve o conteúdo atualizado no arquivo, mantendo o restante da estrutura
            File::put($validationFilePath, $updatedContent);

            $this->info("Atributos atualizados para o idioma '{$locale}' em lang/{$locale}/validation.php");
        }

        return Command::SUCCESS;
    }
}
