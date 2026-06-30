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
                    <span class="text-[10px] font-mono font-bold text-[#A1A1AA] ml-2">
                        @if($connected)
                            ssh-session ({{ $username }}@{{ $host }}:{{ $port }})
                        @else
                            disconnected-session
                        @endif
                    </span>
                </div>
                <div class="flex gap-2">
                    @if($connected)
                        <button onclick="disconnectSSH()" class="text-[10px] font-mono text-[#EF4444] hover:bg-[#EF4444]/10 px-2 py-0.5 rounded border border-[#EF4444]/30 transition-colors cursor-pointer">
                            🔌 Putuskan SSH
                        </button>
                        <button onclick="clearTerminal()" class="text-[10px] font-mono text-[#A1A1AA] hover:text-white px-2 py-0.5 bg-[#27272A] hover:bg-[#3F3F46] rounded border border-[#3F3F46] transition-colors cursor-pointer">
                            Clear Screen
                        </button>
                    @endif
                </div>
            </div>

            @if($connected)
                <!-- Terminal Output Display -->
                <div id="terminal-output" class="flex-1 overflow-y-auto p-4 font-mono text-xs text-[#E4E4E7] space-y-1.5 scroll-smooth">
                    <div class="text-[#10B981] select-none">
                        [SSH Terhubung] Sesi terminal aman aktif untuk {{ $username }}@`{{ $host }}`:{{ $port }}!
                        Ketik perintah Anda di bawah untuk mengeksekusi langsung pada server target.
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
            @else
                <!-- SSH Connection Login Form (PuTTY-style config) -->
                <div class="flex-1 flex flex-col items-center justify-center p-6 text-[#E4E4E7]">
                    <div class="w-full max-w-sm bg-[#18181B] border border-[#27272A] rounded-lg p-6 shadow-2xl">
                        <div class="text-center mb-6">
                            <span class="text-3xl">🔑</span>
                            <h3 class="text-base font-bold text-white mt-2">Masuk Layanan SSH Server</h3>
                            <p class="text-[10px] text-[#A1A1AA] mt-1">Masukkan kredensial SSH untuk mendapatkan akses penuh ke server.</p>
                        </div>

                        <div id="ssh-error-alert" class="hidden p-3 border border-[#EF4444]/20 rounded bg-[#EF4444]/10 text-[#EF4444] text-xs font-semibold mb-4 leading-relaxed whitespace-pre-wrap"></div>

                        <form id="ssh-connect-form" class="space-y-4">
                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-2 flex flex-col gap-1">
                                    <label class="text-[9px] font-bold text-[#A1A1AA] uppercase tracking-wider">Host / IP</label>
                                    <input type="text" id="ssh_host" required placeholder="127.0.0.1" value="127.0.0.1" class="bg-[#09090B] border border-[#27272A] focus:border-[#6366F1]/50 focus:ring-0 rounded p-2 text-xs font-mono text-white placeholder-[#52525B]">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-[9px] font-bold text-[#A1A1AA] uppercase tracking-wider">Port</label>
                                    <input type="number" id="ssh_port" required placeholder="22" value="22" class="bg-[#09090B] border border-[#27272A] focus:border-[#6366F1]/50 focus:ring-0 rounded p-2 text-xs font-mono text-white placeholder-[#52525B]">
                                </div>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[9px] font-bold text-[#A1A1AA] uppercase tracking-wider">SSH Username</label>
                                <input type="text" id="ssh_username" required placeholder="root" value="root" class="bg-[#09090B] border border-[#27272A] focus:border-[#6366F1]/50 focus:ring-0 rounded p-2 text-xs font-mono text-white placeholder-[#52525B]">
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[9px] font-bold text-[#A1A1AA] uppercase tracking-wider">SSH Password</label>
                                <input type="password" id="ssh_password" required placeholder="••••••••" class="bg-[#09090B] border border-[#27272A] focus:border-[#6366F1]/50 focus:ring-0 rounded p-2 text-xs font-mono text-white placeholder-[#52525B]">
                            </div>

                            <button type="submit" id="btn-ssh-connect" class="w-full flex items-center justify-center gap-1.5 bg-[#6366F1] hover:bg-[#4F46E5] text-white text-xs font-bold py-2.5 px-4 rounded transition-all cursor-pointer shadow-md">
                                ⚡ Hubungkan Koneksi SSH
                            </button>
                        </form>
                    </div>
                </div>
            @endif
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

        const sshForm = document.getElementById('ssh-connect-form');
        const sshErrorAlert = document.getElementById('ssh-error-alert');
        const btnSshConnect = document.getElementById('btn-ssh-connect');

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

        // Connect SSH handler
        if (sshForm) {
            sshForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const host = document.getElementById('ssh_host').value.trim();
                const port = document.getElementById('ssh_port').value.trim();
                const username = document.getElementById('ssh_username').value.trim();
                const password = document.getElementById('ssh_password').value;

                if (!host || !port || !username || !password) {
                    showSshError("Semua kolom harus diisi.");
                    return;
                }

                // Show loading status
                btnSshConnect.disabled = true;
                btnSshConnect.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menghubungkan ke SSH...
                `;
                sshErrorAlert.classList.add('hidden');

                fetch('/terminal/connect', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ host, port, username, password })
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.error || 'Gagal terhubung ke SSH.');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(err => {
                    showSshError(err.message);
                })
                .finally(() => {
                    btnSshConnect.disabled = false;
                    btnSshConnect.innerHTML = '⚡ Hubungkan Koneksi SSH';
                });
            });
        }

        function showSshError(message) {
            if (sshErrorAlert) {
                sshErrorAlert.innerText = message;
                sshErrorAlert.classList.remove('hidden');
            }
        }

        // Disconnect SSH handler
        function disconnectSSH() {
            if (confirm("Apakah Anda yakin ingin memutuskan koneksi SSH?")) {
                fetch('/terminal/disconnect', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        location.reload();
                    } else {
                        alert("Gagal memutuskan koneksi.");
                    }
                })
                .catch(err => {
                    console.error("Disconnect error:", err);
                    alert("Koneksi gagal diputus karena masalah jaringan.");
                });
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
            .then(async response => {
                if (response.status === 401) {
                    location.reload();
                    return { error: 'Sesi kedaluwarsa. Menolak kembali...' };
                }
                if (response.status === 419) {
                    return { error: 'Sesi CSRF Kedaluwarsa (Error 419). Harap segarkan (refresh/F5) halaman browser Anda.' };
                }
                if (response.status === 403) {
                    return { error: 'Akses Ditolak (Error 403): Hanya akun Master Admin yang memiliki izin menggunakan terminal.' };
                }
                if (!response.ok) {
                    const data = await response.json();
                    return { error: data.error || 'Server Error.' };
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
                        const hostClean = "{{ $host }}";
                        const usernameClean = "{{ $username }}";
                        terminalPrompt.innerHTML = `${escapeHtml(usernameClean)}@${escapeHtml(hostClean)}:<span class="text-[#38BDF8]">${escapeHtml(data.cwd)}</span>$`;
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
            if (terminalOutput) {
                terminalOutput.scrollTop = terminalOutput.scrollHeight;
            }
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
