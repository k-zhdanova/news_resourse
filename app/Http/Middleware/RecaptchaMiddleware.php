<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;

class RecaptchaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (env('APP_URL') == 'http://localhost') {
            return $next($request);
        }

        if (env('APP_URL') == 'http://127.0.0.1') {
            return $next($request);
        }

        if (env('APP_URL') == 'http://api.cnap.test') {
            return $next($request);
        }

        if (env('APP_URL') == 'http://cnap.test') {
            return $next($request);
        }

        if (!$request->recaptcha)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'No \'recaptcha\' parameter'
            ], 400);
        }

        $url = env('GOOGLE_RECAPTCHA_URL');
        $secret = env('GOOGLE_RECAPTCHA_SECRET');

        $client = new Client();

        $response = $client->get("{$url}?secret={$secret}&response={$request->recaptcha}");
        $json = $response->getBody();
        $recaptcha = json_decode($json);

        if (isset($recaptcha->success) && $recaptcha->success == false)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Recaptcha request failed...'
            ], 400);
        }

        if ($recaptcha->score < 0.9) {
            return response()->json([
                'status' => 'error',
                'message' => 'It seems you are a bot...'
            ], 403);
        }

        return $next($request);
    }
}
