<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel AI Assistant</title>
    <script src="[https://cdn.tailwindcss.com](https://cdn.tailwindcss.com)"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">Laravel AI Writer</h1>

        <!-- API Key Input -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Gemini API Key</label>
            <input type="password" id="apiKey" class="w-full p-2 border rounded mt-1" placeholder="Enter API Key">
        </div>

        <!-- Input -->
        <textarea id="inputText" rows="6" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Type your draft..."></textarea>

        <!-- Buttons -->
        <div class="grid grid-cols-4 gap-4 mt-4">
            <button onclick="rewrite('pro')" class="bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Professional</button>
            <button onclick="rewrite('casual')" class="bg-green-600 text-white py-2 rounded hover:bg-green-700">Casual</button>
            <button onclick="rewrite('fix')" class="bg-yellow-500 text-white py-2 rounded hover:bg-yellow-600">Fix Grammar</button>
            <button onclick="rewrite('shorten')" class="bg-purple-600 text-white py-2 rounded hover:bg-purple-700">Shorten</button>
        </div>

        <!-- Result Area -->
        <div id="resultArea" class="mt-6 hidden">
            <h2 class="font-bold text-lg">Result:</h2>
            <div id="output" class="bg-gray-50 p-4 rounded border mt-2 whitespace-pre-wrap"></div>
            
            <!-- Save Form -->
            <form action="{{ route('save') }}" method="POST" class="mt-4">
                @csrf
                <input type="hidden" name="original_text" id="formOriginal">
                <input type="hidden" name="generated_text" id="formGenerated">
                <input type="hidden" name="action" id="formAction">
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-black">Save to Library</button>
            </form>
        </div>

        <!-- Saved Items List -->
        <div class="mt-10 border-t pt-6">
            <h2 class="text-2xl font-bold mb-4">Saved Library</h2>
            @foreach($savedTexts as $item)
                <div class="bg-gray-50 p-4 rounded-lg border mb-3 flex justify-between items-start">
                    <div>
                        <div class="text-xs text-gray-500 uppercase font-bold">{{ $item->type }}</div>
                        <p class="mt-1 text-gray-800">{{ $item->generated_text }}</p>
                    </div>
                    <form action="{{ route('delete', $item->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        async function rewrite(action) {
            const text = document.getElementById('inputText').value;
            const apiKey = document.getElementById('apiKey').value;
            const output = document.getElementById('output');
            const resultArea = document.getElementById('resultArea');

            if(!text || !apiKey) return alert('Please enter text and API key');

            output.innerText = "Thinking...";
            resultArea.classList.remove('hidden');

            try {
                const response = await fetch('/rewrite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ text, action, api_key: apiKey })
                });
                
                const data = await response.json();
                
                if(data.result) {
                    output.innerText = data.result;
                    // Populate hidden form for saving
                    document.getElementById('formOriginal').value = text;
                    document.getElementById('formGenerated').value = data.result;
                    document.getElementById('formAction').value = action;
                } else {
                    output.innerText = "Error: " + (data.error || "Unknown error");
                }
            } catch (e) {
                console.error(e);
                output.innerText = "Request failed.";
            }
        }
    </script>
</body>
</html>
