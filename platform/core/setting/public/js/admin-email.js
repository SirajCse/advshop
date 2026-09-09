(function(){$(()=>{const e=$('[data-bb-toggle="admin-email"]');if(!e.length)return;const n=e.find("#add");let r=parseInt(e.data("max"),10),t=e.data("emails");t.length===0&&(t=[""]);const l=()=>{e.find("input[type=email]").length>=r?n.addClass("disabled"):n.removeClass("disabled")},i=(a="")=>e.find("label").after(`<div class="d-flex mt-2 more-email align-items-center">
                <input type="email" class="form-control" placeholder="${n.data("placeholder")}" name="admin_email[]" value="${a||""}" />
                <a class="btn btn-link btn-sm text-danger bg-transparent border-0"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-minus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
  <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
  <path d="M5 12l14 0" />
</svg>
</a>
            </div>`),s=()=>{t.forEach(a=>{i(a)}),l()};e.on("click",".more-email > a",function(){$(this).hasClass("disabled")||($(this).parent(".more-email").remove(),l())}),n.on("click",a=>{a.preventDefault(),i(),l()}),s()})})();
