define(["sulumedia/model/media"],function(a){"use strict";var b={toolbarSelector:".list-toolbar-container",datagridSelector:".datagrid-container",listViewStorageKey:"collectionEditListView",mediaLanguageStorageKey:"mediaLanguage"},c={},d={table:{itemId:"table",name:"table"},thumbnailSmall:{itemId:"small-thumbnails",name:"thumbnail",thViewOptions:{large:!1,selectable:!1}},thumbnailLarge:{itemId:"big-thumbnails",name:"thumbnail",thViewOptions:{large:!0,selectable:!1}}};return{view:!0,header:function(){return this.locale=this.sandbox.sulu.getUserSetting(b.mediaLanguageStorageKey)||this.sandbox.sulu.user.locale,{noBack:!0,toolbar:{template:"empty",languageChanger:{url:"/admin/api/localizations",resultKey:"localizations",titleAttribute:"localization",preSelected:this.locale}}}},layout:{navigation:{collapsed:!0},content:{width:"max"}},templates:["/admin/media/template/collection/files"],initialize:function(){this.options=this.sandbox.util.extend(!0,{},c,this.options);var a="/admin/api/collections?sortBy=title";this.sandbox.emit("husky.navigation.select-id","collections-edit",{dataNavigation:{url:a}}),this.listView=this.sandbox.sulu.getUserSetting(b.listViewStorageKey)||"thumbnailSmall",this.bindCustomEvents(),this.render(),this.sandbox.sulu.triggerDeleteSuccessLabel("labels.success.collection-deleted-desc")},bindCustomEvents:function(){this.sandbox.on("sulu.list-toolbar.change.table",function(){this.sandbox.emit("husky.datagrid.view.change","table"),this.sandbox.sulu.saveUserSetting(b.listViewStorageKey,"table")}.bind(this)),this.sandbox.on("sulu.list-toolbar.change.thumbnail-small",function(){this.sandbox.emit("husky.datagrid.view.change","thumbnail",d.thumbnailSmall.thViewOptions),this.sandbox.sulu.saveUserSetting(b.listViewStorageKey,"thumbnailSmall")}.bind(this)),this.sandbox.on("sulu.list-toolbar.change.thumbnail-large",function(){this.sandbox.emit("husky.datagrid.view.change","thumbnail",d.thumbnailLarge.thViewOptions),this.sandbox.sulu.saveUserSetting(b.listViewStorageKey,"thumbnailLarge")}.bind(this)),this.sandbox.on("husky.datagrid.download-clicked",this.download.bind(this)),this.sandbox.on("sulu.header.toolbar.language-changed",this.changeLanguage.bind(this))},render:function(){this.setHeaderInfos(),this.sandbox.dom.html(this.$el,this.renderTemplate("/admin/media/template/collection/files")),this.startDatagrid()},setHeaderInfos:function(){var a=[{title:"navigation.media"},{title:"media.collections.title"}];this.sandbox.emit("sulu.header.set-title","sulu.media.all"),this.sandbox.emit("sulu.header.set-breadcrumb",a)},actionCallback:function(a,b){this.sandbox.emit("sulu.router.navigate","media/collections/edit:"+b.collection+"/files/edit:"+a);var c="/admin/api/collections/"+b.collection+"?depth=1&sortBy=title";this.sandbox.emit("husky.data-navigation.collections.set-url",c)},startDatagrid:function(){this.sandbox.sulu.initListToolbarAndList.call(this,"media","/admin/api/media/fields",{el:this.$find(b.toolbarSelector),instanceName:this.options.instanceName,template:[{id:"change",icon:"th-large",itemsOption:{markable:!0},items:[{id:"small-thumbnails",title:this.sandbox.translate("sulu.list-toolbar.small-thumbnails"),callback:function(){this.sandbox.emit("sulu.list-toolbar.change.thumbnail-small")}.bind(this)},{id:"big-thumbnails",title:this.sandbox.translate("sulu.list-toolbar.big-thumbnails"),callback:function(){this.sandbox.emit("sulu.list-toolbar.change.thumbnail-large")}.bind(this)},{id:"table",title:this.sandbox.translate("sulu.list-toolbar.table"),callback:function(){this.sandbox.emit("sulu.list-toolbar.change.table")}.bind(this)}]}],inHeader:!1},{el:this.$find(b.datagridSelector),url:"/admin/api/media?orderBy=media.changed&orderSort=desc&locale="+this.locale,view:d[this.listView].name,resultKey:"media",sortable:!1,actionCallback:this.actionCallback.bind(this),viewOptions:{table:{selectItem:!0,actionIconColumn:"name"},thumbnail:d[this.listView].thViewOptions||{}}})},download:function(a){this.getMedia(a).then(function(a){this.sandbox.dom.window.location.href=a.versions[a.version].url}.bind(this))},getMedia:function(b){var c=this.sandbox.data.deferred(),d=a.find({id:b});return null!==d?(c.resolve(d.toJSON()),c):(d=new a,d.set({id:b}),d.fetch({success:function(a){c.resolve(a.toJSON())}.bind(this),error:function(){this.sandbox.logger.log("Error while fetching a single media")}.bind(this)}),c)},changeLanguage:function(a){this.sandbox.sulu.saveUserSetting(b.mediaLanguageStorageKey,a.localization),this.sandbox.emit("husky.datagrid.url.update",{locale:a.localization})}}});