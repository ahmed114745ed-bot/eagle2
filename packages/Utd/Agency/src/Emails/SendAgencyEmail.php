<?php

namespace Utd\Agency\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendAgencyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $agency;
    public $additionalInfo;

    /**
     * Create a new message instance.
     *
     * @param mixed $agencyWithAdditionalInfo
     */
    public function __construct($agencyWithAdditionalInfo)
    {
        if (is_array($agencyWithAdditionalInfo)) {
            $this->agency = $agencyWithAdditionalInfo['agency'] ?? $agencyWithAdditionalInfo;
            $this->additionalInfo = $agencyWithAdditionalInfo['additional_info'] ?? null;
        } else {
            $this->agency = $agencyWithAdditionalInfo;
            $this->additionalInfo = $agencyWithAdditionalInfo->additionalInfo ?? null;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Agency Registration')
                    ->view('agency::emails.agency-registration')
                    ->with([
                        'agency' => $this->agency,
                        'additionalInfo' => $this->additionalInfo,
                    ]);
    }
}
