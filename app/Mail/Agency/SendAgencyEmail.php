<?php

namespace App\Mail\Agency;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Alias for backward compatibility
 * @deprecated Use Utd\Agency\Emails\SendAgencyEmail instead
 */
if (class_exists('\Utd\Agency\Emails\SendAgencyEmail')) {
    class SendAgencyEmail extends \Utd\Agency\Emails\SendAgencyEmail
    {
        // This class is just an alias for backward compatibility
        // All functionality is inherited from the package
    }
} else {
    // Fallback implementation when package is not available
    class SendAgencyEmail extends Mailable
    {
        use Queueable, SerializesModels;
        
        public $agency;
        
        public function __construct($agency)
        {
            $this->agency = $agency;
        }
        
        public function build()
        {
            return $this->subject('New Agency Registration')
                        ->view('emails.agency-registration')
                        ->with(['agency' => $this->agency]);
        }
    }
}
