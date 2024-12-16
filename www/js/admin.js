document.addEventListener('DOMContentLoaded', function () {
    // Loading the list of matches
    fetch('/api/v1/matches/')
        .then(response => response.json())
        .then(data => {
            matchesData = data;
            const eventSelect = document.getElementById('event');

            data.forEach(match => {
                const option = document.createElement('option');
                option.value = match.id;
                option.textContent = `${match.team1} - ${match.team2}`;
                eventSelect.appendChild(option);
            });
        });

    // Updating user balance
    document.getElementById('updateBalance').addEventListener('click', function () {
        const userId = document.getElementById('user').value;
        const amount = document.getElementById('balance').value;
        const currency = document.getElementById('currency').value;

        fetch(`/api/v1/user/${userId}/new-balance/`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({amount: parseFloat(amount), currency: currency})
        }).then(response => response.json())
            .then(data => {
                alert(data.message);
            });
    });

    //Update the bet result
    document.getElementById('updateResult').addEventListener('click', function () {
        const matchId = document.getElementById('event').value;
        const actualResult = document.getElementById('actual_result').value;
        fetch(`/api/v1/bet_result/${matchId}/`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({actual_result: actualResult})
        }).then(response => response.json())
            .then(data => {
                alert(data.message);
            });
    });
});

