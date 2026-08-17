<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class InicioController
{
   public function index()
   {
      return view('inicio');
   }

  public function limpiarCookies(Request $request): RedirectResponse
  {
    $request->session()->forget('modulo_activo');
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    $response = redirect()->route('dashboard');

    foreach (array_keys((array) $request->cookies->all()) as $cookieName) {
      $response->withCookie(Cookie::forget($cookieName));
    }

    $sessionCookie = trim((string) config('session.cookie', ''));
    if ($sessionCookie !== '') {
      $response->withCookie(Cookie::forget($sessionCookie));
    }

    $response->withCookie(Cookie::forget('XSRF-TOKEN'));

    return $response;
  }

  public function limpiarCache(): Response
  {
    $commands = [
      'optimize:clear',
      'config:clear',
      'cache:clear',
      'view:clear',
    ];

    foreach ($commands as $command) {
      Artisan::call($command);
    }

    return $this->cacheCleanupResponse(
      'Cache del sistema limpiada',
      'Se limpiaron configuracion, rutas, vistas y cache. Redirigiendo...',
    );
  }

  public function limpiarCacheNavegador(): Response
  {
    $redirectUrl = route('dashboard') . '?v=' . now()->timestamp;

    $html = <<<HTML
        <!doctype html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
            <meta http-equiv="Pragma" content="no-cache">
            <meta http-equiv="Expires" content="0">
            <title>Limpiando cache del navegador</title>
            <style>
                body {
                    align-items: center;
                    background: #f6f7fb;
                    color: #1f2937;
                    display: flex;
                    font-family: Arial, sans-serif;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                }

                .box {
                    background: #fff;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
                    max-width: 420px;
                    padding: 28px;
                    text-align: center;
                }

                h1 {
                    font-size: 20px;
                    margin: 0 0 8px;
                }

                p {
                    color: #6b7280;
                    margin: 0;
                }
            </style>
        </head>
        <body>
            <div class="box">
                <h1>Limpiando cache del navegador</h1>
                <p>Un momento, estamos recargando los archivos actualizados.</p>
            </div>

            <script>
                async function limpiarCacheNavegador() {
                    try {
                        localStorage.clear();
                        sessionStorage.clear();

                        if ('caches' in window) {
                            const keys = await caches.keys();
                            await Promise.all(keys.map((key) => caches.delete(key)));
                        }
                    } catch (error) {
                        console.warn('No se pudo limpiar toda la cache del navegador.', error);
                    }

                    window.location.replace('$redirectUrl');
                }

                limpiarCacheNavegador();
            </script>
        </body>
        </html>
        HTML;

    return response($html)
      ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
      ->header('Pragma', 'no-cache')
      ->header('Expires', '0');
  }

  private function cacheCleanupResponse(string $title, string $message): Response
  {
    $redirectUrl = route('dashboard') . '?v=' . now()->timestamp;

    $html = <<<HTML
        <!doctype html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
            <meta http-equiv="Pragma" content="no-cache">
            <meta http-equiv="Expires" content="0">
            <title>$title</title>
            <style>
                body {
                    align-items: center;
                    background: #f6f7fb;
                    color: #1f2937;
                    display: flex;
                    font-family: Arial, sans-serif;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                }

                .box {
                    background: #fff;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
                    max-width: 420px;
                    padding: 28px;
                    text-align: center;
                }

                h1 {
                    font-size: 20px;
                    margin: 0 0 8px;
                }

                p {
                    color: #6b7280;
                    margin: 0;
                }
            </style>
        </head>
        <body>
            <div class="box">
                <h1>$title</h1>
                <p>$message</p>
            </div>

            <script>
                setTimeout(function () {
                    window.location.replace('$redirectUrl');
                }, 900);
            </script>
        </body>
        </html>
        HTML;

    return response($html)
      ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
      ->header('Pragma', 'no-cache')
      ->header('Expires', '0');
  }

}