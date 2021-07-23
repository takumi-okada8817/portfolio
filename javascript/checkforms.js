'user strict';

//checkforms

const identify = document.signinform.identify;
const userid = document.signinform.userid;
const username = document.signinform.username;
const password = document.signinform.password;
const inputElements = [identify,userid,username,password];

inputElements.forEach((inputElement)=>{
    
    inputElement.addEventListener('input',(e)=>{
        
        if(inputElement.value === ''){

            inputElement.style.backgroundColor = "rgba(255,99,71,0.1)";
            inputElement.nextElementSibling.textContent = "必須";
            inputElement.nextElementSibling.style.display = "block";            

        }else{
            inputElement.style.backgroundColor = "rgba(102,205,170,0.1)";
            inputElement.nextElementSibling.style.display = "none";
        }
    });
});