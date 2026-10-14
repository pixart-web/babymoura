#!/usr/bin/env bash
# Testa o modo de produção localmente, sem enviar nada para fora:
#  1) sem destinatário -> 503 "not_configured" (nada guardado)
#  2) com destinatário e SMTP = Mailpit -> 200, pedido guardado e email capturado no Mailpit
#  3) SMTP indisponível -> 200 (pedido guardado), notificação "falhou" e repetição agendada
# No fim apaga os pedidos de teste e repõe o modo demonstração.
set -euo pipefail
cd "$(dirname "$0")/.."
if docker compose version >/dev/null 2>&1; then DC="docker compose"; else DC="docker-compose"; fi
URL="http://localhost:${WP_PORT:-8090}"
MP="http://localhost:${MAILPIT_PORT:-8026}"
wp() { $DC run --rm -T cli wp "$@" 2>/dev/null; }
restart() { env "$@" $DC up -d --force-recreate wordpress >/dev/null 2>&1; for _ in $(seq 1 30); do curl -s -o /dev/null "$URL/wp-login.php" && break; sleep 1; done; }
submit() {
  local tok; tok=$(curl -s "$URL/wp-json/babymoura/v1/forms/token" | sed 's/.*"token":"\([^"]*\)".*/\1/')
  sleep 3.2
  curl -s -o /tmp/bm-res.json -w "%{http_code}" -H 'Content-Type: application/json' \
    -d "{\"bm_token\":\"$tok\",\"bm_request_id\":\"$1\",\"name\":\"Teste Producao\",\"email\":\"$2\",\"stage\":\"creche\",\"message\":\"Pedido de teste automatico.\"}" \
    "$URL/wp-json/babymoura/v1/forms/visita"
}
pass() { echo "  ✓ $*"; }
fail() { echo "  ✗ $*"; exit 1; }

cleanup() { for p in $(wp post list --post_type=bm_pedido --post_status=any --format=ids); do wp post delete "$p" --force >/dev/null; done; wp cron event delete bm_retry_notification >/dev/null 2>&1 || true; wp transient delete --all >/dev/null; curl -s -X DELETE "$MP/api/v1/messages" >/dev/null; }
cleanup

echo "1) Produção sem destinatário"
restart BABYMOURA_MODE=production BABYMOURA_FORM_RECIPIENT=
code=$(submit prod-1 "familia1@exemplo.pt")
[ "$code" = 503 ] && grep -q not_configured /tmp/bm-res.json && pass "503 not_configured" || fail "esperado 503, obtido $code"
[ "$(wp post list --post_type=bm_pedido --post_status=any --format=count)" = 0 ] && pass "nada guardado" || fail "pedido guardado indevidamente"

echo "2) Produção com destinatário (SMTP = Mailpit local)"
restart BABYMOURA_MODE=production BABYMOURA_FORM_RECIPIENT=pedidos@babymoura.test BABYMOURA_FORM_FROM=website@babymoura.test
code=$(submit prod-2 "familia2@exemplo.pt")
[ "$code" = 200 ] && grep -q '"status":"ok"' /tmp/bm-res.json && pass "200 ok" || fail "esperado 200, obtido $code $(cat /tmp/bm-res.json)"
grep -q "uma marca" /tmp/bm-res.json && pass "mensagem não confirma visita" || fail "mensagem de sucesso inadequada"
id=$(wp post list --post_type=bm_pedido --post_status=private --format=ids | awk '{print $1}')
[ -n "$id" ] && pass "pedido guardado (#$id)" || fail "pedido não guardado"
[ "$(wp post meta get "$id" bm_mail)" = enviado ] && pass "notificação enviada" || fail "estado de notificação errado"
sleep 1
msgs=$(curl -s "$MP/api/v1/messages")
echo "$msgs" | grep -q "pedidos@babymoura.test" && pass "email capturado no Mailpit" || fail "email não chegou ao Mailpit"
echo "$msgs" | grep -q "familia2@exemplo.pt" && pass "Reply-To aponta para a família" || fail "Reply-To em falta"
code=$(submit prod-2 "familia2@exemplo.pt")
grep -q '"status":"duplicate"' /tmp/bm-res.json && pass "reenvio tratado como duplicado" || fail "duplicado não detetado ($code)"
[ "$(wp post list --post_type=bm_pedido --post_status=any --format=count)" = 1 ] && pass "sem registo duplicado" || fail "registo duplicado"

echo "3) Produção com SMTP indisponível"
restart BABYMOURA_MODE=production BABYMOURA_FORM_RECIPIENT=pedidos@babymoura.test BABYMOURA_SMTP_HOST=smtp-inexistente.invalid
code=$(submit prod-3 "familia3@exemplo.pt")
[ "$code" = 200 ] && pass "200: pedido aceite porque ficou guardado" || fail "esperado 200, obtido $code"
id3=$(wp post list --post_type=bm_pedido --post_status=private --format=ids --orderby=ID --order=DESC | awk '{print $1}')
[ "$(wp post meta get "$id3" bm_mail)" = falhou ] && pass "falha de email registada no pedido" || fail "falha de email não registada"
wp cron event list --fields=hook --format=csv | grep -q bm_retry_notification && pass "nova tentativa agendada" || fail "sem nova tentativa"
$DC logs --since 2m wordpress 2>&1 | grep -q "familia3@exemplo.pt" && fail "dados pessoais encontrados nos logs" || pass "logs sem dados pessoais"

echo "Limpeza e reposição do modo demonstração"
cleanup
restart
echo "Concluído: modo $(wp eval 'echo bm_mode();')"
