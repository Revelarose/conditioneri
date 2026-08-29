<?php
// admin.php — админка с React-таблицей заказов
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админка — Климат плюс</title>
    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div id="main-container">
        <div id ="left-panel">
    Здесь будет левая панель
        </div>
        <div id="control-page">
            <div id="root"></div>
            <script type="text/babel">
                
        const root = ReactDOM.createRoot(document.getElementById('root'));

        function OrdersTable() {
            const [orders, setOrders] = React.useState([]);
            const [loading, setLoading] = React.useState(true);

            // Загрузка заказов при монтировании
            React.useEffect(() => {
                fetch('orders.php')
                    .then(res => res.json())
                    .then(data => {
                        setOrders(data);
                        setLoading(false);
                    })
                    .catch(err => {
                        console.error('Ошибка:', err);
                        setLoading(false);
                    });
            }, []);

            if (loading) return <p>Загрузка...</p>;

            return (
                <table className="orders-table" border="1" cellPadding="10">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Телефон</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        {orders.map(order => (
                            <tr key={order.id}>
                                <td>{order.id}</td>
                                <td>{order.name}</td>
                                <td>{order.phone}</td>
                                <td>{order.status}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            );
        }

        function App() {
            return (
                <>
                    <h2>Заказы</h2>
                    <OrdersTable />
                </>
            );
        }

        root.render(<App />);
    </script>
        </div>
    </div>

    
</body>
</html>