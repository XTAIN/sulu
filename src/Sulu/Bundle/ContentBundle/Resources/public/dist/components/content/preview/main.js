define(["app-config"],function(a){"use strict";var b={initiated:!1,init:function(a){var c=this.sandbox.data.deferred();return b.initiated||(this.sandbox.dom.on(this.$el,"focusout",h.bind(this),".preview-update"),b.start.call(this,c,a),b.initiated=!0),c},update:function(a){var b="/admin/content/preview/"+this.data.id+"/update?webspace="+this.options.webspace+"&language="+this.options.language;this.sandbox.util.ajax({url:b,type:"POST",data:{changes:a}})},start:function(a,b){var c="/admin/content/preview/"+this.data.id+"/start?webspace="+this.options.webspace+"&language="+this.options.language;this.sandbox.util.ajax({url:c,type:"POST",data:{data:this.data,template:b},success:function(){a.resolve()}})},stop:function(a){var b="/admin/content/preview/"+this.data.id+"/stop?webspace="+this.options.webspace+"&language="+this.options.language;this.sandbox.util.ajax({url:b,type:"GET",success:function(){a.resolve()}})}},c={detection:function(){var a="MozWebSocket"in window?"MozWebSocket":"WebSocket"in window?"WebSocket":null;return null===a?(this.sandbox.logger.log("Your browser doesn't support Websockets."),!1):(window.MozWebSocket&&(window.WebSocket=window.MozWebSocket),!0)},init:function(d){var e=a.getSection("sulu-content"),f=e.wsUrl+":"+e.wsPort,g=this.sandbox.data.deferred();return this.sandbox.logger.log("Connect to url: "+f),c.socket=new WebSocket(f),c.socket.onopen=function(){this.sandbox.logger.log("Connection established!"),this.opened=!0,this.sandbox.dom.on(this.formId,"keyup change",h.bind(this),".preview-update"),c.start.call(this,g,d),g.resolve()}.bind(this),c.socket.onclose=function(){this.opened||(this.method="ajax",b.init.call(this,d).then(function(){g.resolve()}.bind(this)))}.bind(this),c.socket.onmessage=function(a){var b=JSON.parse(a.data);this.sandbox.logger.log("Message:",b),"start"===b.command&&"OK"===b.message&&this.def&&(this.def.resolve(),this.def=null)}.bind(this),c.socket.onerror=function(a){this.sandbox.logger.warn(a),this.method="ajax",b.init.call(this,d).then(function(){g.resolve()}.bind(this))}.bind(this),g},update:function(b){if("ws"===this.method&&c.socket.readyState===c.socket.OPEN){var d={command:"update",content:this.data.id,type:"form",user:a.getUser().id,webspaceKey:this.options.webspace,languageCode:this.options.language,changes:b};c.socket.send(JSON.stringify(d))}},start:function(b,d){if("ws"===this.method){this.def=b;var e={command:"start",content:this.data.id,type:"form",user:a.getUser().id,webspaceKey:this.options.webspace,languageCode:this.options.language,data:this.data,template:d};c.socket.send(JSON.stringify(e))}},stop:function(b){if("ws"===this.method){var d={command:"stop",content:this.data.id,type:"form",user:a.getUser().id,webspaceKey:this.options.webspace,languageCode:this.options.language};c.socket.send(JSON.stringify(d)),b.resolve()}}},d=function(a){var d;if(!this.initiated)return d=c.detection()?c.init.call(this,a):b.init.call(this,a),this.initiated=!0,d.promise()},e=function(){var a=this.sandbox.data.deferred();return this.initiated&&("ws"===this.method?c.stop.call(this,a):b.stop.call(this,a)),a},f=function(a,d){if(this.initiated){var e={};if(a&&d)e[a]=d;else{if(!this.sandbox.form.getObject(this.formId))return;e=this.sandbox.form.getData(this.formId)}"ws"===this.method?c.update.call(this,e):b.update.call(this,e)}},g=function(){if(this.initiated){var a={};"ws"===this.method?c.update.call(this,a):b.update.call(this,a)}},h=function(a){if(this.data.id&&this.initiated){var b=$(a.currentTarget),c=this.sandbox.dom.data(b,"element");f.call(this,this.getSequence(b),c.getValue())}},i=function(){this.sandbox.on("sulu.preview.update-property",function(a,b){f.call(this,a,b)}.bind(this)),this.sandbox.on("sulu.preview.update-only",function(){g.call(this)}.bind(this)),this.sandbox.on("sulu.preview.update",function(a,b,c){if(this.data.id){var d=this.getSequence(a);"ws"!==this.method&&c||f.call(this,d,b)}},this)};return{sandbox:null,options:null,data:null,$el:null,initiated:!1,opened:!1,method:"ws",formId:"#content-form",initialize:function(a,b,c,e){this.sandbox=a,this.options=b,this.data=c,this.$el=e,d.call(this).then(function(){i.call(this),this.sandbox.emit("sulu.preview.initiated")}.bind(this))},restart:function(a,c){e.call(this).then(function(){this.data=a,this.initiated=!1,this.opened=!1,this.method="ws",b.initiated=!1,d.call(this,c).then(function(){this.sandbox.emit("sulu.preview.initiated")}.bind(this))}.bind(this))},getSequence:function(a,b){this.sandbox&&(b=this.sandbox),a=$(a);for(var c,d=b.dom.data(a,"mapperProperty"),e=a.parents("*[data-mapper-property]"),f=a.parents("*[data-mapper-property-tpl]")[0];!a.data("element");)a=a.parent();return e.length>0&&(c=b.dom.data(e[0],"mapperProperty"),"string"!=typeof c&&(c=b.dom.data(e[0],"mapperProperty")[0].data),d=[c,$(f).index(),b.dom.data(a,"mapperProperty")]),d}}});