document.addEventListener('DOMContentLoaded', function () {
    // Loading the list of users
    fetch('/api/v1/users/')
        .then(response => response.json())
        .then(users => {
            const userSelect = document.getElementById('user');
            users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.first_name + ' ' + user.last_name;
                userSelect.appendChild(option);
            });
            const event = new Event('change');
            userSelect.dispatchEvent(event);
        });

    // User change handler
    document.getElementById('user').addEventListener('change', function () {
        const userId = this.value;
        fetchUserData(userId);
    });

    // Currency change handler
    document.getElementById('currency').addEventListener('change', function () {
        const userId = document.getElementById('user').value;
        const currency = this.value;
        fetchUserBalance(userId, currency);
    });
});

function fetchUserData(userId) {
    fetch(`/api/v1/user/${userId}/currency/`)
        .then(response => response.json())
        .then(data => {
            const currencySelect = document.getElementById('currency');
            currencySelect.innerHTML = '';
            data.forEach(balance => {
                const option = document.createElement('option');
                option.value = balance.currency;
                option.textContent = balance.currency;
                currencySelect.appendChild(option);
            });
            fetchUserBalance(userId, data[0].currency);
        });
}

function fetchUserBalance(userId, currency) {
    fetch(`/api/v1/user/${userId}/balance/`)
        .then(response => response.json())
        .then(data => {
            const balanceElement = document.getElementById('balance');
            const balance = data.find(b => b.currency === currency);
            if (balance) {
                if (balanceElement.tagName.toLowerCase() === 'span') {
                    balanceElement.textContent = `${balance.amount} ${currency}`;
                } else if (balanceElement.tagName.toLowerCase() === 'input') {
                    balanceElement.value = `${balance.amount}`;
                }
            }
        });
}
