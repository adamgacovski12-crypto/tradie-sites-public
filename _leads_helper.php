<?php
/**
 * Shared lead-writing + notification helper.
 * Used by both submit-lead.php (form / fallback) and chat.php (chatbot booking).
 *
 * $data keys (all optional unless marked):
 *   name* phone* email trade suburb message source
 *   has_website goal callback_slot conversation_snippet
 *
 * Returns ['success' => bool, 'error' => string?].
 */

function tradie_write_and_email_lead(array $data): array {
    $name                 = trim((string)($data['name']    ?? ''));
    $phone                = trim((string)($data['phone']   ?? ''));
    $email                = trim((string)($data['email']   ?? ''));
    $trade                = trim((string)($data['trade']   ?? ''));
    $suburb               = trim((string)($data['suburb']  ?? ''));
    $message              = trim((string)($data['message'] ?? ''));
    $source               = trim((string)($data['source']  ?? 'form'));
    $has_website          = trim((string)($data['has_website']          ?? ''));
    $goal                 = trim((string)($data['goal']                 ?? ''));
    $callback_slot        = trim((string)($data['callback_slot']        ?? ''));
    $conversation_snippet = trim((string)($data['conversation_snippet'] ?? ''));

    if ($name === '' || $phone === '') {
        return ['success' => false, 'error' => 'Name and phone number are required.'];
    }

    $date     = date('Y-m-d H:i:s');
    $csvFile  = __DIR__ . '/leads.csv';
    $header   = ['Date','Name','Phone','Email','Trade','Suburb','Message','Source','HasWebsite','Goal','CallbackSlot','ConversationSnippet'];
    $row      = [$date,$name,$phone,$email,$trade,$suburb,$message,$source,$has_website,$goal,$callback_slot,$conversation_snippet];

    $isNew = !file_exists($csvFile);
    $fp = fopen($csvFile, 'a');
    if ($fp === false) {
        return ['success' => false, 'error' => 'Unable to save lead. Please call us directly.'];
    }
    flock($fp, LOCK_EX);
    if ($isNew) fputcsv($fp, $header);
    fputcsv($fp, $row);
    flock($fp, LOCK_UN);
    fclose($fp);

    $adminEmail = getenv('ADMIN_EMAIL') ?: 'info@tradiebud.tech';
    $qualified  = ($callback_slot !== '' || ($source === 'chatbot' && $trade !== ''));

    if ($qualified) {
        $subjTrade  = $trade  !== '' ? $trade  : 'Unknown trade';
        $subjSuburb = $suburb !== '' ? $suburb : 'Australia';
        $subjSlot   = $callback_slot !== '' ? $callback_slot : 'ASAP';
        $subject = "\xF0\x9F\x94\xA5 QUALIFIED LEAD: {$subjTrade} in {$subjSuburb} — wants callback {$subjSlot}";
    } else {
        $subject = "New Lead — {$name}" . ($trade !== '' ? " — {$trade}" : '');
    }

    $telDigits = preg_replace('/[^0-9+]/', '', $phone);
    $body  = "QUALIFICATION\n";
    $body .= "──────────────────\n";
    $body .= "Date:             {$date}\n";
    $body .= "Name:             {$name}\n";
    $body .= "Phone:            {$phone}  (tel:{$telDigits})\n";
    $body .= "Email:            " . ($email  !== '' ? $email  : '—') . "\n";
    $body .= "Trade:            " . ($trade  !== '' ? $trade  : '—') . "\n";
    $body .= "Suburb:           " . ($suburb !== '' ? $suburb : '—') . "\n";
    $body .= "Has website:      " . ($has_website   !== '' ? $has_website   : '—') . "\n";
    $body .= "Primary goal:     " . ($goal          !== '' ? $goal          : '—') . "\n";
    $body .= "Callback slot:    " . ($callback_slot !== '' ? $callback_slot : '—') . "\n";
    $body .= "Source:           {$source}\n";

    if ($message !== '') {
        $body .= "\nMESSAGE\n──────────────────\n{$message}\n";
    }
    if ($conversation_snippet !== '') {
        $body .= "\nLAST MESSAGES\n──────────────────\n{$conversation_snippet}\n";
    }

    $headers  = "From: Tradie Sites Co. <noreply@tradiebud.tech>\r\n";
    $headers .= "Reply-To: " . ($email !== '' ? $email : 'info@tradiebud.tech') . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    @mail($adminEmail, $subject, $body, $headers);

    return ['success' => true];
}
