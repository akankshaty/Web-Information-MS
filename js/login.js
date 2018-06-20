$(document).ready( function(){
    $("#btn_login").click(function() {
        var email = $("#email").val();
        var password = $("#password").val();

        var isOkay = check(email,password);
        if(!isOkay) {
            alert('Email or password cannot be blank');
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
    if(email == '' || password == '') {
        return false;
    }
    return true;
}
