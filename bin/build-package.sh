#!/usr/bin/env bash
# Gera um pacote "carregar e usar" do site para um domínio, sem necessidade de criar base de dados:
# WordPress + tema + plugin + conteúdos já importados, com base de dados SQLite incluída.
#
# Uso: bin/build-package.sh babymoura.pixart.pt
# Resultado: dist/<dominio>.zip (conteúdo para a raiz do subdomínio) e dist/<dominio>-credenciais.txt (NÃO carregar)
set -euo pipefail
cd "$(dirname "$0")/.."

DOMAIN="${1:?Indique o domínio, ex.: babymoura.pixart.pt}"
URL="https://${DOMAIN}"
OUT="dist/${DOMAIN}"
WEB="${OUT}/public"
WP_IMAGE="wordpress:7.1-php8.3-apache"
CLI_IMAGE="wordpress:cli-php8.3"

rm -rf "${OUT}" "dist/${DOMAIN}.zip"
mkdir -p "${WEB}"
ABS_WEB="$(cd "${WEB}" && pwd)"

echo "1/6 WordPress core"
docker run --rm -v "${ABS_WEB}:/out" "${WP_IMAGE}" sh -c 'cp -a /usr/src/wordpress/. /out/ && chown -R $(stat -c %u:%g /out) /out' >/dev/null
rm -rf "${WEB}/wp-content/plugins/akismet" "${WEB}/wp-content/plugins/hello.php" "${WEB}"/wp-content/themes/twenty* "${WEB}/wp-config-sample.php" "${WEB}/readme.html" "${WEB}/license.txt"

echo "2/6 Tema, plugin e integração SQLite"
cp -R wp-content/themes/babymoura "${WEB}/wp-content/themes/"
cp -R wp-content/plugins/babymoura-core "${WEB}/wp-content/plugins/"
curl -sSL -o "${OUT}/sqlite.zip" https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip
unzip -q "${OUT}/sqlite.zip" -d "${WEB}/wp-content/plugins/" && rm "${OUT}/sqlite.zip"
sed -e "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#' . __DIR__ . '/plugins/sqlite-database-integration#" \
    -e "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
    "${WEB}/wp-content/plugins/sqlite-database-integration/db.copy" > "${WEB}/wp-content/db.php"
mkdir -p "${WEB}/wp-content/database"
printf 'Require all denied\nDeny from all\n' > "${WEB}/wp-content/database/.htaccess"
printf '<?php // Silêncio.\n' > "${WEB}/wp-content/database/index.php"

echo "3/6 Configuração"
PREVIEW_USER="babymoura"
PREVIEW_PASS="$(openssl rand -hex 8)"
ADMIN_PASS="$(openssl rand -hex 12)"
salt() { openssl rand -base64 48 | tr -d '\n/+=' ; }
cat > "${WEB}/wp-config.php" <<PHP
<?php
/**
 * Configuração do preview BabyMoura (${DOMAIN}).
 * Base de dados SQLite em wp-content/database/ (protegida por .htaccess).
 */

define( 'DB_ENGINE', 'sqlite' );
define( 'DB_DIR', __DIR__ . '/wp-content/database/' );
define( 'DB_FILE', '.ht.sqlite' );
define( 'DB_NAME', 'babymoura' );
define( 'DB_USER', '' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', '' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         '$(salt)' );
define( 'SECURE_AUTH_KEY',  '$(salt)' );
define( 'LOGGED_IN_KEY',    '$(salt)' );
define( 'NONCE_KEY',        '$(salt)' );
define( 'AUTH_SALT',        '$(salt)' );
define( 'SECURE_AUTH_SALT', '$(salt)' );
define( 'LOGGED_IN_SALT',   '$(salt)' );
define( 'NONCE_SALT',       '$(salt)' );

\$table_prefix = 'wp_';

define( 'WP_HOME', '${URL}' );
define( 'WP_SITEURL', '${URL}' );
define( 'WP_ENVIRONMENT_TYPE', 'staging' );

// Proposta: formulários em demonstração (não enviam nem guardam dados).
define( 'BABYMOURA_MODE', 'demo' );
define( 'BABYMOURA_FORM_RECIPIENT', '' );
define( 'BABYMOURA_FORM_FROM', '' );
define( 'BABYMOURA_SMTP_HOST', '' );
define( 'BABYMOURA_SMTP_PORT', 587 );
define( 'BABYMOURA_SMTP_USER', '' );
define( 'BABYMOURA_SMTP_PASS', '' );
define( 'BABYMOURA_SMTP_SECURE', 'tls' );

// Preview privado (HTTP Basic). Para abrir ao público: deixar vazio.
define( 'BABYMOURA_PREVIEW_AUTH', '${PREVIEW_USER}:${PREVIEW_PASS}' );

define( 'DISALLOW_FILE_EDIT', true );
define( 'WP_POST_REVISIONS', 20 );
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'FS_METHOD', 'direct' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
PHP

cat > "${WEB}/.htaccess" <<'HT'
# BabyMoura — preview
Options -Indexes

# Passa o cabeçalho de autenticação ao PHP (necessário em CGI/FPM para o preview protegido).
<IfModule mod_setenvif.c>
SetEnvIf Authorization "(.+)" HTTP_AUTHORIZATION=$1
</IfModule>

# Nunca servir a base de dados nem ficheiros internos.
<FilesMatch "(^\.ht|\.sqlite|\.sqlite-journal|wp-config\.php)$">
	Require all denied
</FilesMatch>

# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress

<IfModule mod_expires.c>
ExpiresActive On
ExpiresByType image/webp "access plus 30 days"
ExpiresByType image/png "access plus 30 days"
ExpiresByType font/woff2 "access plus 1 year"
ExpiresByType text/css "access plus 7 days"
ExpiresByType application/javascript "access plus 7 days"
</IfModule>
<IfModule mod_deflate.c>
AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json image/svg+xml
</IfModule>
HT

echo "4/6 Instalação e conteúdos (URL ${URL})"
wp() { docker run --rm -i -u "$(id -u):$(id -g)" -e HOME=/tmp -v "${ABS_WEB}:/var/www/html" "${CLI_IMAGE}" wp "$@"; }
wp core install --url="${URL}" --title="BabyMoura" --admin_user=babymoura-admin --admin_password="${ADMIN_PASS}" --admin_email=admin@babymoura.test --skip-email
wp language core install pt_PT --activate
wp option update timezone_string "Europe/Lisbon" >/dev/null
wp option update date_format "j \\d\\e F \\d\\e Y" >/dev/null
wp option update blogdescription "Creche e Jardim de Infância em Vilamoura" >/dev/null
wp rewrite structure '/%postname%/' >/dev/null
wp plugin activate sqlite-database-integration babymoura-core >/dev/null
wp theme activate babymoura >/dev/null
wp babymoura seed
wp rewrite flush >/dev/null
wp babymoura status

echo "5/6 Permissões"
find "${WEB}" -type d -exec chmod 755 {} +
find "${WEB}" -type f -exec chmod 644 {} +
chmod 755 "${WEB}/wp-content/database"
chmod 664 "${WEB}/wp-content/database/.ht.sqlite"

echo "6/6 Arquivo"
( cd "${WEB}" && zip -qr -X "../../${DOMAIN}.zip" . )
cat > "dist/${DOMAIN}-credenciais.txt" <<TXT
Preview BabyMoura — ${URL}
NÃO carregar este ficheiro para o servidor.

Acesso ao site (janela de autenticação do browser)
  Utilizador: ${PREVIEW_USER}
  Palavra-passe: ${PREVIEW_PASS}

Painel WordPress: ${URL}/wp-admin/
  Utilizador: babymoura-admin
  Palavra-passe: ${ADMIN_PASS}
  (Alterar em Utilizadores → Perfil depois do primeiro acesso.)
TXT
chmod 600 "dist/${DOMAIN}-credenciais.txt"
ls -lh "dist/${DOMAIN}.zip"
echo "Credenciais: dist/${DOMAIN}-credenciais.txt"
