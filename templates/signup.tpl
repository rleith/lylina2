{{include file='head.tpl'}}
<div id="signup-container">
    <h1>Create an account</h1>
    <form id="signup-form" method="post" action="signup">
        {{if isset($login_error)}}<div class="error">{{$login_error}}</div>{{/if}}
        Desired Login: <input type="text" name="login" value="{{if isset($login)}}{{$login}}{{/if}}" /><br />
        {{if isset($email_error)}}<div class="error">{{$email_error}}</div>{{/if}}
        Email Address: <input type="text" name="email" value="{{if isset($email)}}{{$email}}{{/if}}" /><br />
        {{if isset($password_error)}}<div class="error">{{$password_error}}</div>{{/if}}
        Password: <input type="password" name="password" value="" /><br />
        Repeat password: <input type="password" name="password2" value="" /><br />
        {{if isset($captcha_error)}}<div class="error">{{$captcha_error}}</div>{{/if}}
        {{$recaptcha}}<br />
        <input type="submit" value="Signup" />
    </form>
</div>
{{include file='foot.tpl'}}
