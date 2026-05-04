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

# ── WORKFLOW GUARD: Controleer of main alle commits van beta bevat ────────────
# Alle commits op beta moeten bereikbaar zijn vanuit main.
# Als dat niet het geval is, zijn er commits op main die nooit via beta zijn gegaan.
BETA_NOT_IN_MAIN=$(git log main..beta --oneline 2>/dev/null | wc -l | tr -d ' ')
MAIN_COMMITS_NOT_VIA_BETA=$(git log beta..main --oneline 2>/dev/null | wc -l | tr -d ' ')

if [ "$MAIN_COMMITS_NOT_VIA_BETA" -gt 0 ]; then
  echo ""
  echo "🚫 STOP — WORKFLOW FOUT GEDETECTEERD"
  echo "   Er staan $MAIN_COMMITS_NOT_VIA_BETA commit(s) op 'main' die NIET via 'beta' zijn gegaan:"
  git log beta..main --oneline
  echo ""
  echo "   De correcte workflow is:"
  echo "   1. feature/* branch → merge naar beta"
  echo "   2. deploy-uat.sh (testen op UAT)"
  echo "   3. git checkout main && git merge beta"
  echo "   4. deploy-prod.sh"
  echo ""
  read -p "Wil je toch doorgaan? Dit is enkel aanvaardbaar bij hotfixes. (yes/no): " force_confirm
  if [ "$force_confirm" != "yes" ]; then
    echo "Geannuleerd. Merge je wijzigingen eerst via beta."
    exit 1
  fi
  echo "⚠️  Doorgegaan op eigen verantwoordelijkheid."
fi

if [ "$BETA_NOT_IN_MAIN" -gt 0 ]; then
  echo ""
  echo "⚠️  WAARSCHUWING: beta heeft $BETA_NOT_IN_MAIN commit(s) die nog NIET in main zitten."
  echo "   Overweeg: git merge beta -- zodat UAT en PROD synchroon zijn."
  echo ""
fi
# ─────────────────────────────────────────────────────────────────────────────

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
  # New structure: repo lives in voetbal/, public_html is a symlink to voetbal/php
  cd ~/www/lineupheroes.com/voetbal/php
  git -C .. fetch origin
  git -C .. checkout main
  git -C .. checkout -- . 2>/dev/null || true
  # Exclude php/.htaccess (contains PROD server vars) and php/site_version.txt
  git -C .. clean -f -d --exclude='php/.htaccess' --exclude='php/site_version.txt' 2>/dev/null || true
  git -C .. pull origin main
  echo "✅ Code bijgewerkt naar laatste commit op 'main'"

  # Composer dependencies installeren (bv. Sentry SDK)
  composer install --no-dev --no-interaction --optimize-autoloader 2>&1 | tail -5
  echo "✅ Composer packages up-to-date"

  # Schrijf site_version.txt met clean versienummer (niet in git, heeft prioriteit in footer)
  BASE_VERSION=$(cat version.txt | tr -d '\n\r')
  echo "${BASE_VERSION}" > site_version.txt
  echo "✅ Versie: ${BASE_VERSION}"

  # Update APP_ENV + Sentry DSN in php/.htaccess (server vars block is at top, preserved by git clean)
  SENTRY_DSN="https://9d70aefea0f7ed519ed0baf6a741869a@o4511324428107776.ingest.de.sentry.io/4511324449013840"
  for line in "SetEnv APP_ENV production" "SetEnv SENTRY_DSN $SENTRY_DSN" "php_value date.timezone Europe/Brussels" "php_value session.gc_maxlifetime 2592000" "php_value session.cookie_lifetime 2592000"; do
    key=$(echo "$line" | awk '{print $2}')
    if grep -q "$key" .htaccess 2>/dev/null; then
      sed -i "s|.*$key.*|$line|" .htaccess
    else
      echo "$line" >> .htaccess
    fi
  done
  echo "✅ Sentry env vars + timezone gecontroleerd in php/.htaccess (production)"
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
  sentry-cli releases deploys "$SENTRY_RELEASE" new -e production --name "PROD deploy $(date '+%Y-%m-%d %H:%M')" --org "$SENTRY_ORG"
  echo "✅ Sentry release $SENTRY_RELEASE gekoppeld aan production"
else
  echo "⚠️  Sentry release overgeslagen (sentry-cli niet gevonden of SENTRY_AUTH_TOKEN niet ingesteld)"
fi
