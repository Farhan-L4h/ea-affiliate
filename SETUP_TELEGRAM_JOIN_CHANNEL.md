# Setup Deteksi Join Channel Telegram

## Masalah
Status prospek tidak berubah dari "Klik Link" ke "Join Channel" meskipun user sudah join channel/grup.

## Penyebab
1. **Bot belum menjadi admin** di channel/grup target
2. **Webhook tidak dikonfigurasi** untuk menerima update `chat_member`
3. **Bot tidak mendapat notifikasi** saat ada user baru join

## Solusi

### 1. Jadikan Bot sebagai Admin di Channel/Grup

#### Untuk Channel:
1. Buka channel Telegram Anda
2. Klik nama channel di atas
3. Pilih **"Administrators"** atau **"Admin"**
4. Klik **"Add Admin"**
5. Cari bot Anda: `@DevDesaTrading_bot`
6. Tambahkan bot sebagai admin
7. Berikan minimal permission:
   - ✅ **Add members** (penting!)
   - ✅ **Delete messages** (opsional)
   - ✅ **Ban users** (opsional)

#### Untuk Grup:
1. Buka grup Telegram Anda
2. Klik nama grup di atas
3. Pilih **"Administrators"**
4. Klik **"Add Admin"**
5. Cari dan pilih: `@DevDesaTrading_bot`
6. Berikan permission yang diperlukan

### 2. Update Webhook dengan Allowed Updates

Jalankan command berikut untuk set ulang webhook dengan konfigurasi yang benar:

```bash
php artisan telegram:set-webhook
```

Command ini akan:
- Set webhook URL
- Menambahkan `allowed_updates`: `message`, `callback_query`, `chat_member`, `my_chat_member`
- Menampilkan konfirmasi status

### 3. Verifikasi Setup

Jalankan script untuk mengecek apakah bot sudah menjadi admin:

```bash
php check_bot_admin.php
```

Script ini akan menampilkan:
- ✅ Status bot di channel/grup
- ✅ Permission yang dimiliki bot
- ✅ Konfigurasi webhook
- ✅ Allowed updates yang aktif

### 4. Test Functionality

1. **Buat link affiliate baru atau gunakan yang ada**
   - Contoh: `https://yoursite.com/r?ref=AFXXXXXX`

2. **Klik link dengan akun Telegram baru/berbeda**
   - Bot akan kirim pesan welcome
   - Status di database: `clicked`

3. **Join channel/grup yang dituju**
   - Bot akan detect join (jika sudah admin)
   - Status otomatis berubah ke: `joined_channel`

4. **Cek di dashboard affiliate**
   - Buka halaman Prospek
   - Status harus berubah dari "Klik Link" ke "Join Channel"

## Troubleshooting

### Status Masih "Klik Link" Setelah Join

**Cek 1: Apakah bot sudah admin?**
```bash
php check_bot_admin.php
```

**Cek 2: Apakah webhook sudah benar?**
```bash
php artisan telegram:set-webhook
```

**Cek 3: Lihat log webhook**
```bash
tail -f storage/logs/laravel.log
```

Saat user join, Anda harus melihat log seperti ini:
```
[2025-12-14 15:30:45] local.INFO: Telegram update {"chat_member": {...}}
```

**Cek 4: Apakah Channel ID sudah benar?**

Periksa file `.env`:
```
TELEGRAM_GROUP_ID=-1003253198824
```

Pastikan ID ini sama dengan channel/grup yang dituju.

### Bot Tidak Menerima Update Chat Member

1. **Pastikan bot adalah ADMIN** (bukan member biasa)
2. **Set ulang webhook**:
   ```bash
   php artisan telegram:set-webhook
   ```
3. **Restart ngrok** (jika menggunakan ngrok):
   ```bash
   ngrok http 80
   # Update TELEGRAM_WEBHOOK_URL di .env
   php artisan telegram:set-webhook
   ```

### Error "Chat not found" atau "Forbidden"

Ini berarti bot belum ditambahkan ke channel/grup atau belum menjadi admin.

**Solusi:**
1. Tambahkan bot ke channel/grup
2. Jadikan bot sebagai admin
3. Jalankan: `php check_bot_admin.php`

## Cara Kerja Sistem

### Flow Deteksi Join Channel

```
1. User klik link affiliate
   └─> Middleware track klik (status: clicked)
   
2. User redirect ke Telegram bot
   └─> User klik /start di bot
   └─> Webhook terima update 'message'
   └─> TelegramWebhookController::handleMessage()
   └─> Update/create ReferralTrack (status: clicked)
   
3. User join channel/grup
   └─> Telegram kirim update 'chat_member' ke webhook
   └─> TelegramWebhookController::handleChatMember()
   └─> Cari ReferralTrack by telegram_id
   └─> Update status ke 'joined_channel'
   
4. Status tampil di dashboard
   └─> Badge berubah dari "Klik Link" (biru) ke "Join Channel" (hijau)
```

### Kode Terkait

**File yang menangani join channel:**
- `app/Http/Controllers/TelegramWebhookController.php`
  - Method: `handleChatMember()`
  - Menerima update dari Telegram
  - Update status ReferralTrack

**File webhook setup:**
- `app/Services/TelegramService.php`
  - Method: `setWebhook()`
  
**File command:**
- `app/Console/Commands/TelegramSetWebhook.php`
  - Command: `telegram:set-webhook`

## Konfigurasi Yang Diperlukan

### .env File
```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_BOT_USERNAME=DevDesaTrading_bot
TELEGRAM_WEBHOOK_URL=https://your-domain.com/telegram/webhook
TELEGRAM_GROUP_ID=-1003253198824
```

### Route
File: `routes/web.php`
```php
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->withoutMiddleware([VerifyCsrfToken::class]);
```

## Checklist Setup

- [ ] Bot sudah ditambahkan ke channel/grup
- [ ] Bot sudah menjadi ADMIN
- [ ] Webhook URL sudah diset di .env
- [ ] Jalankan `php artisan telegram:set-webhook`
- [ ] Verifikasi dengan `php check_bot_admin.php`
- [ ] Test dengan user baru
- [ ] Monitor log saat testing

## Support

Jika masih ada masalah:
1. Cek log: `storage/logs/laravel.log`
2. Jalankan: `php check_bot_admin.php`
3. Pastikan semua checklist di atas sudah ✅
