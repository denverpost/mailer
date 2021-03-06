<?php
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Vary: Accept-Encoding");

$blacklist = file('blacklist_strings', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$ip_ignore = file('blacklist_ips', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
require('config.php');

// Clean the input.
// We whitelist input fields to make sure nothing fishy gets processed.
$clean = array();
$fields = array('comments', 'redirect', 'id', 'which', 'email_address', 'is_ajax', 'friend_email_address', 'subject');
$clean['referer'] = $_SERVER['HTTP_REFERER'];
foreach ( $fields as $field ):
    if ( array_key_exists($field, $_POST) && trim($_POST[$field]) !== '' ):
        $clean[$field] = htmlspecialchars($_POST[$field]);
    endif;
endforeach;
if ( array_key_exists($clean['which'], $list_lookup) ) $clean['whichone'] = $list_lookup[$clean['which']];
if ( array_key_exists('is_ajax', $clean) ) $ajax_return = '1';


$user_ip_full = ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
if ( in_array($user_ip_full, $ip_ignore) == TRUE ):
        header("Location: " . $clean['redirect'] . "?source=IPblacklist");
        exit;
endif;

if ( $clean['id'] != 'autoadd' && $clean['id'] != 'friendsend' && strlen($_POST['comments']) <= 6 ):
    header("Location: " . $clean['redirect'] . "?source=spamShort");
    exit;
endif;


if ( array_key_exists('comment', $_POST) || ( $clean['id'] == 'autoadd' && $clean['whichone'] != '') || $clean['id'] == 'friendsend' ):

    // If the honey pot has been altered...
    if ( array_key_exists('name_first', $clean) && ( $clean['name_first'] != "Humans: Do Not Use" && $clean['name_first'] != "" )):
        //mail($config['emails']['dev'], "honeypot: " . $clean['name_first'] , $clean['comments']);
        header("Location: " . $clean['redirect'] . "?source=spamHP");
        exit;
    endif;

    // If the comments contain certain phrases, we send them to the redirect page
    foreach ( $blacklist as $value ):
        if ( array_key_exists('comments', $clean) && strpos(strtolower($clean['comments']), $value) !== FALSE ):
            //mail($config['emails']['dev'], "blacklist: " . $_SERVER['HTTP_USER_AGENT'] , "$value " . $clean['comments']);
            header("Location: " . $clean['redirect'] . "?source=spamBL");
            exit;
        endif;
    endforeach;

    // If the comments are empty, we send them to our contact page
    if ( array_key_exists('comments', $clean) && $clean['comments'] == "" && $clean['id'] != 'autoadd' ):
        header("Location: http://www.denverpost.com/contactus?source=spamNO");
        exit;
    else:
        $clean['email'] = $config['emails']['from'];
        if ( array_key_exists('email_address', $clean) ) $clean['email'] = rtrim(preg_replace('/\s+/', '', $clean['email_address']),'.');

        //Figure out what the subject line and from-address are
        switch ($clean['id'])
        {
            case 'multiadd':
                // multiadd are subscriptions to the multi-rss-email service.
                // https://github.com/denverpost/multi-rss-mailer
                // We write the email address to a file.
                // The name of the file is a slugified version of the RSS feed URL.
                // The RSS feed URL is passed via the form, and must be
                // on a whitelisted domain.
                break;
            case 'autoadd':
                $subject = $config['handlers'][$clean['id']]['subject'];
                $clean['to'] = $config['handlers'][$clean['id']]['to'];

                if (filter_var($clean['email'], FILTER_VALIDATE_EMAIL)):

                    // Write the user's email address to a text file
                    $emails = substr($clean['email'], 0, 50) . ", " . $clean['which'] . "\n";
                    file_put_contents('addedemails.txt', $emails, FILE_APPEND);

                    // cURL a URL with the email address and newsletter alpha-ID to add it
                    $canna_url = 'http://mail.denverpost.com/Subscribe.do?action=saveSignup&siteID=' . $config['site_id'] . '&address=' . substr($clean['email'], 0, 50) . '&list=' . $clean['whichone'];
                    file_put_contents('submit_urls', $canna_url . "\n", FILE_APPEND);

                    $canna_ch = curl_init();
                    curl_setopt($canna_ch, CURLOPT_URL, $canna_url);
                    curl_setopt($canna_ch, CURLOPT_RETURNTRANSFER, 1);
                    $output = curl_exec($canna_ch);
                    curl_close($canna_ch);
                else:
                    $ajax_return = '0';
                endif;
                break;
            case 'newstip':
            case 'prepscontact':
                $subject = $config['handlers'][$clean['id']]['subject'];
                $clean['to'] = $config['handlers'][$clean['id']]['to'];
                $clean['from'] = $clean['email'];
                break;
            case 'eletters':
                $subject = $config['handlers'][$clean['id']]['subject'];
                $clean['to'] = $config['handlers'][$clean['id']]['to'];
                $clean['from'] = $clean['email'];
                $clean['comments'] = htmlspecialchars($bub_name). "\n" . htmlspecialchars($bub_letteremail). "\n" . htmlspecialchars($bub_phone). "\n" . htmlspecialchars($bub_street). "\n" . ($bub_city). "\n" . $clean['comments'];
                break;
            case 'friendsend':
                $subject = $clean['subject'];
                $clean['to'] = $clean['friend_email_address'];
                $clean['from'] = $clean['email_address'];
                $clean['comments'] = "A friend thought you'd be interested in this page: " . $clean['referer'];
                break;
        }


        // Send the email, if that's what we're doing.
        if ( $clean['id'] != 'autoadd' ):
            $clean['subject'] = '[DenverPost] ' . $subject;
            $clean['headers'] = "From: " . $config['emails']['from'] . " \r\n" .
    'Reply-To: ' . $clean['from'] . ' ' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
            mail($clean['to'], $clean['subject'], $clean['comments'], $clean['headers']);
            $ip = ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
            if ( $clean['id'] == 'newstip' ):
                mail($config['emails']['dev'], $clean['subject']  . ' ' . $ip, $clean['comments'], $clean['headers']);
                mail("kgeers@denverpost.com,fswidler@denverpost.com", $clean['subject'], $clean['comments'], $clean['headers']);
                mail("dpo@denverpost.com", $clean['subject'], $clean['comments'], $clean['headers']);
            elseif ( $clean['id'] == 'eletters' ):
                mail("openforum@denverpost.com", $clean['subject'], $clean['comments'], $clean['headers']);
            endif;
        endif;

        if ( $clean['is_ajax'] === '1' ):
            echo $ajax_return;
        else:
            header("Location: " . $clean['redirect']);
        endif;
    endif;
else: header("Location: " . $clean['redirect'] . "?source=SPAM");
endif;
