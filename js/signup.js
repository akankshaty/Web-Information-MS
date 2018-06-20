$(document).ready(function(){
    $("#btn_signup").click(function(){
        var name = $("#name").val();
        var email = $("#email").val();
        var password=$("#password").val();
        var confirm_password=$("#confirm_password").val();

        var isOkay = check(name,email,password,confirm_password);
        if(!isOkay) {
            return;
        }

        $.ajax(
            {
                url: "/WMIS/php/signup.php",
                type: "POST",
                data: {name: name, email: email, password: password},
                success: function(msg) {
                    alert(msg);
                }
            }
        );
    });

    // don't redirect to php page on submit
    $("#signup_form").submit(function() { 
        return false;
    });
});

function check(name,email,password,confirm_password) {
    var name_ok = false;
    var email_ok = false;
    var password_ok = false;

    var email_addr=email.toLowerCase();
    var email_re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    var password_re = /^(?=.*\d)(?=.*[a-zA-Z])[0-9a-zA-Z]{8,}$/;

    if(name.replace(/[ ]/g,"")==""){
        $('#name').tooltip({trigger: 'manual'}).tooltip('show');
        name_ok = false;
    }
    else{
        $('#name').tooltip('hide');
        name_ok = true;
    }
    if(email_re.test(email_addr)==false){
        $('#email').tooltip({trigger: 'manual'}).tooltip('show');
        email_ok = false;
    }
    else{
        $('#email').tooltip('hide');
        email_ok = true;
    }
    if(password_re.test(password)==false){
        $('#password').tooltip({trigger: 'manual'}).tooltip('show');
        password_ok = false;
    }
    else{
        $('#password').tooltip('hide');
        password_ok = true;
    }
    if(password!=confirm_password){
        $('#confirm_password').tooltip({trigger: 'manual'}).tooltip('show');
        password_ok = false;
    }
    else{
        $('#confirm_password').tooltip('hide');
        password_ok = true;
    }
    return name_ok && email_ok && password_ok;
}
