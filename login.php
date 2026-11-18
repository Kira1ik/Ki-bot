<?php
require_once 'auth.php';
require_once 'config.php';

$error = '';

// Håndter innlogging
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Feil brukernavn eller passord';
    }
}

// Hvis allerede innlogget, redirect til chat
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$colors = AGENT_COLORS;
$ui = UI;
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Logg inn - <?= htmlspecialchars(AGENT_NAME) ?></title>
    
    <!-- Скрипт для темы (без мерцания) -->
    <script>
    (function(){
        try {
            var t = localStorage.getItem('theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', (t === 'dark' || (!t && d)) ? 'dark' : 'light');
        } catch(e) { document.documentElement.setAttribute('data-theme', 'light'); }
    })();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* Динамические цвета агента */
        :root {
            --agent-primary: <?= $colors['primary'] ?>;
            --agent-primary-dark: <?= $colors['primary-dark'] ?>;
            --agent-ai-bubble: <?= $colors['ai-bubble'] ?>;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-body);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            z-index: 10;
        }
        
        .login-card {
            background: var(--bg-app);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: var(--radius);
            padding: 3rem 2.5rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .login-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, var(--agent-primary), var(--agent-primary-dark));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
            color: white;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        
        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-left: 0.2rem;
        }
        
        .form-input {
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 1rem;
            color: var(--text-primary);
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--agent-primary);
            box-shadow: 0 0 0 4px var(--agent-ai-bubble);
            background: var(--bg-app);
        }
        
        .login-button {
            background: linear-gradient(135deg, var(--agent-primary), var(--agent-primary-dark));
            border: none;
            border-radius: var(--radius-sm);
            padding: 1rem;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }

        .login-button:active {
            transform: translateY(0);
        }
        
        .login-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #EF4444;
            padding: 0.875rem 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        /* === АДАПТИВ ДЛЯ ТЕЛЕФОНОВ === */
        @media (max-width: 480px) {
            .login-container {
                padding: 1rem;
                height: 100%;
                display: flex;
                align-items: center;
            }
            .login-card {
                padding: 2rem 1.5rem;
                border-radius: 16px;
            }
            .login-header {
                margin-bottom: 1.5rem;
            }
            .login-icon {
                width: 60px;
                height: 60px;
                font-size: 1.6rem;
                border-radius: 16px;
                margin-bottom: 1rem;
            }
            .login-title {
                font-size: 1.5rem;
            }
            .login-subtitle {
                font-size: 0.85rem;
            }
            .form-input {
                padding: 0.85rem;
                font-size: 16px; /* Предотвращает авто-зум на iPhone */
            }
            .login-button {
                padding: 0.85rem;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <h1 class="login-title"><?= htmlspecialchars(AGENT_NAME) ?></h1>
                <p class="login-subtitle">Logg inn for å starte chatten</p>
            </div>
            
            <?php if ($error): ?>
                <div class="login-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form class="login-form" method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">Brukernavn</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        placeholder="Skriv brukernavn..."
                        required
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Passord</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Skriv passord..."
                        required
                    >
                </div>
                
                <button type="submit" class="login-button">
                    <i class="fas fa-sign-in-alt"></i> Logg inn
                </button>
            </form>
        </div>
    </div>
</body>
</html>