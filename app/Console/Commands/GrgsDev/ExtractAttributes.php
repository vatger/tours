<?php

namespace App\Console\Commands\GrgsDev;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class ExtractAttributes extends Command
{
    protected $signature = 'grgsdev:attributes';
    protected $description = 'Extract attribute keys from rules() method in Request classes';

    public function handle()
    {
        // Diretório de onde iremos buscar os arquivos PHP
        $directory = app_path('Http/Requests');

        // Verificar se o diretório existe
        if (!File::isDirectory($directory)) {
            $this->error("O diretório 'app/Http/Requests' não foi encontrado.");
            return Command::FAILURE;
        }

        // Função para ler todos os arquivos .php recursivamente
        $phpFiles = $this->getPhpFiles($directory);

        $attributes = [];

        // Percorrer todos os arquivos encontrados
        foreach ($phpFiles as $file) {
            $content = File::get($file);

            // Usar regex para encontrar o método rules()
            if (preg_match('/public function rules\(\)\s*\{(.*?)\}/s', $content, $matches)) {
                $rulesContent = $matches[1];

                // Encontrar as chaves dentro do array de rules()
                if (preg_match_all('/\'([^\']+)\'\s*=>/', $rulesContent, $ruleMatches)) {
                    $keys = $ruleMatches[1];
                    $attributes = array_merge($attributes, $keys);
                }
            }
        }

        // Remover duplicatas e ordenar o array
        $attributes = array_unique($attributes);
        sort($attributes);

        // Gerar o arquivo de saída no formato esperado
        $output = "<?php\n\nreturn [\n";
        foreach ($attributes as $attribute) {
            $output .= "    '$attribute',\n";
        }
        $output .= "];\n";

        // Salvar o arquivo appAttributes.php no diretório app_path('documentation/translations')
        $outputFilePath = base_path('documentation/translations/appAttributes.php');

        // Criar o diretório se não existir
        if (!File::isDirectory(base_path('documentation/translations'))) {
            File::makeDirectory(base_path('documentation/translations'), 0755, true);
        }

        // Escrever o arquivo com os atributos extraídos
        File::put($outputFilePath, $output);

        $this->info("Atributos extraídos com sucesso em " . base_path('documentation/translations/appAttributes.php'));

        return Command::SUCCESS;
    }

    // Função para obter todos os arquivos .php recursivamente
    protected function getPhpFiles($directory)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        $regexIterator = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        $files = [];
        foreach ($regexIterator as $file) {
            $files[] = $file[0];
        }

        return $files;
    }
}
