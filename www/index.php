<?php
ini_set('display_errors', '1');
$blacklist = file('blacklist_strings', FILE_IGNORE_NEW_LINES FILE_SKIP_EMPTY_LINES);
$ip_ignore = file('blacklist_ips', FILE_IGNORE_NEW_LINES FILE_SKIP_EMPTY_LINES);
require('config.example.php');

$user_ip_full = ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
if ( in_array($user_ip_full, $ip_ignore) == TRUE ):
        header("Location: $bub_redirect?source=IPblacklist");
        exit;
endif;

// Clean the input
extract($_POST, EXTR_PREFIX_ALL, "clean");
var_dump($_POST);
$fields = [""];

$bub_whichone = '';
if (array_key_exists($bub_which,$list_lookup)) $bub_whichone = $list_lookup[$bub_which];

if ( strlen($_POST['comments']) <= 6 && $bub_id != 'autoadd' ):
    header("Location: $bub_redirect?source=spamShort");
    exit;
endif;

// COMMENTED OUT FOR NEW ELETTERS FORM if ( $_POST && intval($bub_submit_x) > 1 || ( $bub_id == 'autoadd' && $bub_whichone != '') )
//if ( $_POST && intval($bub_submit_x) > 1 || ( $bub_id == 'autoadd' && $bub_whichone != '') )
if ( $_POST && $bub_keebler == 'goof111' || ( $bub_id == 'autoadd' && $bub_whichone != '') ):
    $tmp = explode("http", $bub_comments);
    $bub_comments = str_replace('+', ' ', $bub_comments);

    //If the honey pot has been altered...
    if ( $bub_name_first != "Humans: Do Not Use" && $bub_name_first != "" ):
        mail("jmurphy@denverpost.com", "honeypot: " . $bub_name_first , $bub_comments);
        header("Location: $bub_redirect?source=spamHP");
        exit;
    endif;

    //If the comments contain certain phrases, we send them to our contact page
    foreach ( $blacklist as $value ):
        if ( strpos(strtolower($bub_comments), $value) !== FALSE ):
            mail("jmurphy@denverpost.com", "blacklist: " . $_SERVER['HTTP_USER_AGENT'] , "$value " . $bub_comments);
            header("Location: $bub_redirect?source=spamBL");
            exit;
        endif;
    endforeach;

    //If the comments are empty, we send them to our contact page
    if ( $bub_comments == "" && $bub_id != 'autoadd' ):
        header("Location: http://www.denverpost.com/contactus?source=spamNO");
        exit;
    else:
        $bub_email = 'noreply@denverpostplus.com';
        if ( isset($bub_email_address) ) $bub_email = rtrim(preg_replace('/\s+/', '', $bub_email_address),'.');

        //Figure out what the subject line and from-address ares
        switch ($bub_id)
        {
            case 'autoadd':
                $subject = $config['handlers'][$bub_id]['subject'];
                $bub_to = $config['handlers'][$bub_id]['to'];
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
                $subject = $config['handlers'][$bub_id]['subject'];
                $bub_to = $config['handlers'][$bub_id]['to'];
                $bub_from = $bub_email;
                $bub_message = $bub_comments;
                break;
            case 'eletters':
                $subject = $config['handlers'][$bub_id]['subject'];
                $bub_to = $config['handlers'][$bub_id]['to'];
                $bub_from = $bub_email;
                $bub_message = htmlspecialchars($bub_name). "\n" . htmlspecialchars($bub_letteremail). "\n" . htmlspecialchars($bub_phone). "\n" . htmlspecialchars($bub_street). "\n" . ($bub_city). "\n" . $bub_comments;
                break;
        }


        //Put the information together
        if ( $bub_id != 'autoadd' ):
            $bub_subject = '[DenverPost] ' . $subject;
            $bub_headers = "From: noreply@denverpostplus.com \r\n" .
    'Reply-To: ' . $bub_from . ' ' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
            mail($bub_to, $bub_subject, $bub_message, $bub_headers);
            $ip = ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
            if ( $bub_id == 'newstip' ):
                mail("jmurphy@denverpost.com,joe.murphy@gmail.com", $bub_subject  . ' ' . $ip, $bub_message, $bub_headers);
                mail("vmigoya@denverpost.com, dboniface@denverpost.com", $bub_subject, $bub_message, $bub_headers);
                mail("dpo@denverpost.com", $bub_subject, $bub_message, $bub_headers);
            elseif ( $bub_id == 'eletters' ):
                mail("jmurphy@denverpost.com", $bub_subject  . ' ' . $ip, $bub_message, $bub_headers);
                mail("openforum@denverpost.com", $bub_subject, $bub_message, $bub_headers);
            endif;
        endif;

        header("Location: $bub_redirect?source=form");
    endif;
else header("Location: $bub_redirect?source=SPAM");
