<?php

require_once __DIR__ . '/../../config/Database.php';

class MovieSeeder
{
    private $db;

    private $exportPath;

    public function __construct()
    {
        $this->db = Database::getConnection();

        // путь к exports (ВНЕ public_html)
        $this->exportPath = __DIR__ . '/../../storage/exports/';

        // если папки нет — создаём
        if (!is_dir($this->exportPath)) {
            mkdir($this->exportPath, 0777, true);
        }
    }

    public function run(int $count = 20): void
    {
        $titles = [
            'Последний шанс',
            'Город теней',
            'Ночная погоня',
            'Забытые герои',
            'Предел скорости',
            'Тайна океана',
            'Огни мегаполиса',
            'Побег из будущего',
            'Код времени',
            'Легенда пустыни'
        ];

        $descriptions = [
            'Захватывающая история о борьбе и выборе.',
            'История о тайнах большого города.',
            'Герой оказывается в центре опасных событий.',
            'Путешествие, которое меняет всё.',
            'Драма о силе духа и вере в себя.',
            'Неожиданные повороты и сильные эмоции.'
        ];

        $stmt = $this->db->prepare("
            INSERT INTO movies (title, description, release_year, poster_url)
            VALUES (:title, :description, :year, :poster)
        ");

        $log = [];

        for ($i = 0; $i < $count; $i++) {

            $title = $titles[array_rand($titles)] . ' #' . rand(1, 999);
            $description = $descriptions[array_rand($descriptions)];
            $year = rand(2000, 2025);

            // временные постеры (можешь заменить на uploads)
            $poster = 'https://picsum.photos/400/600?random=' . rand(1, 1000);

            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':year' => $year,
                ':poster' => $poster
            ]);

            // логируем
            $log[] = [
                'title' => $title,
                'year' => $year
            ];
        }

        // сохраняем лог генерации
        $fileName = 'movies_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents(
            $this->exportPath . $fileName,
            json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        echo "✅ Добавлено {$count} фильмов<br>";
        echo "📁 Лог сохранен: {$fileName}";
    }
}