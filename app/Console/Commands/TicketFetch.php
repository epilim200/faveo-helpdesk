<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\MailFetch as Fetch;
use App\Http\Controllers\Agent\helpdesk\MailController;
use App\Http\Controllers\Agent\helpdesk\TicketWorkflowController;
use Illuminate\Console\Command;

class TicketFetch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:fetch
                            {--preview : Preview emails without creating tickets}
                            {--since= : Date to fetch emails from (format: Y-m-d, e.g., 2024-01-15)}
                            {--status=unread : Email read status: unread, read, or all}
                            {--timeout=30 : Connection timeout in seconds}
                            {--retry=2 : Number of retry attempts on connection failure}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetching the tickets from service provider';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (isInstall()) {
            if ($this->option('preview')) {
                $this->previewEmails();

                return;
            }

            // Check if custom options are provided
            $sinceDate = $this->option('since');
            $status = $this->option('status');

            // If custom options provided, use custom fetch method
            if ($sinceDate || $status !== 'unread') {
                $this->fetchEmailsWithOptions($sinceDate, $status);

                return;
            }

            // Default behavior (original code)
            $controller = $this->mailController();
            $emails = new \App\Model\helpdesk\Email\Emails();
            $settings_email = new \App\Model\helpdesk\Settings\Email();
            $system = new \App\Model\helpdesk\Settings\System();
            $ticket = new \App\Model\helpdesk\Settings\Ticket();
            $controller->readmails($emails, $settings_email, $system, $ticket);
            event('ticket.fetch', ['event' => '']);
            loging('fetching-ticket', 'Ticket has read', 'info');
            $this->info('Ticket has read');
        }
    }

    /**
     * Fetch emails with custom date and status options.
     *
     * @param string|null $sinceDate
     * @param string $status
     *
     * @return void
     */
    protected function fetchEmailsWithOptions($sinceDate, $status)
    {
        $settings_email = new \App\Model\helpdesk\Settings\Email();
        $emails = new \App\Model\helpdesk\Email\Emails();

        if ($settings_email->first()->email_fetching != 1) {
            $this->error('Email fetching is disabled in settings.');

            return;
        }

        if ($settings_email->first()->all_emails != 1) {
            $this->error('Fetching from all emails is disabled.');

            return;
        }

        $emailAccounts = $emails->get();

        if ($emailAccounts->count() == 0) {
            $this->warn('No email accounts configured.');

            return;
        }

        // Parse and validate date
        if ($sinceDate) {
            try {
                $parsedDate = new \DateTime($sinceDate);
                $this->info('Fetching emails since: '.$parsedDate->format('Y-m-d'));
            } catch (\Exception $e) {
                $this->error('Invalid date format. Use Y-m-d (e.g., 2024-01-15)');

                return;
            }
        } else {
            $sinceDate = date('Y-m-d', strtotime('-1 days'));
            $this->info('Fetching emails since: '.$sinceDate.' (default: last 1 day)');
        }

        // Validate status
        $status = strtolower($status);
        if (!in_array($status, ['unread', 'read', 'all'])) {
            $this->error('Invalid status. Use: unread, read, or all');

            return;
        }

        $statusLabel = [
            'unread' => 'Unread only',
            'read' => 'Read only',
            'all' => 'All emails',
        ];
        $this->info('Filter: '.$statusLabel[$status]);
        $this->newLine();

        $totalTickets = 0;

        foreach ($emailAccounts as $email) {
            $count = $this->fetchFromAccountWithOptions($email, $sinceDate, $status);
            $totalTickets += $count;
        }

        event('ticket.fetch', ['event' => '']);
        loging('fetching-ticket', 'Ticket has read', 'info');
        $this->newLine();
        $this->info("Total tickets created/updated: $totalTickets");
    }

    /**
     * Fetch emails from a single account with custom options.
     *
     * @param object $email
     * @param string $sinceDate
     * @param string $status
     *
     * @return int Number of tickets created
     */
    protected function fetchFromAccountWithOptions($email, $sinceDate, $status)
    {
        $this->info('=== Account: '.$email->email_address.' ===');

        if (!$email->fetching_status) {
            $this->warn('  Fetching is disabled for this account.');

            return 0;
        }

        if (empty($email->fetching_host) || empty($email->fetching_port) || empty($email->fetching_protocol)) {
            $this->warn('  Fetching not configured (missing host, port, or protocol).');

            return 0;
        }

        try {
            // Build IMAP connection string
            $mailbox = '{'.$email->fetching_host.':'.$email->fetching_port.'/'.$email->fetching_protocol;
            if ($email->fetching_encryption) {
                $mailbox .= '/'.$email->fetching_encryption;
            }
            if ($email->mailbox_protocol) {
                $mailbox .= '/'.$email->mailbox_protocol;
            }
            $mailbox .= '}INBOX';

            // Connect using raw IMAP
            $imapStream = @imap_open($mailbox, $email->email_address, $email->password, 0, 1);

            if (!$imapStream) {
                $this->error('  Error connecting: '.imap_last_error());

                return 0;
            }

            $date = date('d M Y', strtotime($sinceDate));

            // Build search query based on status
            $searchQuery = "SINCE \"$date\"";
            if ($status === 'unread') {
                $searchQuery .= ' UNSEEN';
            } elseif ($status === 'read') {
                $searchQuery .= ' SEEN';
            }

            $messageIds = imap_search($imapStream, $searchQuery, SE_UID);

            if (!$messageIds || count($messageIds) == 0) {
                $this->warn('  No emails found matching criteria.');
                imap_close($imapStream);

                return 0;
            }

            $this->info('  Found '.count($messageIds).' email(s). Processing...');

            $ticketCount = 0;
            $controller = new MailController($this->workflowController());

            foreach ($messageIds as $uid) {
                try {
                    // Get email data using raw IMAP functions
                    $overview = imap_fetch_overview($imapStream, $uid, FT_UID);
                    $overview = $overview ? $overview[0] : null;

                    if (!$overview) {
                        $this->error("    ✗ Could not fetch message #$uid");
                        continue;
                    }

                    // Get headers
                    $headerInfo = imap_headerinfo($imapStream, imap_msgno($imapStream, $uid));

                    // Get from address
                    $fromAddress = [];
                    if (isset($headerInfo->from[0])) {
                        $from = $headerInfo->from[0];
                        $fromEmail = isset($from->mailbox) && isset($from->host)
                            ? $from->mailbox.'@'.$from->host
                            : (isset($from->mailbox) ? $from->mailbox : 'unknown@unknown.com');
                        $fromName = isset($from->personal) ? $this->decodeHeader($from->personal) : '';
                        $fromAddress = [['address' => $fromEmail, 'name' => $fromName]];
                    }

                    // Get subject
                    $subject = isset($overview->subject) ? $this->decodeHeader($overview->subject) : '(No Subject)';

                    // Get body
                    $body = $this->getMessageBody($imapStream, $uid);
                    $body = $this->separateReply($body);

                    // Get collaborators (CC, BCC, TO)
                    $collaborators = $this->getCollaboratorsFromHeader($headerInfo, $email->email_address);

                    // Get attachments
                    $attachments = $this->getAttachmentsFromMessage($imapStream, $uid);

                    // Process ticket using workflow
                    $controller->workflow($fromAddress, $subject, $body, $collaborators, $attachments, $email);
                    $ticketCount++;

                    $displayFrom = isset($fromAddress[0]['address']) ? $fromAddress[0]['address'] : 'Unknown';
                    $this->line("    ✓ Processed: $displayFrom - $subject");
                } catch (\Exception|\Error $e) {
                    $this->error('    ✗ Error processing message #'.$uid.': '.$e->getMessage());
                }
            }

            imap_close($imapStream);
            $this->info("  Created/updated $ticketCount ticket(s).");

            return $ticketCount;
        } catch (\Exception|\Error $e) {
            $this->error('  Error: '.$e->getMessage());

            return 0;
        }
    }

    /**
     * Get message body using raw IMAP.
     *
     * @param resource $imapStream
     * @param int $uid
     *
     * @return string
     */
    protected function getMessageBody($imapStream, $uid)
    {
        $structure = imap_fetchstructure($imapStream, $uid, FT_UID);
        $body = '';

        if (!isset($structure->parts)) {
            // Simple message
            $body = imap_fetchbody($imapStream, $uid, '1', FT_UID);
            $body = $this->decodeBody($body, $structure->encoding ?? 0);
        } else {
            // Multipart message - find HTML or plain text
            $body = $this->getMultipartBody($imapStream, $uid, $structure);
        }

        // Convert to UTF-8 if needed
        if ($structure && isset($structure->parameters)) {
            foreach ($structure->parameters as $param) {
                if (strtolower($param->attribute) == 'charset' && strtolower($param->value) != 'utf-8') {
                    $body = mb_convert_encoding($body, 'UTF-8', $param->value);
                    break;
                }
            }
        }

        return $body;
    }

    /**
     * Get body from multipart message.
     *
     * @param resource $imapStream
     * @param int $uid
     * @param object $structure
     *
     * @return string
     */
    protected function getMultipartBody($imapStream, $uid, $structure)
    {
        $htmlBody = '';
        $plainBody = '';

        foreach ($structure->parts as $partNum => $part) {
            $partNumber = $partNum + 1;

            if ($part->subtype == 'HTML') {
                $htmlBody = imap_fetchbody($imapStream, $uid, $partNumber, FT_UID);
                $htmlBody = $this->decodeBody($htmlBody, $part->encoding ?? 0);
            } elseif ($part->subtype == 'PLAIN') {
                $plainBody = imap_fetchbody($imapStream, $uid, $partNumber, FT_UID);
                $plainBody = $this->decodeBody($plainBody, $part->encoding ?? 0);
            }
        }

        return $htmlBody ?: $plainBody;
    }

    /**
     * Decode email body based on encoding.
     *
     * @param string $body
     * @param int $encoding
     *
     * @return string
     */
    protected function decodeBody($body, $encoding)
    {
        switch ($encoding) {
            case 3: // BASE64
                return base64_decode($body);
            case 4: // QUOTED-PRINTABLE
                return quoted_printable_decode($body);
            default:
                return $body;
        }
    }

    /**
     * Get collaborators from email header.
     *
     * @param object $headerInfo
     * @param string $excludeEmail
     *
     * @return array
     */
    protected function getCollaboratorsFromHeader($headerInfo, $excludeEmail)
    {
        $collaborators = [];

        foreach (['cc', 'bcc', 'to'] as $field) {
            if (isset($headerInfo->$field)) {
                foreach ($headerInfo->$field as $addr) {
                    if (isset($addr->mailbox) && isset($addr->host)) {
                        $email = $addr->mailbox.'@'.$addr->host;
                        if ($email !== $excludeEmail) {
                            $name = isset($addr->personal) ? $this->decodeHeader($addr->personal) : '';
                            $collaborators[$email] = $name;
                        }
                    }
                }
            }
        }

        return $collaborators;
    }

    /**
     * Get attachments from message.
     *
     * @param resource $imapStream
     * @param int $uid
     *
     * @return array
     */
    protected function getAttachmentsFromMessage($imapStream, $uid)
    {
        $attachments = [];
        $structure = imap_fetchstructure($imapStream, $uid, FT_UID);

        if (!isset($structure->parts)) {
            return $attachments;
        }

        foreach ($structure->parts as $partNum => $part) {
            if (isset($part->disposition) && strtolower($part->disposition) == 'attachment') {
                $filename = 'attachment';
                if (isset($part->dparameters)) {
                    foreach ($part->dparameters as $param) {
                        if (strtolower($param->attribute) == 'filename') {
                            $filename = $this->decodeHeader($param->value);
                            break;
                        }
                    }
                }

                $data = imap_fetchbody($imapStream, $uid, $partNum + 1, FT_UID);
                $data = $this->decodeBody($data, $part->encoding ?? 0);

                $attachments[] = [
                    'filename' => $filename,
                    'data' => $data,
                    'type' => $part->subtype ?? 'OCTET-STREAM',
                    'size' => strlen($data),
                ];
            }
        }

        return $attachments;
    }

    /**
     * Get workflow controller instance.
     *
     * @return TicketWorkflowController
     */
    protected function workflowController()
    {
        $PhpMailController = new \App\Http\Controllers\Common\PhpMailController();
        $NotificationController = new \App\Http\Controllers\Common\NotificationController();
        $ticket = new \App\Http\Controllers\Agent\helpdesk\TicketController($PhpMailController, $NotificationController);

        return new TicketWorkflowController($ticket);
    }

    /**
     * Get collaborators from email message.
     *
     * @param object $message
     * @param object $email
     *
     * @return array
     */
    protected function getCollaborators($message, $email)
    {
        $this_address = $email->email_address;
        $collaborator_cc = $message->getAddresses('cc');
        $collaborator_bcc = $message->getAddresses('bcc');
        $collaborator_to = $message->getAddresses('to');
        $array = [];

        foreach ([$collaborator_cc, $collaborator_bcc, $collaborator_to] as $addresses) {
            if ($addresses) {
                foreach ($addresses as $addr) {
                    $name = isset($addr['name']) ? $addr['name'] : '';
                    $address = isset($addr['address']) ? $addr['address'] : '';
                    if ($address) {
                        $array[$address] = $name;
                    }
                }
            }
        }

        if (array_key_exists($this_address, $array)) {
            unset($array[$this_address]);
        }

        return $array;
    }

    /**
     * Separate reply content from email body.
     *
     * @param string $body
     *
     * @return string
     */
    protected function separateReply($body)
    {
        $body2 = explode('---Reply above this line---', $body);
        if (is_array($body2) && array_key_exists(0, $body2)) {
            $body = $body2[0];
        }

        return $body;
    }

    /**
     * Preview emails without creating tickets.
     *
     * @return void
     */
    public function previewEmails()
    {
        $settings_email = new \App\Model\helpdesk\Settings\Email();
        $emails = new \App\Model\helpdesk\Email\Emails();

        if ($settings_email->first()->email_fetching != 1) {
            $this->error('Email fetching is disabled in settings.');

            return;
        }

        if ($settings_email->first()->all_emails != 1) {
            $this->error('Fetching from all emails is disabled.');

            return;
        }

        $emailAccounts = $emails->get();

        if ($emailAccounts->count() == 0) {
            $this->warn('No email accounts configured.');

            return;
        }

        $sinceDate = $this->option('since');
        if ($sinceDate) {
            try {
                $parsedDate = new \DateTime($sinceDate);
                $this->info('Previewing emails since: '.$parsedDate->format('Y-m-d'));
            } catch (\Exception $e) {
                $this->error('Invalid date format. Use Y-m-d (e.g., 2024-01-15)');

                return;
            }
        } else {
            $sinceDate = date('Y-m-d', strtotime('-1 days'));
            $this->info('Previewing emails since: '.$sinceDate.' (default: last 1 day)');
        }

        $status = strtolower($this->option('status'));
        if (!in_array($status, ['unread', 'read', 'all'])) {
            $this->error('Invalid status. Use: unread, read, or all');

            return;
        }

        $statusLabel = [
            'unread' => 'Unread only',
            'read' => 'Read only',
            'all' => 'All emails',
        ];
        $this->info('Filter: '.$statusLabel[$status]);
        $this->info('Checking '.count($emailAccounts).' account(s)...');
        $this->newLine();

        $timeout = (int) $this->option('timeout');
        $retries = (int) $this->option('retry');

        foreach ($emailAccounts as $email) {
            $this->previewFromAccount($email, $sinceDate, $status, $timeout, $retries);
        }
    }

    /**
     * Preview emails from a single account.
     *
     * @param object $email
     * @param string $sinceDate
     * @param string $status
     * @param int $timeout
     * @param int $retries
     *
     * @return void
     */
    public function previewFromAccount($email, $sinceDate, $status, $timeout = 30, $retries = 2)
    {
        $this->info('=== Account: '.$email->email_address.' ===');

        // Check if fetching is enabled for this email
        if (!$email->fetching_status) {
            $this->warn('  Fetching is disabled for this account.');
            $this->newLine();

            return;
        }

        // Check required fetching configuration
        if (empty($email->fetching_host) || empty($email->fetching_port) || empty($email->fetching_protocol)) {
            $this->warn('  Fetching not configured (missing host, port, or protocol).');
            $this->newLine();

            return;
        }

        // Set IMAP timeout
        if (function_exists('imap_timeout')) {
            imap_timeout(IMAP_OPENTIMEOUT, $timeout);
            imap_timeout(IMAP_READTIMEOUT, $timeout);
            imap_timeout(IMAP_WRITETIMEOUT, $timeout);
            imap_timeout(IMAP_CLOSETIMEOUT, $timeout);
        }

        $attempt = 0;
        $lastError = null;

        while ($attempt <= $retries) {
            $attempt++;

            try {
                if ($attempt > 1) {
                    $this->warn("  Retry attempt $attempt of ".($retries + 1).'...');
                    sleep(2); // Wait 2 seconds before retry
                }

                $server = new Fetch(
                    $email->fetching_host,
                    $email->fetching_port,
                    $email->fetching_protocol
                );

                if ($email->fetching_encryption != null && $email->fetching_encryption != '') {
                    $server->setFlag($email->fetching_encryption);
                }
                if ($email->mailbox_protocol) {
                    $server->setFlag($email->mailbox_protocol);
                }
                $server->setAuthentication($email->email_address, $email->password);

                $date = date('d M Y', strtotime($sinceDate));

                // Build search query based on status
                $searchQuery = "SINCE \"$date\"";
                if ($status === 'unread') {
                    $searchQuery .= ' UNSEEN';
                } elseif ($status === 'read') {
                    $searchQuery .= ' SEEN';
                }
                // 'all' = no additional filter

                // Use raw IMAP search to get message UIDs first
                $imapStream = $server->getImapStream();
                $messageIds = imap_search($imapStream, $searchQuery, SE_UID);

                if (!$messageIds || count($messageIds) == 0) {
                    $this->warn('  No emails found.');
                    $this->newLine();

                    return;
                }

                $this->info('  Found '.count($messageIds).' email(s):');
                $this->newLine();

                $tableData = [];
                foreach ($messageIds as $uid) {
                    try {
                        // Get overview using raw IMAP (safer, doesn't parse addresses)
                        $overview = imap_fetch_overview($imapStream, $uid, FT_UID);
                        $overview = $overview ? $overview[0] : null;

                        if (!$overview) {
                            $tableData[] = [
                                'from' => 'Error',
                                'subject' => 'Could not fetch message #'.$uid,
                                'date' => '-',
                                'status' => '-',
                                'attachments' => '-',
                            ];
                            continue;
                        }

                        // Get from address from raw headers and decode MIME encoding
                        $fromEmail = isset($overview->from) ? $this->decodeHeader($overview->from) : 'Unknown';
                        $subject = isset($overview->subject) ? $this->decodeHeader($overview->subject) : '(No Subject)';
                        $msgDate = 'Unknown';
                        if (isset($overview->date)) {
                            $dateTime = new \DateTime($overview->date);
                            $dateTime->setTimezone(new \DateTimeZone('Asia/Kuala_Lumpur'));
                            $msgDate = $dateTime->format('Y-m-d H:i');
                        }
                        $isRead = isset($overview->seen) && $overview->seen ? 'Read' : 'Unread';

                        // Count attachments using structure
                        $attachmentCount = 0;
                        try {
                            $structure = imap_fetchstructure($imapStream, $uid, FT_UID);
                            if (isset($structure->parts)) {
                                foreach ($structure->parts as $part) {
                                    if (isset($part->disposition) && strtolower($part->disposition) == 'attachment') {
                                        $attachmentCount++;
                                    }
                                }
                            }
                        } catch (\Exception|\Error $e) {
                            $attachmentCount = '?';
                        }

                        $tableData[] = [
                            'from' => $fromEmail,
                            'subject' => strlen($subject) > 50 ? substr($subject, 0, 47).'...' : $subject,
                            'date' => $msgDate,
                            'status' => $isRead,
                            'attachments' => $attachmentCount,
                        ];
                    } catch (\Exception|\Error $e) {
                        $tableData[] = [
                            'from' => 'Error reading #'.$uid,
                            'subject' => substr($e->getMessage(), 0, 40),
                            'date' => '-',
                            'status' => '-',
                            'attachments' => '-',
                        ];
                    }
                }

                $this->table(['From', 'Subject', 'Date', 'Status', 'Attachments'], $tableData);
                $this->newLine();

                return; // Success, exit the retry loop

            } catch (\Exception $e) {
                $lastError = $e->getMessage();

                // Check if it's a timeout error
                if (strpos($lastError, 'timed out') !== false || strpos($lastError, 'timeout') !== false) {
                    if ($attempt <= $retries) {
                        $this->warn("  Connection timed out. Retrying...");
                        continue;
                    }
                }

                // For non-timeout errors or last attempt, show error and exit
                if ($attempt > $retries) {
                    $this->error('  Error connecting: '.$lastError);
                    $this->newLine();

                    return;
                }
            }
        }

        // If we get here, all retries failed
        $this->error('  Failed after '.($retries + 1).' attempts. Last error: '.$lastError);
        $this->newLine();
    }

    public function mailController()
    {
        $PhpMailController = new \App\Http\Controllers\Common\PhpMailController();
        $NotificationController = new \App\Http\Controllers\Common\NotificationController();
        $ticket = new \App\Http\Controllers\Agent\helpdesk\TicketController($PhpMailController, $NotificationController);
        $work = new TicketWorkflowController($ticket);
        $controller = new MailController($work);

        return $controller;
    }

    /**
     * Decode MIME encoded header (e.g., =?UTF-8?Q?...?=).
     *
     * @param string $header
     *
     * @return string
     */
    protected function decodeHeader($header)
    {
        if (!$header) {
            return '';
        }

        // Try mb_decode_mimeheader first (handles most cases well)
        if (function_exists('mb_decode_mimeheader')) {
            $decoded = mb_decode_mimeheader($header);
            if ($decoded && $decoded !== $header) {
                return $decoded;
            }
        }

        // Fallback to imap_mime_header_decode
        $decoded = imap_mime_header_decode($header);
        $result = '';

        foreach ($decoded as $element) {
            $charset = $element->charset;
            $text = $element->text;

            // Convert to UTF-8 if needed
            if ($charset !== 'default' && $charset !== 'UTF-8' && function_exists('mb_convert_encoding')) {
                $text = mb_convert_encoding($text, 'UTF-8', $charset);
            }

            $result .= $text;
        }

        return $result ?: $header;
    }
}
