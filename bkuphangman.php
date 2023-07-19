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

// Initialize the game or start a new game
if (!isset($_SESSION['hangman_word']) || isset($_GET['new'])) {
    $_SESSION['hangman_word'] = getRandomWord();
    $_SESSION['hangman_blanks'] = str_repeat('_', strlen($_SESSION['hangman_word']));
    $_SESSION['hangman_wrong_guesses'] = 0;
    $_SESSION['hangman_guessed_letters'] = '';
}

// Set a default image filename
$image_filename = 'backg.png';

// Process the letter guessed
if (isset($_GET['thelet'])) {
    $letter = strtoupper($_GET['thelet']);

    // Check if the letter has already been guessed
    if (strpos($_SESSION['hangman_guessed_letters'], $letter) !== false) {
        echo "You have already guessed the letter $letter. Try another one.";
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
                echo "Congratulations! You won. The word was {$_SESSION['hangman_word']}. <a href='hangman.php?new=true'>Play Again</a>";
                session_destroy();
                exit;
            }
        } else {
            $_SESSION['hangman_wrong_guesses']++;

            // Update the image filename for wrong guesses
            $image_filename = $_SESSION['hangman_wrong_guesses'] . '.png';

            // Check if the player lost
            if ($_SESSION['hangman_wrong_guesses'] >= 6) {
                echo "Sorry, you lost. The word was {$_SESSION['hangman_word']}. <a href='hangman.php?new=true'>Play Again</a>";
                session_destroy();
                exit;
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
    <p>
    <img src="images/<?php echo $image_filename; ?>" width="400" height="400"><br>
    <p>
        <?php echo $_SESSION['hangman_blanks']; ?>
    </p>
    <p>
        <?php echo "Wrong guesses: {$_SESSION['hangman_wrong_guesses']}"; ?>
    </p>
    <form action="hangman.php" method="get">
        <input type="text" name="thelet" maxlength="1" pattern="[A-Za-z]{1}" required>
        <input type="submit" value="Guess">
    </form>
    <p>
        Guessed letters: <?php echo $_SESSION['hangman_guessed_letters']; ?>
    </p>
    <p>
        <a href="hangman.php?new=true">Start a new game</a>
    </p>
</body>
</html>
