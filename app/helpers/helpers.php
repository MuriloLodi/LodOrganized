<?php

function usuarioId(): int {
    return (int)($_SESSION['usuario']['id'] ?? 0);
}

function normalizaValor($valor) {
    $v = trim((string)$valor);

    // remove R$, espaços etc (mantém dígitos, ponto, vírgula e sinal)
    $v = preg_replace('/[^\d,.\-]/', '', $v);

    if ($v === '' || $v === '-' || $v === '.' || $v === ',') return 0;

    // Se tem ponto e vírgula, assume BR: 1.234,56
    if (strpos($v, ',') !== false && strpos($v, '.') !== false) {
        $v = str_replace('.', '', $v);   // tira milhar
        $v = str_replace(',', '.', $v);  // vírgula vira decimal
        return (float)$v;
    }

    // Se só tem vírgula, é decimal BR: 12,34
    if (strpos($v, ',') !== false) {
        $v = str_replace(',', '.', $v);
        return (float)$v;
    }

    // Se só tem ponto, assume decimal EN: 12.34
    return (float)$v;
}
function uuidv4(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
function avatarUrl(?array $usuario): ?string
{
    if (empty($usuario['avatar']) || empty($usuario['id'])) return null;
    return "/financas/public/uploads/avatars/" . $usuario['id'] . "/" . $usuario['avatar'];
}

function iniciais(string $nome): string
{
    $nome = trim($nome);
    if ($nome === '') return 'U';
    $partes = preg_split('/\s+/', $nome);
    $ini = strtoupper(mb_substr($partes[0], 0, 1));
    if (count($partes) > 1) $ini .= strtoupper(mb_substr(end($partes), 0, 1));
    return $ini;
}
function imgToDataUri($absPath) {
  if (!$absPath || !is_file($absPath)) return null;
  $mime = mime_content_type($absPath);
  $data = base64_encode(file_get_contents($absPath));
  return "data:$mime;base64,$data";
}
