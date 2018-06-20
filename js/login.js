$(document).ready( function(){
    $("#btn_login").click(function() {
        var email = $("#email").val();
        var password = $("#password").val();

        var isOkay = check(email,password);
        if(!isOkay) {
            return;
        }

        $.ajax(
            {
                url: "/php/login.php",
                type: "POST",
                data: {email:email, password:password},
                success: function(msg) {
                    alert(msg);
                }
            }
        );
    });

    // don't redirect to php page on submit
    $("#login_form").submit(function() { 
        return false;
    });
});
function check(email,password) {
    var email_ok = false;
    var password_ok = false;

    var email_addr=email.toLowerCase();
    var email_re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
   
    if(email_re.test(email_addr)==false){
        $('#email').tooltip({trigger: 'manual'}).tooltip('show');
        email_ok = false;
    }
    else{
        $('#email').tooltip('hide');
        email_ok = true;
    }
    if(password.replace(/[ ]/g,"")==""){
        $('#password').tooltip({trigger: 'manual'}).tooltip('show');
        password_ok = false;
    }
    else{
        $('#password').tooltip('hide');
        password_ok = true;
    }

    return email_ok && password_ok;
}
