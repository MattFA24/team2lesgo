<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class AiController extends Controller
{
    // Display the app and list saved items
    public function index()
    {
        $savedTexts = DB::table('saved_texts')->orderBy('created_at', 'desc')->get();
        return view('ai-app', ['savedTexts' => $savedTexts]);
    }

    // Call Gemini API
    public function rewrite(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'action' => 'required|string',
            'api_key' => 'required|string',
        ]);

        $prompts = [
            'pro' => "Rewrite this to be professional and polite for a business context:",
            'casual' => "Rewrite this to be casual and friendly:",
            'fix' => "Fix grammar and spelling only:",
            'shorten' => "Make this concise and remove filler words:",
        ];

        $systemPrompt = $prompts[$request->action] ?? "Rewrite this text:";
        
        // Call Google Gemini API using Laravel's HTTP Client
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("[https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=){$request->api_key}", [
            'contents' => [
                ['parts' => [['text' => $request->text]]]
            ],
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]]
            ]
        ]);

        if ($response->successful()) {
            $generatedText = $response->json('candidates.0.content.parts.0.text');
            return response()->json(['result' => $generatedText]);
        }

        return response()->json(['error' => 'API Call Failed'], 500);
    }

    // Save to Database
    public function save(Request $request)
    {
        DB::table('saved_texts')->insert([
            'original_text' => $request->original_text,
            'generated_text' => $request->generated_text,
            'type' => $request->action,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back();
    }

    // Delete from Database
    public function destroy($id)
    {
        DB::table('saved_texts')->where('id', $id)->delete();
        return redirect()->back();
    }
}
