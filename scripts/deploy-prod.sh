#!/bin/bash
set -e

# === CONFIG ===
SERVER="u18-qysfjqo1g9od@c9971.sgvps.net"
SSH_PORT="18765"
BRANCH="main"
# ==============

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
  git pull origin main
  echo "✅ Code bijgewerkt naar laatste commit op 'main'"

  # Schrijf site_version.txt met clean versienummer (niet in git, heeft prioriteit in footer)
  BASE_VERSION=$(cat version.txt | tr -d '\n\r')
  echo "${BASE_VERSION}" > site_version.txt
  echo "✅ Versie: ${BASE_VERSION}"
ENDSSH

# Run migrations via HTTP
echo "🔄 Migrations uitvoeren via HTTP..."
MIGRATION_OUTPUT=$(curl -sk --max-time 30 "https://lineupheroes.com/run_migrations.php?token=super_secret_deploy_key_2026")
echo "$MIGRATION_OUTPUT" | sed 's/<[^>]*>//g' | grep -v '^$' || true

echo ""
echo "✅ PROD deploy klaar → https://lineupheroes.com"
