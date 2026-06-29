<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xs font-bold text-[#111111] uppercase tracking-widest leading-tight">
            Web Console Terminal
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6 p-6">
        
        <!-- Header Info Card -->
        <div class="bg-[#F4F4F5] text-[#111111] border border-[#E4E4E7] rounded-md p-6 relative overflow-hidden shadow-sm">
            <div class="absolute -top-12 -right-12 w-56 h-56 bg-[#6366F1]/5 rounded-full blur-2xl"></div>
            <div class="relative z-10">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-[#FAF9F6] border border-[#E4E4E7] text-[8px] font-bold text-[#111111] uppercase tracking-wider mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#10B981] animate-pulse"></span>
                    Direct Server Console Enabled
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-[#111111]">Server Console Terminal</h1>
                <p class="mt-1.5 text-[#71717A] text-xs leading-relaxed max-w-2xl font-light">
                    Eksekusi perintah shell langsung pada server lokal tempat program billing berjalan. Akses diamankan khusus untuk peran Master Administrator.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-[#F0FDF4] border border-[#DCFCE7] text-[#16A34A] text-xs font-bold px-4 py-3 rounded-md shadow-sm">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-[#FEF2F2] border border-[#FEE2E2] text-[#DC2626] text-xs font-bold px-4 py-3 rounded-md shadow-sm">
                ✗ {{ $errors->first() }}
            </div>
        @endif

        <!-- Server Metadata Badges Strip -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border border-[#E4E4E7] rounded-md p-3 shadow-xs">
                <div class="text-[8px] font-bold text-[#71717A] uppercase tracking-wider">Sistem Operasi (OS)</div>
                <div class="text-xs font-bold text-[#111111] mt-1 font-mono">{{ PHP_OS }}</div>
            </div>
            <div class="bg-white border border-[#E4E4E7] rounded-md p-3 shadow-xs">
                <div class="text-[8px] font-bold text-[#71717A] uppercase tracking-wider">Server Hostname</div>
                <div class="text-xs font-bold text-[#111111] mt-1 font-mono">{{ $host }}</div>
            </div>
            <div class="bg-white border border-[#E4E4E7] rounded-md p-3 shadow-xs">
                <div class="text-[8px] font-bold text-[#71717A] uppercase tracking-wider">Sistem User (PHP)</div>
                <div class="text-xs font-bold text-[#111111] mt-1 font-mono">{{ $username }}</div>
            </div>
            <div class="bg-white border border-[#E4E4E7] rounded-md p-3 shadow-xs">
                <div class="text-[8px] font-bold text-[#71717A] uppercase tracking-wider">Root Proyek (Base)</div>
                <div class="text-[10px] font-bold text-[#6366F1] mt-1 font-mono truncate" title="{{ base_path() }}">{{ basename(base_path()) }}</div>
            </div>
        </div>

        <!-- Terminal Screen Panel (Full Width) -->
        <div class="bg-[#09090B] border border-[#27272A] rounded-md overflow-hidden shadow-lg flex flex-col h-[550px]">
            <!-- Terminal Header Bar -->
            <div class="bg-[#18181B] px-4 py-2.5 flex items-center justify-between border-b border-[#27272A] select-none">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#EF4444]"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B]"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#10B981]"></span>
                    <span class="text-[10px] font-mono font-bold text-[#A1A1AA] ml-2">server-console-session</span>
                </div>
                <button onclick="clearTerminal()" class="text-[10px] font-mono text-[#A1A1AA] hover:text-white px-2 py-0.5 bg-[#27272A] hover:bg-[#3F3F46] rounded border border-[#3F3F46] transition-colors cursor-pointer">
                    Clear Screen
                </button>
            </div>

            <!-- Terminal Output Display -->
            <div id="terminal-output" class="flex-1 overflow-y-auto p-4 font-mono text-xs text-[#E4E4E7] space-y-1.5 scroll-smooth">
                <div class="text-[#10B981] select-none">
                    [Terhubung] Web Terminal aktif untuk {{ $username }}@`{{ $host }}`!
                    Konsol langsung meremot mesin server utama Anda. Ketik perintah di bawah untuk memulai.
                </div>
            </div>

            <!-- Terminal Command Prompt Input -->
            <div class="bg-[#18181B] border-t border-[#27272A] px-4 py-3 flex items-center font-mono text-xs gap-1.5 relative">
                <span id="terminal-prompt" class="text-[#10B981] font-bold select-none">{{ $username }}@`{{ $host }}`:<span class="text-[#38BDF8]">{{ $cwd }}</span>$</span>
                <input type="text" id="terminal-input" autocomplete="off" autofocus
                       class="flex-1 bg-transparent text-[#E4E4E7] focus:outline-none border-0 p-0 m-0 focus:ring-0 placeholder-[#52525B]" 
                       placeholder="Ketik perintah di sini...">
                <div id="terminal-loader" class="hidden absolute right-4 top-1/2 -translate-y-1/2">
                    <svg class="animate-spin h-4 w-4 text-[#6366F1]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>

    </div>

    <!-- TERMINAL JAVASCRIPT MECHANISM -->
    <script>
        // Store past command history
        let commandHistory = [];
        let historyIndex = -1;

        const terminalOutput = document.getElementById('terminal-output');
        const terminalInput = document.getElementById('terminal-input');
        const terminalPrompt = document.getElementById('terminal-prompt');
        const terminalLoader = document.getElementById('terminal-loader');

        // Focus command line on click anywhere in the terminal container
        if (terminalOutput) {
            terminalOutput.addEventListener('click', () => {
                if (terminalInput) terminalInput.focus();
            });
        }

        // Clear terminal output screen
        function clearTerminal() {
            if (terminalOutput) {
                terminalOutput.innerHTML = '<div class="text-[#10B981] select-none">[Screen Cleared] Ketik perintah baru di bawah ini.</div>';
            }
        }

        // Initialize commands handler
        if (terminalInput) {
            terminalInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    const command = this.value.trim();
                    if (command === '') return;

                    // Execute command
                    executeCommand(command);
                    this.value = '';
                } else if (event.key === 'ArrowUp') {
                    // Navigate up in command history
                    event.preventDefault();
                    if (commandHistory.length > 0) {
                        if (historyIndex === -1) {
                            historyIndex = commandHistory.length - 1;
                        } else if (historyIndex > 0) {
                            historyIndex--;
                        }
                        this.value = commandHistory[historyIndex];
                    }
                } else if (event.key === 'ArrowDown') {
                    // Navigate down in command history
                    event.preventDefault();
                    if (historyIndex !== -1) {
                        if (historyIndex < commandHistory.length - 1) {
                            historyIndex++;
                            this.value = commandHistory[historyIndex];
                        } else {
                            historyIndex = -1;
                            this.value = '';
                        }
                    }
                }
            });
        }

        function executeCommand(command) {
            // Add command to history
            commandHistory.push(command);
            historyIndex = -1;

            // Render echo command line to terminal output screen
            const promptClone = terminalPrompt.innerHTML;
            const commandLine = `<div class="flex items-center gap-1.5 font-bold pt-1.5"><span class="text-[#10B981] select-none">${promptClone}</span> <span class="text-white">${escapeHtml(command)}</span></div>`;
            terminalOutput.insertAdjacentHTML('beforeend', commandLine);
            scrollToBottom();

            // Disable input and show spinner loader
            terminalInput.disabled = true;
            terminalLoader.classList.remove('hidden');

            // Send command via AJAX POST
            fetch('/terminal/execute', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ command: command })
            })
            .then(response => {
                if (response.status === 401) {
                    location.reload(); // Reload page to prompt re-authentication
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    const errorOutput = `<div class="text-[#EF4444] font-semibold whitespace-pre-wrap">${escapeHtml(data.error)}</div>`;
                    terminalOutput.insertAdjacentHTML('beforeend', errorOutput);
                } else {
                    const outputLine = `<div class="text-[#E4E4E7] whitespace-pre-wrap leading-relaxed">${escapeHtml(data.output)}</div>`;
                    terminalOutput.insertAdjacentHTML('beforeend', outputLine);

                    // Update dynamic console working directory prompt
                    if (data.cwd) {
                        terminalPrompt.innerHTML = `{{ $username }}@` + `{{ $host }}` + `:<span class="text-[#38BDF8]">${escapeHtml(data.cwd)}</span>$`;
                    }
                }
                scrollToBottom();
            })
            .catch(error => {
                const networkError = `<div class="text-[#EF4444] font-semibold whitespace-pre-wrap">Gagal mengirim perintah: Hubungan jaringan ke server terputus atau server offline.</div>`;
                terminalOutput.insertAdjacentHTML('beforeend', networkError);
                scrollToBottom();
            })
            .finally(() => {
                // Re-enable input and hide spinner loader
                terminalInput.disabled = false;
                terminalLoader.classList.add('hidden');
                terminalInput.focus();
            });
        }

        // Scroll terminal container to the bottom
        function scrollToBottom() {
            terminalOutput.scrollTop = terminalOutput.scrollHeight;
        }

        // Safe helper to escape HTML characters
        function escapeHtml(text) {
            if (!text) return '';
            return text
                .toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</x-app-layout>
