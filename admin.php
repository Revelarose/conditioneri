<?php
// Панель управления Conditioneri
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditioneri — Панель управления</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        <div class="brand"><span class="brand-mark">C</span><div><b>Conditioneri</b><small>Панель управления</small></div></div>
        <nav>
            <button class="nav-item active" data-section="dashboard"><span>⌂</span> Обзор</button>
            <button class="nav-item" data-section="orders"><span>◷</span> Заказы <em id="new-count">0</em></button>
            <button class="nav-item" data-section="catalog"><span>▦</span> Каталог</button>
        </nav>
        <div class="sidebar-bottom"><a href="index.html" target="_blank">↗ Открыть сайт</a><small>Conditioneri © 2026</small></div>
    </aside>

    <main class="content">
        <header class="topbar"><div><div class="eyebrow">Управление сайтом</div><h1 id="page-title">Обзор</h1></div><button class="refresh" id="refresh">↻ Обновить</button></header>

        <section id="dashboard" class="section active-section">
            <div class="stats"><article><span>Всего заказов</span><strong id="stat-orders">—</strong></article><article><span>Новые</span><strong id="stat-new">—</strong></article><article><span>В работе</span><strong id="stat-work">—</strong></article><article><span>Товаров в каталоге</span><strong id="stat-products">—</strong></article></div>
            <div class="card"><div class="card-head"><div><h2>Последние заявки</h2><p>Быстрый контроль входящих обращений</p></div><button class="text-btn" data-go="orders">Все заказы →</button></div><div id="dashboard-orders" class="table-wrap"></div></div>
        </section>

        <section id="orders" class="section">
            <div class="section-head"><div><h2>Заказы</h2><p>Просмотр заявок и управление их статусами</p></div></div>
            <div class="toolbar"><input id="order-search" class="search" placeholder="⌕  Поиск по имени или телефону"><select id="status-filter"><option value="all">Все статусы</option><option value="new">Новые</option><option value="contacted">Связались</option><option value="confirmed">Подтверждены</option><option value="completed">Завершены</option><option value="cancelled">Отменены</option></select></div>
            <div class="card table-wrap" id="orders-table"></div>
        </section>

        <section id="catalog" class="section">
            <div class="section-head"><div><h2>Каталог</h2><p>Товары, которые отображаются на сайте</p></div><button class="primary" id="add-product">＋ Добавить товар</button></div>
            <div id="catalog-grid" class="catalog-grid"></div>
        </section>
    </main>
</div>

<div id="modal" class="modal hidden"><div class="modal-box"><div class="modal-head"><div><h2 id="modal-title">Новый товар</h2><p>Заполните информацию о позиции</p></div><button class="close" id="close-modal">×</button></div><form id="product-form" enctype="multipart/form-data"><input type="hidden" name="id" id="product-id"><label>Название<input required name="name" id="product-name" maxlength="255"></label><label>Цена, ₽<input required type="number" name="price" id="product-price" min="0" step="0.01"></label><label>Описание<textarea name="description" id="product-description" rows="5" maxlength="5000"></textarea></label><label>Фотография<input type="file" name="image" id="product-image" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG или WebP, до 5 МБ</small></label><div id="image-preview"></div><div class="modal-actions"><button type="button" class="secondary" id="cancel-modal">Отмена</button><button class="primary" type="submit">Сохранить</button></div></form></div></div>
<div id="toast" class="toast"></div>
<script>
const state={orders:[],products:[]};
const statusLabels={new:'Новый',contacted:'Связались',confirmed:'Подтверждён',completed:'Завершён',cancelled:'Отменён'};
const $=s=>document.querySelector(s);
const money=v=>new Intl.NumberFormat('ru-RU',{maximumFractionDigits:0}).format(Number(v))+' ₽';
function toast(msg,error=false){const t=$('#toast');t.textContent=msg;t.className='toast show '+(error?'error':'');setTimeout(()=>t.className='toast',2500)}
async function api(url,opt={}){const r=await fetch(url,opt);const d=await r.json();if(!r.ok||d.status==='error')throw Error(d.message||d.error||'Ошибка запроса');return d}
async function load(){try{const [orders,products]=await Promise.all([api('orders.php'),api('catalog.php')]);state.orders=orders;state.products=products;render();}catch(e){toast(e.message,true)}}
function statusBadge(s){return `<span class="status ${s}">${statusLabels[s]||s}</span>`}
function orderRows(list,limit=0){const data=limit?list.slice(0,limit):list; if(!data.length)return '<div class="empty">Заказов пока нет</div>';return `<table><thead><tr><th>ID</th><th>Клиент</th><th>Телефон</th><th>Статус</th><th></th></tr></thead><tbody>${data.map(o=>`<tr><td>#${o.id}</td><td><b>${esc(o.name)}</b></td><td>${esc(o.phone)}</td><td><select class="status-select ${o.status}" data-id="${o.id}">${Object.entries(statusLabels).map(([v,l])=>`<option value="${v}" ${o.status===v?'selected':''}>${l}</option>`).join('')}</select></td><td></td></tr>`).join('')}</tbody></table>`}
function renderOrders(){const q=$('#order-search')?.value.toLowerCase()||'';const f=$('#status-filter')?.value||'all';const list=state.orders.filter(o=>(f==='all'||o.status===f)&&(`${o.name} ${o.phone}`.toLowerCase().includes(q)));$('#orders-table').innerHTML=orderRows(list);$('#dashboard-orders').innerHTML=orderRows(state.orders,5);document.querySelectorAll('.status-select').forEach(x=>x.onchange=changeStatus)}
function renderCatalog(){const g=$('#catalog-grid');if(!state.products.length){g.innerHTML='<div class="empty card">Каталог пуст. Добавьте первый товар.</div>';return}g.innerHTML=state.products.map(p=>`<article class="product"><div class="product-image">${p.image?`<img src="${esc(p.image)}" alt="">`:'<span>Нет фото</span>'}</div><div class="product-body"><h3>${esc(p.name)}</h3><p>${esc(p.description||'Описание не задано')}</p><strong>${money(p.price)}</strong><div class="product-actions"><button class="secondary edit" data-id="${p.id}">Изменить</button><button class="danger delete" data-id="${p.id}">Удалить</button></div></div></article>`).join('');document.querySelectorAll('.edit').forEach(b=>b.onclick=()=>openProduct(+b.dataset.id));document.querySelectorAll('.delete').forEach(b=>b.onclick=()=>deleteProduct(+b.dataset.id))}
function render(){const n=state.orders.filter(o=>o.status==='new').length,w=state.orders.filter(o=>['contacted','confirmed'].includes(o.status)).length;$('#stat-orders').textContent=state.orders.length;$('#stat-new').textContent=n;$('#stat-work').textContent=w;$('#stat-products').textContent=state.products.length;$('#new-count').textContent=n;renderOrders();renderCatalog()}
async function changeStatus(e){try{await api('orders.php',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:e.target.dataset.id,status:e.target.value})});const o=state.orders.find(x=>x.id==e.target.dataset.id);if(o)o.status=e.target.value;render();toast('Статус заказа обновлён')}catch(err){toast(err.message,true);load()}}
function openProduct(id=null){const p=state.products.find(x=>x.id===id);$('#modal-title').textContent=p?'Редактировать товар':'Новый товар';$('#product-id').value=p?.id||'';$('#product-name').value=p?.name||'';$('#product-price').value=p?.price||'';$('#product-description').value=p?.description||'';$('#product-image').value='';$('#image-preview').innerHTML=p?.image?`<img src="${esc(p.image)}" alt="">`:'';$('#modal').classList.remove('hidden')}
function closeModal(){$('#modal').classList.add('hidden')}
async function saveProduct(e){e.preventDefault();try{const fd=new FormData(e.target);const id=fd.get('id');await api('catalog.php'+(id?'?id='+id:''),{method:'POST',body:fd});closeModal();toast(id?'Товар обновлён':'Товар добавлен');await load()}catch(err){toast(err.message,true)}}
async function deleteProduct(id){const p=state.products.find(x=>x.id===id);if(!confirm(`Удалить «${p?.name}»?`))return;try{await api('catalog.php?id='+id,{method:'DELETE'});toast('Товар удалён');await load()}catch(e){toast(e.message,true)}}
function esc(v){return String(v??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]))}
function showSection(id){document.querySelectorAll('.section').forEach(x=>x.classList.remove('active-section'));$('#'+id).classList.add('active-section');document.querySelectorAll('.nav-item').forEach(x=>x.classList.toggle('active',x.dataset.section===id));$('#page-title').textContent={dashboard:'Обзор',orders:'Заказы',catalog:'Каталог'}[id]}
document.querySelectorAll('.nav-item').forEach(x=>x.onclick=()=>showSection(x.dataset.section));document.querySelectorAll('[data-go]').forEach(x=>x.onclick=()=>showSection(x.dataset.go));$('#refresh').onclick=load;$('#order-search').oninput=renderOrders;$('#status-filter').onchange=renderOrders;$('#add-product').onclick=()=>openProduct();$('#close-modal').onclick=closeModal;$('#cancel-modal').onclick=closeModal;$('#product-form').onsubmit=saveProduct;$('#product-image').onchange=e=>{const f=e.target.files[0];if(f){const r=new FileReader();r.onload=()=>$('#image-preview').innerHTML=`<img src="${r.result}" alt="">`;r.readAsDataURL(f)}};load();
</script>
</body>
</html>