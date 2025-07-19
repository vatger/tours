<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use enshrined\svgSanitize\Sanitizer;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    /**
     * remove file or image
     *
     * @param  mixed $file file name to unlink
     * @param  mixed $path path of file
     * @return bool true removed | false unremoved
     */
    public static function removeFile($file, $path): bool
    {
        if (!$file) {
            return true;
        }
        return  Storage::delete($path .  DIRECTORY_SEPARATOR . $file);
    }

    public static function putFile($fileData, string $storagePath, string $storageDisk = 'public', string $originalName = null)
    {
        $extension = $fileData->getClientOriginalExtension();

        // Gera um nome único para o arquivo
        $fileName = self::generateFileName($extension);

        if (!$originalName) {
            $originalName = $fileData->getClientOriginalName();
        }
        // Verifica se o arquivo é base64 e converte para arquivo se necessário
        if (self::isBase64($fileData)) {
            $isSave = self::base64ToFile($fileData, $storagePath, $fileName);
            return [
                'original_name' => $originalName,
                'saved_name' => $fileName,
                'size' => $fileData->getSize(),
                'extension' => $extension,
            ];
        }
        if (self::validateType($extension) === 'file') {
            $contents = file_get_contents($fileData->getRealPath());
            $isSave = Storage::put($storagePath . DIRECTORY_SEPARATOR . $fileName, $contents, $storageDisk);
        } else {
            if (strtolower($extension) === 'svg') {
                //                $sanitizer = new Sanitizer();
                $svgContent = file_get_contents($fileData->getPathname());

                // Limpa o SVG para remover código perigoso
                $cleanSvg = $svgContent; //$sanitizer->sanitize($svgContent);

                // Salva o arquivo limpo
                $isSave = Storage::put($storagePath . DIRECTORY_SEPARATOR . $fileName, $cleanSvg, $storageDisk);
            } else {
                $isSave =  Storage::putFileAs($storagePath, $fileData, $fileName, $storageDisk);
            }
        }
        return [
            'original_name' => $originalName,
            'saved_name' => $fileName,
            'size' => $fileData->getSize(),
            'extension' => $extension,
        ];
    }

    protected static function isBase64($fileData): bool
    {
        return preg_match('/^data:image\/(\w+);base64,/', $fileData) || preg_match('/^data:application\/(\w+);base64,/', $fileData);
    }

    protected static function base64ToFile($base64String, $storagePath, $fileName)
    {
        Log::debug("Estou no base64");
        $replace = substr($base64String, 0, strpos($base64String, ',') + 1);

        // find substring fro replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $base64String);
        $image = str_replace(' ', '+', $image);
        $decoded = base64_decode($image);
        Storage::put($storagePath . DIRECTORY_SEPARATOR . $fileName, $decoded, 'public');
        return true;
    }

    protected static function validateType($extension)
    {
        $allowedImageTypes = config('image-file.supported_images', ['jpg', 'jpeg', 'png', 'svg', 'ifc', 'dwg', 'dxf']);
        $allowedFileTypes = config('image-file.supported_files', ['txt', 'xls', 'pdf', 'doc', 'docx', 'xlsx', 'jpg', 'jpeg', 'png', 'ifc', 'dwg', 'dxf', 'pptx', 'odt', 'ods', 'odp']);
        $allowedTypes = array_merge($allowedImageTypes, $allowedFileTypes);
        if (in_array(strtolower($extension), $allowedImageTypes)) {
            return 'image';
        }
        if (in_array(strtolower($extension), $allowedFileTypes)) {
            return 'file';
        }
    }

    protected static function generateFileName($extension): string
    {
        $date = Carbon::now()->format('Y-m-d');
        return $date . '_' . Str::uuid() . '.' . $extension; // Gera um nome único para o arquivo
    }
}
