<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
 
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

    public function getAllAlbums(Request $request)
    {
        if (!$this->token_info) {
            return response()->json([
                'errorMsg' => 'Invalid api credentials'
                ],401); 
        }
        
        $request->validate([
            'q' => 'required|max:255',
        ]); 

        // Lets search the artist be name
        $name = $request->input('q');
        $artist = $this->getArtistByName($name);

        if (!$artist) {
            return response()->json([
                'errorMsg' => 'No artist found'
                ],404); 
        }

        // Get artist discography
        $albums = $this->getArtistDiscography($artist->id);
        return response()->json($albums);
    }

    private function getArtistByName($name) 
    {
        $token = $this->token_info->access_token;
        $search_endpoint = "https://api.spotify.com/v1/search?q=".$name."&type=artist";
        $response = Http::withToken($token)->get($search_endpoint);

        if ($response->successful())  {
            $data  = json_decode($response->getBody());
            // Getting the first match 
            $artist  = $data->artists->items[0] ?? false;
            return $artist;
        } else {
            return false;
        }
    }

    private function getArtistDiscography($artist_id)
    {
        $token = $this->token_info->access_token;
        $discographe_endpoint = "https://api.spotify.com/v1/artists/".$artist_id."/albums";
        $response = Http::withToken($token)->get($discographe_endpoint);

        if ($response->successful())  {
            $data  = json_decode($response->getBody());
            // Parsing the albums data
            $albums = array_map(function($a) {
                $parsed_data  = new \stdClass();
                $parsed_data->name  = $a->name;
                $parsed_data->released  = $a->release_date;
                $parsed_data->tracks  = $a->total_tracks;
                $parsed_data->cover  = $a->images[0];
                
                return $parsed_data;
            }, $data->items);

            return $albums;
        } else {
            return [];
        }
    }
}
