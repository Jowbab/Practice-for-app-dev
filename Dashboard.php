<?php
session_start();

$levels = [
	1 => ['questions' => 3, 'points' => 1, 'label' => 'Level 1 (Easy)'],
	2 => ['questions' => 5, 'points' => 2, 'label' => 'Level 2 (Medium)'],
	3 => ['questions' => 7, 'points' => 3, 'label' => 'Level 3 (Hard)'],
];

if (!isset($_SESSION['scoreboard'])) {
	$_SESSION['scoreboard'] = [];
}

if (!isset($_SESSION['scoreboard_viewed'])) {
	$_SESSION['scoreboard_viewed'] = false;
}

if (!isset($_SESSION['screen'])) {
	$_SESSION['screen'] = 'menu';
}

function e($value)
{
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function random_number($min, $max)
{
	return random_int($min, $max);
}

function create_question($level)
{
	if ($level === 1) {
		$first = random_number(1, 10);
		$second = random_number(1, 10);
		$operator = random_number(0, 1) === 0 ? '+' : '-';
	} elseif ($level === 2) {
		$first = random_number(5, 20);
		$second = random_number(1, 12);
		$operator = ['+', '-', 'x'][random_number(0, 2)];
	} else {
		$first = random_number(10, 50);
		$second = random_number(2, 12);
		$operator = ['+', '-', 'x', '/'][random_number(0, 3)];
	}

	if ($operator === '-' && $first < $second) {
		[$first, $second] = [$second, $first];
	}

	if ($operator === '/') {
		$second = random_number(2, 12);
		$answer = random_number(2, 12);
		$first = $second * $answer;
	} elseif ($operator === '+') {
		$answer = $first + $second;
	} elseif ($operator === '-') {
		$answer = $first - $second;
	} else {
		$answer = $first * $second;
	}

	return [
		'text' => "$first $operator $second",
		'answer' => $answer,
	];
}

function start_game($level, $name, $levels)
{
	$_SESSION['screen'] = 'quiz';
	$_SESSION['player_name'] = $name;
	$_SESSION['level'] = $level;
	$_SESSION['current'] = 0;
	$_SESSION['score'] = 0;
	$_SESSION['feedback'] = '';
	$_SESSION['question_total'] = $levels[$level]['questions'];
	$_SESSION['questions'] = [];
	$_SESSION['questions'][] = create_question($level);
}

$action = $_POST['action'] ?? $_GET['screen'] ?? '';
$loggedOut = false;

if ($action === 'logout') {
	session_unset();
	session_destroy();
	$loggedOut = true;
} elseif ($action === 'start') {
	$name = trim($_SESSION['player_name'] ?? '');
	$level = (int) ($_POST['level'] ?? 0);

	if ($name === '') {
		$_SESSION['screen'] = 'menu';
		$_SESSION['menu_error'] = 'Please log in before starting a game.';
	} elseif (!isset($levels[$level])) {
		$_SESSION['screen'] = 'menu';
		$_SESSION['menu_error'] = 'Please choose a valid level.';
	} else {
		start_game($level, $name, $levels);
	}
} elseif ($action === 'answer' && $_SESSION['screen'] === 'quiz') {
	$level = $_SESSION['level'];
	$current = $_SESSION['current'];
	$question = $_SESSION['questions'][$current];
	$answer = trim($_POST['answer'] ?? '');

	if ($answer === '' || !is_numeric($answer)) {
		$_SESSION['feedback'] = 'Please enter a number.';
	} elseif ((int) $answer === $question['answer']) {
		$_SESSION['score'] += $levels[$level]['points'];
		$_SESSION['feedback'] = 'Correct!';
		$_SESSION['current']++;
	} else {
		$_SESSION['feedback'] = 'Wrong. Answer: ' . $question['answer'];
		$_SESSION['current']++;
	}

	if ($_SESSION['current'] >= $_SESSION['question_total']) {
		$_SESSION['scoreboard'][] = [
			'name' => $_SESSION['player_name'],
			'level' => $level,
			'score' => $_SESSION['score'],
			'total' => $levels[$level]['questions'] * $levels[$level]['points'],
			'date' => date('Y-m-d H:i:s'),
		];
		$_SESSION['scoreboard_viewed'] = false;
		$_SESSION['screen'] = 'result';
	} else {
		$_SESSION['questions'][] = create_question($level);
	}
} elseif ($action === 'retry' && isset($_SESSION['level'], $_SESSION['player_name'])) {
	start_game($_SESSION['level'], $_SESSION['player_name'], $levels);
} elseif ($action === 'menu') {
	$_SESSION['screen'] = 'menu';
	$_SESSION['menu_error'] = '';
	$_SESSION['scoreboard_viewed'] = false;
} elseif ($action === 'scoreboard') {
	if ($_SESSION['scoreboard_viewed']) {
		$_SESSION['scoreboard'] = [];
	}
	$_SESSION['scoreboard_viewed'] = true;
	$_SESSION['screen'] = 'scoreboard';
}

$screen = $loggedOut ? 'logout' : ($_SESSION['screen'] ?? 'menu');
$menuError = $_SESSION['menu_error'] ?? '';
if (!$loggedOut) {
	$_SESSION['menu_error'] = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Math Quiz Game</title>
	<link rel="stylesheet" href="../css/index.css">
	<link rel="stylesheet" href="../css/theme.css">
	<script src="../js/theme.js" defer></script>
	<?php if ($screen === 'logout'): ?>
		<meta http-equiv="refresh" content="2;url=../login/Login.php">
	<?php endif; ?>
</head>
<body>
<main class="box">
	<?php if ($screen === 'logout'): ?>
		<h1>Logging out...</h1>
		<div class="exit-scene" aria-hidden="true">
			<div class="door"><span class="door-knob"></span></div>
			<div class="walking-person">
				<span class="person-head"></span>
				<span class="person-body"></span>
				<span class="person-leg person-leg-left"></span>
				<span class="person-leg person-leg-right"></span>
			</div>
		</div>
		<p class="info logout-message"><b>See you next time partner😉😉.</p></b>
		<a class="button-link" href="../login/Login.php">Back to Login</a>
	<?php else: ?>
	<h1>Math Quiz Game</h1>
	<?php if (!empty($_SESSION['player_name'])): ?>
		<form class="logout-form" method="post">
			<button class="logout-button" type="submit" name="action" value="logout">Logout</button>
		</form>
	<?php endif; ?>

	<?php if ($screen === 'menu'): ?>
		<form method="post">
			<p class="info">Player: <?= e($_SESSION['player_name'] ?? '') ?></p>

			<?php if ($menuError !== ''): ?>
				<p class="feedback wrong"><?= e($menuError) ?></p>
			<?php endif; ?>

			<p class="info">Choose a level to start:</p>
			<?php foreach ($levels as $level => $details): ?>
				<button type="submit" name="level" value="<?= $level ?>" formaction="Index.php" formmethod="post">
					<?= e($details['label']) ?> - <?= $details['questions'] ?> questions x <?= $details['points'] ?> pt
				</button>
				<input type="hidden" name="action" value="start">
			<?php endforeach; ?>
		</form>
		<a class="text-link" href="../register/Registration.php">Register a player</a>

		<form method="post">
			<input type="hidden" name="action" value="scoreboard">
			<button type="submit">View Scoreboard</button>
		</form>

	<?php elseif ($screen === 'quiz'): ?>
		<?php
			$level = $_SESSION['level'];
			$question = $_SESSION['questions'][$_SESSION['current']];
			$details = $levels[$level];
		?>
		<p class="info"><?= e($details['label']) ?> - Question <?= $_SESSION['current'] + 1 ?> of <?= $_SESSION['question_total'] ?></p>
		<p class="info">Player: <?= e($_SESSION['player_name']) ?> | Score: <?= $_SESSION['score'] ?></p>
		<div class="question"><?= e($question['text']) ?> = ?</div>

		<?php if ($_SESSION['feedback'] !== ''): ?>
			<p class="feedback <?= str_starts_with($_SESSION['feedback'], 'Correct') ? 'correct' : 'wrong' ?>">
				<?= e($_SESSION['feedback']) ?>
			</p>
		<?php endif; ?>

		<form method="post">
			<label for="answer">Your answer</label>
			<input type="number" id="answer" name="answer" required autofocus>
			<input type="hidden" name="action" value="answer">
			<button type="submit">Submit</button>
		</form>

	<?php elseif ($screen === 'result'): ?>
		<?php $details = $levels[$_SESSION['level']]; ?>
		<h2>Result:🎉👏🎊🥳</h2>
		<p>Player: <strong><?= e($_SESSION['player_name']) ?></strong></p>
		<p><?= e($details['label']) ?></p>
		<p>Your score: <strong><?= $_SESSION['score'] ?> / <?= $details['questions'] * $details['points'] ?></strong></p>

		<form method="post">
			<button type="submit" name="action" value="retry">Try Again</button>
			<button type="submit" name="action" value="menu">Back to Menu</button>
			<button type="submit" name="action" value="scoreboard">View Scoreboard</button>
		</form>

	<?php else: ?>
		<h2>Scoreboard</h2>
		<?php if (count($_SESSION['scoreboard']) === 0): ?>
			<p class="info">No games played yet.</p>
		<?php else: ?>
			<table>
				<tr><th>Player</th><th>Level</th><th>Score</th><th>Date</th></tr>
				<?php foreach (array_reverse($_SESSION['scoreboard']) as $entry): ?>
					<tr>
						<td><?= e($entry['name']) ?></td>
						<td><?= e($levels[$entry['level']]['label']) ?></td>
						<td><?= $entry['score'] ?> / <?= $entry['total'] ?></td>
						<td><?= e($entry['date']) ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>

		<form method="post">
			<button type="submit" name="action" value="menu">Back to Menu</button>
		</form>
	<?php endif; ?>
	<?php endif; ?>

</main>
</body>
</html>
