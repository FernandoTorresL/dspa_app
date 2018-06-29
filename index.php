<?php

$query = $pdo->prepare('SELECT * FROM ctas_lotes ORDER BY id_lote DESC');
$query->execute();

$lotes = $query->fetchAll(PDO::FETCH_ASSOC)
?>

<html>
<head>
    <title>Aplicaciones DSPA</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!--<link rel="stylesheet" href="bootstrap-3.3.7/dist/css/bootstrap.min.css"/>-->
    </head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Public Index - Lista de lotes</h1>
        </div>

        <div class="row">
            <div class="col-md-8">
                <?php
                foreach ($lotes as $lote) {
                    echo '<div class="lote-anio">';
                    echo '<h2>' . $lote['lote_anio'] . '</h2>';
                    echo '<p>Jan 1,2018 by <a href="">FT</a> </p>';
                    echo '<div class="lote-anio-image">';
                    echo '<img src="images/keyboard.jpg" alt="">';
                    echo '</div>';
                    echo '<div class="lote-anio-comment">';
                    echo $lote['comentario'];
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
            <div class="col-md-4">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras dapibus quam et sem finibus facilisis at nec libero. Aenean vitae sollicitudin erat, vel dictum elit. Duis vel urna vel lectus tempor vehicula. Nullam tincidunt quam id condimentum malesuada. Morbi id euismod elit. Etiam quis tincidunt nibh. Proin in diam quis ex hendrerit commodo. Nulla eget pulvinar felis. Duis a sem eu neque convallis egestas ac vel justo. In lacus mauris, tincidunt in libero a, ornare auctor sapien. Sed maximus neque ac felis tincidunt ultricies.
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <footer>
                    This is a footer<br>
                    <a href="admin/index.php">Admin Panel</a>
                </footer>
            </div>
        </div>

    </div>
</div>
</body>
</html>