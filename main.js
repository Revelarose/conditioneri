document.addEventListener('DOMContentLoaded', function () {
    const phoneInput = document.getElementById('phone');
    const orderForm = document.getElementById('order-form');

    if (!phoneInput) return;

    // Маска форматирования телефона
    const phoneMask = (value) => {
        const cleanValue = value.replace(/\D/g, '');
        let formatted = '+7';

        if (cleanValue.length >= 2) formatted += ` (${cleanValue.slice(1, 4)}`;
        if (cleanValue.length >= 5) formatted += `) ${cleanValue.slice(4, 7)}`;
        if (cleanValue.length >= 8) formatted += `-${cleanValue.slice(7, 9)}`;
        if (cleanValue.length >= 10) formatted += `-${cleanValue.slice(9, 11)}`;

        return formatted.slice(0, 18);
    };

    // При фокусе — добавить +7, если поле пустое или начинается с 7
    phoneInput.addEventListener('focus', function () {
        if (this.value === '' || this.value === '+7') {
            this.value = '+7';
        }
    });

    // При потере фокуса — оставить +7, если введено только начало
    phoneInput.addEventListener('blur', function () {
        if (this.value === '' || this.value === '+7') {
            this.value = '+7';
        }
    });

    // Форматирование при вводе
    phoneInput.addEventListener('input', function () {
        this.value = phoneMask(this.value);
    });

    // Инициализация при загрузке: если в поле уже есть +7 — сохраняем
    if (phoneInput.value && phoneInput.value.startsWith('+7')) {
        phoneInput.value = phoneMask(phoneInput.value);
    }

    // Отправка формы
    if (orderForm) {
        orderForm.addEventListener('submit', async function (e) {
            e.preventDefault(); // остановить перезагрузку страницы

            const formData = new FormData(orderForm);
            const submitButton = orderForm.querySelector('#order-button');
            
            submitButton.value = 'Отправка...';
            submitButton.disabled = true;

            try {
                const response = await fetch('submit-order.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    alert('🎉 ' + result.message + `\nID заказа: ${result.order_id}`);
                    orderForm.reset(); // сброс формы
                    phoneInput.value = '+7'; // вернуть формат телефона
                } else {
                    alert('❌ Ошибка: ' + result.message);
                }
            } catch (error) {
                console.error('Ошибка отправки:', error);
                alert('⚠️ Ошибка соединения. Проверьте, что XAMPP запущен.');
            } finally {
                submitButton.value = 'Отправить';
                submitButton.disabled = false;
            }
        });
    }
});