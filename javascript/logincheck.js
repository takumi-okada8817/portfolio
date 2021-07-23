//log in check

const useridentify = document.loginform.useridentify;
const loginpassword = document.loginform.loginpassword;

const loginElements = [useridentify,loginpassword];

loginElements.forEach((loginElement)=>{
    loginElement.addEventListener('keyup',(e)=>{
        if(loginElement.value === ''){

            loginElement.style.backgroundColor = "rgba(255,99,71,0.1)";
            loginElement.nextElementSibling.textContent = "必須";
            loginElement.nextElementSibling.style.display = "block";            
        }else{
            loginElement.style.backgroundColor = "rgba(102,205,170,0.1)";
            loginElement.nextElementSibling.style.display = "none";
        }
    })
});