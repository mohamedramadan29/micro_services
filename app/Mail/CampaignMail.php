<?php

namespace App\Mail;

use App\Models\EmailCampaignRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public EmailCampaignRecipient $recipient;
    public string $subjectText;
    public string $bodyHtml;

    public function __construct(EmailCampaignRecipient $recipient, string $subject, string $body)
    {
        $this->recipient = $recipient;
        $this->subjectText = $subject;
        $this->bodyHtml = $body;
    }

    public function envelope(): Envelope
    {
        $campaign = $this->recipient->campaign;

        $envelope = new Envelope(
            subject: $this->subjectText,
        );

        if ($campaign->reply_to) {
            $envelope->replyTo($campaign->reply_to);
        }

        if ($campaign->cc_email) {
            $envelope->cc($campaign->cc_email);
        }

        if ($campaign->bcc_email) {
            $envelope->bcc($campaign->bcc_email);
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->bodyHtml,
        );
    }

    public function attachments(): array
    {
        $campaign = $this->recipient->campaign;
        $attachments = [];

        if ($campaign->has_attachment && $campaign->attachment_path) {
            $fullPath = public_path($campaign->attachment_path);
            if (file_exists($fullPath)) {
                $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromPath($fullPath)
                    ->as($campaign->attachment_name ?? basename($campaign->attachment_path));
            }
        }

        return $attachments;
    }
}
