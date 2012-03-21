{{include file='head.tpl'}}
<div id="lylina-forgot-password" class="center">
    <h1>Reset Password</h1>
    {{if isset($success)}}
    <p>Your password has been changed click <a href="./">here</a> to go back to login</p>
    {{else}}
    <p>Please enter and confirm your new password</p>
    <form method="post" action="ResetPassword">
        {{if isset($error)}}
        <div class="error">
            {{$error}}
        </div>
        {{/if}}
        <div class="field">
            <img src="img/password-trans.png" alt="username" /> <input type="password" name="password" class="focus" />
        </div>
        <div class="field">
            <img src="img/password-trans.png" alt="username" /> <input type="password" name="password2" />
        </div>
        <div class="field">
            <input type="submit" value="Set password" />
        </div>
    </form>
    {{/if}}
</div>
{{include file='foot.tpl'}}
