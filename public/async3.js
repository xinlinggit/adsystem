var isIE=navigator.userAgent.match(/MSIE (\d)/i);
isIE = isIE ? isIE[1] : undefined;
var DONT_ENUM =  "propertyIsEnumerable,isPrototypeOf,hasOwnProperty,toLocaleString,toString,valueOf,constructor".split(","),
    hasOwn = ({}).hasOwnProperty;
for (var i in {
    toString: 1
}){
    DONT_ENUM = false;
}
Object.keys = Object.keys || function(obj){//ecma262v5 15.2.3.14
    var result = [];
    for(var key in obj ) if(hasOwn.call(obj,key)){
        result.push(key) ;
    }
    if(DONT_ENUM && obj){
        for(var i = 0 ;key = DONT_ENUM[i++]; ){
            if(hasOwn.call(obj,key)){
                result.push(key);
            }
        }
    }
    return result;
};
function domGetTag(o){
    return (!document.querySelectorAll)?document.getElementsByTagName(o):document.querySelectorAll(o);
}
(function (e) {
    var ins=domGetTag('ins'),il=ins.length,ia=new Array(),ib=new Array(),api="http://as.cnfol.com/index/index/api";
    var f={
        load:function(a,n,p){
            var t=eval('('+p+')');
            if(t.code=="100"){
                var ht=t.html,w=t.width,h=t.height;
                try{
                    var k = document.createElement('<div  id="ifr_'+a+'" style="width:100%;height:100%;border:0;overflow:hidden;position:relative;"></div>');

                }catch(e){
                    var k = document.createElement('div');
                    k.name = 'ifr_'+a;
                    k.id = 'ifr_'+a;
                    k.style.width='100%';
                    k.style.height='100%';
                    k.style.border='0';
                    k.style.overflow='hidden';
                    k.style.position='relative';
                }
                ins.item(n).appendChild(k);
                var fd=document.getElementById("ifr_"+a);
                fd.innerHTML=ht;

            }else {
               document.getElementById("as_"+a).style.display="none";

            }

        },
        ajax:function(a,c){
            var h = (window.XDomainRequest)?new XDomainRequest():new XMLHttpRequest();
            h.onload = function() {
                f.load(a,c,h.responseText);
            };

            h.open("GET", api+"?as_id="+a+'&t='+Math.random(), true);
            h.withCredentials = false;
            h.send();
        }
    }

    for(var i=0;i<il;i++){
        ia.push(ins.item(i).getAttribute('data-revive-zoneid'));
        f.ajax(ia[i],i);
    }
})(document, window);