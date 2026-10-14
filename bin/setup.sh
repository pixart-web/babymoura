#!/usr/bin/env bash
# Instala a proposta BabyMoura num ambiente local limpo e importa os conteúdos.
# Uso: bin/setup.sh            (instala se necessário e importa conteúdos em falta)
#      bin/setup.sh --reset    (apaga base de dados e ficheiros locais e reinstala)
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || cp .env.example .env
# shellcheck disable=SC1091
set -a; . ./.env; set +a
if docker compose version >/dev/null 2>&1; then COMPOSE="docker compose"; else COMPOSE="docker-compose"; fi
PORT="${WP_PORT:-8090}"
URL="http://localhost:${PORT}"

if [ "${1:-}" = "--reset" ]; then
  $COMPOSE down -v --remove-orphans
fi

$COMPOSE up -d db wordpress mailpit

echo "A aguardar o WordPress em ${URL} ..."
for _ in $(seq 1 60); do
  if curl -s -o /dev/null "${URL}/wp-login.php"; then break; fi
  sleep 2
done

wp() { $COMPOSE run --rm -T cli wp "$@"; }

if ! wp core is-installed >/dev/null 2>&1; then
  ADMIN_PASS="$(openssl rand -hex 12)"
  wp core install --url="${URL}" --title="BabyMoura" \
    --admin_user=babymoura-admin --admin_password="${ADMIN_PASS}" \
    --admin_email=admin@babymoura.test --skip-email
  printf 'URL=%s/wp-admin/\nUSER=babymoura-admin\nPASS=%s\n' "${URL}" "${ADMIN_PASS}" > .credentials.local
  chmod 600 .credentials.local
  echo "Credenciais locais guardadas em .credentials.local (não versionar)."
fi

wp language core install pt_PT --activate || echo "Aviso: pacote pt_PT indisponível (sem rede?). O tema e o plugin já estão em português."
wp option update timezone_string "Europe/Lisbon"
wp option update date_format "j \\d\\e F \\d\\e Y"
wp option update blogdescription "Creche e Jardim de Infância em Vilamoura"
wp rewrite structure '/%postname%/' --hard
wp theme activate babymoura
wp plugin activate babymoura-core
wp plugin delete akismet hello >/dev/null 2>&1 || true
wp babymoura seed
wp rewrite flush --hard
wp babymoura status

echo
echo "Pronto: ${URL}"
echo "Emails locais (Mailpit): http://localhost:${MAILPIT_PORT:-8026}"
