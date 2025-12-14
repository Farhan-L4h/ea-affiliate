# Quick Deploy ke Production

## File yang Perlu Di-Upload

Upload file-file berikut ke server production:

```bash
# 1. Controller yang sudah diperbaiki
app/Http/Controllers/TelegramWebhookController.php

# 2. Service yang sudah diperbaiki
app/Services/TelegramService.php

# 3. Commands yang sudah diperbaiki
app/Console/Commands/TelegramSetWebhook.php
app/Console/Commands/SetupTelegramWebhook.php

# 4. Script helper (opsional tapi sangat berguna)
check_bot_admin.php
check_webhook_errors.sh
test_webhook_locally.php
```

## Langkah Deploy ke Production

### 1. Upload File via SFTP/SCP
```bash
# Dari local, upload ke server
scp app/Http/Controllers/TelegramWebhookController.php root@server:/var/www/ea-affiliate/app/Http/Controllers/
scp app/Services/TelegramService.php root@server:/var/www/ea-affiliate/app/Services/
scp app/Console/Commands/*.php root@server:/var/www/ea-affiliate/app/Console/Commands/
scp check_*.php root@server:/var/www/ea-affiliate/
scp test_*.php root@server:/var/www/ea-affiliate/
scp check_webhook_errors.sh root@server:/var/www/ea-affiliate/
```

### 2. Di Server Production
```bash
# SSH ke server
ssh root@server497066100

cd /var/www/ea-affiliate

# Set permission untuk script
chmod +x check_webhook_errors.sh

# Clear cache Laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Set ulang webhook (PENTING!)
php artisan telegram:set-webhook
```

### 3. Verifikasi
```bash
# Cek status bot
php check_bot_admin.php

# Test webhook handler locally
php test_webhook_locally.php

# Monitor log real-time (buka terminal terpisah)
tail -f storage/logs/laravel.log
```

### 4. Test dengan User Real
1. Buat link affiliate baru atau gunakan existing
2. Klik link dengan akun Telegram yang BELUM PERNAH join channel
3. Bot akan kirim pesan welcome
4. Join channel @scalpermaxproai
5. **Langsung cek log:**
   ```bash
   tail -20 storage/logs/laravel.log
   ```
6. Harus muncul log:
   ```
   Status updated to joined_channel
   ```
7. Cek di dashboard → status harus "Join Channel" (hijau)

## Troubleshooting di Production

### Jika Masih Error 500

```bash
# Lihat error terakhir
tail -100 storage/logs/laravel.log | grep -i "error" -A 5

# Atau jalankan script
./check_webhook_errors.sh
```

### Jika Status Tidak Update

```bash
# 1. Cek apakah webhook menerima update chat_member
tail -50 storage/logs/laravel.log | grep "chat_member"

# 2. Cek apakah ada log "Status updated to joined_channel"
tail -100 storage/logs/laravel.log | grep "joined_channel"

# 3. Cek database langsung
mysql -u root -p
USE ea_affiliate;
SELECT id, prospect_telegram_username, status, updated_at 
FROM referral_tracks 
ORDER BY updated_at DESC 
LIMIT 5;
```

## Common Issues

### Issue 1: Permission Denied
```bash
chmod 755 check_*.php test_*.php
chmod +x check_webhook_errors.sh
```

### Issue 2: Composer Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### Issue 3: File Ownership
```bash
chown -R www-data:www-data /var/www/ea-affiliate
# atau
chown -R nginx:nginx /var/www/ea-affiliate
```

## Monitoring Real-Time

Terminal 1 - Monitor Log:
```bash
tail -f storage/logs/laravel.log
```

Terminal 2 - Test:
```bash
# User klik link affiliate
# User join channel
# Lihat log di terminal 1
```

## Rollback Jika Ada Masalah

```bash
# Backup dulu sebelum deploy
cd /var/www/ea-affiliate
tar -czf backup-$(date +%Y%m%d-%H%M%S).tar.gz app/

# Jika perlu rollback
# tar -xzf backup-XXXXXX.tar.gz
```

## Success Indicators

✅ Webhook return 200 (bukan 500)
✅ Log menampilkan "Status updated to joined_channel"  
✅ Database: status berubah dari 'clicked' ke 'joined_channel'
✅ Dashboard menampilkan badge hijau "Join Channel"
✅ `check_bot_admin.php` tidak ada error webhook lagi

## Quick Commands Reference

```bash
# Cek bot status
php check_bot_admin.php

# Set webhook
php artisan telegram:set-webhook

# Test locally
php test_webhook_locally.php

# Monitor log
tail -f storage/logs/laravel.log

# Cek error
./check_webhook_errors.sh

# Clear cache
php artisan config:clear && php artisan cache:clear && php artisan route:clear
```
