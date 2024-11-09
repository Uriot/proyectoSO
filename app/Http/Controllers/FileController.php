<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class FileController extends Controller
{


    public function submitFile(Request $request)
    {
        // Validar que el archivo se ha enviado
        if (!$request->hasFile('archivo')) {
            return response()->json([
                'error' => 'No se ha enviado ningún archivo.',
            ], 400);
        }

        // Validación para asegurarse de que el archivo no sea comprimido
        $request->validate([
            'archivo' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    // Definir las extensiones de archivos comprimidos a rechazar
                    $extensionesComprimidas = ['zip', 'tar', 'tar.gz', 'rar', '7z', 'gz'];

                    // Obtener la extensión del archivo
                    $extension = $value->getClientOriginalExtension();

                    // Verificar si la extensión está en la lista de archivos comprimidos
                    if (in_array(strtolower($extension), $extensionesComprimidas)) {
                        $fail('Los archivos comprimidos no están permitidos.');
                    }
                }
            ],
            'sistema' => 'required|string', // Valida que el sistema sea Linux o Windows
        ]);

        try {
            $archivo = $request->file('archivo');
            $sistema = $request->input('sistema');

            // Verificar si el archivo se cargó correctamente
            if (!$archivo->isValid()) {
                return response()->json([
                    'error' => 'El archivo no es válido.',
                ], 400);
            }

            // Determina qué disco usar basado en el sistema
            $disk = $sistema === 'Windows' ? 'windows' : 'linux';

            // Intenta guardar el archivo en el disco correspondiente
            $path = $archivo->storeAs('', $archivo->getClientOriginalName(), $disk);

            // Retorna una respuesta indicando que el archivo se guardó
            return response()->json([
                'message' => 'Archivo subido correctamente',
                'name' => $archivo->getClientOriginalName(),
                'sistema' => $sistema,
                'pathOrg' => $archivo->getClientOriginalPath(),
                'patsdad' => $archivo->getPath(),
                'path' => $path
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar el archivo',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
