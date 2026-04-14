<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админ-панель</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="bg-light">

<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="admin-header d-flex justify-content-between align-items-center mb-4 bg-dark text-white p-3 rounded shadow">

        <h2 class="mb-0 admin-title">🛠 Управление каталогом</h2>

        <div class="admin-actions d-flex gap-2 flex-wrap">
            <a href="index.php?route=admin_form" class="btn btn-success fw-bold">
                + Добавить фильм
            </a>
            <a href="index.php" class="btn btn-outline-light">
                На сайт
            </a>
        </div>

    </div>

    <!-- TABLE -->
    <div class="card shadow border-0">

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Постер</th>
                        <th>Название</th>
                        <th>Год</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($movies as $m): ?>
                        <tr>
                            <td><?= $m['id'] ?></td>

                            <td>
                                <img src="<?= h($m['poster_url']) ?>" class="admin-poster">
                            </td>

                            <td>
                                <strong><?= h($m['title']) ?></strong>
                            </td>

                            <td>
                                <?= $m['release_year'] ?>
                            </td>

                            <td class="text-end admin-actions-cell">

                                <a href="index.php?route=admin_form&id=<?= $m['id'] ?>"
                                   class="btn btn-sm btn-primary">
                                    Редактировать
                                </a>

                                <a href="index.php?route=admin_delete&id=<?= $m['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Удалить?')">
                                    Удалить
                                </a>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>

    </div>

</div>

</body>
</html>