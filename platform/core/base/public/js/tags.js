(function(){var r=class{init(){$(document).find(".tags").each(function(a,i){if($(i).hasClass("tagify"))return;let s=new Tagify(i,{keepInvalidTags:$(i).data("keep-invalid-tags")!==void 0?$(i).data("keep-invalid-tags"):!0,enforceWhitelist:$(i).data("enforce-whitelist")!==void 0?$(i).data("enforce-whitelist"):!1,delimiters:$(i).data("delimiters")!==void 0?$(i).data("delimiters"):",",whitelist:i.value?i.value.trim().split(/\s*,\s*/):[],userInput:$(i).data("user-input")!==void 0?$(i).data("user-input"):!0});$(i).data("url")&&s.on("input",e=>{s.settings.whitelist.length=0,s.loading(!0).dropdown.hide.call(s),$httpClient.make().get($(i).data("url")).then(({data:l})=>{s.settings.whitelist=l,s.loading(!1).dropdown.show.call(s,e.detail.value)})})}),document.querySelectorAll(".list-tagify").forEach(a=>{if(!a.dataset.list||$(a).hasClass("tagify"))return;const i=JSON.parse(a.dataset.list);let s=[];for(const[t,n]of Object.entries(i))s.push({value:t,name:n});let e=String(a.value).split(","),l=s.filter(t=>{if(e.includes(String(t.value)))return{value:t.id,name:t.name}});const d=function(t){return`
                <tag title="${t.title||t.name}"
                        contenteditable='false'
                        spellcheck='false'
                        tabIndex="-1"
                        class="${this.settings.classNames.tag} ${t.class?t.class:""}"
                        ${this.getAttributes(t)}>
                    <x title='' class='tagify__tag__removeBtn' role='button' aria-label='remove tag'></x>
                    <div class="d-flex align-items-center">
                        <span class='tagify__tag-text'>${t.name}</span>
                    </div>
                </tag>
            `},o=function(t){return`
                <div ${this.getAttributes(t)}
                    class="tagify__dropdown__item d-flex align-items-center ${t.class?t.class:""}"
                    tabindex="0"
                    role="option">

                    <div class="d-flex flex-column">
                        <strong>${t.name}</strong>
                    </div>
                </div>
            `};new Tagify(a,{tagTextProp:"name",enforceWhitelist:!0,skipInvalid:!0,dropdown:{closeOnSelect:!1,enabled:0,classname:"users-list",searchKeys:["value","name"]},templates:{tag:d,dropdownItem:o},whitelist:s,originalInputValueFormat:t=>t.map(n=>n.value).join(",")}).loadOriginalValues(l)})}};$(()=>{new r().init()})})();
