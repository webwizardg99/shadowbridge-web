#!/bin/bash
# ShadowBridge.Store — Automated Deployment
# Uses cPanel UAPI with Authorization header

set -e

CPANEL_HOST="shadowbridge.store"
CPANEL_USER="ghostwizardg"
CPANEL_TOKEN="WW2C16O4XHEBR6SC2J1UV9K4V8GBMU44"
WEBROOT="/home/ghostwizardg/public_html"
LOCAL_FILE="${1:-./index.html}"

echo "🚀 ShadowBridge Deploy"
echo "📦 File: $LOCAL_FILE ($(wc -c < "$LOCAL_FILE") bytes)"

RESPONSE=$(curl -k -s "https://$CPANEL_HOST:2083/execute/Fileman/save_file_content" \
  -H "Authorization: cpanel $CPANEL_USER:$CPANEL_TOKEN" \
  --data-urlencode "dir=$WEBROOT" \
  --data-urlencode "file=$(basename $LOCAL_FILE)" \
  --data-urlencode "content=$(cat $LOCAL_FILE)")

if echo "$RESPONSE" | grep -q '"status":1'; then
  echo "✅ Deployment successful!"
  echo "🌐 https://$CPANEL_HOST/"
else
  echo "❌ Failed: $RESPONSE"
  exit 1
fi
