<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class FinanceDocumentMail extends Mailable
{
    public function __construct(
        public string $documentType,
        public string $code,
        public string $recipientName,
        public array $artifact,
        public ?string $note = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $label = $this->documentType === 'receipt' ? 'phiếu thu' : 'hóa đơn';

        return new Envelope(
            subject: 'Trung tâm gửi ' . $label . ' ' . $this->code,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.finance.document',
            with: [
                'documentLabel' => $this->documentType === 'receipt' ? 'phiếu thu' : 'hóa đơn',
                'code' => $this->code,
                'recipientName' => $this->recipientName,
                'note' => $this->note,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->artifact['content'],
                $this->artifact['filename']
            )->withMime($this->artifact['mime'] ?? 'application/pdf'),
        ];
    }
}
