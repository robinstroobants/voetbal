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
  git pull origin beta
  echo "✅ Code bijgewerkt naar laatste commit op 'beta'"

  # Schrijf site_version.txt met -beta suffix (niet in git, heeft prioriteit in footer)
  BASE_VERSION=$(cat version.txt | tr -d '\n\r')
  echo "${BASE_VERSION}-beta" > site_version.txt
  echo "✅ Versie: ${BASE_VERSION}-beta"
ENDSSH

# Run migrations via HTTP
echo "🔄 Migrations uitvoeren via HTTP..."
MIGRATION_OUTPUT=$(curl -s "https://lineup.webbit.be/run_migrations.php?token=super_secret_deploy_key_2026")
echo "$MIGRATION_OUTPUT" | sed 's/<[^>]*>//g' | grep -v '^$' || true

echo ""
echo "✅ UAT deploy klaar → https://lineup.webbit.be (v$NEW_VERSION-beta)"
