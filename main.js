document.addEventListener('DOMContentLoaded', function () {
    const phoneInput = document.getElementById('phone');
    const orderForm = document.getElementById('order-form');
    const catalogGrid = document.getElementById('catalog-grid');

    const esc = (v) => String(v ?? '').replace(/[&<>\'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
    const money = (v) => new Intl.NumberFormat('ru-RU', {maximumFractionDigits:0}).format(Number(v)) + ' ₽';

    if (catalogGrid) {
        fetch('catalog.php')
            .then(r => r.json())
            .then(products => {
                if (!Array.isArray(products) || !products.length) {
                    catalogGrid.innerHTML = '<div class="catalog-loading">Сейчас каталог пополняется. Оставьте заявку — поможем подобрать модель.</div>';
                    return;
                }
                catalogGrid.innerHTML = products.map(p => `
                    <article class="catalog-card">
                        <div class="photo">${p.image ? `<img src="${esc(p.image)}" alt="${esc(p.name)}">` : ''}</div>
                        <div class="body"><h3>${esc(p.name)}</h3><p>${esc(p.description || 'Описание уточняется у специалиста.')}</p><strong>${money(p.price)}</strong></div>
                    </article>`).join('');
            })
            .catch(() => { catalogGrid.innerHTML = '<div class="catalog-loading">Не удалось загрузить каталог. Попробуйте обновить страницу.</div>'; });
    }

    if (phoneInput) {
        const phoneMask = (value) => {
            const clean = value.replace(/\D/g, '');
            let formatted = '+7';
            if (clean.length >= 2) formatted += ` (${clean.slice(1, 4)}`;
            if (clean.length >= 5) formatted += `) ${clean.slice(4, 7)}`;
            if (clean.length >= 8) formatted += `-${clean.slice(7, 9)}`;
            if (clean.length >= 10) formatted += `-${clean.slice(9, 11)}`;
            return formatted.slice(0, 18);
        };
        phoneInput.addEventListener('focus', function () { if (!this.value) this.value = '+7'; });
        phoneInput.addEventListener('input', function () { this.value = phoneMask(this.value); });
    }

    if (orderForm) {
        orderForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const button = orderForm.querySelector('#order-button');
            button.value = 'Отправка…'; button.disabled = true;
            try {
                const response = await fetch('submit-order.php', {method:'POST', body:new FormData(orderForm)});
                const result = await response.json();
                if (result.status === 'success') { alert('Заявка отправлена! Мы скоро свяжемся с вами.'); orderForm.reset(); if(phoneInput) phoneInput.value='+7'; }
                else alert('Ошибка: ' + result.message);
            } catch (error) { alert('Не удалось отправить заявку. Проверьте соединение.'); }
            finally { button.value='Отправить заявку'; button.disabled=false; }
        });
    }
});