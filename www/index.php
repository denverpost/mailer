<?php
ini_set('display_errors', '1');
$blacklist = file('blacklist_strings', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$ip_ignore = file('blacklist_ips', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
require('config.example.php');

// Clean the input.
// We whitelist input fields to make sure nothing fishy gets processed.
$clean = [];
var_dump($_POST);
$fields = ['redirect', 'id', 'which', 'email_address'];
foreach ( $fields as $field ):
    if ( array_has_key($field, $_POST) && trim($_POST[$field]) !== '' ):
        $clean[$field] = htmlspecialchars($_POST[$field]);
    endif;
endforeach;
if (array_key_exists($clean['which'],$list_lookup)) $clean['whichone'] = $list_lookup[$clean['which']];


$user_ip_full = ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
if ( in_array($user_ip_full, $ip_ignore) == TRUE ):
        header("Location: " . $clean['redirect'] . "?source=IPblacklist");
        exit;
endif;

if ( strlen($_POST['comments']) <= 6 && $clean['id'] != 'autoadd' ):
    header("Location: " . $clean['redirect'] . "?source=spamShort");
    exit;
endif;


// COMMENTED OUT FOR NEW ELETTERS FORM if ( $_POST && intval($bub_submit_x) > 1 || ( $clean['id'] == 'autoadd' && $bub_whichone != '') )
//if ( $_POST && intval($bub_submit_x) > 1 || ( $clean['id'] == 'autoadd' && $bub_whichone != '') )
if ( $_POST && $bub_keebler == 'goof111' || ( $clean['id'] == 'autoadd' && $bub_whichone != '') ):
    $tmp = explode("http", $bub_comments);
    $bub_comments = str_replace('+', ' ', $bub_comments);

    //If the honey pot has been altered...
    if ( $clean['name_first'] != "Humans: Do Not Use" && $clean['name_first'] != "" ):
        mail($config['emails']['dev'], "honeypot: " . $clean['name_first'] , $bub_comments);
        header("Location: " . $clean['redirect'] . "?source=spamHP");
        exit;
    endif;

    //If the comments contain certain phrases, we send them to our contact page
    foreach ( $blacklist as $value ):
        if ( strpos(strtolower($bub_comments), $value) !== FALSE ):
            mail($config['emails']['dev'], "blacklist: " . $_SERVER['HTTP_USER_AGENT'] , "$value " . $bub_comments);
            header("Location: " . $clean['redirect'] . "?source=spamBL");
            exit;
        endif;
    endforeach;

    //If the comments are empty, we send them to our contact page
    if ( $bub_comments == "" && $clean['id'] != 'autoadd' ):
        header("Location: http://www.denverpost.com/contactus?source=spamNO");
        exit;
    else:
        $bub_email = 'noreply@denverpostplus.com';
        if ( isset($bub_email_address) ) $bub_email = rtrim(preg_replace('/\s+/', '', $bub_email_address),'.');

        //Figure out what the subject line and from-address ares
        switch ($clean['id'])
        {
            case 'autoadd':
                $subject = $config['handlers'][$clean['id']]['subject'];
                $clean['to'] = $config['handlers'][$clean['id']]['to'];
                $bub_message = $bub_comments;
                if (filter_var($bub_email, FILTER_VALIDATE_EMAIL)) {
                    // Write the user's email address to a text file
                    //$emails = file_get_contents('addedemails.txt');
                    $emails = substr($bub_email, 0, 50) . ", " . $bub_which . "\n";
                    file_put_contents('addedemails.txt', $emails, FILE_APPEND);
                    // cURL a URL with the email address and newsletter alpha-ID to add it
                    $canna_url = 'http://mail.denverpost.com/Subscribe.do?action=saveSignup&siteID=' . $config['site_mailer_id'] . '&address=' . substr($bub_email, 0, 50) . '&list=' . $bub_whichone;
                    $canna_ch = curl_init();
                    curl_setopt($canna_ch, CURLOPT_URL, $canna_url);
                    curl_setopt($canna_ch, CURLOPT_RETURNTRANSFER, 1);
                    $output = curl_exec($canna_ch);
                    curl_close($canna_ch);
                }
                break;
            case 'newstip':
            case 'prepscontact':
                $subject = $config['handlers'][$clean['id']]['subject'];
                $clean['to'] = $config['handlers'][$clean['id']]['to'];
                $bub_from = $bub_email;
                $bub_message = $bub_comments;
                break;
            case 'eletters':
                $subject = $config['handlers'][$clean['id']]['subject'];
                $clean['to'] = $config['handlers'][$clean['id']]['to'];
                $bub_from = $bub_email;
                $bub_message = htmlspecialchars($bub_name). "\n" . htmlspecialchars($bub_letteremail). "\n" . htmlspecialchars($bub_phone). "\n" . htmlspecialchars($bub_street). "\n" . ($bub_city). "\n" . $bub_comments;
                break;
        }


        //Put the information together
        if ( $clean['id'] != 'autoadd' ):
            $clean['subject'] = '[DenverPost] ' . $subject;
            $bub_headers = "From: noreply@denverpostplus.com \r\n" .
    'Reply-To: ' . $bub_from . ' ' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
            mail($clean['to'], $clean['subject'], $bub_message, $bub_headers);
            $ip = ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
            if ( $clean['id'] == 'newstip' ):
                mail($config['emails']['dev'], $clean['subject']  . ' ' . $ip, $bub_message, $bub_headers);
                mail("vmigoya@denverpost.com, dboniface@denverpost.com", $clean['subject'], $bub_message, $bub_headers);
                mail("dpo@denverpost.com", $clean['subject'], $bub_message, $bub_headers);
            elseif ( $clean['id'] == 'eletters' ):
                mail($config['emails']['dev'], $clean['subject']  . ' ' . $ip, $bub_message, $bub_headers);
                mail("openforum@denverpost.com", $clean['subject'], $bub_message, $bub_headers);
            endif;
        endif;

        header("Location: " . $clean['redirect'] . "?source=form");
    endif;
else: header("Location: " . $clean['redirect'] . "?source=SPAM");
endif;
