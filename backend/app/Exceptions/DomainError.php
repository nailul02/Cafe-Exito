<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/** Pelanggaran aturan bisnis. Dirender otomatis menjadi respons galat seragam (kode HTTP 404/409/422). */
class DomainError extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status = 409,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return ApiResponse::error($this->errorCode, $this->getMessage(), $this->status, $this->details ?: null);
    }

    /** @param list<string> $ingredients */
    public static function insufficientStock(array $ingredients): self
    {
        return new self('INSUFFICIENT_STOCK', 'Stok bahan tidak cukup: '.implode(', ', $ingredients).'.', 409, ['ingredients' => $ingredients]);
    }

    public static function invalidTransition(string $subject, string $from, string $to): self
    {
        return new self('INVALID_STATE_TRANSITION', "$subject tidak dapat berpindah dari status \"$from\" ke \"$to\".", 409, ['from' => $from, 'to' => $to]);
    }

    public static function menuUnavailable(string $name, ?string $reason = null): self
    {
        return new self('MENU_UNAVAILABLE', "Menu \"$name\" sedang tidak tersedia".($reason ? " ($reason)." : '.'), 409, ['menu' => $name]);
    }

    public static function tableInvalid(string $message = 'Meja tidak ditemukan atau tidak aktif.'): self
    {
        return new self('TABLE_INVALID', $message, 422);
    }

    public static function paymentAlreadyProcessed(): self
    {
        return new self('PAYMENT_ALREADY_PROCESSED', 'Pembayaran ini sudah diproses.', 409);
    }

    public static function amountTooLow(int $total): self
    {
        return new self('AMOUNT_TOO_LOW', 'Uang diterima kurang dari total tagihan.', 422, ['total' => $total]);
    }

    public static function orderNotCancellable(): self
    {
        return new self('ORDER_NOT_CANCELLABLE', 'Pesanan tidak dapat dibatalkan pada status ini.', 409);
    }

    public static function purchaseNotEditable(string $message = 'Pembelian tidak dapat diubah pada status ini.'): self
    {
        return new self('PURCHASE_NOT_EDITABLE', $message, 409);
    }

    public static function overReceipt(): self
    {
        return new self('OVER_RECEIPT', 'Jumlah diterima melebihi sisa pesanan.', 409);
    }

    public static function receiptAlreadyConfirmed(): self
    {
        return new self('RECEIPT_ALREADY_CONFIRMED', 'Penerimaan ini sudah dikonfirmasi dan tidak dapat diubah.', 409);
    }

    public static function forbidden(string $message = 'Anda tidak memiliki izin untuk aksi ini.'): self
    {
        return new self('FORBIDDEN', $message, 403);
    }
}
