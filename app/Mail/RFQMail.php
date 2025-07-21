<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class RFQMail extends Mailable
{
    use Queueable, SerializesModels;

    public $rfq;

    /**
     * Create a new message instance.
     */
    public function __construct($rfq)
    {
        $this->rfq = $rfq;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $pdf = Pdf::loadView('pdf.rfq', ['rfq' => $this->rfq]);

        return $this->subject('Request for Quotation')
            ->view('emails.rfq')
            ->with(['rfq' => $this->rfq])
            ->attachData($pdf->output(), "RFQ-{$this->rfq->rfq_no}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
