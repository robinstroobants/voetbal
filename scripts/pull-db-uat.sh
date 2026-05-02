#!/bin/bash
set -e

# === CONFIG ===
REMOTE_SERVER="u17-sczofusy6qgg@c9971.sgvps.net"
SSH_PORT="18765"
REMOTE_HTACCESS="~/www/lineup.webbit.be/public_html/.htaccess"
LOCAL_CONTAINER="mysql-db"
DUMP_FILE="/tmp/uat_dump_$(date +%Y%m%d_%H%M%S).sql"
# ==============

# Laad lokale .env
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
if [ -f "$SCRIPT_DIR/../.env" ]; then
  export $(grep -v '^#' "$SCRIPT_DIR/../.env" | xargs)
else
  echo "❌ Geen lokale .env gevonden. Zorg dat $SCRIPT_DIR/../.env bestaat."
  exit 1
fi
LOCAL_DB_NAME="${MYSQL_DATABASE}"
LOCAL_DB_USER="${MYSQL_USER}"
LOCAL_DB_PASS="${MYSQL_PASSWORD}"

echo "🔑 DB-credentials ophalen van UAT server (.htaccess)..."
# Lees SetEnv uit .htaccess op de server
REMOTE_HTACCESS_CONTENT=$(ssh -p "$SSH_PORT" "$REMOTE_SERVER" "cat $REMOTE_HTACCESS")
REMOTE_DB_NAME=$(echo "$REMOTE_HTACCESS_CONTENT" | grep 'SetEnv DB_NAME' | awk '{print $3}' | tr -d '"')
REMOTE_DB_USER=$(echo "$REMOTE_HTACCESS_CONTENT" | grep 'SetEnv DB_USER' | awk '{print $3}' | tr -d '"')
REMOTE_DB_PASS=$(echo "$REMOTE_HTACCESS_CONTENT" | grep 'SetEnv DB_PASS' | awk '{print $3}' | tr -d '"')
REMOTE_DB_HOST=$(echo "$REMOTE_HTACCESS_CONTENT" | grep 'SetEnv DB_HOST' | awk '{print $3}' | tr -d '"')

if [ -z "$REMOTE_DB_NAME" ]; then
  echo "❌ Kon DB-credentials niet lezen van server. Controleer $REMOTE_HTACCESS"
  exit 1
fi
echo "   Database: $REMOTE_DB_NAME @ $REMOTE_DB_HOST"

echo "⬇️  UAT database dumpen..."
ssh -p "$SSH_PORT" "$REMOTE_SERVER" "mysqldump -h '$REMOTE_DB_HOST' -u '$REMOTE_DB_USER' -p'$REMOTE_DB_PASS' '$REMOTE_DB_NAME' --no-tablespaces --single-transaction" > "$DUMP_FILE"
echo "   Dump klaar: $(du -sh "$DUMP_FILE" | cut -f1)"

echo ""
echo "⚠️  Dit overschrijft je VOLLEDIGE lokale database: $LOCAL_DB_NAME"
read -p "Doorgaan? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
  rm -f "$DUMP_FILE"
  echo "Geannuleerd."
  exit 0
fi

echo "📥 Importeren in lokale Docker container ($LOCAL_CONTAINER)..."
docker exec -i "$LOCAL_CONTAINER" mysql -u"$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" "$LOCAL_DB_NAME" < "$DUMP_FILE"

rm -f "$DUMP_FILE"
echo ""
echo "✅ UAT database gesynchroniseerd naar lokale container!"
echo "   App draait op: http://localhost:8085"
