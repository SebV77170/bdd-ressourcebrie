<!DOCTYPE HTML>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello Papa</title>
    <style>
        body {
            background-color: green;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            color: white;
            text-align: center;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .icon {
            font-size: 100px;
            margin-bottom: 20px;
        }
        .board {
            display: grid;
            grid-template-columns: repeat(3, 100px);
            grid-template-rows: repeat(3, 100px);
            gap: 5px;
        }
        .cell {
            width: 100px;
            height: 100px;
            background-color: white;
            color: black;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2em;
            cursor: pointer;
        }
        .reset-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">👋</div>
        <h1>Hello papa</h1>
        <div class="board" id="board">
            <div class="cell" data-index="0"></div>
            <div class="cell" data-index="1"></div>
            <div class="cell" data-index="2"></div>
            <div class="cell" data-index="3"></div>
            <div class="cell" data-index="4"></div>
            <div class="cell" data-index="5"></div>
            <div class="cell" data-index="6"></div>
            <div class="cell" data-index="7"></div>
            <div class="cell" data-index="8"></div>
        </div>
        <h2 id="message"></h2>
        <button class="reset-button" id="resetButton">Réinitialiser le jeu</button>
    </div>
    <audio autoplay loop>
        <source src="path/to/your/music.mp3" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <script>
        const board = document.getElementById('board');
        const cells = document.querySelectorAll('.cell');
        const message = document.getElementById('message');
        const resetButton = document.getElementById('resetButton');
        let currentPlayer = 'X';
        let gameState = Array(9).fill(null);

        function handleClick(event) {
            const index = event.target.dataset.index;
            if (gameState[index] || checkWinner()) return;
            gameState[index] = currentPlayer;
            event.target.textContent = currentPlayer;
            if (checkWinner()) {
                message.textContent = `Player ${currentPlayer} wins!`;
            } else if (gameState.every(cell => cell)) {
                message.textContent = 'Draw!';
            } else {
                currentPlayer = currentPlayer === 'X' ? 'O' : 'X';
            }
        }

        function checkWinner() {
            const winningCombinations = [
                [0, 1, 2],
                [3, 4, 5],
                [6, 7, 8],
                [0, 3, 6],
                [1, 4, 7],
                [2, 5, 8],
                [0, 4, 8],
                [2, 4, 6]
            ];
            return winningCombinations.some(combination => {
                const [a, b, c] = combination;
                return gameState[a] && gameState[a] === gameState[b] && gameState[a] === gameState[c];
            });
        }

        function resetGame() {
            gameState = Array(9).fill(null);
            cells.forEach(cell => cell.textContent = '');
            message.textContent = '';
            currentPlayer = 'X';
        }

        cells.forEach(cell => cell.addEventListener('click', handleClick));
        resetButton.addEventListener('click', resetGame);
    </script>
</body>
</html>

