<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">
    <title>lylina rss aggregator {{if $title}} - {{$title}} {{/if}}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="-1" />

    <link rel="stylesheet" type="text/css" href="style/new.css" media="screen" />
    <link rel="stylesheet" type="text/css" media="only screen and (max-device-width: 720px)" href="style/small-device.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="style/jquery-ui.css" />

    <script language="JavaScript" type="text/javascript" src="js/jquery.js"></script>

    <script language="JavaScript" type="text/javascript" src="js/jquery-ui-custom.js"></script>
    <script language="JavaScript" type="text/javascript" src="js/jquery.nextALL.js"></script>
    <script language="JavaScript" type="text/javascript" src="js/jquery.scrollTo.js"></script>
    <script language="JavaScript" type="text/javascript" src="js/new.js"></script>
    {{if isset($extra_js)}}
        <script language="JavaScript" type="text/javascript" src="{{$extra_js}}"></script>
    {{/if}}
    {{if isset($analyticsID) && strlen($analyticsID) > 0}}
        <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', '{{$analyticsID}}']);
            _gaq.push(['_setSiteSpeedSampleRate', 10]);
            _gaq.push(['_trackPageview']);

            (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();
        </script>
    {{/if}}

    <meta name="viewport" content="width=device-width, height=device-height" />
    <meta name="HandheldFriendly" content="true" /> 

    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <!--<link rel="alternate" type="application/rss+xml" title="RSS" href="rss.php/cfcd208495d565ef66e7dff9f98764da.xml" />-->
    <script language="JavaScript" type="text/javascript">
    <!--
        var showDetails = false;
        var markID = '';
        {{if $update}}
        var show_updates = true;
        {{else}}
        var show_updates = false;
        {{/if}}
    //-->
    </script>
</head>
<body>
<div id="navigation"><a href="index.php"><img src="img/mini.png" width="39" height="25" alt="lylina" id="logo" /></a>
<img src="img/div.png" width="1" height="20" alt="" />
{{if !$title}}
    <div id="message"><img src="img/4-1.gif" alt="..." />Please wait while lylina updates...</div>
{{else}}
    {{$title}}
{{/if}}

{{if !isset($front)}}
{{if !$auth}}
<div id="login">
    <a id="home" href="index.php">Home</a>
    <a id="signup" href="signup">Signup</a>
</div>
{{else}}
<div id="login">
    <div  id="search">
        <form method="post" action="">
            <input id="search-text" type="text" name="search" />
            <input id="search-button" type="submit" value="Search" />
        </form>
    </div>
    <a href="admin">Preferences</a>
    <a href="logout">Logout</a>
</div>
{{/if}}
{{/if}}
</div>
<div id="content">
<div id="search-results">
    <div id="search-header">
        <span id="search-message"></span>
        <span id="search-close-button">(close)</span>
    </div>
</div>
<div id="main">

