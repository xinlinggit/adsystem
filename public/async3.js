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
    var ins=domGetTag('ins'),il=ins.length,ia=new Array(),ib=new Array(),api="http://ad.api.dev.cnfol.wh/index/index/api2";
    var f={
        load:function(a,n,p){
            var t=eval('('+p+')'),ht=t.html,w=t.width,h=t.height;
            try{  
                var k = document.createElement('<iframe name="ifr_'+a+'" id="ifr_'+a+'" style="width:'+w+'px;height:'+h+'px;border:0;overflow:hidden;"></iframe>');
            }catch(e){ 
                var k = document.createElement('iframe');
                k.name = 'ifr_'+a;
                k.id = 'ifr_'+a;
                k.style.width=w+'px';
                k.style.height=h+'px';
                k.style.border='0';
                k.style.overflow='hidden';
            }
            ins.item(n).appendChild(k);
            var fd=window.frames['ifr_'+a];
            fd.document.write(ht);
            fd.document.body.style.overflow='hidden';
            fd.document.body.style.margin='0';
            fd.document.body.style.padding='0';
            fd.document.body.style.border='0';
        },
        ajax:function(a,b,c){
            var h = (window.XDomainRequest)?new XDomainRequest():new XMLHttpRequest();
            h.onload = function() {
                f.load(a,c,h.responseText);
            };
            //this.dispatchEvent("send", g);
            h.open("GET", api+"?zones="+a+'&aid='+b+'&t='+Math.random(), true);
            h.withCredentials = false;
            h.send();
        }
    }
    for(var i=0;i<il;i++){
        ia.push(ins.item(i).getAttribute('data-revive-zoneid'));
        ib.push(ins.item(i).getAttribute('data-revive-id'));
        f.ajax(ia[i],ib[i],i);
    }
})(document, window);
