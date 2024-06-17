<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;

class FileUploadController extends Controller
{
    public function uploadFile(Request $request)
    {
        // Log para registrar la información del request
        Log::info('Request recibido', [
            'request' => $request->all(),
        ]);

        // Validación de los datos de entrada
        $request->validate([
            'file' => 'required|file',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Obtener el archivo enviado
        $file = $request->file('file');
        $username = $request->input('username');
        $password = $request->input('password');
        $host = '192.168.1.102'; // Coloca la IP o hostname del servidor remoto

        // Guardar archivo temporalmente en Laravel
        $filePath = $file->store('temp', 'temp');

        // Log para registrar la información del archivo recibido y su ubicación temporal
        Log::info('Archivo recibido y almacenado temporalmente', [
            'filename' => $file->getClientOriginalName(),
            'temp_path' => Storage::disk('temp')->path($filePath),
        ]);

        // Conectar y enviar el archivo por SFTP
        $ssh = new SSH2($host);
        if (!$ssh->login($username, $password)) {
            Log::error('SSH login failed', [
                'username' => $username,
                'host' => $host,
            ]);
            Storage::disk('temp')->delete($filePath); // Eliminar archivo temporal en caso de error
            return response()->json(['error' => 'SSH login failed.'], 401);
        }

        $sftp = new SFTP($host);
        if (!$sftp->login($username, $password)) {
            Log::error('SFTP login failed', [
                'username' => $username,
                'host' => $host,
            ]);
            Storage::disk('temp')->delete($filePath); // Eliminar archivo temporal en caso de error
            return response()->json(['error' => 'SFTP login failed.'], 401);
        }

        // Ruta y nombre de archivo remoto en el servidor
        $remoteDirectory = 'C:/Users/' . $username . '/Downloads/'; // Ruta deseada en el servidor remoto para SFTP
        $remoteFilePath = $remoteDirectory . $file->getClientOriginalName();

        // Transferencia de archivo desde almacenamiento temporal a servidor remoto por SFTP
        if (!$sftp->put($remoteFilePath, Storage::disk('temp')->path($filePath), SFTP::SOURCE_LOCAL_FILE)) {
            Log::error('Error al enviar el archivo por SFTP', [
                'filename' => $file->getClientOriginalName(),
                'remote_path' => $remoteFilePath,
                'local_path' => Storage::disk('temp')->path($filePath),
            ]);

            Storage::disk('temp')->delete($filePath); // Eliminar archivo temporal en caso de error
            return response()->json(['error' => 'Error al enviar el archivo por SFTP.'], 500);
        }

        // Log para registrar el éxito en la transferencia por SFTP
        Log::info('Archivo enviado correctamente por SFTP', [
            'filename' => $file->getClientOriginalName(),
            'remote_path' => $remoteFilePath,
        ]);

        // Eliminar el archivo temporal después de la transferencia
        Storage::disk('temp')->delete($filePath);

        return response()->json(['message' => 'Archivo enviado correctamente por SFTP.'], 200);
    }
}
