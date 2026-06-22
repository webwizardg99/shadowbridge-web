#!/bin/bash
# ShadowBridge — Auth system batch deploy
set -e

CPANEL_HOST="shadowbridge.store"
CPANEL_USER="ghostwizardg"
CPANEL_TOKEN="WW2C16O4XHEBR6SC2J1UV9K4V8GBMU44"
WEBROOT="/home/ghostwizardg/public_html"

deploy_file() {
    local local_path="$1"
    local remote_dir="$2"
    local filename=$(basename "$local_path")
    echo -n "  Uploading $remote_dir/$filename ... "
    RESPONSE=$(curl -k -s "https://$CPANEL_HOST:2083/execute/Fileman/save_file_content" \
        -H "Authorization: cpanel $CPANEL_USER:$CPANEL_TOKEN" \
        --data-urlencode "dir=$remote_dir" \
        --data-urlencode "file=$filename" \
        --data-urlencode "content=$(cat "$local_path")")
    if echo "$RESPONSE" | grep -q '"status":1'; then
        echo "✅"
    else
        echo "❌ FAILED"
        echo "   $RESPONSE"
    fi
}

echo "🚀 ShadowBridge Auth System Deploy"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Root files
deploy_file "./register.php"   "$WEBROOT"
deploy_file "./login.php"      "$WEBROOT"
deploy_file "./logout.php"     "$WEBROOT"
deploy_file "./dashboard.php"  "$WEBROOT"
deploy_file "./.htaccess"      "$WEBROOT"

# Auth directory
deploy_file "./auth/db_config.php" "$WEBROOT/auth"
deploy_file "./auth/setup.php"     "$WEBROOT/auth"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Deploy complete!"
echo ""
echo "Next steps:"
echo "  1. cPanel → MySQL Databases → create DB + user"
echo "     DB name: ghostwiz_shadowbridge"
echo "     DB user: ghostwiz_sbuser"
echo "     Update ./auth/db_config.php with the real password"
echo "     Re-deploy: ./deploy-auth.sh"
echo ""
echo "  2. Run DB setup (once):"
echo "     https://shadowbridge.store/auth/setup.php?key=NOX_SETUP_2026_DELETE_AFTER_RUN"
echo ""
echo "  3. Delete setup.php from server after it runs!"
echo ""
echo "  4. Test:"
echo "     https://shadowbridge.store/register"
echo "     https://shadowbridge.store/login"
