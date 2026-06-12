<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Swal from 'sweetalert2';
import {
    DEMO_TITLE,
    PRESENTERS,
    DISCIPLINE,
    INSTITUTION,
    TECH_STACK,
    REFERENCES,
} from '@/demoConfig';

// ─── Tipos ────────────────────────────────────────────────────────────────────
interface DemoState {
    active: boolean;
    step: number;
    minimized: boolean;
    serverDownConfirmed: boolean;
}

interface Slide {
    id: number;
    title?: string;
    body?: string | string[];
    icon?: string;
    badge?: string;
    color?: 'indigo' | 'green' | 'red';
    extra?: 'terminal' | 'login' | 'attacker-intro';
    typewriter?: boolean;
}

// ─── Estado persistente ───────────────────────────────────────────────────────
const STORAGE_KEY = 'draftcircle_demo_state';

function loadState(): DemoState {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw)
            return { active: false, step: 0, minimized: false, serverDownConfirmed: false };
        const parsed = JSON.parse(raw);
        if (typeof parsed.serverDownConfirmed !== 'boolean') {
            return { active: false, step: 0, minimized: false, serverDownConfirmed: false };
        }
        return {
            active: Boolean(parsed.active),
            step: Number(parsed.step) || 0,
            minimized: Boolean(parsed.minimized),
            serverDownConfirmed: Boolean(parsed.serverDownConfirmed),
        };
    } catch {
        return { active: false, step: 0, minimized: false, serverDownConfirmed: false };
    }
}

function saveState(s: DemoState): void {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
}

// ─── Definição dos slides ─────────────────────────────────────────────────────
const slides: Slide[] = [
    // ── Introdução ──────────────────────────────────────────────────────────────
    {
        id: 1,
        title: DEMO_TITLE,
        body: [DISCIPLINE, INSTITUTION],
        icon: '🎓',
        badge: 'Capa',
        color: 'indigo',
    },
    {
        id: 2,
        title: 'Apresentação',
        body: PRESENTERS,
        icon: '👥',
        badge: 'Equipe',
        color: 'indigo',
    },
    {
        id: 3,
        title: 'Objetivo',
        body: 'Demonstrar na prática um ataque DoS via Slowloris contra um sistema web real, utilizando Docker, Laravel e Nginx, e mostrar como mitigá-lo com configurações simples.',
        badge: 'Prólogo',
        color: 'indigo',
        typewriter: true,
    },
    {
        id: 4,
        title: 'Tecnologias',
        body: TECH_STACK,
        badge: 'Stack',
        color: 'indigo',
    },
    {
        id: 5,
        title: 'O que é Slowloris?',
        body: [
            'Ataque DoS na camada de aplicação (Layer 7)',
            'Não é volumétrico — usa pouca banda, difícil de detectar',
            'Abre centenas de conexões HTTP com o servidor',
            'Envia headers incompletos — nunca conclui a requisição',
            'Servidor aguarda 60s por padrão → slots de conexão permanecem ocupados',
            'No laboratório, o limite reduzido torna o efeito observável',
        ],
        badge: 'Conceito',
        color: 'indigo',
    },
    {
        id: 6,
        title: 'Conexão HTTP & Slowloris',
        body: [
            'Normal: cliente envia GET completo → servidor processa e responde',
            'Slowloris: envia GET parcial, nunca finaliza o header',
            'Servidor aguarda: timeout padrão de 60 segundos',
            'Atacante envia X-Keepalive a cada 10s, renovando o timer',
            'Repetido 500× → worker_connections esgotado → site indisponível',
        ],
        badge: 'Funcionamento',
        color: 'indigo',
    },
    {
        id: 7,
        title: 'Plano de Ação',
        body: [
            '1. Fazer login como administrador.',
            '2. Verificar sistema funcionando.',
            '3. Iniciar ataque Slowloris via container Docker.',
            '4. Observar servidor cair.',
            '5. Executar script de mitigação (Nginx).',
            '6. Verificar sistema restaurado.',
        ],
        badge: 'Roteiro',
        color: 'indigo',
    },
    // ── Demo ao vivo ────────────────────────────────────────────────────────────
    {
        id: 8,
        title: 'Autenticação',
        badge: 'Passo 1',
        color: 'indigo',
        extra: 'login',
    },
    {
        id: 9,
        title: 'Sistema Operando Normalmente',
        body: 'Catálogo, empréstimos e notificações disponíveis. Estado saudável antes do ataque.',
        icon: '✅',
        badge: 'Passo 2',
        color: 'green',
    },
    {
        id: 10,
        title: 'Ameaça',
        body: 'Um atacante (joão_gamer_XxXxSuperScriptKiddie) vai usar Slowloris: 500 conexões mantidas incompletas, esgotando o limite worker_connections reduzido deste laboratório.',
        icon: '💀',
        badge: 'Passo 3',
        color: 'red',
        extra: 'attacker-intro',
    },
    {
        id: 11,
        title: '',
        extra: 'terminal',
        color: 'red',
    },
    // ── Mitigação ───────────────────────────────────────────────────────────────
    {
        id: 12,
        title: 'Como a Mitigação Funciona',
        body: [
            'client_header_timeout 5s — Slowloris envia a cada 10s → timeout dispara primeiro',
            'client_body_timeout 5s — idem para o corpo da requisição',
            'keepalive_timeout 15s — libera conexões ociosas mais rápido',
            'worker_connections 1024 — restaura capacidade realista após o limite didático',
            'limit_conn addr 20 — defesa adicional após o recebimento dos headers',
        ],
        badge: 'Solução',
        color: 'green',
    },
    {
        id: 13,
        title: 'Soluções em Produção',
        body: [
            'CDN / WAF para terminar e filtrar conexões no edge',
            'Timeouts e limites calibrados no proxy reverso',
            'Rate limiting por IP (limit_req_zone)',
            'Firewall e limites de conexão no sistema operacional',
            'Observabilidade, alertas e resposta automatizada',
        ],
        badge: 'Além do básico',
        color: 'green',
    },
    {
        id: 14,
        title: 'Ataque Neutralizado!',
        body: 'O Slowloris continua ativo, mas o Nginx restaura a capacidade normal e encerra headers incompletos em 5s. O sistema responde normalmente para usuários legítimos.',
        icon: '🛡️',
        badge: 'Passo Final',
        color: 'green',
    },
    // ── Encerramento ────────────────────────────────────────────────────────────
    {
        id: 15,
        title: 'Conclusões',
        body: [
            'Slowloris é cirúrgico: baixo custo, alto impacto',
            'O laboratório reduz worker_connections para demonstrar o esgotamento',
            'A mitigação restaura capacidade e corta clientes lentos rapidamente',
            'Timeouts curtos (5s) cortam conexões lentas efetivamente',
            'limit_conn complementa a defesa para requisições concluídas',
            'Em produção: WAF, CDN e auto-scaling são essenciais',
        ],
        badge: 'Lições',
        color: 'green',
    },
    {
        id: 16,
        title: 'Referências',
        body: REFERENCES,
        badge: 'Fontes',
        color: 'indigo',
    },
    {
        id: 17,
        title: 'Obrigado!',
        body: PRESENTERS,
        icon: '🎓',
        badge: 'Perguntas?',
        color: 'indigo',
    },
];

// ─── Refs ─────────────────────────────────────────────────────────────────────
const state = ref<DemoState>(loadState());
const currentPath = ref(window.location.pathname);
const isTyping = ref(false);
const terminalLines = ref<{ text: string; type: string }[]>([]);
const terminalFinished = ref(false);
const terminalAnimationComplete = ref(false);
const attackError = ref('');
const checkingServer = ref(false);
const checkingAttempt = ref(0);
const confirmingMitigation = ref(false);
const typewriterText = ref('');
const showCursor = ref(false);
const advanceLocked = ref(false);

// ─── Computados ───────────────────────────────────────────────────────────────
const isActive = computed(() => state.value.active);
const step = computed(() => state.value.step);
const minimized = computed(() => state.value.minimized);
const onLoginPage = computed(
    () => currentPath.value === '/login' || currentPath.value.startsWith('/login'),
);
const onHomePage = computed(() =>
    ['/home', '/dashboard', '/'].some(
        (p) => currentPath.value === p || currentPath.value.startsWith('/home'),
    ),
);

const currentSlide = computed(() => slides.find((s) => s.id === step.value));
const lastSlideId = slides[slides.length - 1].id;

// ─── Spotlight dinâmico ───────────────────────────────────────────────────────
let spotlightStyle: HTMLStyleElement | null = null;

function setSpotlight(selectors: string[]): void {
    removeSpotlight();
    if (selectors.length === 0) return;
    spotlightStyle = document.createElement('style');
    const rules = selectors
        .map(
            (sel) => `#${sel} {
      outline: 3px solid #6366f1 !important;
      box-shadow: 0 0 0 9999px rgba(0,0,0,0.72), 0 0 0 6px rgba(99,102,241,0.35) !important;
      position: relative !important;
      z-index: 10000 !important;
      border-radius: 4px !important;
    }`,
        )
        .join('\n');
    spotlightStyle.textContent = rules;
    document.head.appendChild(spotlightStyle);
}

function removeSpotlight(): void {
    if (spotlightStyle) {
        spotlightStyle.remove();
        spotlightStyle = null;
    }
}

// ─── Typewriter ───────────────────────────────────────────────────────────────
let typewriterTimer: ReturnType<typeof setTimeout> | null = null;
let advanceUnlockTimer: ReturnType<typeof setTimeout> | null = null;

let typewriterStopped = false;

async function runTypewriter(fullText: string): Promise<void> {
    typewriterStopped = false;
    typewriterText.value = '';
    showCursor.value = true;
    for (let i = 0; i < fullText.length; i++) {
        if (typewriterStopped) return;
        typewriterText.value += fullText.charAt(i);
        await new Promise((resolve) => setTimeout(resolve, 28 + Math.random() * 18));
    }
    if (!typewriterStopped) showCursor.value = false;
}

function stopTypewriter(): void {
    typewriterStopped = true;
    if (typewriterTimer) clearTimeout(typewriterTimer);
    typewriterText.value = '';
    showCursor.value = false;
}

// ─── Auto-type login ─────────────────────────────────────────────────────────
function sleep(ms: number): Promise<void> {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

async function typeInto(el: HTMLInputElement, text: string): Promise<void> {
    el.focus();
    for (const char of text) {
        el.value += char;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        await sleep(110 + Math.random() * 90);
    }
}

async function startAutoType(): Promise<void> {
    isTyping.value = true;
    const loginEl = document.querySelector<HTMLInputElement>('#login');
    const passwordEl = document.querySelector<HTMLInputElement>('#password');
    if (!loginEl || !passwordEl) {
        isTyping.value = false;
        return;
    }
    await sleep(800);
    setSpotlight(['login']);
    await sleep(600);
    await typeInto(loginEl, 'ADM001');
    await sleep(500);
    setSpotlight(['password']);
    await sleep(500);
    await typeInto(passwordEl, 'password');
    await sleep(700);
    const submitBtn = document.querySelector<HTMLButtonElement>('button[type="submit"]');
    if (submitBtn) {
        setSpotlight([submitBtn.id || 'submit-btn']);
        submitBtn.focus();
        await sleep(900);
    }
    removeSpotlight();
    const form = document.querySelector('form');
    form?.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    isTyping.value = false;
}

// ─── Terminal animation ──────────────────────────────────────────────────────
const TERMINAL_CMD = 'slowloris nginx -p 8080 -s 500 --sleeptime 10';
const TERMINAL_OUTPUT: { text: string; type: string; delay: number }[] = [
    { text: 'Iniciando ataque Slowloris com 500 conexões...', type: 'out', delay: 800 },
    { text: 'Alvo: nginx:8080', type: 'out', delay: 600 },
    { text: 'Abrindo sockets...', type: 'hi', delay: 750 },
    { text: '[■■░░░░░░░░] 102/500 conexões estabelecidas', type: 'out', delay: 1100 },
    { text: 'Enviando headers incompletos a cada 10s...', type: 'out', delay: 900 },
    { text: '[■■■■░░░░░░] 213/500 conexões', type: 'out', delay: 1200 },
    { text: 'Renovando keep-alive...', type: 'dim', delay: 800 },
    { text: '[■■■■■■░░░░] 318/500 conexões', type: 'hi', delay: 1100 },
    { text: '⚠  Slots worker_connections sob pressão...', type: 'warn', delay: 950 },
    { text: '[■■■■■■■■░░] 412/500 conexões', type: 'out', delay: 1300 },
    { text: '⚠  Limite de conexões próximo do esgotamento...', type: 'warn', delay: 900 },
    { text: '[■■■■■■■■■■] 500/500 conexões ativas', type: 'hi', delay: 1200 },
    { text: 'Mantendo ataque... headers renovados a cada 10s', type: 'dim', delay: 700 },
];

const terminalCmdLine = ref('');
let terminalTimeout: ReturnType<typeof setTimeout> | null = null;
let terminalStopped = false;

function finishTerminalAfterConfirmedFailure(): void {
    if (!terminalAnimationComplete.value || terminalFinished.value) return;

    terminalLines.value.push(
        { text: '✓ Queda confirmada — servidor NÃO RESPONDE.', type: 'success' },
        { text: '→ Execute ./demo/mitigate.sh no terminal e aguarde finalizar.', type: 'info' },
        { text: '→ Então clique em "Verificar mitigação" abaixo.', type: 'info' },
    );
    terminalFinished.value = true;
}

async function startTerminalAnimation(): Promise<void> {
    terminalLines.value = [];
    terminalFinished.value = false;
    terminalAnimationComplete.value = false;
    terminalCmdLine.value = '$ ';
    terminalStopped = false;

    // Typewriter no comando
    for (const char of TERMINAL_CMD) {
        if (terminalStopped) return;
        terminalCmdLine.value += char;
        await new Promise((r) => setTimeout(r, 38 + Math.random() * 22));
    }
    if (terminalStopped) return;

    await new Promise((r) => setTimeout(r, 450));
    if (terminalStopped) return;

    // Move comando para as linhas e limpa o campo de digitação
    terminalLines.value.push({ text: terminalCmdLine.value, type: 'cmd' });
    terminalCmdLine.value = '';

    // Anima as linhas de output
    let i = 0;
    function addNext() {
        if (terminalStopped) return;
        if (i >= TERMINAL_OUTPUT.length) {
            terminalAnimationComplete.value = true;
            finishTerminalAfterConfirmedFailure();
            return;
        }
        const line = TERMINAL_OUTPUT[i++];
        terminalLines.value.push({ text: line.text, type: line.type });
        if (i < TERMINAL_OUTPUT.length) {
            terminalTimeout = setTimeout(addNext, TERMINAL_OUTPUT[i].delay);
        } else {
            terminalTimeout = setTimeout(() => {
                terminalAnimationComplete.value = true;
                if (!state.value.serverDownConfirmed) {
                    terminalLines.value.push({
                        text: 'Aguardando confirmação real da indisponibilidade...',
                        type: 'warn',
                    });
                }
                finishTerminalAfterConfirmedFailure();
            }, 600);
        }
    }
    addNext();
}

function stopTerminalAnimation(): void {
    terminalStopped = true;
    if (terminalTimeout) {
        clearTimeout(terminalTimeout);
        terminalTimeout = null;
    }
    terminalCmdLine.value = '';
}

// ─── Attack API ──────────────────────────────────────────────────────────────
function csrfHeaders(): Record<string, string> {
    const tokenCookie = document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith('XSRF-TOKEN='));
    const cookieToken = tokenCookie?.slice('XSRF-TOKEN='.length);

    if (cookieToken) {
        return { 'X-XSRF-TOKEN': decodeURIComponent(cookieToken) };
    }

    const metaToken =
        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

    return { 'X-CSRF-TOKEN': metaToken };
}

async function postDemoAction(path: string) {
    return fetch(path, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            ...csrfHeaders(),
            'Content-Type': 'application/json',
            Accept: 'application/json',
        },
    });
}

async function pingServer(timeout = 2000): Promise<boolean> {
    const controller = new window.AbortController();
    const timeoutId = window.setTimeout(() => controller.abort(), timeout);

    try {
        const response = await fetch(`/up?demo-check=${Date.now()}`, {
            cache: 'no-store',
            headers: { Accept: 'text/html' },
            signal: controller.signal,
        });
        return response.ok;
    } catch {
        return false;
    } finally {
        clearTimeout(timeoutId);
    }
}

async function waitUntilServerFalls(): Promise<void> {
    let consecutiveFailures = 0;

    for (let attempt = 0; attempt < 20 && !terminalStopped; attempt++) {
        if (await pingServer()) {
            consecutiveFailures = 0;
        } else {
            consecutiveFailures++;
            if (consecutiveFailures >= 3) {
                state.value.serverDownConfirmed = true;
                saveState(state.value);
                finishTerminalAfterConfirmedFailure();
                return;
            }
        }
        await sleep(500);
    }

    throw new Error('O ataque iniciou, mas o servidor continuou respondendo.');
}

async function triggerAttack(): Promise<void> {
    attackError.value = '';
    state.value.serverDownConfirmed = false;
    saveState(state.value);

    try {
        let response;
        try {
            response = await postDemoAction('/demo/attack/start');
        } catch {
            // A resposta pode ser interrompida porque o próprio ataque já derrubou o Nginx.
            await waitUntilServerFalls();
            return;
        }

        if (!response.ok) {
            throw new Error(`O servidor recusou o início do ataque (HTTP ${response.status}).`);
        }

        await waitUntilServerFalls();
    } catch (error) {
        attackError.value =
            error instanceof Error ? error.message : 'Não foi possível iniciar o ataque.';
        terminalLines.value.push({ text: `ERRO: ${attackError.value}`, type: 'warn' });
    }
}

async function advanceTo6AfterMitigation(): Promise<void> {
    checkingServer.value = true;
    checkingAttempt.value = 0;
    confirmingMitigation.value = false;

    const MAX_ATTEMPTS = 15; // até ~45 segundos

    for (let attempt = 0; attempt < MAX_ATTEMPTS; attempt++) {
        checkingAttempt.value = attempt + 1;

        if (attempt > 0) {
            await new Promise((r) => setTimeout(r, 2000));
        }

        if (!(await pingServer())) continue;

        // Primeira resposta OK — confirma com 2 pings rápidos para garantir
        // que não foi só uma janela passageira durante o ataque
        confirmingMitigation.value = true;
        await new Promise((r) => setTimeout(r, 700));
        if (!(await pingServer())) {
            confirmingMitigation.value = false;
            continue;
        }
        await new Promise((r) => setTimeout(r, 700));
        if (!(await pingServer())) {
            confirmingMitigation.value = false;
            continue;
        }

        // Servidor respondeu 3 vezes seguidas — mitigação confirmada
        checkingServer.value = false;
        confirmingMitigation.value = false;
        state.value.step = 12;
        saveState(state.value);
        onStepEnter(12);
        return;
    }

    checkingServer.value = false;
    Swal.fire({
        title: 'Servidor ainda não responde',
        html: 'Execute <code style="background:#0f172a;padding:2px 6px;border-radius:4px;color:#38bdf8">./demo/mitigate.sh</code> no terminal e aguarde o script finalizar antes de tentar novamente.',
        icon: 'warning',
        confirmButtonText: 'Entendido',
        background: '#1e293b',
        color: '#e2e8f0',
        confirmButtonColor: '#4f46e5',
        iconColor: '#fbbf24',
        didOpen: () => {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            const container = document.querySelector('.swal2-container') as any;
            if (container) container.style.zIndex = '10010';
        },
    });
}

// ─── Controles de passo ─────────────────────────────────────────────────────
function startDemo(): void {
    state.value = { active: true, step: 1, minimized: false, serverDownConfirmed: false };
    saveState(state.value);
}

function advance(): void {
    if (advanceLocked.value || state.value.step >= lastSlideId) return;

    // Evita que um clique duplo atravesse a transição e avance dois cards.
    advanceLocked.value = true;
    if (advanceUnlockTimer) clearTimeout(advanceUnlockTimer);
    advanceUnlockTimer = setTimeout(() => {
        advanceLocked.value = false;
        advanceUnlockTimer = null;
    }, 500);

    // Limpa imediatamente para o próximo card não herdar texto antigo
    stopTypewriter();
    state.value.step++;
    saveState(state.value);
    onStepEnter(state.value.step);
}

function onStepEnter(stepId: number): void {
    const slide = slides.find((s) => s.id === stepId);
    if (!slide) return;

    // Reiniciar typewriter
    if (slide.typewriter && typeof slide.body === 'string') {
        runTypewriter(slide.body);
    } else {
        stopTypewriter();
    }

    // Ações especiais — login NÃO inicia automaticamente (botão manual no card)
    if (slide.extra === 'terminal') {
        terminalFinished.value = false;
        terminalAnimationComplete.value = false;
        attackError.value = '';
        terminalLines.value = [];
        startTerminalAnimation();
        triggerAttack();
    }
}

function minimize(): void {
    state.value.minimized = true;
    saveState(state.value);
}

function restore(): void {
    state.value.minimized = false;
    saveState(state.value);
}

async function endDemo(): Promise<void> {
    removeSpotlight();
    stopTerminalAnimation();
    stopTypewriter();
    state.value = { active: false, step: 0, minimized: false, serverDownConfirmed: false };
    saveState(state.value);

    try {
        await postDemoAction('/demo/attack/stop');
    } catch {
        // A apresentação já terminou; o reload abaixo restaura a interface.
    }

    location.reload();
}

// ─── Navegação Inertia ──────────────────────────────────────────────────────
function autoAdvanceFromLogin(): void {
    state.value.step = 9;
    saveState(state.value);
    onStepEnter(9);
}

function handleNavigate(): void {
    currentPath.value = window.location.pathname;
    if (state.value.active && state.value.step === 8 && onHomePage.value) {
        autoAdvanceFromLogin();
    }
}

onMounted(() => {
    document.addEventListener('inertia:navigate', handleNavigate);
    if (state.value.active) {
        // Se estava no passo de login mas já está na home (ex: reload pós-login),
        // avança automaticamente sem mostrar o card de login de novo.
        if (state.value.step === 8 && onHomePage.value) {
            autoAdvanceFromLogin();
        } else if (state.value.step === 11) {
            if (state.value.serverDownConfirmed) {
                terminalAnimationComplete.value = true;
                terminalLines.value = [
                    { text: 'Queda real do servidor já confirmada.', type: 'success' },
                ];
                finishTerminalAfterConfirmedFailure();
            } else {
                startTerminalAnimation();
                triggerAttack();
            }
        } else {
            onStepEnter(state.value.step);
        }
    }
});

onUnmounted(() => {
    document.removeEventListener('inertia:navigate', handleNavigate);
    if (advanceUnlockTimer) clearTimeout(advanceUnlockTimer);
    stopTerminalAnimation();
    stopTypewriter();
    removeSpotlight();
});
</script>

<template>
    <!-- Botão iniciar (inativo e na página de login) -->
    <Transition name="fade">
        <button v-if="!isActive && onLoginPage" class="demo-float-btn" @click="startDemo">
            ▶ Iniciar Demo
        </button>
    </Transition>

    <!-- Overlay principal -->
    <Transition name="fade">
        <div
            v-if="isActive && !minimized"
            class="demo-backdrop"
            :class="{ 'demo-backdrop--light': step === 8 }"
        >
            <button class="demo-minimize-btn" @click="minimize" title="Minimizar">✕</button>

            <Transition name="card" mode="out-in">
                <!-- Terminal fullscreen (passo 11) -->
                <div
                    v-if="currentSlide?.extra === 'terminal'"
                    key="terminal"
                    class="demo-terminal-wrapper"
                >
                    <div class="demo-terminal">
                        <div class="demo-terminal__bar">
                            <span class="demo-terminal__dot demo-terminal__dot--red" />
                            <span class="demo-terminal__dot demo-terminal__dot--yellow" />
                            <span class="demo-terminal__dot demo-terminal__dot--green" />
                            <span class="demo-terminal__title"
                                >⚡ joão_gamer_XxXxSuperScriptKiddie@kali — slowloris</span
                            >
                        </div>
                        <div class="demo-terminal__body">
                            <!-- Linha do comando sendo digitada (typewriter) -->
                            <div
                                v-if="terminalCmdLine"
                                class="demo-terminal__line demo-terminal__line--cmd"
                            >
                                {{ terminalCmdLine }}<span class="demo-terminal__cursor">█</span>
                            </div>
                            <!-- Output do ataque -->
                            <div
                                v-for="(line, i) in terminalLines"
                                :key="i"
                                class="demo-terminal__line"
                                :class="`demo-terminal__line--${line.type}`"
                            >
                                {{ line.text }}
                            </div>
                            <!-- Cursor enquanto output ainda está chegando -->
                            <span
                                v-if="!terminalFinished && !terminalCmdLine"
                                class="demo-terminal__cursor"
                                >█</span
                            >
                        </div>
                        <div class="demo-terminal__footer">
                            <div v-if="!terminalFinished" class="demo-terminal__status">
                                <span
                                    class="demo-terminal__pulse"
                                    :class="{ 'demo-terminal__pulse--yellow': attackError }"
                                />
                                {{ attackError ? 'FALHA AO INICIAR ATAQUE' : 'ATAQUE EM ANDAMENTO' }}
                            </div>
                            <template v-if="terminalFinished">
                                <button
                                    v-if="!checkingServer"
                                    class="demo-btn demo-btn--indigo demo-btn--sm"
                                    @click="advanceTo6AfterMitigation"
                                >
                                    Verificar mitigação →
                                </button>
                                <span
                                    v-else
                                    class="demo-terminal__status demo-terminal__status--checking"
                                >
                                    <span
                                        class="demo-terminal__pulse demo-terminal__pulse--yellow"
                                    />
                                    {{
                                        confirmingMitigation
                                            ? 'Confirmando estabilidade...'
                                            : `Verificando... tentativa ${checkingAttempt}/15`
                                    }}
                                </span>
                            </template>
                            <button
                                v-else-if="attackError"
                                class="demo-btn demo-btn--red demo-btn--sm"
                                @click="triggerAttack"
                            >
                                Tentar iniciar ataque novamente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Slides normais -->
                <div
                    v-else-if="currentSlide"
                    :key="currentSlide.id"
                    class="demo-card"
                    :class="`demo-card--${currentSlide.color || 'indigo'}`"
                >
                    <div
                        v-if="currentSlide.badge"
                        class="demo-badge"
                        :class="`demo-badge--${currentSlide.color || 'indigo'}`"
                    >
                        {{ currentSlide.badge }}
                    </div>
                    <div v-if="currentSlide.icon" class="demo-card__icon">
                        {{ currentSlide.icon }}
                    </div>
                    <h2 v-if="currentSlide.title" class="demo-card__title">
                        {{ currentSlide.title }}
                    </h2>

                    <!-- Corpo especial: login -->
                    <div
                        v-if="currentSlide.extra === 'login'"
                        class="demo-card__body demo-card__body--sm"
                    >
                        <div class="demo-code">
                            login: <strong>ADM001</strong> · senha: <strong>password</strong>
                        </div>
                        <div v-if="!isTyping" style="margin-top: 12px">
                            <button class="demo-btn demo-btn--indigo" @click="startAutoType">
                                ▶ Preencher automaticamente
                            </button>
                        </div>
                        <div
                            v-else
                            style="display: flex; align-items: center; gap: 10px; margin-top: 12px"
                        >
                            <div class="demo-spinner" />
                            <p style="margin: 0; color: #94a3b8; font-size: 13px">Digitando...</p>
                        </div>
                    </div>

                    <!-- Corpo especial: atacante -->
                    <div
                        v-else-if="currentSlide.extra === 'attacker-intro'"
                        class="demo-card__body"
                    >
                        <div class="demo-attacker">
                            <div class="demo-attacker__avatar">💀</div>
                            <code class="demo-attacker__name"
                                >joão_gamer_XxXxSuperScriptKiddie</code
                            >
                        </div>
                        <p class="demo-slide-text">{{ currentSlide.body }}</p>
                    </div>

                    <!-- Typewriter para string -->
                    <div
                        v-else-if="currentSlide.typewriter && typeof currentSlide.body === 'string'"
                        class="demo-card__body"
                    >
                        <p class="demo-slide-text">
                            {{ typewriterText }}<span v-if="showCursor" class="demo-cursor">|</span>
                        </p>
                    </div>

                    <!-- Array (lista) -->
                    <div v-else-if="Array.isArray(currentSlide.body)" class="demo-card__body">
                        <ul class="demo-list">
                            <li
                                v-for="(item, idx) in currentSlide.body"
                                :key="idx"
                                class="demo-list-item"
                            >
                                {{ item }}
                            </li>
                        </ul>
                    </div>

                    <!-- Texto simples -->
                    <div v-else class="demo-card__body">
                        <p class="demo-slide-text">{{ currentSlide.body }}</p>
                    </div>

                    <!-- Contador de slides -->
                    <div class="demo-slide-counter">{{ step }} / {{ lastSlideId }}</div>

                    <!-- Botões de navegação -->
                    <button
                        v-if="step < lastSlideId && step !== 11"
                        class="demo-btn"
                        :class="`demo-btn--${currentSlide.color || 'indigo'}`"
                        :disabled="advanceLocked"
                        @click.stop="advance"
                    >
                        Próximo →
                    </button>
                    <button
                        v-if="step === lastSlideId"
                        class="demo-btn demo-btn--green"
                        @click="endDemo"
                    >
                        ✓ Encerrar Apresentação
                    </button>
                </div>
            </Transition>
        </div>
    </Transition>

    <!-- Botão restaurar quando minimizado -->
    <Transition name="fade">
        <button v-if="isActive && minimized" class="demo-restore-btn" @click="restore">
            📋 Mostrar apresentação
        </button>
    </Transition>
</template>

<style scoped>
/* ── Transições ───────────────────────────────────────────────── */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.card-enter-active,
.card-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}
.card-enter-from {
    opacity: 0;
    transform: scale(0.96) translateY(6px);
}
.card-leave-to {
    opacity: 0;
    transform: scale(0.96) translateY(-6px);
}

/* ── Fundo ───────────────────────────────────────────────────── */
.demo-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.78);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    font-family:
        system-ui,
        -apple-system,
        sans-serif;
}
.demo-backdrop--light {
    background: rgba(0, 0, 0, 0.55);
    align-items: flex-end;
    padding-bottom: 32px;
}

/* ── Botões flutuantes ───────────────────────────────────────── */
.demo-float-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 24px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.55);
    transition: transform 0.15s;
}
.demo-float-btn:hover {
    transform: translateY(-2px);
}

.demo-minimize-btn {
    position: fixed;
    top: 16px;
    right: 16px;
    z-index: 10000;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    width: 32px;
    height: 32px;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.demo-restore-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 700;
    cursor: pointer;
    font-size: 14px;
}

/* ── Card ─────────────────────────────────────────────────────── */
.demo-card {
    background: #1e293b;
    border-radius: 16px;
    padding: 32px;
    width: 100%;
    max-width: 520px;
    border: 1px solid transparent;
    position: relative;
}
.demo-card--indigo {
    border-color: #4f46e5;
    box-shadow: 0 0 50px rgba(79, 70, 229, 0.28);
}
.demo-card--green {
    border-color: #16a34a;
    box-shadow: 0 0 50px rgba(22, 163, 74, 0.25);
}
.demo-card--red {
    border-color: #dc2626;
    box-shadow: 0 0 50px rgba(220, 38, 38, 0.28);
}

.demo-card__icon {
    font-size: 36px;
    margin-bottom: 12px;
}

.demo-card__title {
    font-size: 20px;
    font-weight: 700;
    color: white;
    margin: 0 0 12px;
}

.demo-slide-text {
    font-size: 16px;
    line-height: 1.7;
    color: #e2e8f0;
}

.demo-card__body {
    margin-bottom: 16px;
}

.demo-card__body--sm {
    font-size: 14px;
}

/* Contador de slides */
.demo-slide-counter {
    font-size: 11px;
    color: #475569;
    text-align: right;
    margin-bottom: 12px;
    font-family: 'JetBrains Mono', monospace;
}

/* Lista */
.demo-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.demo-list-item {
    font-size: 15px;
    line-height: 1.8;
    color: #cbd5e1;
    padding-left: 1.5em;
    position: relative;
}
.demo-list-item::before {
    content: '▹';
    position: absolute;
    left: 0;
    color: #6366f1;
}

/* Badge */
.demo-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 14px;
}
.demo-badge--indigo {
    background: #312e81;
    color: #a5b4fc;
}
.demo-badge--green {
    background: #14532d;
    color: #86efac;
}
.demo-badge--red {
    background: #7f1d1d;
    color: #fca5a5;
}

/* Botões */
.demo-btn {
    display: block;
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition:
        opacity 0.15s,
        transform 0.15s;
}
.demo-btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}
.demo-btn:disabled {
    cursor: default;
    opacity: 0.65;
    transform: none;
}
.demo-btn--indigo {
    background: #4f46e5;
    color: white;
}
.demo-btn--green {
    background: #16a34a;
    color: white;
}
.demo-btn--red {
    background: #dc2626;
    color: white;
}
.demo-btn--sm {
    width: auto;
    padding: 8px 16px;
    font-size: 13px;
}

/* Code block */
.demo-code {
    background: #0f172a;
    border-radius: 8px;
    padding: 12px 16px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 14px;
    color: #38bdf8;
    margin-bottom: 12px;
}

/* Attacker */
.demo-attacker {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #0f172a;
    border: 1px solid #7f1d1d;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 16px;
}
.demo-attacker__avatar {
    font-size: 24px;
    width: 40px;
    height: 40px;
    background: #7f1d1d;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.demo-attacker__name {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    color: #fca5a5;
    font-weight: 700;
}

/* Spinner */
.demo-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #334155;
    border-top-color: #6366f1;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    margin-top: 8px;
}
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Cursor typewriter */
.demo-cursor {
    color: #6366f1;
    animation: blink 1s step-end infinite;
}
@keyframes blink {
    50% {
        opacity: 0;
    }
}

/* ── Terminal VERMELHO (hacker) ──────────────────────────────── */
.demo-terminal-wrapper {
    width: 100%;
    max-width: 680px;
}
.demo-terminal {
    background: #0a0000;
    border: 1px solid #ff3333;
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 0 60px rgba(255, 0, 0, 0.2),
        0 24px 64px rgba(0, 0, 0, 0.8);
}
.demo-terminal__bar {
    background: #1a0000;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 7px;
    border-bottom: 1px solid #330000;
}
.demo-terminal__dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
.demo-terminal__dot--red {
    background: #ff4444;
}
.demo-terminal__dot--yellow {
    background: #ffaa00;
}
.demo-terminal__dot--green {
    background: #00cc44;
}
.demo-terminal__title {
    flex: 1;
    text-align: center;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: #ff6666;
}
.demo-terminal__body {
    padding: 16px 18px;
    min-height: 200px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    line-height: 2;
}
.demo-terminal__line {
    display: block;
}
.demo-terminal__line--cmd {
    color: #ff6666;
}
.demo-terminal__line--out {
    color: #ff4444;
}
.demo-terminal__line--hi {
    color: #ffaaaa;
}
.demo-terminal__line--warn {
    color: #ff0000;
}
.demo-terminal__line--dim {
    color: #660000;
}
.demo-terminal__line--success {
    color: #ff8888;
    font-weight: 700;
}
.demo-terminal__line--info {
    color: #ffbb33;
}
.demo-terminal__cursor {
    display: inline-block;
    color: #ff0000;
    animation: blink 1s step-end infinite;
}
.demo-terminal__footer {
    background: #0a0000;
    border-top: 1px solid #330000;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.demo-terminal__status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: #ff0000;
    font-weight: 700;
}
.demo-terminal__status--checking {
    color: #fbbf24;
}
.demo-terminal__pulse {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ff0000;
    animation: pulse 1s ease-in-out infinite;
}
.demo-terminal__pulse--yellow {
    background: #fbbf24;
}
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.4;
        transform: scale(0.85);
    }
}
</style>
