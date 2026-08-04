<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\RfqRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Pemberitahuan RFQ baru ke tim sales. Dikirim lewat queue supaya submission
 * buyer tidak menunggu SMTP.
 *
 * @see docs/09-tasks.md bagian 4.1
 */
final class NewRfqReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly RfqRequest $rfq) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("RFQ baru {$this->rfq->reference_no} dari {$this->rfq->company_name}")
            ->greeting('Ada permintaan penawaran baru.')
            ->line("Nomor referensi: {$this->rfq->reference_no}")
            ->line("Perusahaan: {$this->rfq->company_name} ({$this->rfq->country_code})")
            ->line("Email: {$this->rfq->email}")
            ->when(
                $this->rfq->monthly_tonnage !== null,
                fn (MailMessage $mail): MailMessage => $mail->line("Perkiraan volume: {$this->rfq->monthly_tonnage}"),
            )
            ->action('Buka di admin', route('admin.rfqs.show', $this->rfq))
            ->line('Balas secepatnya; buyer biasanya mengirim permintaan ke beberapa pemasok sekaligus.');
    }
}
