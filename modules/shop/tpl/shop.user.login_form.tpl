<div id="Login">
    <h3><img src="/images/icons/default/users.png" style="vertical-align: middle" /> {PHP.L.aut_logintitle}:</h3>
    <h4>{PHP.L.shop.login_form}:</h4>
    <form name="login" action="{USERS_AUTH_SEND}" method="post">
        <table align="center">
        <tr>
            <td style="vertical-align: middle; padding: 0 4px">{PHP.L.Username}: </td>
            <td style="vertical-align: middle; padding: 0 4px">{USERS_AUTH_USER}</td>

            <td style="vertical-align: middle; padding: 0 4px">{PHP.L.Password}: </td>
            <td style="vertical-align: middle; padding: 0 4px">{USERS_AUTH_PASSWORD}</td>

            <td style="vertical-align: middle; padding: 0 4px">{PHP.L.users_rememberme}: </td>
            <td style="vertical-align: middle; padding: 0 4px">{USERS_AUTH_REMEMBER}</td>

            <td><input type="submit" value="{PHP.L.Login}" /></td>
            <td align="right"></td>
        </tr>
    </table>


    </form>
    <p style="padding-left: 25px"><a href="{PHP|cot_url('users','m=passrecover')}">{PHP.L.users_lostpass}</a></p>
</div>