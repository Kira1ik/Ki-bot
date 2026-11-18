<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error'=>'Method not allowed']); exit; }

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$message = trim($input['message'] ?? '');
$history = $input['history'] ?? [];
$temperature = isset($input['temperature']) ? (float)$input['temperature'] : OPENAI_TEMPERATURE;
$langCode = $input['language'] ?? 'no';
$agentId = isset($input['agent_id']) ? (int)$input['agent_id'] : ACTIVE_AGENT;

if ($message === '') { echo json_encode(['error'=>'Empty message']); exit; }
if (empty(OPENAI_API_KEY)) { echo json_encode(['error'=>'API-nøkkel mangler']); exit; }
if (!isset($AGENTS[$agentId])) $agentId = 1;

// Жёсткая карта языков (не зависит от config.php scope)
$langMap = ['no' => 'Norwegian (Bokmål)', 'en' => 'English', 'ru' => 'Russian'];
$langName = $langMap[$langCode] ?? 'Norwegian (Bokmål)';

// Берём промпт именно того агента, что прислал клиент
$agentPrompt = $AGENTS[$agentId]['prompt'];

// Инструкция ДО и ПОСЛЕ промпта — модель не сможет проигнорировать
$systemPrompt = 
    ">>> MANDATORY: Your ENTIRE response MUST be in {$langName}. Not Norwegian unless explicitly {$langName}. Do NOT mix languages. <<<\n\n" .
    $agentPrompt .
    "\n\n>>> FINAL REMINDER: Respond ONLY in {$langName}. If you respond in any other language, you have failed the task. <<<";

$messages = [['role'=>'system','content'=>$systemPrompt]];
foreach ($history as $m) {
    if (isset($m['role'], $m['content']) && in_array($m['role'], ['user','assistant'])) {
        $messages[] = ['role'=>$m['role'], 'content'=>$m['content']];
    }
}
$messages[] = ['role'=>'user', 'content'=>$message];

$payload = ['model'=>OPENAI_MODEL, 'messages'=>$messages, 'temperature'=>$temperature, 'max_tokens'=>OPENAI_MAX_TOKENS];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . OPENAI_API_KEY],
    CURLOPT_TIMEOUT => 60
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    $err = json_decode($response, true);
    echo json_encode(['error' => 'OpenAI: ' . ($err['error']['message'] ?? 'Ukjent feil')]);
    exit;
}
$data = json_decode($response, true);
echo json_encode(['response' => $data['choices'][0]['message']['content'] ?? 'Tomt svar fra AI.']);