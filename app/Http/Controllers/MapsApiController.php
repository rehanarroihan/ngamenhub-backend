<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class MapsApiController extends Controller
{
    protected $apiKey = 'AIzaSyBqfs_2SoY4YteDlc7hZEB9HU6FVLC3kns';

    public function autocomplete(Request $request) {
        $client = new Client();
        $request = $client->get('https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.$request->input.'&components=country:id&key='.$this->apiKey);

        return $request->getBody()->getContents();
    }

    public function detail($place_id) {
        $client = new Client();
        $request = $client->get('https://maps.googleapis.com/maps/api/place/details/json?place_id='.$place_id.'&fields=place_id,name,formatted_address,formatted_phone_number,geometry,icon&key='.$this->apiKey);

        return $request->getBody()->getContents();
    }
}
