<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emailData)
    {

        $this->emailData = $emailData;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        // Use the product name from the first invoice if available
        $accountName = isset($this->emailData[0]['account_name']) ? $this->emailData[0]['account_name'] : 'Invoice';

        return new Envelope(
            from: new Address('info@digitalweb247.com'),
            subject: 'Invoice Details - ' . $accountName, // Dynamic subject with product name from the first invoice
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.invoice-mail', // The view file name for the invoice email content
            with: [
                'emailData' => $this->emailData, // Passing the email data to the view
            ]
        );
    }

    public function build()
    {
        // Generate the PDF from the 'emails.invoice-mail' view
        $pdf = Pdf::loadView('emails.invoice-mail', ['emailData' => $this->emailData]);

        return $this->from(new Address('info@digitalweb247.com'))
        ->subject('Invoice Details - ' . ($this->emailData[0]['account_name'] ?? 'Invoice'))
        ->view('emails.invoice-mail')
        ->with(['emailData' => $this->emailData])
            ->attachData($pdf->output(), 'invoice.pdf', [
                'mime' => 'application/pdf',
            ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        // You can attach files if necessary, for now returning an empty array
        return [];
    }
}
