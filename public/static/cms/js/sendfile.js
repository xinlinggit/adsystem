(function(){
    var options={
        id:'',
        url:'',
        name:'',
        num:'one',
        beforeup:function(obj){},
        doingup:function(obj){},
        afterup:function(msg){}
    };
    var elements={
        insertelement:function(){
            this.form.appendChild(this.input);
            this.div.appendChild(this.form);
            window.document.body.appendChild(this.div);
        }
    };
    var createlements={
        creatediv:function(){
            elements.div=document.createElement('div');
            elements.div.style.display='none';
        },
        createform:function(){
            elements.form=document.createElement('form');
            elements.form.method='post';
            elements.form.name='fileinfo';
            elements.form.enctype='multipart/form-data';
            //elements.form.action=options.url;
            //elements.form.target='upifr';
        },
        createinput:function(){
            elements.input=document.createElement('input');
            elements.input.type='file';
            if(options.num=='more'){
                elements.input.multiple=true;
                elements.input.name=options.name+'[]';
            }else{
                elements.input.multiple=false;
                elements.input.name=options.name;
            }
            elements.input.onchange=function(){
                options.beforeup(elements.input);
                var oData = new FormData(document.forms.namedItem("fileinfo"));
                var oReq = new XMLHttpRequest();
                oReq.open( "POST", options.url , true );
                oReq.onload = function(oEvent) {
                    options.afterup(oReq.responseText);
                };
                options.doingup(elements.input);
                oReq.send(oData);
            }
        },
        createlement:function(){
            this.creatediv();
            this.createform();
            this.createinput();
        }
    }
    var api={
        up:function(info){
            for (var i in info){
                if(options[i]!=undefined){
                    options[i]=info[i];
                }
            }
            createlements.createlement();
            elements.insertelement();
            document.getElementById(options.id).onclick=function(){
                elements.input.click();
            }
        }
    };
    this.sendfile=api;
})();