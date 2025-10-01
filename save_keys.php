<?php
// save_keys.php
// Recebe POST JSON: { "keys": ["ABC","DEF"], "token": "opcional" }
// Salva no arquivo 'keys' (sem extensão) no mesmo diretório.
// ATENÇÃO: configure $USE_TOKEN e $TOKEN_VALUE para ativar autenticação simples.

header('Content-Type: application/json');

// --- CONFIGURAÇÃO ---
$USE_TOKEN = false;               // true para exigir token (senha simples)
$TOKEN_VALUE = 'coloque_senha';   // token que o frontend deve enviar (se ativado)
// -----------------------

$raw = file_get_contents('php://input');
if(!$raw){
  echo json_encode(['ok'=>false,'error'=>'no body']); exit;
}
$data = json_decode($raw, true);
if(!$data || !isset($data['keys']) || !is_array($data['keys'])){ echo json_encode(['ok'=>false,'error'=>'invalid payload']); exit; }

// token simples (opcional)
if($USE_TOKEN){
  if(!isset($data['token']) || $data['token'] !== $TOKEN_VALUE){
    echo json_encode(['ok'=>false,'error'=>'unauthorized']); exit;
  }
}

// validação: apenas A-Z a-z 0-9 e tamanho entre 4 e 128 (ajuste se quiser)
$allowedPattern = '/^[A-Za-z0-9]{4,128}$/';
$toAppend = '';
foreach($data['keys'] as $k){
  $k = trim($k);
  if($k === '') continue;
  if(!preg_match($allowedPattern, $k)){
    echo json_encode(['ok'=>false,'error'=>'invalid key format']); exit;
  }
  $toAppend .= $k . PHP_EOL;
}

if($toAppend === '') { echo json_encode(['ok'=>false,'error'=>'no valid keys']); exit; }

$file = __DIR__ . DIRECTORY_SEPARATOR . 'keys'; // arquivo sem extensão
// cria arquivo se não existir e tenta gravar (append) com LOCK_EX
$ret = @file_put_contents($file, $toAppend, FILE_APPEND | LOCK_EX);

if($ret === false){
  echo json_encode(['ok'=>false,'error'=>'write failed']);
}else{
  echo json_encode(['ok'=>true]);
}
?>
