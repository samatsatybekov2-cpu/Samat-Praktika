'use strict';

function $(s,c){return (c||document).querySelector(s);}
function $$(s,c){return Array.from((c||document).querySelectorAll(s));}
function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function fmt(n){return Number(n).toLocaleString('ru-KZ')+' ₸';}

var _toastTimer;
function toast(msg,type){
  var t=$('#toast');if(!t)return;
  t.textContent=msg;
  t.className='toast show'+(type?' '+type:'');
  clearTimeout(_toastTimer);
  _toastTimer=setTimeout(function(){t.className='toast';},3000);
}

function api(url,data,cb){
  var opts={method:'GET'};
  if(data){opts.method='POST';opts.body=new URLSearchParams(data);}
  fetch(url,opts).then(function(r){return r.json();}).then(cb)
    .catch(function(e){toast('Ошибка сети','err');console.error(e);});
}

var SHOP=window.SHOP||{};

var _wishIds=[];
var _cartIds=[];

function loadUserState(cb){
  if(!SHOP.logged){if(cb)cb();return;}
  api(SHOP.base+'/pages/wishlist.php?action=ids',null,function(d){
    if(d.success){
      _wishIds=(d.ids||[]).map(Number);
      _cartIds=(d.cart_ids||[]).map(Number);
    }
    if(cb)cb();
  });
}

function setCartBadge(n){
  var b=$('#cartBadge');if(!b)return;
  b.textContent=n;b.style.display=n>0?'flex':'none';
}
function setWishBadge(n){
  var b=$('#wishBadge');if(!b)return;
  b.textContent=n;b.style.display=n>0?'flex':'none';
}
function setBalance(n){
  var hb=$('#hdrBalance');if(hb)hb.textContent=Number(n).toLocaleString('ru-KZ');
  var el=$('.bal-block__amt');if(el)el.textContent=Number(n).toLocaleString('ru-KZ')+' ₸';
}

var EMO={'Электроника':'📱','Одежда':'👗','Обувь':'👟','Дом и сад':'🏡','Красота':'💄','Спорт':'⚽','Детские товары':'🧸','Продукты':'🛒'};
function catEmo(name,icon){return icon||EMO[name]||'🛍️';}

function productCard(p){
  var em=catEmo(p.cat_name,p.cat_icon);
  var disc=p.old_price>0?Math.round((1-p.price/p.old_price)*100):0;
  var img=p.image?'<img src="'+esc(p.image)+'" alt="'+esc(p.name)+'" loading="lazy">'
                 :'<span class="ei">'+em+'</span>';
  var pid=parseInt(p.id,10);
  var inWish=_wishIds.indexOf(pid)!==-1;
  var inCart=_cartIds.indexOf(pid)!==-1;
  return '<div class="pcard-item" onclick="goProd('+p.id+')">'+
    (disc>0?'<span class="disc-badge">-'+disc+'%</span>':'')+
    '<button class="wish-btn'+(inWish?' in-wish':'')+'" data-pid="'+p.id+
      '" onclick="event.stopPropagation();toggleWish(this)" title="Избранное">'+(inWish?'❤️':'🤍')+'</button>'+
    '<div class="pcard-item__img">'+img+'</div>'+
    '<div class="pcard-item__body">'+
    '<p class="pcard-item__name">'+esc(p.name)+'</p>'+
    '<div class="pcard-item__rat"><span class="stars">⭐</span> '+p.rating+
      ' ('+Number(p.reviews).toLocaleString()+')</div>'+
    '<div class="prow"><span class="pprice">'+fmt(p.price)+'</span>'+
    (p.old_price>0?'<span class="pold">'+fmt(p.old_price)+'</span>':'')+
    '</div>'+
    '<button class="cart-add'+(inCart?' added':'')+'" data-pid="'+p.id+
      '" onclick="event.stopPropagation();addCart(this)">'+(inCart?'✓ В корзине':'В корзину')+'</button>'+
    '</div></div>';
}

function wishCard(p){
  var em = catEmo(p.cat_name,p.cat_icon);
  var disc = p.old_price > 0
    ? Math.round((1 - p.price / p.old_price) * 100)
    : 0;

  var pid = parseInt(p.product_id, 10);
  var inCart = _cartIds.indexOf(pid) !== -1;

  var img = p.image
    ? '<img src="'+esc(p.image)+'" alt="'+esc(p.name)+'" loading="lazy">'
    : '<span class="ei">'+em+'</span>';

  return '<div class="pcard-item" id="wcard-'+pid+'">'+
    (disc > 0 ? '<span class="disc-badge">-'+disc+'%</span>' : '')+

    '<button class="wish-btn in-wish" data-pid="'+pid+'" '+
    'onclick="wishRemove(this,'+pid+')" title="Убрать из избранного">❤️</button>'+

    '<div class="pcard-item__img" style="cursor:pointer" onclick="goProd('+pid+')">'+
      img+
    '</div>'+

    '<div class="pcard-item__body">'+

      '<p class="pcard-item__name" style="cursor:pointer" onclick="goProd('+pid+')">'+
        esc(p.name)+
      '</p>'+

      '<div class="pcard-item__rat">'+
        '<span class="stars">⭐</span> '+
        p.rating+' ('+Number(p.reviews).toLocaleString()+')'+
      '</div>'+

      '<div class="prow">'+
        '<span class="pprice">'+fmt(p.price)+'</span>'+
        (p.old_price > 0
          ? '<span class="pold">'+fmt(p.old_price)+'</span>'
          : '')+
      '</div>'+

      '<div style="display:flex;gap:6px;margin-top:8px">'+

        '<button class="cart-add'+(inCart?' added':'')+'" '+
        'data-pid="'+pid+'" '+
        'onclick="addCart(this)" '+
        'style="flex:1;margin-top:0">'+
          (inCart ? '✓ В корзине' : 'В корзину')+
        '</button>'+

        '<button class="btn-go" onclick="goProd('+pid+')" title="Открыть товар">→</button>'+

      '</div>'+

    '</div>'+
  '</div>';
}

function goProd(id){location.href=SHOP.base+'/product.php?id='+id;}

function addCart(btn,qty){
  if(!SHOP.logged){openAuth();return;}
  var pid=parseInt(btn.dataset.pid,10);
  if(btn.classList.contains('added')){location.href=SHOP.base+'/cart.php';return;}
  qty=qty||1;
  api(SHOP.base+'/pages/cart.php',{action:'add',product_id:pid,qty:qty},function(d){
    if(d.auth){openAuth();return;}
    if(d.success){
      setCartBadge(d.count);
      btn.textContent='✓ В корзине';
      btn.classList.add('added');
      if(_cartIds.indexOf(pid)===-1)_cartIds.push(pid);
      toast('Добавлено в корзину 🛒','ok');
    }else{toast(d.message,'err');}
  });
}

function toggleWish(btn){
  if(!SHOP.logged){openAuth();return;}
  if(btn.disabled)return;
  btn.disabled=true;
  var pid=parseInt(btn.dataset.pid,10);
  api(SHOP.base+'/pages/wishlist.php',{action:'toggle',product_id:pid},function(d){
    btn.disabled=false;
    if(d.auth){openAuth();return;}
    if(d.success){
      if(d.added){
        if(_wishIds.indexOf(pid)===-1)_wishIds.push(pid);
        btn.textContent='❤️';btn.classList.add('in-wish');
        toast('Добавлено в избранное ❤️');
      }else{
        _wishIds=_wishIds.filter(function(id){return id!==pid;});
        btn.textContent='🤍';btn.classList.remove('in-wish');
        toast('Удалено из избранного');
      }
      setWishBadge(d.count);
    }
  });
}

function wishRemove(btn,pid){
  if(btn.disabled)return;
  btn.disabled=true;
  pid=parseInt(pid,10);
  api(SHOP.base+'/pages/wishlist.php',{action:'toggle',product_id:pid},function(d){
    if(!d.success){btn.disabled=false;toast('Ошибка','err');return;}
    if(!d.added){
      _wishIds=_wishIds.filter(function(id){return id!==pid;});
      setWishBadge(d.count);
      var card=document.getElementById('wcard-'+pid);
      if(card){
        card.style.transition='opacity .22s,transform .22s';
        card.style.opacity='0';
        card.style.transform='scale(.9)';
        setTimeout(function(){
          card.remove();
          var grid=$('#wishlistWrap .wish-grid');
          if(grid&&grid.children.length===0)renderWishEmpty();
          var lbl=$('#wishCountLabel');
          if(lbl)lbl.textContent=d.count>0?d.count+' товаров':'';
        },230);
      }
      toast('Удалено из избранного');
    }else{
      btn.disabled=false;
      btn.textContent='❤️';
    }
  });
}

function renderWishEmpty(){
  var wrap=$('#wishlistWrap');if(!wrap)return;
  wrap.innerHTML=
    '<div style="display:flex;flex-direction:column;align-items:center;text-align:center;padding:64px 20px;min-height:280px">'+
    '<div style="font-size:56px;margin-bottom:14px">❤️</div>'+
    '<h2 style="font-family:\'Unbounded\',sans-serif;font-size:18px;font-weight:700;margin-bottom:8px">Избранное пусто</h2>'+
    '<p style="font-size:14px;color:var(--text2);margin-bottom:22px">Добавляйте товары, нажимая на сердечко</p>'+
    '<a href="'+SHOP.base+'/" class="btn btn-primary">Перейти к товарам</a>'+
    '</div>';
}

function loadProducts(catId,sort,page){
  var grid=$('#productGrid');if(!grid)return;
  var limit=16,offset=((page||1)-1)*limit;
  grid.innerHTML=skeletons(8);
  var qs='?action=list&limit='+limit+'&offset='+offset+
    (catId?'&cat='+catId:'')+(sort?'&sort='+sort:'');
  api(SHOP.base+'/pages/products.php'+qs,null,function(d){
    if(!d.success||!d.products.length){
      grid.innerHTML='<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text2)">Товары не найдены</div>';
      return;
    }
    grid.innerHTML=d.products.map(productCard).join('');
    renderPagination(d.total,limit,page||1,catId,sort);
  });
}

function skeletons(n){
  var s='<div class="pcard-item" style="pointer-events:none"><div class="pcard-item__img skel skel-img"></div>'+
    '<div class="pcard-item__body"><div class="skel skel-line" style="width:80%"></div>'+
    '<div class="skel skel-line" style="width:55%;margin-top:8px"></div>'+
    '<div class="skel skel-line" style="width:65%;margin-top:10px"></div></div></div>';
  var r='';for(var i=0;i<n;i++)r+=s;return r;
}

function renderPagination(total,limit,current,catId,sort){
  var wrap=$('#pagination');if(!wrap)return;
  var pages=Math.ceil(total/limit);
  if(pages<=1){wrap.innerHTML='';return;}
  var html='';
  for(var i=1;i<=pages;i++){
    html+='<button class="pag-btn'+(i===current?' on':'')+'" onclick="loadProducts('+
      (catId||'null')+','+JSON.stringify(sort||'newest')+','+i+')">'+i+'</button>';
  }
  wrap.innerHTML=html;
}

var _st;
function initSearch(){
  var input=$('#searchInput'),btn=$('#searchBtn'),drop=$('#searchDrop');
  if(!input)return;
  function doSearch(q){
    if(q.length<2){if(drop)drop.classList.remove('open');return;}
    api(SHOP.base+'/pages/products.php?action=search&q='+encodeURIComponent(q),null,function(d){
      if(!drop)return;
      if(!d.success||!d.products.length){
        drop.innerHTML='<div class="sdrop-item" style="justify-content:center;color:var(--text2);padding:16px">Ничего не найдено</div>';
      }else{
        drop.innerHTML=d.products.slice(0,7).map(function(p){
          return '<div class="sdrop-item" onclick="goProd('+p.id+')">'+
            '<span class="ei">'+catEmo(p.cat_name,p.cat_icon)+'</span>'+
            '<div><div class="sn">'+esc(p.name)+'</div><div class="sp">'+fmt(p.price)+'</div></div></div>';
        }).join('');
      }
      drop.classList.add('open');
    });
  }
  input.addEventListener('input',function(){clearTimeout(_st);_st=setTimeout(function(){doSearch(input.value.trim());},300);});
  if(btn)btn.addEventListener('click',function(){doSearch(input.value.trim());});
  input.addEventListener('keydown',function(e){if(e.key==='Enter')doSearch(input.value.trim());});
  document.addEventListener('click',function(e){
    var sw=input.closest('.search-wrap');
    if(drop&&sw&&!sw.contains(e.target))drop.classList.remove('open');
  });
}

function openAuth(tab){
  var ov=$('#authModal');
  if(ov){ov.classList.add('open');document.body.style.overflow='hidden';if(tab)switchAuthTab(tab);}
}
function closeAuth(){
  var ov=$('#authModal');
  if(ov){ov.classList.remove('open');document.body.style.overflow='';}
}
function switchAuthTab(name){
  $$('.mtab').forEach(function(t){t.classList.toggle('on',t.dataset.tab===name);});
  var fl=$('#tabLogin'),fr=$('#tabRegister');
  if(fl)fl.style.display=name==='login'?'block':'none';
  if(fr)fr.style.display=name==='register'?'block':'none';
  $$('.fmsg').forEach(function(m){m.className='fmsg';m.textContent='';});
}

function initAuth(){
  var ov=$('#authModal');if(!ov)return;
  var xBtn=$('#authClose');
  if(xBtn)xBtn.addEventListener('click',closeAuth);
  ov.addEventListener('click',function(e){if(e.target===ov)closeAuth();});
  $$('.mtab').forEach(function(t){t.addEventListener('click',function(){switchAuthTab(t.dataset.tab);});});
  $$('[data-switch]').forEach(function(a){a.addEventListener('click',function(e){e.preventDefault();switchAuthTab(a.dataset.switch);});});
  $$('.eye-btn').forEach(function(b){
    b.addEventListener('click',function(){
      var inp=document.getElementById(b.dataset.target);
      if(inp)inp.type=inp.type==='password'?'text':'password';
    });
  });
  var pwInp=$('#regPassword'),pwBar=$('#pwBar');
  if(pwInp&&pwBar){
    pwInp.addEventListener('input',function(){
      var v=pwInp.value,s=0;
      if(v.length>=6)s++;if(v.length>=10)s++;
      if(/[A-Z]/.test(v))s++;if(/[0-9]/.test(v))s++;
      pwBar.style.width=Math.min(s/4*100,100)+'%';
      pwBar.style.background=['#ef5350','#ef5350','#ff9800','#66bb6a','#2e7d32'][s]||'#ef5350';
    });
  }
  var loginBtn=$('#loginBtn');
  if(loginBtn){
    loginBtn.addEventListener('click',function(){
      var email=$('#loginEmail').value.trim(),pass=$('#loginPassword').value,msg=$('#loginMsg');
      setLoading(loginBtn,true);
      api(SHOP.base+'/pages/auth.php',{action:'login',email:email,password:pass},function(d){
        setLoading(loginBtn,false);
        if(d.success){closeAuth();toast('Добро пожаловать, '+d.user.name+'! 👋','ok');setTimeout(function(){location.reload();},700);}
        else{msg.textContent=d.message;msg.className='fmsg err';}
      });
    });
  }
  var regBtn=$('#registerBtn');
  if(regBtn){
    regBtn.addEventListener('click',function(){
      var msg=$('#regMsg');
      setLoading(regBtn,true);
      api(SHOP.base+'/pages/auth.php',{
        action:'register',
        name:$('#regName').value.trim(),
        email:$('#regEmail').value.trim(),
        password:$('#regPassword').value,
        confirm:$('#regConfirm').value
      },function(d){
        setLoading(regBtn,false);
        if(d.success){closeAuth();toast('Добро пожаловать! 🎉','ok');setTimeout(function(){location.reload();},700);}
        else{msg.textContent=d.message;msg.className='fmsg err';}
      });
    });
  }
  ['loginEmail','loginPassword'].forEach(function(id){
    var el=document.getElementById(id);
    if(el)el.addEventListener('keydown',function(e){if(e.key==='Enter'&&loginBtn)loginBtn.click();});
  });
  var profBtn=$('#profileOpenAuth');
  if(profBtn)profBtn.addEventListener('click',function(){openAuth('login');});
}

function setLoading(btn,on){
  var sp=btn.querySelector('span'),spn=btn.querySelector('.spin');
  if(sp)sp.style.display=on?'none':'';
  if(spn)spn.style.display=on?'block':'none';
  btn.disabled=on;
}

function initProfileDrop(){
  var drop=$('#profDrop'),btn=$('#profBtn');if(!drop)return;
  if(btn)btn.addEventListener('click',function(e){e.stopPropagation();drop.classList.toggle('open');});
  document.addEventListener('click',function(){drop.classList.remove('open');});
  drop.addEventListener('click',function(e){e.stopPropagation();});
  var logoutBtn=$('#logoutBtn');
  if(logoutBtn)logoutBtn.addEventListener('click',function(){
    api(SHOP.base+'/pages/auth.php',{action:'logout'},function(){location.reload();});
  });
}

function initBurger(){
  var burger=$('#burger'),drawer=$('#drawer'),overlay=$('#drawerOv'),close=$('#drawerClose');
  if(!burger||!drawer)return;
  function openD(){drawer.classList.add('open');if(overlay)overlay.classList.add('open');document.body.style.overflow='hidden';burger.classList.add('open');}
  function closeD(){drawer.classList.remove('open');if(overlay)overlay.classList.remove('open');document.body.style.overflow='';burger.classList.remove('open');}
  burger.addEventListener('click',function(){drawer.classList.contains('open')?closeD():openD();});
  if(overlay)overlay.addEventListener('click',closeD);
  if(close)close.addEventListener('click',closeD);
  var dl=$('#drawerLogout');
  if(dl)dl.addEventListener('click',function(){api(SHOP.base+'/pages/auth.php',{action:'logout'},function(){location.reload();});});
}

function initScroll(){
  var last=0;
  window.addEventListener('scroll',function(){
    var y=window.scrollY,h=document.getElementById('header');
    if(h)h.style.transform=(y>last&&y>100)?'translateY(-100%)':'translateY(0)';
    last=y;
  },{passive:true});
}

function loadCartPage(){
  var wrap=$('#cartWrap');if(!wrap)return;
  api(SHOP.base+'/pages/cart.php?action=get',null,function(d){
    if(!d.success){wrap.innerHTML='<div class="box" style="text-align:center;padding:40px">Ошибка загрузки</div>';return;}
    if(!d.items||!d.items.length){
      wrap.innerHTML='<div style="display:flex;flex-direction:column;align-items:center;text-align:center;padding:64px 20px">'+
        '<div style="font-size:52px;margin-bottom:12px">🛒</div>'+
        '<h2 style="font-family:\'Unbounded\',sans-serif;margin-bottom:10px">Корзина пуста</h2>'+
        '<p style="color:var(--text2);margin-bottom:20px">Добавьте товары из каталога</p>'+
        '<a href="'+SHOP.base+'/" class="btn btn-primary">Перейти к покупкам</a></div>';
      return;
    }
    var bal=window.USER_BALANCE||0;
    var items=d.items.map(function(i){
      var img=i.image?'<img src="'+esc(i.image)+'">':'<span style="font-size:28px">'+(i.cat_icon||'📦')+'</span>';
      return '<div class="cart-item" id="ci-'+i.product_id+'">'+
        '<div class="cart-item__img">'+img+'</div>'+
        '<div class="cart-item__info">'+
        '<div class="cart-item__name">'+esc(i.name)+'</div>'+
        '<div class="cart-item__price">'+fmt(i.price)+' / шт.</div>'+
        '<div class="cart-item__total">Итого: <strong>'+fmt(i.price*i.quantity)+'</strong></div>'+
        '</div>'+
        '<div class="qty-ctrl">'+
        '<button onclick="cartQty('+i.product_id+','+(i.quantity-1)+')">−</button>'+
        '<span>'+i.quantity+'</span>'+
        '<button onclick="cartQty('+i.product_id+','+(i.quantity+1)+')">+</button>'+
        '</div>'+
        '<button class="cart-item__del" onclick="removeCart('+i.product_id+')" title="Удалить">✕</button>'+
        '</div>';
    }).join('');
    var enough=bal>=d.total;
    wrap.innerHTML='<div class="cart-layout">'+
      '<div class="box">'+items+'</div>'+
      '<div class="cart-summary">'+
      '<h3>Ваш заказ</h3>'+
      '<div class="sum-row"><span>Товаров (шт.)</span><span>'+d.count+'</span></div>'+
      '<div class="sum-row total"><span>Итого</span><span>'+fmt(d.total)+'</span></div>'+
      '<div class="bal-info">💳 Ваш баланс: <strong>'+fmt(bal)+'</strong>'+
      (!enough?'<div style="color:#C62828;margin-top:6px;font-size:12px">⚠️ Не хватает: '+fmt(d.total-bal)+'</div>':'')+
      '</div>'+
      '<textarea id="checkoutAddress" class="addr-input" placeholder="Адрес доставки (или Самовывоз)"></textarea>'+
      '<button class="btn btn-primary btn-full" onclick="checkoutCart()"'+(!enough?' style="opacity:.5"':'')+'>Оформить заказ</button>'+
      (!enough?'<a href="'+SHOP.base+'/profile.php?tab=balance" class="btn btn-outline btn-full" style="margin-top:8px">Пополнить баланс</a>':'')+
      '</div></div>';
  });
}

function cartQty(pid,qty){
  api(SHOP.base+'/pages/cart.php',{action:'update',product_id:pid,qty:qty},function(d){
    if(d.success){setCartBadge(d.count);loadCartPage();}
  });
}
function removeCart(pid){
  api(SHOP.base+'/pages/cart.php',{action:'remove',product_id:pid},function(d){
    if(d.success){
      _cartIds=_cartIds.filter(function(id){return id!==pid;});
      setCartBadge(d.count);loadCartPage();
      toast('Удалено из корзины');
    }
  });
}
function checkoutCart(){
  var addr=$('#checkoutAddress');
  api(SHOP.base+'/pages/cart.php',{action:'checkout',address:addr?addr.value.trim():'Самовывоз'},function(d){
    if(!d.success){toast(d.message,'err');return;}
    _cartIds=[];setCartBadge(0);
    toast(d.message,'ok');
    setTimeout(function(){location.href=SHOP.base+'/profile.php?tab=orders';},1200);
  });
}

function loadWishlistPage(){
  var wrap=$('#wishlistWrap');if(!wrap)return;
  api(SHOP.base+'/pages/wishlist.php?action=get',null,function(d){
    if(!d.success){
      wrap.innerHTML='<p style="text-align:center;padding:40px;color:var(--text2)">Ошибка загрузки</p>';
      return;
    }
    _wishIds=(d.ids||[]).map(Number);
    var lbl=$('#wishCountLabel');
    if(lbl)lbl.textContent=d.count>0?d.count+' товаров':'';
    if(!d.items||!d.items.length){renderWishEmpty();return;}
    wrap.innerHTML='<div class="wish-grid">'+d.items.map(wishCard).join('')+'</div>';
  });
}

document.addEventListener('DOMContentLoaded',function(){
  initAuth();
  initProfileDrop();
  initBurger();
  initScroll();
  initSearch();

  if(window.location.search.indexOf('need_auth=1')!==-1)openAuth('login');
  if(window.location.search.indexOf('auth=1')!==-1)openAuth('login');

  var hrb=$('#heroRegBtn');
  if(hrb)hrb.addEventListener('click',function(){openAuth('register');});

  loadUserState(function(){
    var pg=$('#productGrid');
    if(pg&&window._initCat!==undefined)loadProducts(window._initCat,window._initSort||'newest',1);
    var cw=$('#cartWrap');
    if(cw)loadCartPage();
    var ww=$('#wishlistWrap');
    if(ww)loadWishlistPage();
  });
});