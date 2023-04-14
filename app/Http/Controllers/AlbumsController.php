<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
 
class AlbumsController extends Controller
{
    private $client_id;
    private $client_secret;
    private $token_info = false;

    public function __construct(Request $request)
    {
        // Loading the credentials 
        $this->client_id = getenv('SPOTIFY_CLIENT_ID', false);
        $this->client_secret = getenv('SPOTIFY_CLIENT_SECRET', false);
        $this->token_info = Cache::get('token_info', false);
        $this->getAuthToken($request);
    }

    private function getAuthToken(Request $request) 
    {
        // Getting the token if needed
        if (!$this->token_info) {
            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
    
            $params = [
                  'grant_type' => 'client_credentials',
                  'client_id' => $this->client_id,
                  'client_secret' => $this->client_secret
            ];
            
            $client   = new \GuzzleHttp\Client();
    
            try {
                $response = $client->request('POST', 'https://accounts.spotify.com/api/token', [
                    'form_params' => $params,
                    'headers' => $headers
                ]);
    
                if ($response->getBody()) {
                    $this->token_info = json_decode($response->getBody());
                    // Caching the token for 55 minutes;
                    Cache::put('token_info', $this->token_info, 3100);
                }
            } catch (\GuzzleHttp\Exception\ClientException $e) {
            }
        }
    }

    public function getAllAlbums()
    {
        if (!$this->client_id || !$this->client_secret) {
            return response()->json([
                'errorMsg' => 'Invalid api credentials'
                ],401); 
        }
       dd($this->token_info);
    }
}
