#!/bin/bash

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║         RESET TELEGRAM WEBHOOK (DELETE + SET ULANG)            ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

BOT_TOKEN=$(php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); echo config('services.telegram.bot_token');")
WEBHOOK_URL=$(php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); echo config('services.telegram.webhook_url');")

echo "Bot Token: ${BOT_TOKEN:0:15}..."
echo "Webhook URL: $WEBHOOK_URL"
echo ""

# Step 1: Delete existing webhook
echo "🗑️  STEP 1: Menghapus webhook yang ada..."
DELETE_RESPONSE=$(curl -s "https://api.telegram.org/bot$BOT_TOKEN/deleteWebhook")
echo "$DELETE_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$DELETE_RESPONSE"
echo ""

sleep 2

# Step 2: Set webhook dengan allowed_updates
echo "🔧 STEP 2: Set webhook dengan konfigurasi baru..."
SET_RESPONSE=$(curl -s -X POST "https://api.telegram.org/bot$BOT_TOKEN/setWebhook" \
  -H "Content-Type: application/json" \
  -d "{
    \"url\": \"$WEBHOOK_URL\",
    \"allowed_updates\": [\"message\", \"callback_query\", \"chat_member\", \"my_chat_member\"]
  }")
echo "$SET_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$SET_RESPONSE"
echo ""

sleep 2

# Step 3: Verify webhook info
echo "✅ STEP 3: Verifikasi webhook..."
INFO_RESPONSE=$(curl -s "https://api.telegram.org/bot$BOT_TOKEN/getWebhookInfo")
echo "$INFO_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$INFO_RESPONSE"
echo ""

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                         SELESAI                                ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo "Jika ada 'pending_update_count' > 0, jalankan lagi script ini."
echo "Setelah itu, test dengan user yang BELUM PERNAH join channel."
echo ""
