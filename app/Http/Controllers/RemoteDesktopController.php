<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;

class RemoteDesktopController extends Controller
{
    public function executeSSHCommand(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'host' => 'required|string',
        ]);

        // Obtener los datos del formulario
        $username = $request->input('username');
        $password = $request->input('password');
        $host = $request->input('host');

        // Guardar el archivo en una ubicación específica (opcional)


        // Conectar via SSH
        $ssh = new SSH2($host);
        if (!$ssh->login($username, $password)) {
            return response()->json(['error' => 'SSH login failed.'], 401);
        }
        Log::info($request);

        // Aquí puedes ejecutar los comandos SSH que necesites
        // $command = 'tu_comando_aquí';
        // $output = $ssh->exec($command);

        // Retornar una respuesta exitosa
        return response()->json(['message' => 'Conectado']);
    }
}
