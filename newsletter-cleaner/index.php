<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Roundup Newsletter Cleaner</title>
        <meta name="description" content="" />
        <meta name="viewport" content="width=device-width" />
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="text/html;" />
        <link rel='stylesheet' id='knowlton-styles-css'  href='https://assets.digitalfirstmedia.com/prod/static/css/denverpost.css?ver=1.0' type='text/css' media='all' />
        <link rel='stylesheet' id='mason-fonts-css'  href='https://fonts.googleapis.com/css?family=Open%20Sans|Source+Serif+Pro%3A400%2C400italic%2C600%2C600italic%2C700%2C700italic%7CSource+Sans+Pro%3A400%2C400italic%2C600%2C600italic%2C700%2C400italic&#038;ver=4.5.3' type='text/css' media='all' />
        <style type="text/css">
            input, textarea { clear: both; }
        </style>
    </head>
    <body class="body-copy">
        <h1>Clean The Newsletter</h1>
<?php
if ( isset($_POST['content']) ):
?>
            <p>Copy the markup below and paste it into an article. You'll probably want to add a featured image before publishing.</p>
            <textarea name="content" id="content" cols="100" rows="30">
<?php
    $content = htmlspecialchars($_POST['content']); 

    // Fix the widths on the hr's
    $content = str_replace('width:300px;', 'width:100%;', $content);

    // Strip the custom markup on the paragraphs
    $content = str_replace("font-family:-apple-system-headline, 'Roboto', 'Helvetica Neue', sans-serif;font-size:1.4em;line-height:1.5em;margin-top:5%;margin-bottom:5%;margin-right:1%;margin-left:1%;", '', $content);
    // Strip the heavy underline on the links
    $content = str_replace("color:#0D4F8B;text-decoration:none;border-style:dotted;border-top-style:none;border-right-style:none;border-left-style:none;border-width:2px;", '', $content);

    // Strip the head and opening body element
    $content = preg_replace('/.*BODY --&gt;/s', '', $content);
    // Strip the unnecessary lower regions
    $content = preg_replace('/&lt;!-- FOOTER --&gt;.*/s', '</div>', $content);
    
    echo $content;
?>
<aside>
[dfm_iframe src="http://extras.denverpost.com/app/mailer-rules/roundup-widget.html" width="100%" height="500px"]
</aside>
</textarea>
<?php

else:
?>
        <p>Note: Please run your markup through <a href="https://inliner.cm/">https://inliner.cm/</a> before running it through this.</p>
        <form action="." method="POST">
            <input type="submit" value="Submit">
            <hr noshade>
            <textarea name="content" id="content" cols="100" rows="40" style="clear: both; width:100%; height:80%;"></textarea>
            <input type="submit" value="Submit">
        </form>
<?php
endif;

