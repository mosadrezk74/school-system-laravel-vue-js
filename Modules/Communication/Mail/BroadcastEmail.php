<?php

namespace Modules\Communication\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BroadcastEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $emailSubject,
        public readonly string $emailBody,
    ) {}

    public function build(): self
    {
        return $this->subject($this->emailSubject)
            ->view('communication::emails.broadcast', [
                'subject' => $this->emailSubject,
                'body' => $this->emailBody,
            ]);
    }
}
