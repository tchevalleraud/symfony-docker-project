import 'bootstrap';
import * as bootstrap from 'bootstrap';

var securityIdle;
var securityIdleTimer = 0;
var toastSecurityIdle = document.getElementById('securityIdle')

function checkSecurityIdle(){
    securityIdleTimer++;
    if(securityIdleTimer >= (toastSecurityIdle.dataset.idle - 60)){
        var toast = new bootstrap.Toast(toastSecurityIdle)
        toast.show()
    }
    console.log(securityIdleTimer);
    securityIdle = setTimeout(checkSecurityIdle, 1000);
}

checkSecurityIdle();