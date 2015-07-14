define(["mvc/relationalstore","text!sulucontact/components/accounts/components/contacts/contact-relation.form.html","text!sulucontact/components/accounts/components/contacts/contact.form.html","config","widget-groups"],function(a,b,c,d,e){"use strict";var f={relationFormSelector:"#contact-relation-form",contactSelector:"#contact-field",positionSelector:"#company-contact-position",newContactFormSelector:"#contact-form",contactListSelector:"#people-list"},g=null,h=function(a){this.sandbox.emit("husky.navigation.select-item","contacts/contacts"),this.sandbox.emit("sulu.contacts.contact.load",a)},i=function(){this.sandbox.on("sulu.list-toolbar.delete",function(){this.sandbox.emit("husky.datagrid.items.get-selected",function(a){this.sandbox.emit("sulu.contacts.accounts.delete",a)}.bind(this))},this),this.sandbox.on("sulu.header.back",function(){this.sandbox.emit("sulu.contacts.accounts.list",this.options.data)},this),this.sandbox.on("sulu.contacts.accounts.contact.saved",function(a){this.sandbox.emit("husky.datagrid.record.add",a)},this),this.sandbox.on("sulu.contacts.accounts.contact.created",function(a){a.position=a.position.position,this.sandbox.emit("husky.datagrid.record.add",a)},this),this.sandbox.on("sulu.contacts.accounts.contacts.removed",function(a){this.sandbox.emit("husky.datagrid.record.remove",a)},this),this.sandbox.on("husky.datagrid.radio.selected",function(a){this.sandbox.emit("sulu.contacts.accounts.contacts.set-main",a)},this),this.sandbox.on("husky.select.company-position-select.selected.item",function(a){g=a},this),this.sandbox.dom.on("husky.datagrid.number.selections",function(a){a>0?this.sandbox.emit("husky.toolbar.contacts.item.enable","delete"):this.sandbox.emit("husky.toolbar.contacts.item.disable","delete")}.bind(this)),this.sandbox.on("sulu.contacts.accounts.set-form-of-address",function(a){this.formOfAddress=a}.bind(this)),this.sandbox.on("husky.overlay.new-contact.opened",function(){var a=this.sandbox.dom.find(f.newContactFormSelector,this.$el);this.sandbox.start(a),this.sandbox.form.create(f.newContactFormSelector)}.bind(this))},j=function(a){var b,d,e;a=this.sandbox.util.extend(!0,{},{translate:this.sandbox.translate,formOfAddress:this.formOfAddress},a),b=this.sandbox.util.template(c,a),d=this.sandbox.dom.createElement("<div />"),e=this.sandbox.dom.find(f.contactListSelector),this.sandbox.dom.append(e,d),this.sandbox.start([{name:"overlay@husky",options:{el:d,title:this.sandbox.translate("contact.accounts.add-new-contact-to-account"),openOnStart:!0,removeOnClose:!0,instanceName:"new-contact",data:b,skin:"wide",okCallback:k.bind(this)}}])},k=function(){if(this.sandbox.form.validate(f.newContactFormSelector)){var a=this.sandbox.form.getData(f.newContactFormSelector);return a.account=this.options.data,this.sandbox.emit("sulu.contacts.accounts.new.contact",a),!0}return!1},l=function(a){var c,e,g,h;h=d.get("sulucontact.components.autocomplete.default.contact"),h.el=f.contactSelector,a=this.sandbox.util.extend(!0,{},{translate:this.sandbox.translate},a),c=this.sandbox.util.template(b,a),e=this.sandbox.dom.createElement("<div />"),g=this.sandbox.dom.find("#people-list"),this.sandbox.dom.append(g,e),this.sandbox.start([{name:"overlay@husky",options:{el:e,title:this.sandbox.translate("contact.accounts.add-contact"),openOnStart:!0,removeOnClose:!0,instanceName:"contact-relation",data:c,okCallback:o.bind(this)}},{name:"auto-complete@husky",options:h}]),this.sandbox.util.load("/admin/api/contact/positions").then(function(a){this.sandbox.start([{name:"select@husky",options:{el:f.positionSelector,instanceName:"company-position-select",valueName:"position",returnValue:"id",data:a._embedded.positions,noNewValues:!0,isNative:!0}}])}.bind(this)).fail(function(a,b){this.sandbox.logger.error(a,b)}.bind(this)),this.data=a},m=function(){this.sandbox.emit("husky.datagrid.items.get-selected",function(a){a.length>0&&this.sandbox.emit("sulu.contacts.accounts.contacts.remove",a)}.bind(this))},n=function(){return[{id:"add",icon:"plus-circle","class":"highlight-white",position:1,title:this.sandbox.translate("sulu.list-toolbar.add"),items:[{id:"add-account-contact",title:this.sandbox.translate("contact.account.add-account-contact"),callback:l.bind(this)},{id:"add-new-contact-to-account",title:this.sandbox.translate("contact.accounts.add-new-contact-to-account"),callback:j.bind(this)}],callback:function(){this.sandbox.emit("sulu.list-toolbar.add")}.bind(this)},{id:"settings",icon:"gear",items:[{id:"delete",title:this.sandbox.translate("contact.accounts.contact-remove"),callback:m.bind(this),disabled:!0}]}]},o=function(){var a=this.sandbox.dom.find(f.contactSelector+" input",f.relationFormSelector),b=this.sandbox.dom.data(a,"id");b&&this.sandbox.emit("sulu.contacts.accounts.contact.save",b,g)};return{view:!0,layout:function(){return{content:{width:"fixed"},sidebar:{width:"max",cssClasses:"sidebar-padding-50"}}},templates:["/admin/contact/template/contact/list"],initialize:function(){this.formOfAddress=null,this.render(),i.call(this),this.options.data&&this.options.data.id&&e.exists("account-detail")&&this.initSidebar("/admin/widget-groups/account-detail?account=",this.options.data.id),this.sandbox.emit("sulu.contacts.accounts.contacts.initialized")},initSidebar:function(a,b){this.sandbox.emit("sulu.sidebar.set-widget",a+b)},render:function(){a.reset(),this.sandbox.emit("sulu.",this.options.account),this.sandbox.dom.html(this.$el,this.renderTemplate("/admin/contact/template/contact/list")),this.sandbox.sulu.initListToolbarAndList.call(this,"accountsContactsFields","/admin/api/contacts/fields?accountContacts=true",{el:this.$find("#list-toolbar-container"),instanceName:"contacts",inHeader:!0,template:n.call(this)},{el:this.sandbox.dom.find("#people-list",this.$el),url:"/admin/api/accounts/"+this.options.data.id+"/contacts?flat=true",searchInstanceName:"contacts",searchFields:["fullName"],resultKey:"contacts",actionCallback:h.bind(this),contentFilters:{isMainContact:"radio"},viewOptions:{table:{selectItem:{type:"checkbox"},removeRow:!1}}})}}});