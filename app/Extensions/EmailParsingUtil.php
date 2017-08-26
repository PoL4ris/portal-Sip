<?php

namespace App\Extensions;

use App\Extensions\SIPCustomer;
use App\Extensions\SIPTicket;
use Log;
use PhpMimeMailParser\Parser as Parser;


class EmailParsingUtil {

    protected $sipCustomer;
    protected $sipTicket;

    public function __construct()
    {
        $this->sipCustomer = new SIPCustomer();
        $this->sipTicket = new SIPTicket();
    }

    public function readEmailFromCommandPrompt()
    {
        $data = file_get_contents("php://stdin");
        $this->parseSupportEmail($data);
    }

    public function readEmailFromFile($path)
    {
        $data = file_get_contents($path);
        $this->parseSupportEmail($data);
    }

    protected function parseSupportEmail($data)
    {

        $Parser = new Parser();

        /** For the live production version read the email content
         *  from standard input
         */
        $Parser->setText($data);

        $to = $Parser->getHeader('to');
        $from = preg_replace('/.*</', '', $Parser->getHeader('from'));
        $from = preg_replace('/>.*/', '', $from);

        $subject = $Parser->getHeader('subject');
        $text = $Parser->getMessageBody('text');
        $html = $Parser->getMessageBody('html');
        $attachments = $Parser->getAttachments();
        $ticketContents = $text;

        if ($ticketContents == '' && trim($html) != '')
        {
            $ticketContents = convert_html_to_text($html);
        }

        if ($this->shouldIgnoreSender($from, $subject))
        {
            Log::info("parseSupportEmail(): Sender " . $from . " with subject: " . $subject . " on ignore list. Ignored ... ");

            return false;
        }

        if ($from == 'feedback@fusedsolutions.com' || $from == 'frontlinefusedsupervisors@frontlinecallcenter.com')
        {
            $subject = preg_replace('/^.*Fused:/', 'Fused:', $subject);
            Log::debug("parseSupportEmail(): Fused email detected: $subject");
            $this->processFusedEmail($to, $from, $subject, $ticketContents, $html, $attachments);
        } else if ($from == 'website@silverip.com')
        {
            $subject = preg_replace('/^.*Website Help:/', '', $subject);
            Log::debug("parseSupportEmail(): Website email detected: $subject");
            $this->processWebsiteEmail($to, $from, $subject, $ticketContents, $html, $attachments);
        } else
        {
            $customer = $this->sipCustomer->getCustomerByEmail($from);
            $adminUserId = $this->sipCustomer->getAdminUserIdByEmail($from);

            $ticketMatchArr = $this->findTicketInSubject($subject);
            if (count($ticketMatchArr) > 0)
            {
                $cleanEmailBody = $this->removeEmailHistory($ticketContents);
                $this->sipTicket->updateTicketByNumber($ticketMatchArr[0], config('const.ticket_status.escalated'), $cleanEmailBody, $adminUserId, false);
            } else
            {
                if ($customer != null)
                {
                    Log::debug("parseSupportEmail(): Sender  " . $from . " is customer id=" . $customer->id . ". Creating a ticket for this customer ... ");
                    $this->sipTicket->createTicket($customer->id, config('const.reason.other'), config('const.ticket_status.escalated'), $ticketContents, $adminUserId, false);
                } else
                {
                    Log::debug("parseSupportEmail(): Sender  " . $from . " not found in the customer database. Creating a generic ticket ... ");
                    $this->sipTicket->createTicket('1', config('const.reason.other'), config('const.ticket_status.escalated'), $ticketContents, $adminUserId, false);
                }
            }

            $this->saveEmailOnLocalDisk($to, $from, $subject, $text, $html, $attachments);

            /** Handling attachments * */
            /*
              $save_dir = '/var/www/scripts/esupport/attachments/';
              foreach($attachments as $attachment) {
              // get the attachment name
              $filename = $attachment->filename;
              // write the file to the directory you want to save it in
              if ($fp = fopen($save_dir.$filename, 'w')) {
              while($bytes = $attachment->read()) {
              fwrite($fp, $bytes);
              }
              fclose($fp);
              }
              }
             */

            //echo $emailMessage;
            //exit;
        }
    }

    protected function processFusedEmail($to, $from, $subject, $text, $html, $attachments)
    {

        $subjectArr = explode(' ', $subject);
        $subjectLength = count($subjectArr);
        if ($subjectLength >= 6)
        {
            $locCode = $subjectArr[1];
            $unitNumber = str_replace('#', '', $subjectArr[2]);
            $ticketIssue = '0';

            # Set the status for Fused tickets to the default: escalated
            $ticketStatus = config('const.ticket_status.escalated');
            $caseId = $this->findVendorCaseIdInBody($text);
            $caseNotes = $text;

            $customerId = 1; // Default 'UNKNOWN' customer
            $customer = null;

            if ($locCode != '' && $unitNumber != '')
            {
                $customer = $this->sipCustomer->getActiveCustomerByLocUnitNumber($locCode, $unitNumber);
            }

            if ($customer != null)
            {
                Log::debug("processFusedEmail(): Found active customer for " . $locCode . ' #' . $unitNumber);
                $customerId = $customer->id;
            }

            if ($caseId == '')
            {
                Log::debug("processFusedEmail(): No existing Case ID found. Creating a new ticket with customer id=" . $customerId . " ... ");
                $this->sipTicket->createTicket($customerId, $ticketIssue, $caseNotes, $ticketStatus, 0, false, $caseId);
            } else
            {
                $ticket = $this->sipTicket->getSupportTicketByVendorId($caseId);
                if ($ticket != null)
                {
                    Log::debug("processFusedEmail(): Case ID is present and an existing ticket was found. Updating ticket for customer in " . $locCode . ' #' . $unitNumber . " ... ");
                    $cleanEmailBody = $this->removeEmailHistory($caseNotes);
                    $this->sipTicket->updateTicket($ticket, $ticket->id_reasons, $ticketStatus, $cleanEmailBody, 0, false);
                } else
                {
                    Log::debug("processFusedEmail(): Case ID is present but no existing ticket found. Creating a new ticket with customer id=" . $customerId . " ... ");
                    $this->sipTicket->createTicket($customerId, config('const.reason.unknown'), config('const.ticket_status.escalated'), $caseNotes, 0);
                }
            }
            $this->saveEmailOnLocalDisk($to, $from, $subject, $text, $html, $attachments);

        } else
        {
            Log::debug("processFusedEmail(): Subject too short: should be 6 or more words long. Ignoring email with subject: " . $subject);
        }
    }

    protected function processWebsiteEmail($to, $from, $subject, $text, $html, $attachments)
    {

        $ticketStatus = 'escalated';
        $customerId = 1;

        $phone1 = '';
        $phone2 = '';
        $email = '';

        $phoneOrEmailMatchArr = array();
        /*
         * $phoneOrEmailMatchArr[0] will have the entire matched string
         * $phoneOrEmailMatchArr[1] will have the first matched ()
         */
        preg_match('/.*Phone Or Email:\s*\*\s*(.*@.*)/', $text, $phoneOrEmailMatchArr);
        if ( ! empty($phoneOrEmailMatchArr))
        {
            $email = trim(strtolower($phoneOrEmailMatchArr[1]));
//        Log::info("Email detected: " . $email);
        } else
        {
            preg_match('/Phone Or Email:\s*\*\s*(.*)/', $text, $phoneOrEmailMatchArr);
            if ( ! empty($phoneOrEmailMatchArr))
            {
                $phone = trim(strtolower($phoneOrEmailMatchArr[1]));
                $phone1 = preg_replace('/[- ]/', '', $phone);
                if (strlen($phone1) >= 10)
                {
                    $phone2 = substr_replace($phone1, '-', 3, 0);
                    $phone2 = substr_replace($phone2, '-', 7, 0);
                } else
                {
                    $phone2 = $phone1;
                }
//            Log::info("Phone detected: \$phone1 = " . $phone1 . "  & \$phone2 = $phone2");
            }
        }

        $customerInfo = array();

        if ($email != '')
        {
            $customer = $this->sipCustomer->getCustomerByEmail($email);
        } else if ($phone1 != '')
        {
            $customer = $this->sipCustomer->getActiveCustomerByPhoneNumber($phone1);
            if ($customer == null)
            {
                $customer = $this->sipCustomer->getActiveCustomerByPhoneNumber($phone2);
            }
        }

        if ($customer != null)
        {
            Log::debug("processWebsiteEmail(): Found customer id=" . $customer->id . ". Creating a ticket for this customer ... ");
            $customerId = $customer->id;
        }

        $this->sipTicket->createTicket($customerId, config('const.reason.unknown'), $text, $ticketStatus, 0, false);
    }

    protected function saveEmailOnLocalDisk($to, $from, $subject, $text, $html, $attachments)
    {
        $emailMessage = 'to: ' . $to . "\n";
        $emailMessage .= 'from: ' . $from . "\n";
        $emailMessage .= 'subject: ' . $subject . "\n";
        $emailMessage .= "text version:\n" . $text . "\n";
        $emailMessage .= "html version:\n" . $html . "\n";
        $emailMessage .= "attachements:\n" . print_r($attachments, true) . "\n";

        $timeString = date('m-d-Y_H-i-s');
        $myFile = config('mail.processed-email-directory') . '/email_' . $timeString . '.txt';
        $fh = fopen($myFile, 'w') or die("can't open file");
        fwrite($fh, "Email Header:\n" . $emailMessage);
        fclose($fh);
        chmod($myFile, 0755);
    }

    protected function removeEmailHistory($emailBody)
    {

        $emailPattern = '[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})';
        $cleanedEmailBodyLines = array();
        $emailBodyLines = preg_split("/\\r\\n|\\r|\\n/", $emailBody);

        $line = array_shift($emailBodyLines);
        while (isset($line) && preg_match('/^>+.*/', $line) !== 1 && (preg_match('/^[Oo]n.*wrote:\s*/', $line) !== 1 && preg_match('/^[Oo]n\s+.*<' . $emailPattern . '>\s*/', $line) !== 1))
        {

            $cleanedEmailBodyLines[] = $line;
            $line = array_shift($emailBodyLines);
        }

        return implode("\n", $cleanedEmailBodyLines);
    }

    protected function shouldIgnoreSender($email, $subject)
    {
        /**  Email filters **
         *
         * @ip-echelon.com
         * @radyn.com
         * @intelpath.com
         * @micronetcom.com
         * @wacorp.net
         * @copyright-compliance.com
         * no-reply@asana.com
         */
        if (preg_match('/^.*\@ip-echelon\.com/', $email) ||
            preg_match('/^.*\@radyn\.com/', $email) ||
            preg_match('/^.*\@intelpath\.com/', $email) ||
            preg_match('/^.*\@micronetcom\.com/', $email) ||
            preg_match('/^.*\@wacorp\.com/', $email) ||
            preg_match('/^.*\@copyright-compliance\.com/', $email) ||
            preg_match('/^.*no-reply\@asana\.com/', $email)
        )
        {
            return true;
        }

        if ($email == 'help@silverip.com' && preg_match('/^Account Update Notification:/', $subject))
        {
            return true;
        }

        if ($email == 'noreply@silverip.net')
        {
            return true;
        }

        return false;
    }

    protected function findTicketInSubject($subject)
    {
        $ticketMatchArr = array();
        $ticketMatch = preg_match('/ST-[0-9]+/', $subject, $ticketMatchArr);

        return $ticketMatchArr;
    }

    protected function findVendorCaseIdInBody($body)
    {
        $caseId = '';
        $caseIdMatchArr = array();
        preg_match('/Case ID:\s+(.*)/', $body, $caseIdMatchArr);
        if ( ! empty($caseIdMatchArr))
        {
            /*
             * $caseIDMatchArr[0] will have the entire matched string
             * $caseIDMatchArr[1] will have the first matched ()
             *
             */
            $caseId = $caseIdMatchArr[1];
        }

        return $caseId;
    }

}