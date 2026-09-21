<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Socio;
use App\Models\TarjetaAbono;
use Illuminate\Support\Str;

class BotController extends Controller
{
    /* // COMENTADO POR AHORA: Lógica de socios
    private function responderSocio($socio)
    {
        $nombre = $socio->persona->nombre ?? 'Socio';
        $eventos = $socio->eventos()->get();
        $beneficios = $socio->beneficios()->where('activo', true)->get();

        $respuesta = "👤 Hola {$nombre}\n\n";
        $respuesta .= "🆔 Tu número de socio es: {$socio->codigo_socio}\n\n";

        if ($eventos->isNotEmpty()) {
            $respuesta .= "🎟️ Eventos:\n";
            foreach ($eventos as $evento) {
                $estado = $evento->pivot->habilitado ? "✅" : "❌";
                $respuesta .= "• {$evento->nombre} ({$evento->fecha}) {$estado}\n";
            }
        } else {
            $respuesta .= "❌ No tienes eventos asignados.\n";
        }

        if ($beneficios->isNotEmpty()) {
            $respuesta .= "\n🎁 Beneficios:\n";
            foreach ($beneficios as $beneficio) {
                $respuesta .= "• {$beneficio->titulo}\n";
            }
        }

        return $respuesta;
    } 
    */

    public function procesarConsulta(Request $request)
    {
        $mensajeOriginal = trim($request->mensaje);
        $mensaje = strtoupper($mensajeOriginal);
        $telefono = str_replace('@s.whatsapp.net', '', $request->telefono);

        // 1. Detección de Saludos e Inicio
        $saludos = ['HOLA', 'BUENOS DIAS', 'BUENAS TARDES', 'BUENAS NOCHES', 'INICIO', 'MENU', 'HOLA LEON'];
        $esSaludo = false;
        foreach ($saludos as $s) {
            if (Str::contains($mensaje, $s)) {
                $esSaludo = true;
                $break;
            }
        }

        if ($esSaludo) {
            return response()->json([
                'respuesta' => "🦁 *¡Hola! Soy el León Bot de Real Potosí* \n\n" .
                    "Es un gusto saludarte. Por favor, elige una opción enviando el número:\n\n" .
                    "1️⃣ *Información de partidos (Liga 2026)*\n\n" .
                    "Estamos para servirte. ¡Vamos Lila! 🏟️💜"
            ]);
        }

        // 2. LÓGICA DE POLERA: Corregido con el enlace de descarga directa usando tu ID real
        if (Str::contains($mensaje, 'POLERA') || Str::contains($mensaje, 'CAMISETA')) {
            return response()->json([
                'respuesta' => "👕 *SOBRE LA POLERA OFICIAL 2026*\n\n" . 
                            "¡Gracias por tu apoyo!",
                'media' => [
                    'https://lh3.googleusercontent.com/d/1ziC50Ss_sK0SgY5eBJO0gED9xlPd_WNL'
                ],
                'location' => 'https://maps.google.com/?q=-19.5892,-65.7535' // O tu URL: https://maps.app.goo.gl/WDUC1JkN7MDN3Z617
            ]);

        }
        
        // 3. OPCIÓN 1: Fixture de la Liga (ACTIVO)
        if ($mensaje == '1' || Str::contains($mensaje, 'PARTIDO') || Str::contains($mensaje, 'FIXTURE')) {
            return response()->json([
                'respuesta' => "📅 *PRÓXIMOS PARTIDOS - FECHA 5* 🏆\n\n" .
                    "⚽ *Jueves 16 de agosto:*\n" .
                    "🦁 *REAL POTOSÍ* 🆚 always ready \n" .
                    "🏟️ Estadio: villa ingenio\n" .
                    "⏰ Hrs: 15:00\n\n" .
                    "¡Apoyemos a nuestro equipo! 🏟️💜",
                // Aquí agrupamos todas las imágenes en el array 'media'
                'media' => [
                    
                    'https://lh3.googleusercontent.com/d/1lA2TiD_5niwHWCdpAJHf1-PR1Z3L8mGD'
                ],
                'location' => 'https://www.google.com/maps/search/97X3%2B3FC%2C%20Villa%20Imperial%20de%20Potos%C3%AD%2C%20Bolivia/@-19.602147068930474,-65.74633419513702,17z?hl=es'
            ]);
        }

        /* // 4. COMENTADO: Precios de Abonos
        if ($mensaje == '2' || Str::contains($mensaje, 'PRECIO') || Str::contains($mensaje, 'COSTO') || Str::contains($mensaje, 'ABONO')) {
            // ... (lógica comentada)
        }
        */

        /* // 5. COMENTADO: Beneficios
        if ($mensaje == '3' || Str::contains($mensaje, 'BENEFICIO')) {
            // ... (lógica comentada)
        }
        */

        // 6. Respuesta por defecto
        return response()->json([
            'respuesta' => "📌 No entiendo esa opción. Por favor, escribe *MENU* para ver las opciones disponibles de información de partidos."
        ]);
    }
}