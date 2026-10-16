(()=>{'use strict';
 const lang=document.documentElement.lang||'ru';
 const message={ru:'Это демонстрационная версия сайта. Заявка не отправлена. Для записи позвоните +371 22 004 040.',lv:'Šī ir vietnes demonstrācijas versija. Pieteikums nav nosūtīts. Lai pieteiktos, zvaniet +371 22 004 040.',en:'This is a website demonstration. Your request has not been sent. To book, call +371 22 004 040.'}[lang];
 document.querySelectorAll('[data-demo-submit]').forEach(button=>button.type='submit');
 document.addEventListener('submit',event=>{const form=event.target;if(!form.matches('[data-demo-form]'))return;event.preventDefault();event.stopImmediatePropagation();if(!form.reportValidity())return;const result=form.querySelector('.form-result');result.className='form-result demo-result';result.textContent=message;result.tabIndex=-1;result.scrollIntoView({behavior:'smooth',block:'nearest'});result.focus({preventScroll:true});},true);
})();
