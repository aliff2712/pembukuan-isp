<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TelegramNotificationService
 *
 * Service untuk mengirim notifikasi via Telegram Bot API.
 * Gratis, tidak ada quota, tidak ada expiry.
 */
class TelegramNotificationService
{
    private ?string $token;
    private ?string $chatId;
    private ?string $apiUrl;

    public function __construct()
    {
        $this->token  = config('services.telegram.token', env('TELEGRAM_BOT_TOKEN', ''));
        $this->chatId = config('services.telegram.chat_id', env('TELEGRAM_ADMIN_CHAT_ID', ''));
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}/sendMessage";
    }

    // =========================================================
    // Kirim alert jika ada data flagged saat import
    // =========================================================

    public function sendFlaggedAlert(int $jumlahFlagged, array $flaggedReasons): bool
    {
        if (empty($this->token) || empty($this->chatId)) {
            Log::warning('Telegram: Token atau Chat ID belum dikonfigurasi di .env');
            return false;
        }

        $message = $this->buildFlaggedMessage($jumlahFlagged, $flaggedReasons);

        return $this->send($message);
    }

    // =========================================================
    // Kirim notif jurnal berhasil (opsional)
    // =========================================================

    public function sendJournalSuccess(int $jumlahJurnal, string $bulan): bool
    {
        if (empty($this->token) || empty($this->chatId)) {
            return false;
        }

        $appUrl  = config('app.url');
        $message = "✅ *Jurnal Berhasil Dibuat*\n\n"
                 . "Sejumlah *{$jumlahJurnal} transaksi* bulan *{$bulan}* berhasil dijurnal\.\n\n"
                 . "🔗 [Lihat di sini]({$appUrl}/pembukuan/staging)";

        return $this->send($message);
    }

    // =========================================================
    // Core: kirim pesan via Telegram Bot API
    // =========================================================

    private function send(string $message): bool
    {
        try {
            $response = Http::timeout(10)->post($this->apiUrl, [
                'chat_id'    => $this->chatId,
                'text'       => $message,
                'parse_mode' => 'MarkdownV2',
            ]);

            $body = $response->json();

            if ($response->successful() && isset($body['ok']) && $body['ok'] === true) {
                Log::info('Telegram: Notifikasi berhasil dikirim');
                return true;
            }

            Log::warning('Telegram: Gagal kirim notifikasi', [
                'response' => $body,
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Telegram: Exception saat kirim notifikasi', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    // =========================================================
    // Build pesan flagged (format MarkdownV2)
    // =========================================================

    private function buildFlaggedMessage(int $jumlah, array $reasons): string
    {
        $appUrl = $this->escapeMarkdown(config('app.url'));

        $lines   = [];
        $lines[] = "⚠️ *Payment Import Alert*";
        $lines[] = "";
        $lines[] = "Ada *{$jumlah} data* yang di\-flag dan perlu direview manual\.";
        $lines[] = "";
        $lines[] = "*Detail:*";

        $displayed = array_slice($reasons, 0, 5);
        foreach ($displayed as $item) {
            $ref    = $this->escapeMarkdown($item['source_ref'] ?? '-');
            $nama   = $this->escapeMarkdown($item['nama']       ?? '-');
            $reason = $this->escapeMarkdown($item['reason']     ?? '-');
            $lines[] = "• `{$ref}` {$nama} → _{$reason}_";
        }

        $sisa = count($reasons) - 5;
        if ($sisa > 0) {
            $lines[] = "• _\.\.\. dan {$sisa} data lainnya_";
        }

        $lines[] = "";
        $lines[] = "🔗 [Review di sini]({$appUrl}/pembukuan/staging)";

        return implode("\n", $lines);
    }

    // =========================================================
    // Escape karakter khusus MarkdownV2
    // Telegram MarkdownV2 wajib escape: _ * [ ] ( ) ~ ` > # + - = | { } . !
    // =========================================================

    private function escapeMarkdown(string $text): string
    {
        $characters = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];

        foreach ($characters as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }

        return $text;
    }
}