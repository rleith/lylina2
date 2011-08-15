{{include file='head.tpl'}}
<div id="signup-container">
    <h1>Signup for Lylina</h1>
    <form id="signup-form" method="post" action="signup">
        {{if isset($login_error)}}<div class="error">{{$login_error}}</div>{{/if}}
        Desired Login: <input type="text" name="login" value="{{if isset($login)}}{{$login}}{{/if}}" /><br />
        {{if isset($password_error)}}<div class="error">{{$password_error}}</div>{{/if}}
        Password: <input type="password" name="password" value="" /><br />
        Repeat password: <input type="password" name="password2" value="" /><br />
        <input type="submit" value="Signup" />
    </form>
</div>
{{include file='foot.tpl'}}
