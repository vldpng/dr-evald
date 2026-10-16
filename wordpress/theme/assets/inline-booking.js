(()=>{
 'use strict';
 const messages={ru:{sending:'Отправляем…',error:'Не удалось отправить заявку. Попробуйте ещё раз или позвоните +371 22 004 040.'},lv:{sending:'Nosūta…',error:'Neizdevās nosūtīt. Mēģiniet vēlreiz vai zvaniet +371 22 004 040.'},en:{sending:'Sending…',error:'Unable to send. Please try again or call +371 22 004 040.'}};
 document.querySelectorAll('.booking-form-inline').forEach(form=>{
  let sending=false;
  form.addEventListener('submit',async event=>{
   event.preventDefault();if(sending||!form.reportValidity())return;
   const button=form.querySelector('[type=submit]'),result=form.querySelector('.form-result'),original=button.innerHTML,msg=messages[drevaldConfig.lang]||messages.ru;
   sending=true;button.disabled=true;button.textContent=msg.sending;form.setAttribute('aria-busy','true');result.textContent='';result.className='form-result';
   try{
    const response=await fetch(drevaldConfig.ajaxUrl,{method:'POST',body:new FormData(form),credentials:'same-origin'});const data=await response.json();
    if(!response.ok||!data.success)throw new Error(data.data?.message||msg.error);
    result.textContent=data.data.message;result.classList.add('success');form.reset();
   }catch(error){result.textContent=error.message||msg.error;result.classList.add('error')}
   finally{sending=false;button.disabled=false;button.innerHTML=original;form.removeAttribute('aria-busy');result.tabIndex=-1;result.scrollIntoView({behavior:'smooth',block:'nearest'});result.focus({preventScroll:true})}
  });
 });
})();
