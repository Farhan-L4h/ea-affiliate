#!/bin/bash

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║         CEK ERROR WEBHOOK TELEGRAM DI LOG                      ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

LOG_FILE="storage/logs/laravel.log"

if [ ! -f "$LOG_FILE" ]; then
    echo "❌ File log tidak ditemukan: $LOG_FILE"
    exit 1
fi

echo "📋 Mencari error terkait Telegram Webhook..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Cek error TelegramWebhookController
echo "🔍 Error dari TelegramWebhookController:"
echo ""
tail -500 "$LOG_FILE" | grep -i "TelegramWebhook" -A 10 | tail -50
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Cek update chat_member
echo "🔍 Update chat_member yang diterima:"
echo ""
tail -500 "$LOG_FILE" | grep -i "chat_member" | tail -20
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Cek Exception/Error
echo "🔍 Exception/Error terakhir:"
echo ""
tail -200 "$LOG_FILE" | grep -E "Exception|Error|Fatal" -A 5 | tail -50
echo ""

echo "💡 TIP:"
echo "- Jika ada error 'Undefined array key', tambahkan null coalescing"
echo "- Jika ada error 'Call to a member function', tambahkan null check"
echo "- Untuk debugging live, jalankan: tail -f storage/logs/laravel.log"
echo ""
