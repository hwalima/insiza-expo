<?php

use App\Models\Expo;
use App\Models\Stand;
use App\Services\GrokService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public bool   $isOpen   = false;
    public string $input    = '';
    public bool   $thinking = false;
    public array  $messages = [];

    public function mount(): void
    {
        $this->messages[] = [
            'role'    => 'assistant',
            'content' => 'Hello! I am the IDIEXPO 2026 AI Assistant. I can help you find available stands, answer questions about the expo, and guide you through the booking process. How can I help you today?',
        ];
    }

    public function send(): void
    {
        $text = trim($this->input);
        if (empty($text)) return;

        $this->messages[] = ['role' => 'user', 'content' => $text];
        $this->input      = '';
        $this->thinking   = true;

        $reply = $this->askGroq($text);

        $this->messages[] = ['role' => 'assistant', 'content' => $reply];
        $this->thinking   = false;
        $this->dispatch('chat-updated');
    }

    public function clearChat(): void
    {
        $this->messages = [[
            'role'    => 'assistant',
            'content' => 'Chat cleared. How can I help you?',
        ]];
    }

    private function askGroq(string $message): string
    {
        $expo    = Expo::active();
        $service = app(GrokService::class);

        $history = array_slice($this->messages, 0, -1); // all but the user message we just added

        return $service->chat(
            $this->buildSystemPrompt($expo),
            $history,
            $message
        );
    }

    private function buildSystemPrompt(?Expo $expo): string
    {
        if (! $expo) {
            return 'You are an AI assistant for the Insiza District Industrial Expo. There is currently no active expo. Politely let users know and invite them to check back soon.';
        }

        $stands = Stand::where('expo_id', $expo->id)
            ->where('is_placed', true)
            ->get()
            ->map(fn($s) => sprintf(
                'Stand %s (%s, %s, USD %s) - %s',
                $s->stand_number,
                $s->size->label(),
                $s->category->label(),
                number_format($s->price, 2),
                $s->status->label()
            ))
            ->join("\n");

        return <<<PROMPT
You are IDIEXPO AI, the official intelligent assistant for the {$expo->name}.

EXPO DETAILS:
- Name: {$expo->name}
- Theme: "{$expo->theme}"
- Dates: {$expo->start_date->format('d F Y')} to {$expo->end_date->format('d F Y')}
- Venue: {$expo->venue}
- Contact: {$expo->contact_phone} | {$expo->contact_email}

AVAILABLE STANDS ON THE FLOOR PLAN:
{$stands}

YOUR ROLE:
1. Answer questions about the expo in a friendly, professional tone.
2. Help exhibitors understand stand options (3x3m = small square, 6x3m = large rectangle).
3. Guide users to the floor plan page to book: /floor-plan
4. Explain the booking process: click a stand, fill the form, admin approves within 24 hours.
5. For WhatsApp booking help, mention the WhatsApp AI bot is also available.
6. Keep responses concise and mobile-friendly (short paragraphs).
7. If asked about pricing, refer to specific stand prices above.
8. Sector categories: Mining, Agriculture, Education, Organisations, General.

IMPORTANT: You are a website chatbot. Do NOT ask users to send messages via WhatsApp - they can use this chat or visit /floor-plan directly.
PROMPT;
    }
};
?>

<div
    x-data="{ open: @entangle('isOpen') }"
    class="fixed bottom-4 right-4 z-50 flex flex-col items-end gap-3 sm:bottom-6 sm:right-6"
>
    {{-- Chat panel --}}
    <div
        x-show="open"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="glass-card flex w-[min(92vw,420px)] flex-col rounded-3xl shadow-2xl border border-[#185909]/40"
        style="height:480px; display:none; background:rgba(17,29,2,0.92); backdrop-filter:blur(20px);"
    >
        {{-- Header --}}
        <div class="flex items-center gap-3 border-b border-white/10 px-4 py-3">
            <div class="flex size-9 items-center justify-center rounded-full bg-[#185909]">
                <span class="text-base font-extrabold text-[#D29500]">AI</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-white">IDIEXPO AI Assistant</p>
                <p class="text-[10px] text-green-400">Powered by Groq &bull; Online</p>
            </div>
            <button wire:click="clearChat" class="text-xs text-white/30 hover:text-white/60" title="Clear chat">&#8635;</button>
            <button @click="open = false" class="text-white/40 hover:text-white text-lg leading-none">&times;</button>
        </div>

        {{-- Messages --}}
        <div
            id="chat-messages"
            class="flex-1 space-y-3 overflow-y-auto px-4 py-3"
            x-init="$el.scrollTop = $el.scrollHeight"
            @chat-updated.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
        >
            @foreach($messages as $msg)
                @if($msg['role'] === 'assistant')
                    <div class="flex items-end gap-2">
                        <div class="flex size-6 shrink-0 items-center justify-center rounded-full bg-[#185909] text-[10px] font-bold text-[#D29500]">AI</div>
                        <div class="max-w-[85%] rounded-2xl rounded-bl-sm bg-[#213A24] px-3 py-2 text-sm leading-relaxed text-white/90">
                            {!! nl2br(e($msg['content'])) !!}
                        </div>
                    </div>
                @else
                    <div class="flex items-end justify-end gap-2">
                        <div class="max-w-[85%] rounded-2xl rounded-br-sm bg-[#185909] px-3 py-2 text-sm text-white">
                            {{ $msg['content'] }}
                        </div>
                    </div>
                @endif
            @endforeach

            @if($thinking)
                <div class="flex items-end gap-2">
                    <div class="flex size-6 shrink-0 items-center justify-center rounded-full bg-[#185909] text-[10px] font-bold text-[#D29500]">AI</div>
                    <div class="rounded-2xl rounded-bl-sm bg-[#213A24] px-4 py-2.5">
                        <span class="flex gap-1">
                            <span class="size-1.5 animate-bounce rounded-full bg-[#D29500]" style="animation-delay:0ms"></span>
                            <span class="size-1.5 animate-bounce rounded-full bg-[#D29500]" style="animation-delay:150ms"></span>
                            <span class="size-1.5 animate-bounce rounded-full bg-[#D29500]" style="animation-delay:300ms"></span>
                        </span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Quick suggestions --}}
        @if(count($messages) === 1)
        <div class="flex flex-wrap gap-1.5 border-t border-white/10 px-3 py-2">
            @foreach(['What stands are available?', 'How do I book a stand?', 'Tell me about the expo', 'What sectors are there?'] as $q)
                <button
                    wire:click="$set('input', '{{ $q }}')"
                    class="rounded-full border border-white/15 bg-white/5 px-2.5 py-1 text-[10px] text-white/60 hover:bg-white/10 hover:text-white transition"
                >{{ $q }}</button>
            @endforeach
        </div>
        @endif

        {{-- Input --}}
        <form wire:submit="send" class="border-t border-white/10 p-3">
            <div class="flex gap-2">
                <input
                    wire:model="input"
                    type="text"
                    placeholder="Ask me anything about the expo..."
                    class="flex-1 rounded-xl border border-white/15 bg-white/10 px-3 py-2 text-sm text-white placeholder-white/30 focus:border-[#D29500] focus:outline-none"
                    @keydown.enter.prevent="$wire.send()"
                    {{ $thinking ? 'disabled' : '' }}
                >
                <button
                    type="submit"
                    class="flex size-9 items-center justify-center rounded-xl bg-[#185909] text-white transition hover:bg-[#1e6e0a] disabled:opacity-50"
                    {{ $thinking ? 'disabled' : '' }}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    {{-- FAB toggle button --}}
    <button
        @click="open = !open"
        class="flex size-14 items-center justify-center rounded-full bg-[#185909] shadow-lg shadow-[#185909]/40 transition-all duration-200 hover:bg-[#1e6e0a] hover:scale-105 active:scale-95"
        :class="open ? 'rotate-0' : ''"
        title="AI Chat Assistant"
    >
        <span x-show="!open" class="text-2xl font-extrabold text-[#D29500] leading-none">AI</span>
        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>