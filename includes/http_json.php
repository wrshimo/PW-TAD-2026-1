<?php
// Funções utilitárias para respostas JSON e leitura de payload bruto.

function json_response($data, int $status = 200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
  exit;
}

function get_request_body_params(): array {
  // Suporta: application/x-www-form-urlencoded (PUT) e JSON
  $raw = file_get_contents('php://input');
  $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

  if (str_contains($contentType, 'application/json')) {
    $parsed = json_decode($raw, true);
    return is_array($parsed) ? $parsed : [];
  }

  // default: tenta parse_str (x-www-form-urlencoded)
  $data = [];
  parse_str($raw, $data);
  return is_array($data) ? $data : [];
}

function require_fields(array $data, array $required): array {
  $missing = [];
  foreach ($required as $field) {
    if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
      $missing[] = $field;
    }
  }
  return $missing;
}