#!/bin/bash
set -e

# === CONFIG ===
SERVER="u18-qysfjqo1g9od@c9971.sgvps.net"
SSH_PORT="18765"
BRANCH="main"
# ==============

# Laad .env variabelen (voor Sentry credentials)
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
if [ -f "$REPO_ROOT/.env" ]; then
  export $(grep -v '^#' "$REPO_ROOT/.env" | grep -E 'SENTRY_' | xargs)
fi


# Veiligheidscheck: zorg dat je op main staat
CURRENT=$(git branch --show-current)
if [ "$CURRENT" != "$BRANCH" ]; then
  echo "❌ Je bent niet op de '$BRANCH' branch (je zit op '$CURRENT')."
  echo "   Doe eerst: git checkout main && git merge beta"
  exit 1
fi

echo "⚠️  Je staat op het punt te deployen naar PRODUCTIE (lineupheroes.com)!"
echo "   Branch: $BRANCH"
echo "   Laatste commit: $(git log --oneline -1)"
echo ""
read -p "Ben je zeker? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
  echo "Geannuleerd."
  exit 0
fi

echo "🚀 Deploying branch '$BRANCH' to PROD (lineupheroes.com)..."

git push origin "$BRANCH"

ssh -p "$SSH_PORT" "$SERVER" bash << 'ENDSSH'
  set -e
  cd ~/www/lineupheroes.com/public_html
  git fetch origin
  git checkout main
  git checkout -- . 2>/dev/null || true   # Gooi server-side lokale wijzigingen weg (bv. log files)
  git clean -f -d --exclude='.htaccess' --exclude='site_version.txt' 2>/dev/null || true
  git pull origin main
  echo "✅ Code bijgewerkt naar laatste commit op 'main'"

  # Composer dependencies installeren (bv. Sentry SDK)
  composer install --no-dev --no-interaction --optimize-autoloader 2>&1 | tail -5
  echo "✅ Composer packages up-to-date"

  # Schrijf site_version.txt met clean versienummer (niet in git, heeft prioriteit in footer)
  BASE_VERSION=$(cat version.txt | tr -d '\n\r')
  echo "${BASE_VERSION}" > site_version.txt
  echo "✅ Versie: ${BASE_VERSION}"

  # Sentry & environment config via .htaccess (shared hosting, geen toegang tot php.ini)
  SENTRY_DSN="https://9d70aefea0f7ed519ed0baf6a741869a@o4511324428107776.ingest.de.sentry.io/4511324449013840"
  for line in "SetEnv APP_ENV production" "SetEnv SENTRY_DSN $SENTRY_DSN" "php_value date.timezone Europe/Brussels" "php_value session.gc_maxlifetime 2592000" "php_value session.cookie_lifetime 2592000"; do
    key=$(echo "$line" | awk '{print $2}')
    if grep -q "$key" .htaccess 2>/dev/null; then
      sed -i "s|.*$key.*|$line|" .htaccess
    else
      echo "$line" >> .htaccess
    fi
  done
  echo "✅ Sentry env vars + timezone gezet in .htaccess (production)"
ENDSSH

# Run migrations via HTTP
echo "🔄 Migrations uitvoeren via HTTP..."
MIGRATION_OUTPUT=$(curl -sk --max-time 30 "https://lineupheroes.com/run_migrations.php?token=super_secret_deploy_key_2026")
echo "$MIGRATION_OUTPUT" | sed 's/<[^>]*>//g' | grep -v '^$' || true

echo ""
echo "✅ PROD deploy klaar → https://lineupheroes.com"

# --- Sentry Release ---
PROD_VERSION=$(cat "$REPO_ROOT/php/version.txt" | tr -d '\n\r ')
SENTRY_RELEASE="v${PROD_VERSION}"
if command -v sentry-cli &>/dev/null && [ -n "$SENTRY_AUTH_TOKEN" ] && [ -n "$SENTRY_ORG" ]; then
  echo "📡 Sentry release aanmaken: $SENTRY_RELEASE"
  sentry-cli releases new "$SENTRY_RELEASE" --org "$SENTRY_ORG" --project "$SENTRY_PROJECT"
  sentry-cli releases set-commits "$SENTRY_RELEASE" --auto --org "$SENTRY_ORG"
  sentry-cli releases finalize "$SENTRY_RELEASE" --org "$SENTRY_ORG"
  sentry-cli releases deploys "$SENTRY_RELEASE" new -e production --org "$SENTRY_ORG"
  echo "✅ Sentry release $SENTRY_RELEASE gekoppeld aan production"
else
  echo "⚠️  Sentry release overgeslagen (sentry-cli niet gevonden of SENTRY_AUTH_TOKEN niet ingesteld)"
fi
