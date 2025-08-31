<?php
session_start();

$characters = parse_ini_file(__DIR__ . '/personagens.ini', true);

$columns = ['Primeira Aparição', 'Status', 'Idade', 'Afinidade', 'Classe', 'Ocupações', 'Associações'];

$today = date('Y-m-d');
$seed = hexdec(substr(md5($today), 0, 8));
mt_srand($seed);
$index = mt_rand(0, count($characters) - 1);

$today_character = array_keys($characters)[$index];

if (!isset($_SESSION['guesses'])) {
    $_SESSION['guesses'] = [];
}

if (isset($_SESSION['found']) && $_SESSION['found'] != $today) {
    $_SESSION['guesses'] = [];
    unset($_SESSION['found']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guess = $_POST['guess'] ?? '';

    if ($guess == $today_character) {
        $_SESSION['found'] = $today;
    }

    if (!array_key_exists($guess, $characters)) {
        $error = 'Personagem desconhecido. Use um dos nomes da lista.';
        header('Location: ' . '/?error=' . urlencode($error));
    } else if (in_array($guess, $_SESSION['guesses'])) {
        $error = 'Você já chutou este personagem.';
        header('Location: ' . '/?error=' . urlencode($error));
    } else {
        array_push($_SESSION['guesses'], $guess);
        header('Location: ' . '/');
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paranordle</title>

    <link rel="icon" href="/favicon.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="/styles.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&display=swap" rel="stylesheet">
</head>

<body data-bs-theme="dark">
    <h1 class="text-center my-4">
        <img src="/icon.png" width="120" alt="">
        <span class="align-middle pt-3">Paranordle</span>
    </h1>

    <?php if (isset($_GET['error'])) { ?>
        <div class="alert alert-danger d-flex gap-3 align-items-center w-50 mx-auto px-4">
            <p class="fs-1 m-0">❌</p>
            <div><?php echo htmlspecialchars($_GET['error']); ?></div>
        </div>
    <?php } ?>

    <?php if (isset($_SESSION['found']) && $_SESSION['found'] == $today) { ?>
        <div class="alert alert-success d-flex gap-3 align-items-center w-50 mx-auto px-4">
            <p class="fs-1 m-0">🎉</p>
            <div>
                <span class="fw-bold">Parabéns! Você acertou!</span>
                <br />
                O personagem de hoje é <?php echo htmlspecialchars($today_character); ?>.
            </div>
        </div>
    <?php } else { ?>
        <form action=" /" method="POST" class="w-50 mx-auto">
            <div class="input-group">
                <span class="input-group-text" id="basic-addon1">🔎</span>
                <input type="text" name="guess" class="form-control" list="characters" autocomplete="off" placeholder="Digite o nome de um personagem para começar" required>
                <input type="submit" class="btn btn-light" value="Chutar">
            </div>

            <datalist id="characters">
                <?php foreach ($characters as $name => $data) {
                    if (in_array($name, $_SESSION['guesses'])) continue;
                    echo '<option value="' . htmlspecialchars($name) . '">';
                } ?>
            </datalist>
        </form>
    <?php } ?>

    <div class="table-response px-4 mt-4">
        <table class="table table-sm align-middle1 table-bordered">
            <thead>
                <tr>
                    <th>Personagem</th>
                    <?php foreach ($columns as $column) {
                        echo '<th>' . htmlspecialchars($column) . '</th>';
                    } ?>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach (array_Reverse($_SESSION['guesses']) as $guess) {
                    $character = $characters[$guess] ?? null;
                    if (is_null($character)) continue;

                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($guess) . '</td>';

                    foreach ($columns as $column) {
                        $value = $character[$column] ?? '?';

                        if ($value == '?') {
                            echo '<td>?</td>';
                            continue;
                        }

                        $tc_items = explode(',', $characters[$today_character][$column] ?? '');
                        $items = explode(',', $value);

                        $all_in_tc = !array_diff($items, $tc_items);
                        $some_in_tc = count(array_intersect($items, $tc_items)) > 0;

                        if ($all_in_tc) {
                            echo '<td class="text-success">' . implode(';<br>', array_map('htmlspecialchars', $items)) . '</td>';
                        } else if ($some_in_tc) {
                            echo '<td class="text-warning">' . implode(';<br>', array_map('htmlspecialchars', $items)) . '</td>';
                        } else {
                            echo '<td class="text-danger">' . implode(';<br>', array_map('htmlspecialchars', $items)) . '</td>';
                        }
                    }
                    echo '</tr>';
                }

                ?>
            </tbody>
        </table>
    </div>

    <a class="d-block text-center" href="/reset.php">Resetar</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>