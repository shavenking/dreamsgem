webpackJsonp([1],{"+skl":function(t,e){},"/rwm":function(t,e){},"9EUY":function(t,e){},EUW7:function(t,e){},H7QK:function(t,e){},NHnr:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r=n("7+uW"),a={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{attrs:{id:"app"}},[e("router-view")],1)},staticRenderFns:[]};var s=n("VU/8")({name:"App"},a,!1,function(t){n("NoCs")},null,null).exports,i=n("Xxa5"),o=n.n(i),c=n("exGp"),u=n.n(c),p=n("/ocq"),l={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"layout"},[n("Layout",[n("Sider",{attrs:{breakpoint:"md",collapsible:"","collapsed-width":78},model:{value:t.isCollapsed,callback:function(e){t.isCollapsed=e},expression:"isCollapsed"}},[n("Menu",{class:t.menuitemClasses,attrs:{"active-name":"1-2",theme:"dark",width:"auto"}},[n("Submenu",{attrs:{name:"2"}},[n("template",{slot:"title"},[n("Icon",{attrs:{type:"person"}}),t._v("\n                      個人中心\n                  ")],1),t._v(" "),n("router-link",{attrs:{to:"notStart"}},[n("MenuItem",{attrs:{name:"2-1"}},[t._v("基本資料")])],1),t._v(" "),n("router-link",{attrs:{to:"ChildAccount"}},[n("MenuItem",{attrs:{name:"2-2"}},[t._v("子母帳號")])],1),t._v(" "),n("router-link",{attrs:{to:"notStart"}},[n("MenuItem",{attrs:{name:"2-3"}},[t._v("更換手機")])],1),t._v(" "),n("router-link",{attrs:{to:"notStart"}},[n("MenuItem",{attrs:{name:"2-4"}},[t._v("夢想卡解涷")])],1)],2),t._v(" "),n("Submenu",{attrs:{name:"3"}},[n("template",{slot:"title"},[n("Icon",{attrs:{type:"leaf"}}),t._v("\n                      夢寶龍樹種區\n                  ")],1),t._v(" "),n("router-link",{attrs:{to:"BuyDragon"}},[n("MenuItem",{attrs:{name:"3-1"}},[t._v("夢寶龍購買")])],1),t._v(" "),n("router-link",{attrs:{to:"Dragon"}},[n("MenuItem",{attrs:{name:"3-2"}},[t._v("夢寶龍激活")])],1),t._v(" "),n("router-link",{attrs:{to:"Tree"}},[n("MenuItem",{attrs:{name:"3-3"}},[t._v("夢寶樹激活")])],1),t._v(" "),n("router-link",{attrs:{to:"Activating"}},[n("MenuItem",{attrs:{name:"3-4"}},[t._v("開採狀況")])],1)],2),t._v(" "),n("Submenu",{attrs:{name:"4"}},[n("template",{slot:"title"},[n("Icon",{attrs:{type:"social-usd"}}),t._v("\n                      寶石卡片管理\n                  ")],1),t._v(" "),n("router-link",{attrs:{to:"Wallet"}},[n("MenuItem",{attrs:{name:"4-3"}},[t._v("寶石庫存")])],1),t._v(" "),n("router-link",{attrs:{to:"notStart"}},[n("MenuItem",{attrs:{name:"4-1"}},[t._v("外部轉帳")])],1),t._v(" "),n("router-link",{attrs:{to:"notStart"}},[n("MenuItem",{attrs:{name:"4-2"}},[t._v("平台轉帳")])],1),t._v(" "),n("router-link",{attrs:{to:"notStart"}},[n("MenuItem",{attrs:{name:"4-4"}},[t._v("進出明細")])],1),t._v(" "),n("router-link",{attrs:{to:"TransferUSD"}},[n("MenuItem",{attrs:{name:"4-5"}},[t._v("美金轉帳")])],1)],2),t._v(" "),n("router-link",{attrs:{to:"Group"}},[n("MenuItem",{attrs:{name:"5-1"}},[n("Icon",{attrs:{type:"person-stalker"}}),t._v("\n                家族開採狀況\n              ")],1)],1)],1),t._v(" "),n("div",{attrs:{slot:"trigger"},slot:"trigger"})],1),t._v(" "),n("Layout",[n("Header",{staticClass:"layout-header-bar"}),t._v(" "),n("Content",{style:{margin:"20px",background:"#fff",minHeight:"220px"}},[n("keep-alive",[n("router-view")],1)],1)],1)],1)],1)},staticRenderFns:[]};var d=n("VU/8")({data:function(){return{isCollapsed:!1}},computed:{menuitemClasses:function(){return["menu-item",this.isCollapsed?"collapsed-menu":""]}}},l,!1,function(t){n("9EUY")},null,null).exports,m={render:function(){this.$createElement;this._self._c;return this._m(0)},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"noStart"},[e("img",{attrs:{src:"https://fakeimg.pl/350x200/fff/000/?text=Comming soon...&font=bebas",alt:""}})])}]};var g=n("VU/8")({},m,!1,function(t){n("m1Mv")},null,null).exports,h=n("gRE1"),f=n.n(h),v={data:function(){var t=this;return{columns1:[{title:"ID",key:"id",width:50},{title:"夢寶龍的擁有者",key:"owner_name",minWidth:150},{title:"夢寶龍激活對象",key:"user_name",minWidth:150},{title:"是否激活",key:"activated",minWidth:100},{title:"操作",key:"operate",width:200,render:function(e,n){return e("div",[e("Dropdown",{props:{trigger:"click"},class:"defaultStyle",on:{"on-click":function(e){n.row.operate=t.dropdownItems.filter(function(t){return t.id===e}).shift()}}},[e("span",[n.row.operate.id+" "+n.row.operate.name+" ",e("Icon",{props:{type:"arrow-down-b"},style:{marginRight:"5px"}})]),e("DropdownMenu",{slot:"list"},t.dropdownItems.sort(function(t,e){return t.id-e.id}).map(function(t){return e("DropdownItem",{props:{name:t.id,disabled:n.row.activated}},t.id+" "+t.name)}))])])}},{title:"動作",key:"action",width:150,align:"center",render:function(e,n){return e("div",[e("Button",{props:{type:"primary",size:"small",disabled:n.row.activated},on:{click:function(){var e=n.row.id;t.activate({data:{user_id:n.row.operate.id},idDragon:e})}}},"激活")])}}]}},computed:{dragon:function(){return this.$store.getters.isExist("dragon","dragon")?this.$store.getters.dragon.map(function(t){return t.owner_name=t.owner&&t.owner.name||"未指定",t.user_name=t.user&&t.user.name||"未指定",t.operate={id:"",name:"選一個對象"},t}):[]},paging:function(){return this.$store.getters.paging("dragon","dragon")},dropdownItems:function(){var t={};return t[""+this.$store.getters.self.id]=this.$store.getters.self,this.$store.getters.allChildAccount.forEach(function(e){t[e.id]=e}),this.$store.getters.downlines.forEach(function(e){t[e.id]=e}),f()(t).filter(function(t){return!t.activated})}},methods:{changePage:function(t){var e=this;return u()(o.a.mark(function n(){return o.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return n.next=2,e.$store.dispatch("goToDragonPage",{nextIndex:t});case 2:case"end":return n.stop()}},n,e)}))()},activate:function(t){var e=this;return u()(o.a.mark(function n(){var r;return o.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return n.prev=0,r=e.$store.getters.paging("dragon","dragon").curr_page,n.next=4,e.$store.dispatch("activateDragon",t);case 4:e.$store.dispatch("goToDragonPage",{nextIndex:r}),n.next=9;break;case 7:n.prev=7,n.t0=n.catch(0);case 9:e.$store.dispatch("userDownLines",{idUser:e.$store.getters.myId}),e.$store.dispatch("allChildAccount"),e.$store.dispatch("whoAmI");case 12:case"end":return n.stop()}},n,e,[[0,7]])}))()}}},w={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("Page",{attrs:{total:t.paging.total,"page-size":t.paging.pre_page,simple:"",size:"small"},on:{"on-change":function(e){t.changePage(e)}}}),t._v(" "),n("Table",{attrs:{stripe:"",columns:t.columns1,data:t.dragon}})],1)},staticRenderFns:[]};var _=n("VU/8")(v,w,!1,function(t){n("nhNy")},null,null).exports,x={data:function(){var t=this;return{columns1:[{title:"夢寶樹的擁有者",key:"owner_name",minWidth:120},{title:"夢寶樹激活對象",key:"user_name",minWidth:120},{title:"是否激活",key:"activated",minWidth:50},{title:"操作",key:"operate",width:200,render:function(e,n){return e("div",[e("Dropdown",{props:{trigger:"click",disabled:!0},class:"defaultStyle",on:{"on-click":function(e){n.row.operate=t.dropdownItems.filter(function(t){return t.id===e}).shift()}}},[e("span",[n.row.operate.id+" "+n.row.operate.name+" ",e("Icon",{props:{type:"arrow-down-b"},style:{marginRight:"5px"}})]),e("DropdownMenu",{slot:"list"},t.dropdownItems.sort(function(t,e){return t.id-e.id}).map(function(t){return e("DropdownItem",{props:{name:t.id,disabled:n.row.activated}},t.id+" "+t.name)}))])])}},{title:"動作",key:"action",width:150,align:"center",render:function(e,n){return e("div",[e("Button",{props:{type:"primary",size:"small",disabled:n.row.activated},on:{click:function(){var e=n.row.id;t.activate({data:{user_id:n.row.operate.id},idTree:e})}}},"激活")])}}]}},computed:{tree:function(){return this.$store.getters.isExist("tree","tree")?this.$store.getters.tree.map(function(t){return t.owner_name=t.owner&&t.owner.name||"未指定",t.user_name=t.user&&t.user.name||"未指定",t.operate={id:"",name:"選一個對象"},t}):[]},paging:function(){return this.$store.getters.paging("tree","tree")},dropdownItems:function(){var t={};return t[""+this.$store.getters.self.id]=this.$store.getters.self,this.$store.getters.allChildAccount.forEach(function(e){t[e.id]=e}),f()(t).filter(function(t){return t.activated})}},methods:{changePage:function(t){var e=this;return u()(o.a.mark(function n(){return o.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return n.next=2,e.$store.dispatch("goToTreePage",{nextIndex:t});case 2:case"end":return n.stop()}},n,e)}))()},buy:function(){var t=this;return u()(o.a.mark(function e(){var n,r;return o.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return n={user_id:"1"},r=t.$store.getters.paging("tree","tree").curr_page,e.next=4,t.$store.dispatch("buyTree",{data:n});case 4:t.$store.dispatch("goToTreePage",{nextIndex:r});case 5:case"end":return e.stop()}},e,t)}))()},activate:function(t){var e=this;return u()(o.a.mark(function n(){var r;return o.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return r=e.$store.getters.paging("tree","tree").curr_page,n.next=3,e.$store.dispatch("activateTree",t);case 3:e.$store.dispatch("goToTreePage",{nextIndex:r});case 4:case"end":return n.stop()}},n,e)}))()}}},k={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("Button",{attrs:{type:"error"},on:{click:function(e){t.buy()}}},[t._v("買一顆全新夢寶樹")]),t._v(" "),n("Page",{attrs:{total:t.paging.total,"page-size":t.paging.pre_page,simple:"",size:"small"},on:{"on-change":function(e){t.changePage(e)}}}),t._v(" "),n("Table",{attrs:{stripe:"",columns:t.columns1,data:t.tree}})],1)},staticRenderFns:[]};var y=n("VU/8")(x,k,!1,function(t){n("sCj6")},null,null).exports,I={data:function(){return{columnsDragon:[{title:"夢寶龍的擁有者",key:"owner_name",minWidth:150},{title:"夢寶龍激活對象",key:"user_name",minWidth:150},{title:"是否激活",key:"activated",minWidth:100}],columnsTree:[{title:"夢寶樹的擁有者",key:"owner_name",minWidth:120},{title:"夢寶樹激活對象",key:"user_name",minWidth:120},{title:"夢寶樹剩餘開採數量",key:"remain",minWidth:170},{title:"夢寶樹原始開採數量",key:"capacity",minWidth:170},{title:"夢寶樹目前開採進度",key:"progress",minWidth:200},{title:"激活",key:"activated",minWidth:120}]}},computed:{dragon:function(){return this.$store.getters.isExist("dragon","activeDragon")?this.$store.getters.activeDragon.map(function(t){return t.owner_name=t.owner&&t.owner.name||"未指定",t.user_name=t.user&&t.user.name||"未指定",t}):[]},tree:function(){return this.$store.getters.isExist("tree","activeTree")?this.$store.getters.activeTree.map(function(t){return t.owner_name=t.owner&&t.owner.name||"未指定",t.user_name=t.user&&t.user.name||"未指定",t}):[]},pagingDragon:function(){return this.$store.getters.paging("dragon","activeDragon")},pagingTree:function(){return this.$store.getters.paging("tree","activeTree")}},methods:{changeDragonPage:function(t){var e=this;return u()(o.a.mark(function n(){return o.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return n.next=2,e.$store.dispatch("goToActiveDragonPage",{nextIndex:t});case 2:case"end":return n.stop()}},n,e)}))()},changeTreePage:function(t){var e=this;return u()(o.a.mark(function n(){return o.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return n.next=2,e.$store.dispatch("goToActiveTreePage",{nextIndex:t});case 2:case"end":return n.stop()}},n,e)}))()}}},T={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("h1",[t._v("夢寶龍")]),t._v(" "),n("Page",{attrs:{total:t.pagingDragon.total,"page-size":t.pagingDragon.pre_page,simple:"",size:"small"},on:{"on-change":function(e){t.changeDragonPage(e)}}}),t._v(" "),n("Table",{attrs:{stripe:"",columns:t.columnsDragon,data:t.dragon}}),t._v(" "),n("h1",[t._v("夢寶樹")]),t._v(" "),n("Page",{attrs:{total:t.pagingTree.total,"page-size":t.pagingTree.pre_page,simple:"",size:"small"},on:{"on-change":function(e){t.changeTreePage(e)}}}),t._v(" "),n("Table",{attrs:{stripe:"",columns:t.columnsTree,data:t.tree}})],1)},staticRenderFns:[]};var $=n("VU/8")(I,T,!1,function(t){n("vxEI")},null,null).exports,b={data:function(){return{columns1:[{title:"ID",key:"id",width:50},{title:"使用者名稱",key:"name",minWidth:150},{title:"使用者信箱",key:"email",minWidth:270},{title:this.$store.getters.gems[0],key:"gem0",minWidth:100},{title:this.$store.getters.gems[1],key:"gem1",minWidth:100},{title:this.$store.getters.gems[2],key:"gem2",minWidth:100},{title:this.$store.getters.gems[3],key:"gem3",minWidth:100},{title:"是否被凍結帳號",key:"frozen",minWidth:150},{title:"是否已激活",key:"activated",minWidth:150}]}},computed:{childAccount:function(){return this.$store.getters.isExist("user","childAccount")?this.$store.getters.childAccount:[]},paging:function(){return this.$store.getters.paging("user","childAccount")}},methods:{changePage:function(t){var e=this;return u()(o.a.mark(function n(){return o.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return n.next=2,e.$store.dispatch("goToChildAccountPage",{nextIndex:t});case 2:case"end":return n.stop()}},n,e)}))()},addChildAccount:function(){var t=this;return u()(o.a.mark(function e(){var n;return o.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return n=t.$store.getters.paging("user","childAccount").curr_page,e.next=3,t.$store.dispatch("addChildAccount");case 3:t.$store.dispatch("goToChildAccountPage",{currIndex:n});case 4:case"end":return e.stop()}},e,t)}))()}}},D={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("h1",[t._v("子帳號")]),t._v(" "),n("Button",{attrs:{type:"error"},on:{click:function(e){t.addChildAccount()}}},[t._v("增加子帳號")]),t._v(" "),n("Button",{attrs:{type:"primary"},on:{click:function(e){t.callbackMe()}}},[t._v("一鍵召回")]),t._v(" "),n("Page",{attrs:{total:t.paging.total,"page-size":t.paging.pre_page,simple:"",size:"small"},on:{"on-change":function(e){t.changePage(e)}}}),t._v(" "),n("Table",{attrs:{stripe:"",columns:t.columns1,data:t.childAccount}})],1)},staticRenderFns:[]};var A=n("VU/8")(b,D,!1,function(t){n("/rwm")},null,null).exports,S={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{},[e("Table",{attrs:{stripe:"",columns:this.columns1,data:this.wallet}})],1)},staticRenderFns:[]};var U=n("VU/8")({data:function(){return{columns1:[{title:"寶石種類",key:"gem_name",minWidth:120},{title:"寶石種類",key:"amount",minWidth:120}]}},computed:{wallet:function(){var t=this;return this.$store.getters.isExist("wallet","wallet")?this.$store.getters.wallet.map(function(e){return e.gem_name=t.$store.getters.gems[e.gem],e}):[]}}},S,!1,function(t){n("Usyk")},null,null).exports,C={data:function(){var t=this;return{columns1:[{title:"ID",key:"id",width:50},{title:"夢寶龍的擁有者",key:"owner_name",minWidth:150},{title:"夢寶龍激活對象",key:"user_name",minWidth:150},{title:"是否激活",key:"activated",minWidth:50},{title:"動作",key:"action",maxWidth:100,render:function(e,n){return e("div",[e("Button",{props:{type:"primary",size:"small",disabled:n.row.activated},on:{click:function(){var e=n.row.id;t.buy({data:{owner_id:t.$store.getters.self.id},idDragon:e})}}},"購買")])}}]}},computed:{allDragon:function(){return this.$store.getters.isExist("dragon","allDragon")?this.$store.getters.allDragon.map(function(t){return t.owner_name=t.owner&&t.owner.name||"未指定",t.user_name=t.user&&t.user.name||"未指定",t.operate={id:"",name:"選一個對象"},t}):[]},paging:function(){return this.$store.getters.paging("dragon","allDragon")}},methods:{changePage:function(t){var e=this;return u()(o.a.mark(function n(){return o.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return n.next=2,e.$store.dispatch("goToAllDragonPage",{nextIndex:t});case 2:case"end":return n.stop()}},n,e)}))()},buy:function(t){var e=this;return u()(o.a.mark(function n(){var r;return o.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return r=e.$store.getters.paging("dragon","allDragon").curr_page,n.next=3,e.$store.dispatch("buyDragon",t);case 3:e.$store.dispatch("goToAllDragonPage",{nextIndex:r});case 4:case"end":return n.stop()}},n,e)}))()}}},W={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("Page",{attrs:{total:t.paging.total,"page-size":t.paging.pre_page,simple:"",size:"small"},on:{"on-change":function(e){t.changePage(e)}}}),t._v(" "),n("Table",{attrs:{stripe:"",columns:t.columns1,data:t.allDragon}})],1)},staticRenderFns:[]};var P=n("VU/8")(C,W,!1,function(t){n("H7QK")},null,null).exports,E={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("h1",[t._v("自己")]),t._v(" "),n("Table",{attrs:{stripe:"",columns:t.columnsUser,data:t.self}}),t._v(" "),n("h1",[t._v("下線")]),t._v(" "),n("Table",{attrs:{stripe:"",columns:t.columnsUser,data:t.downlines}})],1)},staticRenderFns:[]};var L=n("VU/8")({data:function(){return{columnsUser:[{title:"ID",key:"id",width:50},{title:"e-mail",key:"email",minWidth:150},{title:"是否涷結",key:"frozen",minWidth:100},{title:"是否子帳號",key:"activated",minWidth:100},{title:"是否激活",key:"activated",minWidth:100}]}},computed:{self:function(){return[this.$store.getters.self]},downlines:function(){return this.$store.getters.downlines}}},E,!1,function(t){n("ZfN3")},null,null).exports,F={data:function(){return{id:0,amount:0,busy:!1}},computed:{currUSD:function(){return this.$store.getters.isExist("wallet","wallet")?this.$store.getters.wallet.filter(function(t){return 4===t.gem}).pop().amount:0},myId:function(){return this.$store.getters.myId}},methods:{transferTo:function(){var t=this;return u()(o.a.mark(function e(){var n;return o.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.prev=0,t.busy=!0,n={user_id:t.id,amount:t.amount},e.next=5,t.$store.dispatch("TransferUSD",{data:n});case 5:return e.next=7,t.$store.dispatch("WalletPage");case 7:t.busy=!1,e.next=13;break;case 10:e.prev=10,e.t0=e.catch(0),t.busy=!1;case 13:case"end":return e.stop()}},e,t,[[0,10]])}))()}}},M={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("p"),n("h4",[t._v("轉帳對象")]),t._v(" "),n("Input",{staticClass:"input-id",staticStyle:{width:"300px"},attrs:{placeholder:""+t.myId,clearable:""},model:{value:t.id,callback:function(e){t.id=e},expression:"id"}}),t._v(" "),n("p"),t._v(" "),n("p"),n("h4",[t._v("目前額度")]),t._v(" "),n("span",{staticClass:"currUSD"},[t._v(t._s(t.currUSD))]),t._v(" "),n("p"),t._v(" "),n("p"),n("h4",[t._v("轉出額度")]),t._v(" "),n("Input",{staticClass:"input-amount",staticStyle:{width:"300px"},attrs:{placeholder:"0",clearable:""},model:{value:t.amount,callback:function(e){t.amount=e},expression:"amount"}}),t._v(" "),n("p"),t._v(" "),n("p",[n("Alert",{attrs:{type:"error"}},[t._v("接下來的行為，將不可回溯!!")])],1),t._v(" "),n("Button",{attrs:{type:"error"},on:{click:function(e){t.transferTo()}}},[t._v("轉帳")]),t._v(" "),t.busy?n("Spin",[t._v("轉帳中...")]):t._e()],1)},staticRenderFns:[]};var R=n("VU/8")(F,M,!1,function(t){n("EUW7")},"data-v-90389f7c",null).exports,q={data:function(){var t=this;return{areaType:{true:"SignIn",false:"SignUp"},switchAreaType:!0,SignIn:{username:"",password:""},SignInRule:{username:[{required:!0,message:"填入要登入的使用者帳號",trigger:"blur"}],password:[{required:!0,message:"填入要登入的使用者帳號相對應的密碼",trigger:"blur"},{type:"string",min:6,message:"長度要有 6 個字元",trigger:"blur"}]},SignUp:{name:"",email:"",password:"",passwordCheck:"",upline_id:0},SignUpRule:{name:[{required:!0,message:"填入要註冊的使用者帳號",trigger:"blur"}],email:[{required:!0,message:"填入要註冊的 email",trigger:"blur"},{type:"email",message:"請檢查 email 格式，是否有誤",trigger:"blur"}],password:[{required:!0,validator:function(e,n,r){""===n?r(new Error("填入一組密碼，符合長度 6 個字元以上")):(""!==t.SignUp.passwordCheck&&t.$refs.SignUp.validateField("passwordCheck"),r())},trigger:"blur"}],passwordCheck:[{required:!0,validator:function(e,n,r){""===n?r(new Error("再填入一樣的密碼")):n!==t.SignUp.password?r(new Error("兩邊不一樣，請再確認")):r()},trigger:"blur"}],upline_id:[{required:!0,type:"number",validator:function(t,e,n){e<1?n(new Error("填入上線的 ID 號碼")):n()},trigger:"blur"}]}}},methods:{signIn:function(){var t,e=this;this.$refs.SignIn.validate((t=u()(o.a.mark(function t(n){return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(!n){t.next=12;break}return t.prev=1,t.next=4,e.$store.dispatch("Login",{password:e.SignIn.password,name:e.SignIn.username});case 4:e.$router.push("/Main"),t.next=10;break;case 7:t.prev=7,t.t0=t.catch(1),e.$Message.error("帳號密碼不匹配");case 10:t.next=13;break;case 12:e.$Message.error("帳號密碼格式出錯");case 13:case"end":return t.stop()}},t,e,[[1,7]])})),function(e){return t.apply(this,arguments)}))},signUp:function(){var t,e=this;this.$refs.SignUp.validate((t=u()(o.a.mark(function t(n){var r;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(!n){t.next=7;break}return r={email:e.SignUp.email,name:e.SignUp.name,password:e.SignUp.password,upline_id:e.SignUp.upline_id},t.next=4,e.$store.dispatch("CreateUser",r);case 4:e.switchAreaType=!0,t.next=8;break;case 7:e.$Message.error("註冊失敗");case 8:case"end":return t.stop()}},t,e)})),function(e){return t.apply(this,arguments)}))},reset:function(){this.$refs.SignUp.resetFields()}}},z={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"loginIn"},[n("i-switch",{model:{value:t.switchAreaType,callback:function(e){t.switchAreaType=e},expression:"switchAreaType"}}),t._v(" "),n("span",[t._v(t._s(t.areaType[t.switchAreaType]))]),t._v(" "),n("div",{directives:[{name:"show",rawName:"v-show",value:!!t.switchAreaType,expression:"!!switchAreaType"}],staticClass:"SignIn"},[n("Form",{ref:"SignIn",attrs:{model:t.SignIn,rules:t.SignInRule,"label-position":"top"}},[n("FormItem",{attrs:{label:"User Name",prop:"username"}},[n("Input",{attrs:{type:"text"},model:{value:t.SignIn.username,callback:function(e){t.$set(t.SignIn,"username",e)},expression:"SignIn.username"}})],1),t._v(" "),n("FormItem",{attrs:{label:"Password",prop:"password"}},[n("Input",{attrs:{type:"password"},model:{value:t.SignIn.password,callback:function(e){t.$set(t.SignIn,"password",e)},expression:"SignIn.password"}})],1),t._v(" "),n("FormItem",[n("Button",{attrs:{type:"primary"},on:{click:function(e){t.signIn()}}},[t._v("SignIn")])],1)],1)],1),t._v(" "),n("div",{directives:[{name:"show",rawName:"v-show",value:!t.switchAreaType,expression:"!switchAreaType"}],staticClass:"SignUp"},[n("Form",{ref:"SignUp",attrs:{model:t.SignUp,rules:t.SignUpRule,"label-position":"top"}},[n("FormItem",{attrs:{label:"User Name",prop:"name"}},[n("Input",{attrs:{type:"text"},model:{value:t.SignUp.name,callback:function(e){t.$set(t.SignUp,"name",e)},expression:"SignUp.name"}})],1),t._v(" "),n("FormItem",{attrs:{label:"email",prop:"email"}},[n("Input",{attrs:{type:"email"},model:{value:t.SignUp.email,callback:function(e){t.$set(t.SignUp,"email",e)},expression:"SignUp.email"}})],1),t._v(" "),n("FormItem",{attrs:{label:"Password",prop:"password"}},[n("Input",{attrs:{type:"password"},model:{value:t.SignUp.password,callback:function(e){t.$set(t.SignUp,"password",e)},expression:"SignUp.password"}})],1),t._v(" "),n("FormItem",{attrs:{label:"Confirm",prop:"passwordCheck"}},[n("Input",{attrs:{type:"password"},model:{value:t.SignUp.passwordCheck,callback:function(e){t.$set(t.SignUp,"passwordCheck",e)},expression:"SignUp.passwordCheck"}})],1),t._v(" "),n("FormItem",{attrs:{label:"Upline Id",prop:"upline_id"}},[n("Input",{model:{value:t.SignUp.upline_id,callback:function(e){t.$set(t.SignUp,"upline_id",e)},expression:"SignUp.upline_id"}})],1),t._v(" "),n("FormItem",[n("Button",{attrs:{type:"primary"},on:{click:function(e){t.signUp()}}},[t._v("SignUp")]),t._v(" "),n("Button",{attrs:{type:"ghost"},on:{click:function(e){t.reset()}}},[t._v("Reset")])],1)],1)],1)],1)},staticRenderFns:[]};var B=n("VU/8")(q,z,!1,function(t){n("tvYn")},null,null).exports,G=this;r.default.use(p.a);var N,V=new p.a({routes:[{path:"/",redirect:"/Login"},{path:"/Login",name:"Login",component:B},{path:"/Main",name:"Main",component:d,redirect:"/Main/notStart",children:[{path:"notStart",name:"notStart",component:g},{path:"Dragon",name:"Dragon",component:_},{path:"Tree",name:"Tree",component:y},{path:"Activating",name:"Activating",component:$},{path:"ChildAccount",name:"ChildAccount",component:A},{path:"Wallet",name:"Wallet",component:U},{path:"TransferUSD",name:"TransferUSD",component:R},{path:"BuyDragon",name:"BuyDragon",component:P},{path:"Group",name:"Group",component:L}]}]});V.beforeEach((N=u()(o.a.mark(function t(e,n,r){return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(console.log(n.name,e.name),null!==n.name||"Login"===e.name||void 0!==V.app.$store&&0!==V.app.$store.getters.token.length||V.push("/Login"),void 0===V.app.$store){t.next=25;break}t.t0=e.name,t.next="Activating"===t.t0?6:"BuyDragon"===t.t0?10:"Tree"===t.t0?13:"Dragon"===t.t0?13:"ChildAccount"===t.t0?13:"Group"===t.t0?19:"Wallet"===t.t0?22:"TransferUSD"===t.t0?22:25;break;case 6:return t.next=8,V.app.$store.dispatch("goToActiveDragonPage",{nextIndex:1});case 8:return V.app.$store.dispatch("goToActiveTreePage",{nextIndex:1}),t.abrupt("break",25);case 10:return t.next=12,V.app.$store.dispatch("goToAllDragonPage",{nextIndex:1});case 12:return t.abrupt("break",25);case 13:return t.next=15,V.app.$store.dispatch("goTo"+e.name+"Page",{nextIndex:1});case 15:return V.app.$store.dispatch("userDownLines",{idUser:V.app.$store.getters.myId}),V.app.$store.dispatch("allChildAccount"),V.app.$store.dispatch("whoAmI"),t.abrupt("break",25);case 19:return t.next=21,V.app.$store.dispatch("userDownLines",{idUser:V.app.$store.getters.myId});case 21:return t.abrupt("break",25);case 22:return t.next=24,V.app.$store.dispatch("WalletPage");case 24:return t.abrupt("break",25);case 25:r();case 26:case"end":return t.stop()}},t,G)})),function(t,e,n){return N.apply(this,arguments)}));var j=V,O=n("NYxO"),Q=n("woOf"),Y=n.n(Q),H={state:{childAccount:{},userDownLines:{},allChildAccount:{},mySelf:{}},getters:{childAccount:function(t){return t.childAccount.data},downlines:function(t){return t.userDownLines.downlines},self:function(t){return t.mySelf},allChildAccount:function(t){return t.allChildAccount},myId:function(t){return t.mySelf.id}},mutations:{allChildAccount:function(t,e){t.allChildAccount=e},setChildAccount:function(t,e){t.childAccount=e},setUserDownLines:function(t,e){t.userDownLines=e},IAm:function(t,e){t.mySelf=e}},actions:{allChildAccount:function(t){var e=this,n=t.dispatch,r=t.commit,a=t.getters;return u()(o.a.mark(function t(){var s,i;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return s=a.myId,t.next=3,n("GET","/api/users/"+s+"/child-accounts?hello=world&activated=1");case 3:i=t.sent,r("allChildAccount",i);case 5:case"end":return t.stop()}},t,e)}))()},goToChildAccountPage:function(t,e){var n=this,r=t.dispatch,a=t.commit,s=t.getters,i=e.nextIndex;return u()(o.a.mark(function t(){var e,c;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return i=i||1,e=s.myId,t.next=4,r("GET","/api/users/"+e+"/child-accounts?page="+i);case 4:return c=t.sent,t.next=7,r("accountAndWallet",{array:c.data});case 7:c.data=t.sent,a("setChildAccount",c);case 9:case"end":return t.stop()}},t,n)}))()},addChildAccount:function(t){var e=this,n=t.dispatch,r=t.commit,a=t.getters;return u()(o.a.mark(function t(){var s,i;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return s=a.myId,t.next=3,n("POST",{path:"/api/users/"+s+"/child-accounts"});case 3:return i=t.sent,t.next=6,n("accountAndWallet",{array:i.data});case 6:i.data=t.sent,r("setChildAccount",i);case 8:case"end":return t.stop()}},t,e)}))()},userDownLines:function(t,e){var n=this,r=t.dispatch,a=t.commit,s=e.idUser;return u()(o.a.mark(function t(){var e;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,r("GET","/api/users/"+s);case 2:e=t.sent,a("setUserDownLines",e);case 4:case"end":return t.stop()}},t,n)}))()},childAccountWallet:function(t,e){var n=this,r=t.dispatch,a=(t.commit,e.idUser);return u()(o.a.mark(function t(){var e;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,r("GET","/api/users/"+a+"/wallets?page=1");case 2:return e=t.sent,t.abrupt("return",e);case 4:case"end":return t.stop()}},t,n)}))()},accountAndWallet:function(t,e){var n,r=this,a=t.dispatch,s=(t.commit,[]);return e.array.forEach((n=u()(o.a.mark(function t(e){return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,a("childAccountWallet",{idUser:e.id});case 2:t.sent.data.forEach(function(t){e["gem"+t.gem]=e.activated?t.amount:""}),s.push(Y()({},e));case 5:case"end":return t.stop()}},t,r)})),function(t){return n.apply(this,arguments)})),s},whoAmI:function(t){var e=this,n=t.dispatch,r=t.commit;return u()(o.a.mark(function t(){var a;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,n("GET","/api/users/me");case 2:a=t.sent,r("IAm",a);case 4:case"end":return t.stop()}},t,e)}))()}}},K=n("Dd8w"),Z=n.n(K),J=n("mtWM"),X=n.n(J),tt={CreateUser:function(t,e){var n=this,r=(t.dispatch,t.getters);return u()(o.a.mark(function t(){var a;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return a={Accept:"application/json","Content-Type":"application/json"},t.next=3,X.a.post(r.host+"/api/users",e,{headers:a});case 3:case"end":return t.stop()}},t,n)}))()},Login:function(t,e){var n=this,r=t.dispatch,a=t.commit,s=t.getters,i=e.name,c=e.password;return u()(o.a.mark(function t(){var e,u,p;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return e={"Content-Type":"application/json"},u={grant_type:"password",username:i,password:c,client_id:"2",client_secret:"vZ08ruaFRkqnDgzWJhnUImmIBtNON19YAzdKWSRF"},t.next=4,X.a.post(s.host+"/oauth/token",u,{headers:e});case 4:return p=t.sent,a("token",p.data),t.next=8,r("whoAmI");case 8:case"end":return t.stop()}},t,n)}))()},LoginQRcode:function(t){var e=this,n=t.dispatch,r=t.commit,a=t.getters;return u()(o.a.mark(function t(){var s,i,c;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return s={"Content-Type":"application/json"},i={grant_type:"qrcode",id:""+a.qrcodeUser.id,password:""+a.qrcodeUser.password,client_id:"2",client_secret:"vZ08ruaFRkqnDgzWJhnUImmIBtNON19YAzdKWSRF"},t.next=4,X.a.post(a.host+"/oauth/token",i,{headers:s});case 4:return c=t.sent,r("token",c.data),t.next=8,n("whoAmI");case 8:case"end":return t.stop()}},t,e)}))()},CreateQRcode:function(t){var e=this,n=t.commit,r=t.getters;return u()(o.a.mark(function t(){var a,s,i;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return a={Accept:"application/json"},s={id:10,password:"h%1`2{A/A",qrcode_url:"https://uim.dreamsgem.net/applets?action=/applets/dreamsgem/auth/scan-login/10"},t.next=4,X.a.post(r.host+"/api/qrcodes",s,{headers:a});case 4:i=t.sent,n("setQRcodeData",i.data);case 6:case"end":return t.stop()}},t,e)}))()},GET:function(t,e){var n=this,r=t.getters;return u()(o.a.mark(function t(){var a;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,X.a.get(""+r.host+e,{headers:Z()({},r.headers)});case 2:if(200!==(a=t.sent).status){t.next=7;break}return t.abrupt("return",a.data);case 7:return t.abrupt("return",a);case 8:case"end":return t.stop()}},t,n)}))()},POST:function(t,e){var n=this,r=t.getters,a=e.path,s=e.data;return u()(o.a.mark(function t(){var e;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,X.a.post(""+r.host+a,s,{headers:Z()({},r.headers,{"Content-Type":"application/json"})});case 2:return e=t.sent,t.abrupt("return",e);case 4:case"end":return t.stop()}},t,n)}))()},PUT:function(t,e){var n=this,r=t.getters,a=e.path,s=e.data;return u()(o.a.mark(function t(){var e;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,X.a.put(""+r.host+a,s,{headers:Z()({},r.headers,{"Content-Type":"application/json"})});case 2:return e=t.sent,t.abrupt("return",e);case 4:case"end":return t.stop()}},t,n)}))()}},et={state:{allDragon:{},dragon:{},activeDragon:{}},getters:{allDragon:function(t){return t.allDragon.data},dragon:function(t){return t.dragon.data},activeDragon:function(t){return t.activeDragon.data}},mutations:{setAllDragonList:function(t,e){t.allDragon=e},setDragonList:function(t,e){t.dragon=e},setActiveDragonList:function(t,e){t.activeDragon=e}},actions:{buyDragon:function(t,e){var n=this,r=t.dispatch,a=e.idDragon,s=e.data;return u()(o.a.mark(function t(){return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,r("PUT",{path:"/api/dragons/"+a,data:s});case 2:case"end":return t.stop()}},t,n)}))()},activateDragon:function(t,e){var n=this,r=t.dispatch,a=e.idDragon,s=e.data;return u()(o.a.mark(function t(){return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,r("PUT",{path:"/api/dragons/"+a,data:s});case 2:case"end":return t.stop()}},t,n)}))()},goToAllDragonPage:function(t,e){var n=this,r=t.dispatch,a=t.commit,s=e.nextIndex;return u()(o.a.mark(function t(){var e;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return s=s||1,t.next=3,r("GET","/api/dragons?page="+s);case 3:e=t.sent,a("setAllDragonList",e);case 5:case"end":return t.stop()}},t,n)}))()},goToDragonPage:function(t,e){var n=this,r=t.dispatch,a=t.commit,s=t.getters,i=e.nextIndex;return u()(o.a.mark(function t(){var e,c;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return i=i||1,e=s.myId,t.next=4,r("GET","/api/dragons?owner_id="+e+"&activated=0&page="+i);case 4:c=t.sent,a("setDragonList",c);case 6:case"end":return t.stop()}},t,n)}))()},goToActiveDragonPage:function(t,e){var n=this,r=t.dispatch,a=t.commit,s=t.getters,i=e.nextIndex;return u()(o.a.mark(function t(){var e,c;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return i=i||1,e=s.myId,t.next=4,r("GET","/api/dragons?user_id="+e+"&page="+i);case 4:c=t.sent,a("setActiveDragonList",c);case 6:case"end":return t.stop()}},t,n)}))()}}},nt={state:{tree:{},activeTree:{}},getters:{tree:function(t){return t.tree.data},activeTree:function(t){return t.activeTree.data}},mutations:{setTreeList:function(t,e){t.tree=e},setActiveTreeList:function(t,e){t.activeTree=e}},actions:{buyTree:function(t,e){var n=this,r=t.dispatch,a=t.getters,s=e.data;return u()(o.a.mark(function t(){var e,i;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return e=a.myId,i="/api/users/"+e+"/trees",t.next=4,r("POST",{path:i,data:s});case 4:case"end":return t.stop()}},t,n)}))()},activateTree:function(t,e){var n=this,r=t.dispatch,a=t.getters,s=e.idTree,i=e.data;return u()(o.a.mark(function t(){var e;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return e=a.myId,t.next=3,r("PUT",{path:"/api/users/"+e+"/trees/"+s,data:i});case 3:case"end":return t.stop()}},t,n)}))()},goToTreePage:function(t,e){var n=this,r=t.dispatch,a=t.commit,s=t.getters,i=e.nextIndex;return u()(o.a.mark(function t(){var e,c;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return i=i||1,e=s.myId,t.next=4,r("GET","/api/users/"+e+"/trees?owner_id="+e+"&activated=0&page="+i);case 4:c=t.sent,a("setTreeList",c);case 6:case"end":return t.stop()}},t,n)}))()},goToActiveTreePage:function(t,e){var n=this,r=t.dispatch,a=t.commit,s=t.getters,i=e.nextIndex;return u()(o.a.mark(function t(){var e,c;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return i=i||1,e=s.myId,t.next=4,r("GET","/api/users/"+e+"/trees?user_id="+e+"&page="+i);case 4:c=t.sent,a("setActiveTreeList",c);case 6:case"end":return t.stop()}},t,n)}))()}}},rt={state:{wallet:{}},getters:{wallet:function(t){return t.wallet.data},idUsdWallet:function(t,e){return e.isExist("wallet","wallet")?t.wallet.data.filter(function(t){return 4===t.gem}).shift().id:0}},mutations:{setWallet:function(t,e){t.wallet=e}},actions:{WalletPage:function(t){var e=this,n=t.dispatch,r=t.commit,a=t.getters;return u()(o.a.mark(function t(){var s,i;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return s=a.myId,t.next=3,n("GET","/api/users/"+s+"/wallets?page=1");case 3:i=t.sent,r("setWallet",i);case 5:case"end":return t.stop()}},t,e)}))()},TransferUSD:function(t,e){var n=this,r=t.dispatch,a=t.getters,s=e.data;return u()(o.a.mark(function t(){var e;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return e=a.idUsdWallet,t.next=3,r("POST",{path:"/api/wallets/"+e+"/transfers",data:s});case 3:case"end":return t.stop()}},t,n)}))()}}};r.default.use(O.a);var at=new O.a.Store({state:{host:"http://dreamsgem-staging.ap-northeast-1.elasticbeanstalk.com",token:"",token_type:"",qrCode:{}},getters:{isExist:function(t){return function(e,n){return!!t[e][n].data}},isLogin:function(t){return 0!==t.token.length},token:function(t){return t.token},host:function(t){return t.host},paging:function(t){return function(e,n){return{total:t[e][n].total,pre_page:t[e][n].per_page,curr_page:t[e][n].current_page}}},headers:function(t){return{Accept:"application/json",Authorization:t.token_type+" "+t.token}},gems:function(){return["七彩寶石","多喜寶石","多福寶石","多財寶石","美金"]},qrcode:function(t){return t.qrCode.qrcode_url},qrcodeUser:function(t){return t.qrCode}},mutations:{token:function(t,e){t.token=e.access_token,t.token_type=e.token_type},setQRcodeData:function(t,e){t.qrCode=e}},actions:tt,modules:{user:H,dragon:et,tree:nt,wallet:rt}}),st=n("BTaQ"),it=n.n(st);n("+skl");r.default.use(it.a),r.default.config.productionTip=!1,new r.default({el:"#app",router:j,store:at,components:{App:s},template:"<App/>"})},NoCs:function(t,e){},Usyk:function(t,e){},ZfN3:function(t,e){},m1Mv:function(t,e){},nhNy:function(t,e){},sCj6:function(t,e){},tvYn:function(t,e){},vxEI:function(t,e){}},["NHnr"]);
//# sourceMappingURL=app.60e3a3a84331cd68f43b.js.map