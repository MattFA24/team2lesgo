<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Writing Assistant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col md:flex-row overflow-hidden">

    <!-- LEFT SIDEBAR: Saved History -->
    <div class="w-full md:w-80 bg-white border-r border-gray-200 flex flex-col h-1/3 md:h-full">
        <div class="p-5 border-b border-gray-100 bg-white z-10 flex justify-between items-center">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <i data-lucide="library" class="w-5 h-5 text-indigo-600"></i>
                Saved Library
            </h2>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50/50">
            @forelse($savedTexts as $item)
                <div class="group bg-white p-4 rounded-xl border border-gray-200 hover:border-indigo-300 hover:shadow-md transition-all duration-200 relative">
                    <div class="flex justify-between items-start mb-2">
                        <span class="px-2 py-1 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold uppercase tracking-wider">
                            {{ $item->type ?? 'Draft' }}
                        </span>
                        <form action="{{ route('delete', $item->id) }}" method="POST" onsubmit="return confirm('Delete this item?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors p-1">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                    <p class="text-sm text-gray-600 line-clamp-3 leading-relaxed">
                        {{ $item->generated_text }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</span>
                        <button onclick="copyText(`{{ addslashes($item->generated_text) }}`)" class="text-gray-400 hover:text-indigo-600 text-xs flex items-center gap-1">
                            <i data-lucide="copy" class="w-3 h-3"></i> Copy
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 px-6">
                    <div class="bg-gray-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="inbox" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 text-sm">No saved texts yet.</p>
                    <p class="text-gray-400 text-xs mt-1">Generate something to get started!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- MAIN CONTENT: Editor -->
    <div class="flex-1 flex flex-col h-2/3 md:h-full bg-gray-50 relative overflow-hidden">
        
        <!-- Top Bar -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center shadow-sm z-20">
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-6 h-6 text-indigo-600 fill-indigo-100"></i>
                AI Writer Assistant
            </h1>
            <div class="flex items-center gap-2 text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                System Ready
            </div>
        </div>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8">
            <div class="max-w-4xl mx-auto space-y-6">
                
                <!-- Input Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 flex items-center gap-2">
                        <i data-lucide="pen-line" class="w-4 h-4 text-gray-400"></i>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Original Text</span>
                    </div>
                    <textarea id="inputText" rows="6" class="w-full p-6 border-0 focus:ring-0 text-gray-700 placeholder-gray-300 text-base resize-none" placeholder="Type or paste your rough draft here..."></textarea>
                    
                    <!-- Action Bar -->
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-2">
                        <button onclick="rewrite('pro')" class="action-btn flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-medium shadow-sm hover:border-indigo-300 hover:text-indigo-600 hover:shadow-md transition-all">
                            <i data-lucide="briefcase" class="w-4 h-4"></i> Professional
                        </button>
                        <button onclick="rewrite('casual')" class="action-btn flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-medium shadow-sm hover:border-green-300 hover:text-green-600 hover:shadow-md transition-all">
                            <i data-lucide="coffee" class="w-4 h-4"></i> Casual
                        </button>
                        <button onclick="rewrite('fix')" class="action-btn flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-medium shadow-sm hover:border-amber-300 hover:text-amber-600 hover:shadow-md transition-all">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i> Fix Grammar
                        </button>
                        <button onclick="rewrite('shorten')" class="action-btn flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-medium shadow-sm hover:border-purple-300 hover:text-purple-600 hover:shadow-md transition-all">
                            <i data-lucide="scissors" class="w-4 h-4"></i> Shorten
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="loadingArea" class="hidden text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-100 border-t-indigo-600 mb-3"></div>
                    <p class="text-gray-500 text-sm font-medium">AI is thinking...</p>
                </div>

                <!-- Result Section -->
                <div id="resultArea" class="hidden bg-white rounded-2xl shadow-lg border border-indigo-100 overflow-hidden ring-1 ring-indigo-50">
                    <div class="border-b border-indigo-50 bg-indigo-50/30 px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="bot" class="w-4 h-4 text-indigo-500"></i>
                            <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wide">AI Generated Result</span>
                        </div>
                        <button onclick="copyResult()" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Copy">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                        </button>
                    </div>
                    
                    <div id="output" class="p-6 text-gray-800 text-base leading-relaxed whitespace-pre-wrap min-h-[100px]"></div>

                    <!-- Save Bar -->
                    <form action="{{ route('save') }}" method="POST" class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end">
                        @csrf
                        <input type="hidden" name="original_text" id="formOriginal">
                        <input type="hidden" name="generated_text" id="formGenerated">
                        <input type="hidden" name="action" id="formAction">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Save to Library
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Initialize Icons
        lucide.createIcons();

        async function rewrite(action) {
            const text = document.getElementById('inputText').value;
            
            // UI Elements
            const resultArea = document.getElementById('resultArea');
            const loadingArea = document.getElementById('loadingArea');
            const output = document.getElementById('output');

            if(!text) return alert('Please enter some text to rewrite.');

            // Show Loading
            resultArea.classList.add('hidden');
            loadingArea.classList.remove('hidden');

            try {
                const response = await fetch('/rewrite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ text, action }) // API key is now handled by the backend
                });
                
                const data = await response.json();
                
                // Hide Loading
                loadingArea.classList.add('hidden');

                if(data.result) {
                    output.innerText = data.result;
                    resultArea.classList.remove('hidden');
                    
                    // Populate hidden form for saving
                    document.getElementById('formOriginal').value = text;
                    document.getElementById('formGenerated').value = data.result;
                    document.getElementById('formAction').value = action;
                } else {
                    alert("Error: " + (data.error || "Unknown error"));
                }
            } catch (e) {
                console.error(e);
                loadingArea.classList.add('hidden');
                alert("Request failed. Check console for details.");
            }
        }

        function copyText(text) {
            navigator.clipboard.writeText(text);
        }
        
        function copyResult() {
            const text = document.getElementById('output').innerText;
            copyText(text);
        }
    </script>
</body>
</html>