#!/bin/bash
set -e

# === CONFIG ===
SERVER="u17-sczofusy6qgg@c9971.sgvps.net"
SSH_PORT="18765"
BRANCH="beta"
VERSION_FILE="php/version.txt"
# ==============

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Laad .env variabelen (voor Sentry credentials)
if [ -f "$REPO_ROOT/.env" ]; then
  export $(grep -v '^#' "$REPO_ROOT/.env" | grep -E 'SENTRY_' | xargs)
fi

# Zorg dat we op de juiste branch staan
CURRENT=$(git -C "$REPO_ROOT" branch --show-current)
if [ "$CURRENT" != "$BRANCH" ]; then
  echo "⚠️  Je bent niet op de '$BRANCH' branch (je zit op '$CURRENT')."
  read -p "Toch doorgaan? (yes/no): " confirm
  if [ "$confirm" != "yes" ]; then
    echo "Geannuleerd."
    exit 0
  fi
fi

# --- Automatische patch bump ---
CURRENT_VERSION=$(cat "$REPO_ROOT/$VERSION_FILE" | tr -d '\n\r ')
IFS='.' read -r MAJOR MINOR PATCH <<< "$CURRENT_VERSION"
PATCH=$((PATCH + 1))
NEW_VERSION="$MAJOR.$MINOR.$PATCH"

echo "📦 Versie: $CURRENT_VERSION → $NEW_VERSION"
echo "$NEW_VERSION" > "$REPO_ROOT/$VERSION_FILE"
git -C "$REPO_ROOT" add "$VERSION_FILE"
git -C "$REPO_ROOT" commit -m "chore: bump version to $NEW_VERSION"

echo "🚀 Deploying branch '$BRANCH' to UAT (lineup.webbit.be)..."

# Push de branch
git -C "$REPO_ROOT" push origin "$BRANCH"

# SSH in en deploy
ssh -p "$SSH_PORT" "$SERVER" bash << 'ENDSSH'
  set -e
  cd ~/www/lineup.webbit.be/public_html
  git fetch origin
  git checkout beta
  git checkout -- . 2>/dev/null || true   # Gooi server-side lokale wijzigingen weg (bv. log files)
  git clean -f -d --exclude='.htaccess' --exclude='site_version.txt' 2>/dev/null || true
  git pull origin beta
  echo "✅ Code bijgewerkt naar laatste commit op 'beta'"

  # Composer dependencies installeren (bv. Sentry SDK)
  composer install --no-dev --no-interaction --optimize-autoloader 2>&1 | tail -5
  echo "✅ Composer packages up-to-date"

  # Schrijf site_version.txt met -beta suffix (niet in git, heeft prioriteit in footer)
  BASE_VERSION=$(cat version.txt | tr -d '\n\r')
  echo "${BASE_VERSION}-beta" > site_version.txt
  echo "✅ Versie: ${BASE_VERSION}-beta"

  # Sentry & environment config via .htaccess (shared hosting, geen toegang tot php.ini)
  SENTRY_DSN="https://9d70aefea0f7ed519ed0baf6a741869a@o4511324428107776.ingest.de.sentry.io/4511324449013840"
  for line in "SetEnv APP_ENV staging" "SetEnv SENTRY_DSN $SENTRY_DSN" "php_value date.timezone Europe/Brussels" "php_value session.gc_maxlifetime 2592000" "php_value session.cookie_lifetime 2592000"; do
    key=$(echo "$line" | awk '{print $2}')
    if grep -q "$key" .htaccess 2>/dev/null; then
      sed -i "s|.*$key.*|$line|" .htaccess
    else
      echo "$line" >> .htaccess
    fi
  done
  echo "✅ Sentry env vars + timezone gezet in .htaccess (staging)"
ENDSSH

# Run migrations via HTTP
echo "🔄 Migrations uitvoeren via HTTP..."
MIGRATION_OUTPUT=$(curl -s "https://lineup.webbit.be/run_migrations.php?token=super_secret_deploy_key_2026")
echo "$MIGRATION_OUTPUT" | sed 's/<[^>]*>//g' | grep -v '^$' || true

echo ""
echo "✅ UAT deploy klaar → https://lineup.webbit.be (v$NEW_VERSION-beta)"

# --- Sentry Release ---
SENTRY_RELEASE="v${NEW_VERSION}-beta"
if command -v sentry-cli &>/dev/null && [ -n "$SENTRY_AUTH_TOKEN" ] && [ -n "$SENTRY_ORG" ]; then
  echo "📡 Sentry release aanmaken: $SENTRY_RELEASE"
  sentry-cli releases new "$SENTRY_RELEASE" --org "$SENTRY_ORG" --project "$SENTRY_PROJECT"
  sentry-cli releases set-commits "$SENTRY_RELEASE" --auto --org "$SENTRY_ORG"
  sentry-cli releases finalize "$SENTRY_RELEASE" --org "$SENTRY_ORG"
  sentry-cli releases deploys "$SENTRY_RELEASE" new -e staging --name "UAT deploy $(date '+%Y-%m-%d %H:%M')" --org "$SENTRY_ORG"
  echo "✅ Sentry release $SENTRY_RELEASE gekoppeld aan staging"
else
  echo "⚠️  Sentry release overgeslagen (sentry-cli niet gevonden of SENTRY_AUTH_TOKEN niet ingesteld)"
fi
