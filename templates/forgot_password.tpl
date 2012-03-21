{{include file='head.tpl'}}
<div id="lylina-forgot-password" class="center">
    <h1>Forgot Password</h1>
    {{if isset($success)}}
    <p>An e-mail will be sent shortly including your username and instructions on how to reset your password</p>
    {{else}}
    <p>Enter your username or e-mail to reset your password. You will recieve an email containing your username as well as a link to reset your password.</p>
    <form method="post" action="ForgotPassword">
        {{if isset($error)}}
        <div class="error">
            {{$error}}
        </div>
        {{/if}}
        <div class="field">
            <img src="img/users-trans.png" alt="username" /> <input type="text" name="user" class="focus" autocorrect="off" autocapitalize="off"/>
        </div>
        <div class="field">
            {{$recaptcha}}
        </div>
        <div class="field">
            <input type="submit" value="Send Reset E-mail" />
        </div>
    </form>
    {{/if}}
</div>
{{include file='foot.tpl'}}
