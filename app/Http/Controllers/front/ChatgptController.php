<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
class ChatgptController extends Controller
{
    public function index()
    {
        return view('website.chatgpt');
    }
    public function chatgpt(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        try {
            $client = new Client();
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'user', 'content' => $request->input('message')],
                    ],
                    'max_tokens' => 100, // تقليل عدد الكلمات لتقليل التكلفة
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            dd($data);
            if (isset($data['choices'][0]['message']['content'])) {
                return response()->json(['message' => trim($data['choices'][0]['message']['content'])]);
            } else {
                return response()->json(['error' => 'Unexpected response from OpenAI'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
