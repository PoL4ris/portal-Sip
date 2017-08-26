<?php

namespace App\Extensions;

use App\Models\Ticket;

class SIPTicket {

    /**
     * @param Request $request
     * id_customers = id_customers to add new ticket TO.
     * id_reasons = id_reason of the ticket.
     * comment = comment of the ticket, Description.
     * status = status of the ticket, Escalated/Closed.
     * @return OK after sending Mail of new ticket created.
     */
    public function createTicket($customerId, $ticketReason, $ticketStatus, $ticketComment, $adminUserId, $vendorTicketId = '', $sendEmail = false)
    {
        $newTicket = new Ticket;

        $newTicket->id_customers = $customerId;
        $newTicket->ticket_number = $this->generateTicketNumber();
        $newTicket->id_reasons = $ticketReason;
        $newTicket->comment = $ticketComment;
        $newTicket->status = $ticketStatus;
        $newTicket->id_users = $adminUserId;
        $newTicket->save();

        return $newTicket;
        //1 = new ticket
        //2 = update ticket
//        SendMail::ticketMail($newTicket, 1);
//        return 'OK';
    }

    protected function generateTicketNumber()
    {
        $lastTicketId = Ticket::max('id');
        $lastTicketNumber = Ticket::find($lastTicketId)->ticket_number;
        $ticketNumber = explode('ST-', $lastTicketNumber);
        $ticketNumberCast = (int) $ticketNumber[1] + 1;

        return 'ST-' . $ticketNumberCast;
    }

    public function getSupportTicketByVendorId($vendorTicketId)
    {
        return Ticket::where('vendor_ticket', $vendorTicketId)->first();
    }

    public function getSupportTicketByTicketNumber($ticketNumber)
    {
        return Ticket::where('ticket_number', $ticketNumber)->first();
    }

//    public function updateTicketById($ticketId, $updateStatus, $updateComment, $adminUserId, $sendEmail = false)
//    {
//
//        if ($ticketNumber != '' && $updateComment != '' && $updateStatus != '')
//        {
//            $ticket = $this->getSupportTicketByTicketNumber($ticketNumber);
//
//            return $this->updateTicket($ticket, $ticket->id_reasons, $updateStatus, $updateComment, $adminUserId, $sendEmail);
//        }
//
//        return false;
//    }

    public function updateTicketByNumber($ticketNumber, $updateStatus, $updateComment, $adminUserId, $sendEmail = false)
    {

        if ($ticketNumber != '' && $updateComment != '' && $updateStatus != '')
        {
            $ticket = $this->getSupportTicketByTicketNumber($ticketNumber);

            return $this->updateTicket($ticket, $ticket->id_reasons, $updateStatus, $updateComment, $adminUserId, $sendEmail);
        }

        return false;
    }

    public function updateTicket(Ticket $ticket, $updateReason, $updateStatus, $updateComment, $adminUserId, $sendEmail = false)
    {

        $ticketHistory = new TicketHistory;
        $ticketHistory->id_tickets = $ticket->id;
        $ticketHistory->id_reasons = $updateReason;
        $ticketHistory->comment = $updateComment;
        $ticketHistory->status = $updateStatus;
        $ticketHistory->id_users = $adminUserId;
        $ticketHistory->save();

        $ticket->id_reasons = $updateReason;
        $ticket->status = $updateStatus;
        $ticket->save();

//        if($AdminUser_ID == 0){
//            $updateStrSQL .= ", `ReadStatus` = '1'";
//        } else {
//            $updateStrSQL .= ", `ReadStatus` = '0'";
//        }

        //1 = new ticket
        //2 = update ticket
//        SendMail::ticketMail($newTicket, 1);
//        return 'OK';


        return $ticketHistory->id;
    }

//    protected function ticketMail()
//    {
//
//        $reasonInfoSql = "SELECT * FROM supportTicketReasons WHERE `RID` = '" . $issue . "'";
//        $reasonInfoRes = mysql_query($reasonInfoSql) or die(mysql_error());
//        $reasonInfoRow = mysql_fetch_array($reasonInfoRes);
//        if ($newTicketStatus == 'escalated')
//        {
//            $mail_config ['emailHeader'] = 'Ticket Update';
//            $mail_config['senderName'] = 'Ticket Update';
//        } else
//        {
//            $mail_config ['emailHeader'] = 'Ticket Closed';
//            $mail_config['senderName'] = 'Ticket Closed';
//        }
//        $customerInfo = getCustomerByTID($ticketID);
//        $adminUserInfo = getAdminUserByID($AdminUser_ID);
//        $serviceLocInfo = getServiceLocationByCID($customerInfo['CID']);
//
//
//        $mail_config ['fields']['Ticket Status'] = ucfirst($newTicketStatus);
//        if ($customerInfo['CID'] == '0')
//        {
//            $mail_config ['fields']['Name'] = 'Unknown';
//        } else
//        {
//            $mail_config ['fields']['Name'] = $customerInfo['Firstname'] . ' ' . $customerInfo['Lastname'];
//        }
//        $mail_config ['fields']['Ticket'] = '<a href="https://admin.silverip.net/customerinfo/browser_detect.php?tid=' . $ticketID . '">' . $ticketNumber . '</a>';
//        $mail_config ['fields']['Timestamp'] = date("g:i a M j, Y ", strtotime($currTimestamp));
//        if (trim($customerInfo['Address']) != '')
//        {
//            $mail_config ['fields']['Address'] = $customerInfo['Address'] . ', #' . $customerInfo['Unit'];
//        }
//        if (trim($customerInfo['Tel']) != '')
//        {
//            $mail_config ['fields']['Phone'] = $customerInfo['Tel'];
//        }
//        if (trim($customerInfo['Email']) != '')
//        {
//            $mail_config ['fields']['Email'] = $customerInfo['Email'];
//        }
//        $mail_config ['fields']['Call Taker'] = $adminUserInfo['Name'];
//        $mail_config ['fields']['Reason For Calling'] = $reasonInfoRow['ReasonName'];
//        $mail_config ['fields']['Call Details'] = $newTicketDetails;
//        $mail_config ['Unit'] = $customerInfo['Unit'];
//        $mail_config ['ReasonCode'] = $reasonInfoRow['ReasonCategory'];
//        $mail_config ['LocCode'] = $serviceLocInfo['ShortName'];
//        $mail_config ['recipient'] = array('Silver Support Portal' => 'help@silverip.com');
////            $mail_config['senderName'] = 'Silver Support Portal';
//        $mail_config['serverSenderName'] = $mail_config['senderName'];
//        $mail_config['serverSenderEmail'] = 'noreply@silverip.net';
//        $mail_config['emailServerHostname'] = 'mail.silverip.net';
//
//        sendSipEmail($mail_config);
//    }
}