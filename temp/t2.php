<?php
session_start();

// Function to choose a random word from the list
function getRandomWord()
{
    $file_path = "hangword.txt"; // Replace with the actual path to your word list file
    $words = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    while (true) {
        $random_word = $words[array_rand($words)];
        if (strlen($random_word) >= 5) {
            return strtoupper($random_word);
        }
    }
}

// Start a new game or reset the game
if (isset($_GET['new'])) {
    $_SESSION['hangman_word'] = '';
    $_SESSION['hangman_blanks'] = '';
    $_SESSION['hangman_wrong_guesses'] = 0;
    $_SESSION['hangman_guessed_letters'] = '';
}

// Initialize the game if needed
if (!isset($_SESSION['hangman_word'])) {
    $_SESSION['hangman_word'] = getRandomWord();
    $_SESSION['hangman_blanks'] = str_repeat('_', strlen($_SESSION['hangman_word']));
}

// Process the letter guessed
if (isset($_GET['thelet'])) {
    $letter = strtoupper($_GET['thelet']);

    // Check if the letter has already been guessed
    if (strpos($_SESSION['hangman_guessed_letters'], $letter) !== false) {
        $message = "You have already guessed the letter $letter. Try another one.";
    } else {
        // Update guessed letters
        $_SESSION['hangman_guessed_letters'] .= $letter;

        // Check if the letter exists in the word
        if (strpos($_SESSION['hangman_word'], $letter) !== false) {
            for ($i = 0; $i < strlen($_SESSION['hangman_word']); $i++) {
                if ($_SESSION['hangman_word'][$i] == $letter) {
                    $_SESSION['hangman_blanks'][$i] = $letter;
                }
            }

            // Check if the player won
            if (strpos($_SESSION['hangman_blanks'], '_') === false) {
                $message = "Congratulations! You won. The word was {$_SESSION['hangman_word']}.";
                $_SESSION['hangman_word'] = '';
                $_SESSION['hangman_blanks'] = '';
            }
        } else {
            $_SESSION['hangman_wrong_guesses']++;

            // Check if the player lost
            if ($_SESSION['hangman_wrong_guesses'] >= 6) {
                $message = "Sorry, you lost. The word was {$_SESSION['hangman_word']}.";
                $_SESSION['hangman_word'] = '';
                $_SESSION['hangman_blanks'] = '';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hangman Game</title>
</head>
<body>
    <h1>Hangman Game</h1>

    <?php if (empty($_SESSION['hangman_blanks'])) : ?>
        <!-- Display winning message -->
        <p><?php echo $message; ?></p>
        <p>
            <a href="hangman.php?new=true">Start a new game</a>
        </p>
    <?php else : ?>
        <!-- Display Hangman image -->
        <img src="images/<?php echo $_SESSION['hangman_wrong_guesses']; ?>.png" width="400" height="400"><br>
        <p>
            <!-- Display word blanks -->
            <?php echo $_SESSION['hangman_blanks']; ?>
        </p>
        <p>
            <!-- Display wrong guesses count -->
            <?php echo "Wrong guesses: {$_SESSION['hangman_wrong_guesses']}"; ?>
        </p>
        <form action="hangman.php" method="get">
            <!-- Input field to guess a letter -->
            <input type="text" name="thelet" maxlength="1" pattern="[A-Za-z]{1}" required>
            <input type="submit" value="Guess">
        </form>
        <p>
            <!-- Display guessed letters -->
            Guessed letters: <?php echo $_SESSION['hangman_guessed_letters']; ?>
        </p>
    <?php endif; ?>
</body>
</html>
