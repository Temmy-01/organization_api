<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address; // Add this line to import the Address class
use Illuminate\Queue\SerializesModels;

class UserMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $email_data;
    public $loginUrl;

    public function __construct($email_data)
    {
        $this->email_data = $email_data;
        // Determine the login URL based on the environment
        if (app()->environment('local')) {
            $this->loginUrl = env('LOCAL_LOGIN_URL');
        } elseif (app()->environment('staging')) {
            $this->loginUrl = env('STAGING_LOGIN_URL');
        } else {
            $this->loginUrl = env('PRODUCTION_LOGIN_URL');
        }

        // // Debug the login URL
        // dd($this->loginUrl);
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address('info@digitalweb247.com', $this->email_data['user']['first_name']),
            subject: 'Your Account Has Been Created.',
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
            markdown: 'emails.system-user',
            with: [
                'email_data' => $this->email_data,
                'loginUrl' => config('frontend.login.url')
                // 'loginUrl' => $this->loginUrl

            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
