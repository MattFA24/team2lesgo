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
        // Fetch saved texts from the database
        // We use try-catch to prevent crashing if the table is missing
        try {
            $savedTexts = DB::table('saved_texts')->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            $savedTexts = [];
        }
        
        return view('ai-app', ['savedTexts' => $savedTexts]);
    }

    // Call Gemini API
    public function rewrite(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'action' => 'required|string',
        ]);

        // GET KEY FROM ENV (Server-side secure)
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json(['error' => 'API Key not configured on server.'], 500);
        }

        $prompts = [
            'pro' => "Rewrite this to be professional and polite for a business context:",
            'casual' => "Rewrite this to be casual and friendly:",
            'fix' => "Fix grammar and spelling only:",
            'shorten' => "Make this concise and remove filler words:",
        ];

        $systemPrompt = $prompts[$request->action] ?? "Rewrite this text:";
        
        // Call Google Gemini API
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key={$apiKey}", [
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

        return response()->json(['error' => 'API Call Failed: ' . $response->body()], 500);
    }

    // Save to Database
    public function save(Request $request)
    {
        try {
            DB::table('saved_texts')->insert([
                'original_text' => $request->original_text,
                'generated_text' => $request->generated_text,
                'type' => $request->action,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to save: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    // Delete from Database
    public function destroy($id)
    {
        DB::table('saved_texts')->where('id', $id)->delete();
        return redirect()->back();
    }
}