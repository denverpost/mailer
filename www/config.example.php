<?php
// Set up the implementation-specific information for the email handler.

$config = array(
    'site_mailer_id' => $_ENV['MAILER_ID'],
    'emails' => array(
        'dev' => 'jmurphy@denverpost.com',
        'from' => 'noreply@denverpostplus.com'
    ),
    // When you want to create a new type of email handler, you need to add one of these.
    'handlers' => array(
        'autoadd' => array(
            'subject' => '',
            'to' => ''
        )
        'newstip' => array(
            'subject' => 'News Tip from denverpost.com',
            'to' => 'newsroom@denverpost.com'
        )
        'eletters' => array(
            'subject' => 'Letter to the editor',
            'to' => 'jmurphy@denverpost.com, khamm@denverpost.com, openforum@denverpost.com'
        )
        'prepscontact' => array(
            'subject' => '[Preps] Contact Form submission',
            'to' => 'postpreps@denverpost.com,jnguyen@denverpost.com'
            ),
    )
);


//An array of PulsePoint alpha-IDs to lookup which list to cURL when subscribing automatically.
$list_lookup = array(
    'marijuana' => 'fqybdrb',
    'avsinsider' => 'wnswlgv',
    'afternoonnews' => 'kslmfhs',
    'sportsdaily' => 'fnbhjrn',
    'dailydigest' => 'fnnhjrr',
    'bronxinsider' => 'fhnbdyq',
    'yourhub' => 'fdghrgg',
    'avsbreaking' => 'pccyqmy',
    'bnbroncos' => 'mnsynsw',
    'bnrockies' => 'tjlbzvj',
    'bnrapids' => 'vmvkvhy',
    'bnpreps' => 'ymfflgm',
    'bnnuggets' => 'abhykpm',
    'bncollege' => 'xgbpvbv',
    'reverb' => 'dcphscr',
    'dptv' => 'lqmdgzk',
    'sportsshow' => 'njwtfwq',
    'photoweek' => 'xhjpdcd',
    'photoday' => 'wwlwvld',
    'transgender' => 'xdgphpj',
    'sports' => 'mdntydw'
    );
