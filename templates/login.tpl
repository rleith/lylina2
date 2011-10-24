{{include file='head.tpl'}}
<div class="container">
{{if isset($message)}}
<div class="message">{{$message}}</div>
{{/if}}
<form id="elevate-form" method="post" action="index.php" class="login">
    <input type="hidden" name="p" value="admin" />
    <input type="hidden" name="op" value="login" />
    {{if isset($redirect)}}
    <input type="hidden" name="redirect" value="{{$redirect}}" />
    {{/if}}
    <img src="img/users-trans.png" alt="username" /> <input type="text" name="user" {{if isset($user)}} value="{{$user}}" {{$focus_password = true}} {{else}} id="focus" {{/if}} /><br />
    <img src="img/password-trans.png" alt="password" /> <input type="password" name="pass" {{if isset($focus_password)}}id="focus"{{/if}} /><br />
    <input type="submit" value="Login" />
</form>
</div>
{{include file='foot.tpl'}}
