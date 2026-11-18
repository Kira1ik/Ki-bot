<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
requireLogin();
$currentTemp = number_format(OPENAI_TEMPERATURE, 1, '.', '');
$currentLang = ACTIVE_LANG;
$colors = AGENT_COLORS;
$ui = UI;
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?= htmlspecialchars(AGENT_NAME, ENT_QUOTES, 'UTF-8') ?></title>
<script>
(function(){
    try {
        var t = localStorage.getItem('theme');
        var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.setAttribute('data-theme', (t === 'dark' || (!t && d)) ? 'dark' : 'light');
    } catch(e) { document.documentElement.setAttribute('data-theme', 'light'); }
})();
</script>
<script>
window.MathJax = {
  tex: { inlineMath: [['$', '$'], ['\\(', '\\)']], displayMath: [['$$', '$$'], ['\\[', '\\]']], processEscapes: true }
};
</script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
<style>
:root {
    --agent-primary: <?= $colors['primary'] ?>;
    --agent-primary-dark: <?= $colors['primary-dark'] ?>;
    --agent-user-bubble: <?= $colors['user-bubble'] ?>;
    --agent-ai-bubble: <?= $colors['ai-bubble'] ?>;
    --agent-bg-main: <?= $colors['bg-main'] ?>;
    --agent-text: <?= $colors['text'] ?>;
}
</style>
</head>
<body>

<div class="app-wrapper">
  <aside class="sidebar" id="sidebar">
      <div class="sidebar__header">
          <div class="sidebar__title"><i class="fas fa-comments"></i> Historikk</div>
          <button class="sidebar__new-btn" id="sidebarNewChatBtn">
              <i class="fas fa-plus"></i> <?= $ui['new_chat'] ?>
          </button>
      </div>
      <div class="sidebar__list" id="chatList">
          <!-- Сюда будут загружаться чаты через JS -->
      </div>
  </aside>

  <div class="app">
    <header class="header">
      <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
      <div class="header__icon"><i class="fas fa-graduation-cap"></i></div>
      <div class="header__info">
        <h1 class="header__title"><?= htmlspecialchars(AGENT_NAME) ?></h1>
        <p class="header__subtitle"><?= htmlspecialchars(AGENT_DESCRIPTION) ?></p>
      </div>
      <div class="header__actions">
          <!-- НОВАЯ КНОПКА: Свернуть/Развернуть панель настроек -->
          <button id="settingsToggle" class="theme-toggle" aria-label="Innstillinger" title="Vis/skjul innstillinger">
              <i class="fas fa-sliders-h"></i>
          </button>
          <button id="themeToggle" class="theme-toggle" aria-label="Theme">
              <i class="fas fa-moon" id="themeIcon"></i>
          </button>
          <div class="header__status"><span class="header__status-dot"></span> <?= $ui['online'] ?></div>
          <a href="logout.php" class="header__logout" title="<?= $ui['logout'] ?>"><i class="fas fa-sign-out-alt"></i></a>
      </div>
    </header>

    <section class="chat-toolbar" id="chatToolbar">
      <form method="get" class="toolbar-form" id="settingsForm">
        <label><?= $ui['agent'] ?>
          <select name="agent" id="agentSelect">
            <?php foreach ($AGENTS as $id => $a): ?>
              <option value="<?= (int)$id ?>" <?= $id === ACTIVE_AGENT ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label><?= $ui['language'] ?>
          <select name="lang" id="languageSelect">
            <?php foreach ($LANGUAGES as $code => $name): ?>
              <option value="<?= $code ?>" <?= $code === $currentLang ? 'selected' : '' ?>><?= $name ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label><?= $ui['temp'] ?>
          <select name="temp" id="temperatureSelect">
            <option value="0.2" <?= $currentTemp === '0.2' ? 'selected' : '' ?>>0.2</option>
            <option value="0.7" <?= $currentTemp === '0.7' ? 'selected' : '' ?>>0.7</option>
            <option value="1.0" <?= $currentTemp === '1.0' ? 'selected' : '' ?>>1.0</option>
          </select>
        </label>
        <button type="submit" class="toolbar-button"><?= $ui['use'] ?></button>
      </form>
    </section>

    <main class="chat" id="chat" aria-live="polite">
      <div class="welcome" id="welcome">
        <div class="welcome__icon"><i class="fas fa-book-open"></i></div>
        <h2 class="welcome__title"><?= $ui['welcome'] ?> <?= htmlspecialchars(AGENT_NAME) ?></h2>
        <p class="welcome__text"><?= htmlspecialchars(AGENT_GREETING) ?></p>
      </div>
    </main>

    <footer class="input-area">
      <form class="input-area__form" id="chatForm">
        <textarea class="input-area__input" id="messageInput" placeholder="<?= $ui['placeholder'] ?>" rows="1" maxlength="5000"></textarea>
        <button type="submit" class="input-area__button" id="sendButton" disabled><i class="fas fa-paper-plane"></i></button>
      </form>
      <p class="input-area__hint"><?= $ui['hint'] ?></p>
    </footer>
  </div>
</div>

<script>
const UI = <?= json_encode($ui) ?>;
const AGENT_ID = <?= json_encode((string)ACTIVE_AGENT) ?>;
const STORAGE_LIST_KEY = `cozy-chats-list-${AGENT_ID}`;
const STORAGE_CONTENT_PREFIX = `cozy-chat-content-${AGENT_ID}-`;

let currentChatId = null;
let history = [];
let busy = false;

// === THEME & SIDEBAR & TOOLBAR ===
const htmlEl = document.documentElement;
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
if (htmlEl.getAttribute('data-theme') === 'dark') themeIcon.classList.replace('fa-moon', 'fa-sun');
themeToggle.addEventListener('click', () => {
    const isDark = htmlEl.getAttribute('data-theme') === 'dark';
    htmlEl.setAttribute('data-theme', isDark ? 'light' : 'dark');
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
    themeIcon.classList.replace(isDark ? 'fa-sun' : 'fa-moon', isDark ? 'fa-moon' : 'fa-sun');
});

const sidebar = document.getElementById('sidebar');
document.getElementById('sidebarToggle').addEventListener('click', () => sidebar.classList.toggle('open'));

// Сворачивание панели настроек
const settingsToggle = document.getElementById('settingsToggle');
const chatToolbar = document.getElementById('chatToolbar');
settingsToggle.addEventListener('click', () => {
    chatToolbar.classList.toggle('collapsed');
});

// === CHAT ===
const chat = document.getElementById('chat');
const form = document.getElementById('chatForm');
const input = document.getElementById('messageInput');
const send = document.getElementById('sendButton');
const welcome = document.getElementById('welcome');
const tempSel = document.getElementById('temperatureSelect');
const langSel = document.getElementById('languageSelect');
const agentSel = document.getElementById('agentSelect');
const chatListEl = document.getElementById('chatList');

const esc = v => String(v).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const fmt = v => {
    let s = esc(v);
    s = s.replace(/```([\w-]*)\n?([\s\S]*?)```/g, '<pre><code>$2</code></pre>');
    s = s.replace(/`([^`]+)`/g, '<code>$1</code>');
    s = s.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    s = s.replace(/\*([^*]+)\*/g, '<em>$1</em>');
    return s.split(/\n{2,}/).map(p => `<p>${p.replace(/\n/g,'<br>')}</p>`).join('');
};

// --- ИСТОРИЯ ---
function getChatList() {
    try { return JSON.parse(localStorage.getItem(STORAGE_LIST_KEY) || '[]'); } catch(e) { return []; }
}
function saveChatList(list) { localStorage.setItem(STORAGE_LIST_KEY, JSON.stringify(list)); }

function renderChatList() {
    const list = getChatList();
    chatListEl.innerHTML = '';

    if (list.length === 0) {
        chatListEl.innerHTML = `
            <div class="sidebar__empty">
                <i class="fas fa-ghost"></i>
                <p>Ingen samtaler ennå.<br>Start en ny for å komme i gang!</p>
            </div>`;
        return;
    }

    list.sort((a, b) => b.timestamp - a.timestamp);

    const groups = { today: [], yesterday: [], older: [] };
    const now = new Date();
    const todayStr = now.toDateString();
    const yesterdayStr = new Date(now.setDate(now.getDate() - 1)).toDateString();

    list.forEach(item => {
        const d = new Date(item.timestamp).toDateString();
        if (d === todayStr) groups.today.push(item);
        else if (d === yesterdayStr) groups.yesterday.push(item);
        else groups.older.push(item);
    });

    const groupNames = { today: 'I dag', yesterday: 'I går', older: 'Eldre' };

    for (const [key, items] of Object.entries(groups)) {
        if (items.length === 0) continue;

        const groupEl = document.createElement('div');
        groupEl.className = 'sidebar__group';
        
        const titleEl = document.createElement('div');
        titleEl.className = 'sidebar__group-title';
        titleEl.textContent = groupNames[key];
        groupEl.appendChild(titleEl);

        items.forEach(item => {
            const div = document.createElement('div');
            div.className = `chat-item ${item.id === currentChatId ? 'active' : ''}`;
            
            const icon = document.createElement('i');
            icon.className = 'fas fa-message chat-item__icon';
            
            const title = document.createElement('div');
            title.className = 'chat-item__title';
            title.textContent = item.title;
            
            const delBtn = document.createElement('button');
            delBtn.className = 'chat-item__delete';
            delBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
            delBtn.onclick = (e) => {
                e.stopPropagation();
                if(confirm('Slette denne samtalen?')) deleteChat(item.id);
            };

            div.appendChild(icon);
            div.appendChild(title);
            div.appendChild(delBtn);
            div.onclick = () => loadChat(item.id);
            groupEl.appendChild(div);
        });

        chatListEl.appendChild(groupEl);
    }
}

function deleteChat(id) {
    let list = getChatList().filter(c => c.id !== id);
    saveChatList(list);
    localStorage.removeItem(STORAGE_CONTENT_PREFIX + id);
    if (currentChatId === id) startNewChat();
    else renderChatList();
}

function startNewChat() {
    if (busy) return;
    currentChatId = null;
    history = [];
    chat.querySelectorAll('.message, .error, .typing').forEach(el => el.remove());
    if (welcome) welcome.style.display = '';
    input.value = ''; input.style.height = 'auto'; updateSend(); input.focus();
    renderChatList();
    if (window.innerWidth <= 768) sidebar.classList.remove('open');
}

function loadChat(id) {
    if (busy || currentChatId === id) return;
    currentChatId = id;
    history = [];
    chat.querySelectorAll('.message, .error, .typing').forEach(el => el.remove());
    if (welcome) welcome.style.display = 'none';
    
    try {
        const saved = JSON.parse(localStorage.getItem(STORAGE_CONTENT_PREFIX + id) || '[]');
        if (Array.isArray(saved)) {
            history = saved;
            history.forEach(m => addMessage(m.content, m.role === 'assistant' ? 'ai' : 'user', false));
        }
    } catch(e) {}
    
    renderChatList();
    if (window.innerWidth <= 768) sidebar.classList.remove('open');
}

function persist() {
    if (!currentChatId) {
        currentChatId = Date.now().toString();
        const list = getChatList();
        const firstMsg = history.find(m => m.role === 'user')?.content || 'Ny samtale';
        list.push({ id: currentChatId, title: firstMsg.substring(0, 30) + (firstMsg.length > 30 ? '...' : ''), timestamp: Date.now() });
        saveChatList(list);
        renderChatList();
    }
    localStorage.setItem(STORAGE_CONTENT_PREFIX + currentChatId, JSON.stringify(history.slice(-50)));
}

// --- ОТПРАВКА ---
function addMessage(content, role, save = false) {
    if (welcome) welcome.style.display = 'none';
    const item = document.createElement('div');
    item.className = `message message--${role === 'user' ? 'user' : 'ai'}`;
    item.innerHTML = `<div class="message__avatar"><i class="fas fa-${role === 'user' ? 'user' : 'robot'}"></i></div><div class="message__content">${fmt(content)}</div>`;
    chat.appendChild(item);
    if (window.MathJax && window.MathJax.typesetPromise) {
        MathJax.typesetPromise([item]).catch(err => console.log(err));
    }
    if (save) { history.push({role, content}); persist(); }
    chat.scrollTop = chat.scrollHeight;
}

function updateSend() { send.disabled = busy || !input.value.trim(); }
input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 150) + 'px';
    updateSend();
});
input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
});

document.getElementById('sidebarNewChatBtn').addEventListener('click', startNewChat);

form.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg || busy) return;
    
    addMessage(msg, 'user', true);
    input.value = ''; input.style.height = 'auto';
    busy = true; updateSend();
    
    const typing = document.createElement('div');
    typing.className = 'typing'; typing.textContent = UI.thinking;
    chat.appendChild(typing); chat.scrollTop = chat.scrollHeight;
    
    try {
        const res = await fetch('api/chat.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                message: msg,
                history: history.slice(-12),
                temperature: tempSel.value,
                language: langSel.value,
                agent_id: parseInt(agentSel.value, 10)
            })
        });
        const data = await res.json();
        typing.remove();
        if (!res.ok || data.error) throw new Error(data.error || `Feil ${res.status}`);
        addMessage(data.response, 'assistant', true);
    } catch (err) {
        typing.remove();
        const e = document.createElement('div');
        e.className = 'error'; e.textContent = err.message || 'Tilkoblingsfeil';
        chat.appendChild(e); chat.scrollTop = chat.scrollHeight;
    } finally {
        busy = false; updateSend(); input.focus();
    }
});

renderChatList();
updateSend();
</script>
</body>
</html>