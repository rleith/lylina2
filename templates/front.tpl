{{$front = 1}}
{{include file='head.tpl'}}
<div id="lylina-front">
    <div class="column left-col">
        <img alt="Lylina screenshot" src="img/lylina-screenshot.png" width="250" height="188"/>
    </div>
    <div class="column middle-col">
        <div class="section first" id="welcome">
            <h1>Lylina</h1>
            <p>Welcome to Lylina RSS aggregator. Please log in to view your feeds or create a free account to get started.</p>
        </div>
        <div class="section" id="about">
            <h1>About</h1>
            <p>Lylina is a clean, "river of news" style feed reader designed to work well on desktop, mobile, and tablet browsers.</p>
        </div>
    </div>
    <div class="column right-col">
        <h2>Login</h2>
        {{if isset($error)}}
        <div class="error">
            {{$error}}
        </div>
        {{/if}}
        <form method="post" action="https://{{$smarty.server.SERVER_NAME}}{{$smarty.server.REQUEST_URI}}" class="login">
            <div class="field">
                <img src="img/users-trans.png" alt="username" /> <input type="text" name="user" class="focus" autocorrect="off" autocapitalize="off"/>
            </div>
            <div class="field">
                <img src="img/password-trans.png" alt="password" /> <input type="password" name="pass" />
            </div>
            <div>
                <input type="submit" value="Login" />
                <a id="signup" href="signup">Signup</a><br />
                <a href="ForgotPassword">Forgot username/password</a>
            </div>
        </form>
    </div>
    <div class="clear"></div>
</div>
{{include file='foot.tpl'}}
