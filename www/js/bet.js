document.addEventListener('DOMContentLoaded', function () {
    let matchesData = [];

    // Loading the list of matches
    fetch('/api/v1/matches/')
        .then(response => response.json())
        .then(data => {
            matchesData = data;
            const eventSelect = document.getElementById('event');
            const win1Div = document.getElementById('win1');
            const drawDiv = document.getElementById('draw');
            const win2Div = document.getElementById('win2');

            data.forEach(match => {
                const option = document.createElement('option');
                option.value = match.id;
                option.textContent = `${match.team1} - ${match.team2}`;
                eventSelect.appendChild(option);
            });

            function updateOdds() {
                const selectedMatch = data.find(match => match.id == eventSelect.value);
                if (selectedMatch) {
                    win1Div.textContent = `Победа первой команды: ${selectedMatch.winning_percentage}`;
                    drawDiv.textContent = `Ничья: ${selectedMatch.draw_percentage}`;
                    win2Div.textContent = `Победа второй команды: ${selectedMatch.loss_percentage}`;
                } else {
                    win1Div.textContent = '';
                    drawDiv.textContent = '';
                    win2Div.textContent = '';
                }
            }

            eventSelect.addEventListener('change', updateOdds);
            updateOdds();
        });

    // Bet form submission handler
    document.getElementById('betForm').addEventListener('submit', function (event) {
        event.preventDefault();

        const userId = document.getElementById('user').value;
        const matchId = document.getElementById('event').value;
        const expected_result = document.getElementById('expected_result').value;
        const amountInput = document.getElementById('amount');
        const amount = parseFloat(amountInput.value);
        const currency = document.getElementById('currency').value;

        if (amount < 1 || amount > 500) {
            alert('Сумма ставки должна быть в пределах от 1 до 500 единиц.');
            return;
        }

        const selectedMatch = matchesData.find(match => match.id == matchId);
        let coefficient;
        if (expected_result === 'win1') {
            coefficient = selectedMatch.winning_percentage;
        } else if (expected_result === 'draw') {
            coefficient = selectedMatch.draw_percentage;
        } else if (expected_result === 'win2') {
            coefficient = selectedMatch.loss_percentage;
        }

        const betData = {
            client_id: userId,
            match_id: matchId,
            expected_result: expected_result,
            amount: amount,
            currency: currency,
            coefficient: coefficient
        };

        fetch('/api/v1/bet/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(betData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    fetchUserBalance(userId, currency);
                    amountInput.value = '';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при совершении ставки.');
            });
    });
});
