(function(){function l(t){"@babel/helpers - typeof";return l=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(e){return typeof e}:function(e){return e&&typeof Symbol=="function"&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},l(t)}function p(t,e){if(l(t)!="object"||!t)return t;var o=t[Symbol.toPrimitive];if(o!==void 0){var n=o.call(t,e||"default");if(l(n)!="object")return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return(e==="string"?String:Number)(t)}function c(t){var e=p(t,"string");return l(e)=="symbol"?e:e+""}function d(t,e,o){return(e=c(e))in t?Object.defineProperty(t,e,{value:o,enumerable:!0,configurable:!0,writable:!0}):t[e]=o,t}var u=class{constructor(t){d(this,"defaults",{oldestFirst:!0,text:"Toastify is awesome!",node:void 0,duration:3e3,selector:void 0,callback:function(){},close:!1,gravity:"toastify-top",position:"",className:"",stopOnFocus:!0,onClick:function(){},offset:{x:0,y:0},escapeMarkup:!0,ariaLive:"polite",style:{background:""}}),this.options={},this.toastElement=null,this._rootElement=document.body,this._init(t)}showToast(){if(this.toastElement=this._buildToast(),typeof this.options.selector=="string"?this._rootElement=document.getElementById(this.options.selector):this.options.selector instanceof HTMLElement||this.options.selector instanceof ShadowRoot?this._rootElement=this.options.selector:this._rootElement=document.body,!this._rootElement)throw"Root element is not defined";return this._rootElement.insertBefore(this.toastElement,this._rootElement.firstChild),this._reposition(),this.options.duration>0&&(this.toastElement.timeOutValue=window.setTimeout(()=>{this._removeElement(this.toastElement)},this.options.duration)),this}hideToast(){this.toastElement.timeOutValue&&clearTimeout(this.toastElement.timeOutValue),this._removeElement(this.toastElement)}_init(t){this.options=Object.assign(this.defaults,t),this.toastElement=null,this.options.gravity=t.gravity==="bottom"?"toastify-bottom":"toastify-top",this.options.stopOnFocus=t.stopOnFocus===void 0?!0:t.stopOnFocus}_buildToast(){if(!this.options)throw"Toastify is not initialized";let t=document.createElement("div");t.className=`toastify on ${this.options.className} pe-5`,t.className+=` toastify-${this.options.position}`,t.className+=` ${this.options.gravity}`;for(const o in this.options.style)t.style[o]=this.options.style[o];if(this.options.ariaLive&&t.setAttribute("aria-live",this.options.ariaLive),this.options.icon!==""){let o=document.createElement("div");o.className="toastify-icon",o.innerHTML=this.options.icon,t.appendChild(o)}const e=document.createElement("span");if(e.className="toastify-text",this.options.node&&this.options.node.nodeType===Node.ELEMENT_NODE?e.appendChild(this.options.node):this.options.escapeMarkup?e.innerText=this.options.text:e.innerHTML=this.options.text,t.appendChild(e),this.options.close===!0){let o=document.createElement("button");o.type="button",o.setAttribute("aria-label","Close"),o.className="toast-close",o.style.cssText="position: absolute; top: 8px; inset-inline-end: 8px;",o.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M18 6l-12 12"></path>
                <path d="M6 6l12 12"></path>
            </svg>`,o.addEventListener("click",s=>{s.stopPropagation(),this._removeElement(this.toastElement),window.clearTimeout(this.toastElement.timeOutValue)});const n=window.innerWidth>0?window.innerWidth:screen.width;this.options.position==="left"&&n>360?t.insertAdjacentElement("afterbegin",o):t.appendChild(o)}if(this.options.stopOnFocus&&this.options.duration>0&&(t.addEventListener("mouseover",o=>{window.clearTimeout(t.timeOutValue)}),t.addEventListener("mouseleave",()=>{t.timeOutValue=window.setTimeout(()=>{this._removeElement(t)},this.options.duration)})),typeof this.options.onClick=="function"&&t.addEventListener("click",o=>{o.stopPropagation(),this.options.onClick()}),typeof this.options.offset=="object"){const o=this._getAxisOffsetAValue("x",this.options),n=this._getAxisOffsetAValue("y",this.options),s=this.options.position==="left"?o:`-${o}`,i=this.options.gravity==="toastify-top"?n:`-${n}`;t.style.transform=`translate(${s},${i})`}return t}_removeElement(t){t.className=t.className.replace(" on",""),window.setTimeout(()=>{this.options.node&&this.options.node.parentNode&&this.options.node.parentNode.removeChild(this.options.node),t.parentNode&&t.parentNode.removeChild(t),this.options.callback.call(t),this._reposition()},400)}_reposition(){const t=parseInt(getComputedStyle(document.documentElement).getPropertyValue("--toastify-bottom-offset"))||15;let e={top:15,bottom:t},o={top:15,bottom:t},n={top:15,bottom:t},s=this._rootElement.querySelectorAll(".toastify"),i;for(let a=0;a<s.length;a++){s[a].classList.contains("toastify-top")===!0?i="toastify-top":i="toastify-bottom";let f=s[a].offsetHeight;i=i.substr(9,i.length-1);let h=15;(window.innerWidth>0?window.innerWidth:screen.width)<=360?(s[a].style[i]=`${n[i]}px`,n[i]+=f+h):s[a].classList.contains("toastify-left")===!0?(s[a].style[i]=`${e[i]}px`,e[i]+=f+h):(s[a].style[i]=`${o[i]}px`,o[i]+=f+h)}}_getAxisOffsetAValue(t,e){return e.offset[t]?isNaN(e.offset[t])?e.offset[t]:`${e.offset[t]}px`:"0px"}};function m(){const t=document.createElement("style");t.textContent=`
        .toastify {
            padding: 0.75rem 2rem 0.75rem 0.75rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow:
                0 3px 6px -1px rgba(0, 0, 0, 0.12),
                0 10px 36px -4px rgba(77, 96, 232, 0.3);
            background: -webkit-linear-gradient(315deg, #73a5ff, #5477f5);
            background: linear-gradient(135deg, #73a5ff, #5477f5);
            position: fixed;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
            border-radius: 2px;
            cursor: pointer;
            text-decoration: none;
            z-index: 999999;
            width: 25rem;
            max-width: calc(100% - 30px);
        }

        .toastify.on {
            opacity: 1;
        }

        .toastify-icon {
            width: 1.5rem;
            height: 1.5rem;
        }

        .toast-close {
            background: transparent;
            border: 0;
            color: white;
            cursor: pointer;
            font-family: inherit;
            font-size: 1em;
            opacity: 0.4;
            padding: 0 5px;
            position: absolute;
            top: 0.25rem;
            inset-inline-end: 0.25rem;
        }

        .toast-close svg {
            width: 1em;
            height: 1em;
        }

        .toastify-text a {
            text-decoration: underline;
            color: #fff;
        }

        .toastify-right {
            inset-inline-end: 15px;
        }

        .toastify-left {
            inset-inline-start: 15px;
        }

        .toastify-top {
            top: -150px;
        }

        .toastify-bottom {
            bottom: -150px;
        }

        .toastify-rounded {
            border-radius: 25px;
        }

        .toastify-center {
            margin-inline-start: auto;
            margin-inline-end: auto;
            inset-inline-start: 0;
            inset-inline-end: 0;
            max-width: fit-content;
            max-width: -moz-fit-content;
        }

        @media only screen and (max-width: 768px) {
            .toastify {
                width: auto;
                max-width: calc(100% - 30px);
                inset-inline-start: 15px !important;
                inset-inline-end: 15px !important;
                transform: none !important;
            }
        }
    `,document.head.appendChild(t)}m();function w(t){return new u(t)}window.Theme=window.Theme||{};var r=window.Theme;r.getToastConfig=function(){return window.ThemeToastConfig||{position:"bottom",alignment:"right",offsetX:15,offsetY:15,timeout:5e3,successIcon:"",errorIcon:""}},r.getToastIcon=function(t,e){return t||e},r.showNotice=function(t,e){const o=this.getToastConfig();let n="#fff",s="";const i='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>',a='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>';switch(t){case"success":n="#437a43",s=this.getToastIcon(o.successIcon,i);break;case"danger":n="#bd362f",s=this.getToastIcon(o.errorIcon,a);break;case"warning":n="#f89406",s='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8v4" /><path d="M12 16h.01" /></svg>';break;case"info":n="#2f96b4",s='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>';break}w({text:e,icon:s,duration:parseInt(o.timeout)||5e3,close:!0,gravity:o.position,position:o.alignment,offset:{x:parseInt(o.offsetX)||15,y:parseInt(o.offsetY)||15},stopOnFocus:!0,style:{background:n},escapeMarkup:!1,className:"toastify-"+t}).showToast()},r.showError=function(t){this.showNotice("danger",t)},r.showSuccess=function(t){this.showNotice("success",t)},r.handleError=t=>{typeof t.errors!="undefined"&&t.errors.length?r.handleValidationError(t.errors):typeof t.responseJSON!="undefined"?typeof t.responseJSON.errors!="undefined"?t.status===422&&r.handleValidationError(t.responseJSON.errors):typeof t.responseJSON.message!="undefined"?r.showError(t.responseJSON.message):r.showError(t.responseJSON.join(", ").join(", ")):r.showError(t.statusText)},r.handleValidationError=t=>{let e="";Object.values(t).forEach(o=>{e!==""&&(e+=`
`),e+=o}),r.showError(e)}})();
