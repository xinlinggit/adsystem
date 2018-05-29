var isIE=navigator.userAgent.match(/MSIE (\d)/i);
isIE = isIE ? isIE[1] : undefined;
    if (!document.querySelectorAll) {
        document.querySelectorAll = function (selectors) {
            var style = document.createElement('style'), elements = [], element;
            document.documentElement.firstChild.appendChild(style);
            document._qsa = [];
            style.styleSheet.cssText = selectors + '{x-qsa:expression(document._qsa && document._qsa.push(this))}';
            window.scrollBy(0, 0);
            style.parentNode.removeChild(style);
            while (document._qsa.length) {
                element = document._qsa.shift();
                element.style.removeAttribute('x-qsa');
                elements.push(element);
            }
            document._qsa = null;
            return elements;
        };
    }
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
/**/
(function() {
    if (typeof window.CustomEvent === 'undefined') {
        function CustomEvent(event, params) {
            params = params || {
                bubbles: false,
                cancelable: false,
                detail: undefined
            };
            var evt = document.createEvent('Events');
            var bubbles = true;
            for (var name in params) {
                (name === 'bubbles') ? (bubbles = !!params[name]) : (evt[name] = params[name]);
            }
            evt.initEvent(event, bubbles, true);
            return evt;
        };
        CustomEvent.prototype = window.Event.prototype;
        window.CustomEvent = CustomEvent;
    }
})();
/**/    
-[1,]||(function(){
  //为window对象添加
  addEventListener=function(n,f){
    if("on"+n in this.constructor.prototype)
      this.attachEvent("on"+n,f);
    else {
      var o=this.customEvents=this.customEvents||{};
      n in o?o[n].push(f):(o[n]=[f]);
    };
  };
  removeEventListener=function(n,f){
    if("on"+n in this.constructor.prototype)
      this.detachEvent("on"+n,f);
    else {
      var s=this.customEvents&&this.customEvents[n];
      if(s)for(var i=0;i<s.length;i++)
        if(s[i]==f)return void s.splice(i,1);
    };
  };
  dispatchEvent=function(e){
    if("on"+e.type in this.constructor.prototype)
      this.fireEvent("on"+e.type,e);
    else {
      var s=this.customEvents&&this.customEvents[e.type];
      if(s)for(var s=s.slice(0),i=0;i<s.length;i++)
        s[i].call(this,e);
    }
  };
  //为document对象添加
  HTMLDocument.prototype.addEventListener=addEventListener;
  HTMLDocument.prototype.removeEventListener=removeEventListener;
  HTMLDocument.prototype.dispatchEvent=dispatchEvent;
  HTMLDocument.prototype.createEvent=function(){
    var e=document.createEventObject();
    e.initMouseEvent=function(en){this.type=en;};
    e.initEvent=function(en){this.type=en;};
    return e;
  };
  //为全元素添加
  var tags=[
    "Unknown","UList","Title","TextArea","TableSection","TableRow",
    "Table","TableCol","TableCell","TableCaption","Style","Span",
    "Select","Script","Param","Paragraph","Option","Object","OList",
    "Meta","Marquee","Map","Link","Legend","Label","LI","Input",
    "Image","IFrame","Html","Heading","Head","HR","FrameSet",
    "Frame","Form","Font","FieldSet","Embed","Div","DList",
    "Button","Body","Base","BR","Area","Anchor"
  ],html5tags=[
    "abbr","article","aside","audio","canvas","datalist","details",
    "dialog","eventsource","figure","footer","header","hgroup","mark",
    "menu","meter","nav","output","progress","section","time","video"
  ],properties={
    addEventListener:{value:addEventListener},
    removeEventListener:{value:removeEventListener},
    dispatchEvent:{value:dispatchEvent}
  };
  for(var o,n,i=0;o=window["HTML"+tags[i]+"Element"];i++)
    tags[i]=o.prototype;
  for(i=0;i<html5tags.length;i++)
    tags.push(document.createElement(html5tags[i]).constructor.prototype);
  for(i=0;o=tags[i];i++)
    for(n in properties)Object.defineProperty(o,n,properties[n]);
})();

/*** addEventlistener ***/
var addListener = (function(){
    if(document.addEventListener){
        /* ie9以上正常使用addEventListener */
        return function(element, type, fun, useCapture){
            element.addEventListener(type, fun, useCapture ? useCapture : false);
        };
    }else{
        /* ie7、ie8使用attachEvent */
        return function(element, type, fun){
            if(!fun.prototype["_" + type]){
                /* 该事件第一次绑定 */
                fun.prototype["_" + type] = {
                    _function: function(event){
                        fun.call(element, event);
                    },
                    _element: [element]
                };
                element.attachEvent("on" + type, fun.prototype["_" + type]._function);
            }else{
                /* 该事件被绑定过 */
                var s = true;
                // 判断当前的element是否已经绑定过该事件
                for(var i in fun.prototype["_" + type]._element){
                    if(fun.prototype["_" + type]._element[i] === element){
                        s = false;
                        break;
                    }
                }
                // 当前的element没有绑定过该事件
                if(s === true){
                    element.attachEvent("on" + type, fun.prototype["_" + type]._function);
                    fun.prototype["_" + type]._element.push(element);
                }
            }
        };
    }
})();
/*** removeEventlistener ***/
var removeListener = (function(){
    if(document.addEventListener){
        /* ie9以上正常使用removeEventListener */
        return function(element, type, fun){
            element.removeEventListener(type, fun);
        };
    }else{
        /* ie7、ie8使用detachEvent */
        return function(element, type, fun){
            element.detachEvent("on" + type, fun.prototype["_" + type]._function);
            if(fun.prototype["_" + type]._element.length === 1){
                // 该事件只有一个element监听，删除function.prototype["_" + type]
                delete fun.prototype["_" + type];
            }else{
                // 该事件只有多个element监听，从function.prototype["_" + type]._element数组中删除该element
                for(var i in fun.prototype["_" + type]._element){
                    if(fun.prototype["_" + type]._element[i] === element){
                        fun.prototype["_" + type]._element.splice(i, 1);
                        break;
                    }
                }
            }
        };
    }
})();
