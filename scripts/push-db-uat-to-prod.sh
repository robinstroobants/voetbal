#!/bin/bash
set -e

# === CONFIG ===
UAT_SERVER="u17-sczofusy6qgg@c9971.sgvps.net"
PROD_SERVER="u18-qysfjqo1g9od@c9971.sgvps.net"
SSH_PORT="18765"
UAT_HTACCESS="~/www/lineup.webbit.be/public_html/.htaccess"
PROD_HTACCESS="~/www/lineupheroes.com/public_html/.htaccess"
DUMP_FILE="/tmp/uat_to_prod_$(date +%Y%m%d_%H%M%S).sql"
# ==============

echo "🔑 Credentials ophalen van UAT server..."
UAT_ENV=$(ssh -p "$SSH_PORT" "$UAT_SERVER" "cat $UAT_HTACCESS")
UAT_DB_NAME=$(echo "$UAT_ENV" | grep 'SetEnv DB_NAME' | awk '{print $3}' | tr -d '"')
UAT_DB_USER=$(echo "$UAT_ENV" | grep 'SetEnv DB_USER' | awk '{print $3}' | tr -d '"')
UAT_DB_PASS=$(echo "$UAT_ENV" | grep 'SetEnv DB_PASS' | awk '{print $3}' | tr -d '"')
UAT_DB_HOST=$(echo "$UAT_ENV" | grep 'SetEnv DB_HOST' | awk '{print $3}' | tr -d '"')
echo "   UAT database: $UAT_DB_NAME @ $UAT_DB_HOST"

echo "🔑 Credentials ophalen van PROD server..."
PROD_ENV=$(ssh -p "$SSH_PORT" "$PROD_SERVER" "cat $PROD_HTACCESS")
PROD_DB_NAME=$(echo "$PROD_ENV" | grep 'SetEnv DB_NAME' | awk '{print $3}' | tr -d '"')
PROD_DB_USER=$(echo "$PROD_ENV" | grep 'SetEnv DB_USER' | awk '{print $3}' | tr -d '"')
PROD_DB_PASS=$(echo "$PROD_ENV" | grep 'SetEnv DB_PASS' | awk '{print $3}' | tr -d '"')
PROD_DB_HOST=$(echo "$PROD_ENV" | grep 'SetEnv DB_HOST' | awk '{print $3}' | tr -d '"')
echo "   PROD database: $PROD_DB_NAME @ $PROD_DB_HOST"

echo ""
echo "⚠️  Je staat op het punt de UAT database te kopiëren naar PRODUCTIE!"
echo "   Van: $UAT_DB_NAME (lineup.webbit.be)"
echo "   Naar: $PROD_DB_NAME (lineupheroes.com)"
echo "   De volledige PROD database wordt overschreven."
echo ""
read -p "Typ 'yes' om te bevestigen: " confirm
if [ "$confirm" != "yes" ]; then
  echo "Geannuleerd."
  exit 0
fi

echo ""
echo "⬇️  UAT database dumpen..."
ssh -p "$SSH_PORT" "$UAT_SERVER" \
  "mysqldump -h '$UAT_DB_HOST' -u '$UAT_DB_USER' -p'$UAT_DB_PASS' '$UAT_DB_NAME' --no-tablespaces --single-transaction 2>/dev/null" \
  > "$DUMP_FILE"
echo "   Dump klaar: $(du -sh "$DUMP_FILE" | cut -f1)"

echo "📥 Importeren in PROD database..."
ssh -p "$SSH_PORT" "$PROD_SERVER" \
  "mysql -h '$PROD_DB_HOST' -u '$PROD_DB_USER' -p'$PROD_DB_PASS' '$PROD_DB_NAME' 2>/dev/null" \
  < "$DUMP_FILE"

rm -f "$DUMP_FILE"
echo ""
echo "✅ UAT → PROD database sync klaar!"
echo "   https://lineupheroes.com is nu voorzien van UAT data."
