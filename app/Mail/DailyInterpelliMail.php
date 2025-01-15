<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyInterpelliMail extends Mailable
{
    use Queueable, SerializesModels;

    public $downloadLink = '';
    public $webLink = '';
    public $data = '';
    public $nome = '';

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $downloadLink,
        string $webLink,
        string $data,
        string $nome
    ){
        $this->downloadLink = $downloadLink;
        $this->webLink = $webLink;
        $this->data = $data;
        $this->nome = $nome;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily Interpelli Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emailTemplates.newAlert',
            with: [
                'downloadLink' => $this->downloadLink,
                'webLink' => $this->webLink,
                'data' => $this->data,
                'nome' => $this->nome,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
